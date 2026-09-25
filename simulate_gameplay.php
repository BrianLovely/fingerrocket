<?php
set_time_limit(0);
require __DIR__ . '/handler.php';

$db = new mysqli('localhost', 'root', 'root', 'db_fingerrocket');
$gameId = '68443e5fd233b';
$shouldSeed = in_array('--seed', $argv, true);
$playerIds = ['681f762053cb6', '681f762053cb5'];
$fortressIds = ['68436decad0dd', '684d7c5d869b7'];
$rocketClasses = ['FingerRocket', 'Dart', 'Flechette', 'Bolt', 'ICYMI', 'ICBM', 'TCB', 'CanOfWhoopAss'];

function seedRockets(mysqli $db, string $fortressId, array $rocketClasses): void {
    $select = $db->prepare('SELECT armory FROM fortress WHERE id=?');
    $select->bind_param('s', $fortressId);
    $select->execute();
    $row = $select->get_result()->fetch_assoc();
    $armory = json_decode($row['armory'], true);
    if (!is_array($armory)) {
        $armory = [];
    }
    foreach ($rocketClasses as $className) {
        for ($index = 0; $index < 50; $index++) {
            $rocket = new $className();
            $rocket->id = uniqid();
            $armory[] = json_decode(json_encode($rocket), true);
        }
    }
    $jsonArmory = json_encode($armory);
    $update = $db->prepare('UPDATE fortress SET armory=? WHERE id=?');
    $update->bind_param('ss', $jsonArmory, $fortressId);
    $update->execute();
}

if ($shouldSeed) {
    foreach ($fortressIds as $fortressId) {
        seedRockets($db, $fortressId, $rocketClasses);
    }
}

$attacks = 0;
$stoppedBecause = 'armories depleted';
while ($attacks < 1200) {
    $stmt = $db->prepare('SELECT p1, p2, playerUp FROM gamehandler WHERE id=?');
    $stmt->bind_param('s', $gameId);
    $stmt->execute();
    $game = $stmt->get_result()->fetch_assoc();
    $turnPlayer = $game['playerUp'] === 'player' ? $game['p1'] : ($game['playerUp'] === 'opponent' ? $game['p2'] : $game['playerUp']);
    if ($turnPlayer !== $game['p1'] && $turnPlayer !== $game['p2']) {
        $stoppedBecause = 'invalid turn state';
        break;
    }
    $opponentId = $turnPlayer === $game['p1'] ? $game['p2'] : $game['p1'];
    $handler = new combatHandler();
    if (!$handler->findHandlerForBothPlayers($turnPlayer, $opponentId)) {
        $stoppedBecause = 'handler not found';
        break;
    }
    $armory = $handler->player->fortress->getArmory();
    if (count($armory) === 0) {
        $otherHandler = new combatHandler();
        if ($otherHandler->findHandlerForBothPlayers($opponentId, $turnPlayer)
            && count($otherHandler->player->fortress->getArmory()) > 0) {
            $setTurn = $db->prepare('UPDATE gamehandler SET playerUp=? WHERE id=?');
            $setTurn->bind_param('ss', $opponentId, $gameId);
            $setTurn->execute();
            continue;
        }
        $stoppedBecause = 'both armories depleted';
        break;
    }
    $rocket = $handler->player->fortress->getRocket($armory[0]->getId());
    echo "ATTACK " . ($attacks + 1) . " player=" . $turnPlayer . " rocket=" . $rocket->getName() . PHP_EOL;
    try {
        $handler->handleCombat($rocket);
    } catch (Throwable $error) {
        echo "ERROR " . get_class($error) . ': ' . $error->getMessage() . PHP_EOL;
        $stoppedBecause = 'combat exception';
        break;
    }
    $attacks++;
}

$inventory = [];
foreach ($playerIds as $playerId) {
    $stmt = $db->prepare('SELECT items FROM players WHERE id=?');
    $stmt->bind_param('s', $playerId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $items = json_decode($row['items'] ?: '[]', true);
    $inventory[$playerId] = [
        'rocket_debris' => count($items[0] ?? []),
        'cladding_debris' => count($items[1] ?? []),
        'blueprints' => count($items[2] ?? []),
        'crafted_items' => count($items[3] ?? []),
        'blueprint_names' => array_values(array_map(function($item){ return $item['module'] ?? $item['name'] ?? 'unknown'; }, $items[2] ?? []))
    ];
}

$craftResults = [];
foreach ($playerIds as $index => $playerId) {
    $opponentId = $playerIds[1 - $index];
    $handler = new combatHandler();
    $handler->findHandlerForBothPlayers($playerId, $opponentId);
    $blueprints = $handler->player->itemArray[2] ?? [];
    foreach ($blueprints as $blueprint) {
        $blueprintId = is_array($blueprint) ? ($blueprint['id'] ?? '') : $blueprint->id;
        $craftResults[] = ['player' => $playerId, 'result' => $handler->player->craftBlueprint($blueprintId)];
    }
}

echo json_encode([
    'attacks' => $attacks,
    'stopped_because' => $stoppedBecause,
    'inventory' => $inventory,
    'craft_results' => $craftResults
], JSON_PRETTY_PRINT) . PHP_EOL;