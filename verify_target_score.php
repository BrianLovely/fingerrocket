<?php
require __DIR__ . '/handler.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = new mysqli('localhost', 'root', 'root', 'db_fingerrocket');
$ids = array(
    'players' => array(uniqid(), uniqid()),
    'fortresses' => array(uniqid(), uniqid()),
    'handler' => uniqid()
);

// Reads the gamehandler row for `$gameId` using `$db`; returns the row or throws when absent.
function readSimulationGame(mysqli $db, string $gameId): array {
    $statement = $db->prepare('SELECT * FROM gamehandler WHERE id = ?');
    $statement->bind_param('s', $gameId);
    $statement->execute();
    $game = $statement->get_result()->fetch_assoc();
    $statement->close();
    if ($game === NULL) {
        throw new RuntimeException('Simulation game row was not found.');
    }
    return $game;
}

// Simulates turns for `$gameId` using `$db` until completion or `$maxAttacks`; returns attack count and final game row.
function simulateUntilWinner(mysqli $db, string $gameId, int $maxAttacks): array {
    $attacks = 0;
    while ($attacks < $maxAttacks) {
        $game = readSimulationGame($db, $gameId);
        if ($game['gameStatus'] !== 'active') {
            return array('attacks' => $attacks, 'game' => $game);
        }
        $turnPlayer = $game['playerUp'];
        $opponentId = $turnPlayer === $game['p1'] ? $game['p2'] : $game['p1'];
        $handler = new combatHandler();
        if (!$handler->findHandlerForBothPlayers($turnPlayer, $opponentId)) {
            throw new RuntimeException('Combat handler could not load the generated match.');
        }
        $armory = $handler->player->fortress->getArmory();
        if (empty($armory)) {
            throw new RuntimeException('A generated test armory ran out before the target was reached.');
        }
        $rocket = $handler->player->fortress->getRocket($armory[0]->getId());
        $handler->handleCombat($rocket);
        $attacks++;
    }
    return array('attacks' => $attacks, 'game' => readSimulationGame($db, $gameId));
}

$result = array();
try {
    $emptyItems = json_encode(array(array(), array(), array(), array()));
    $insertPlayer = $db->prepare('INSERT INTO players (id, fId, username, pass, items) VALUES (?, ?, ?, ?, ?)');
    foreach ($ids['players'] as $index => $playerId) {
        $fortressId = $ids['fortresses'][$index];
        $username = $index === 0 ? 'simA' : 'simB';
        $password = 'simulation';
        $insertPlayer->bind_param('sssss', $playerId, $fortressId, $username, $password, $emptyItems);
        $insertPlayer->execute();
    }
    $insertPlayer->close();

    $insertFortress = $db->prepare('INSERT INTO fortress (flak, damageResistance, hitResistance, points, id, pId, cladding, storedCladding, name, damage, armory) VALUES (0, 0, 0, 150, ?, ?, 8, 8, ?, \'undamaged\', ?)');
    foreach ($ids['fortresses'] as $index => $fortressId) {
        $playerId = $ids['players'][$index];
        $name = $index === 0 ? 'Simulation Fortress A' : 'Simulation Fortress B';
        $armory = array();
        for ($rocketIndex = 0; $rocketIndex < 10; $rocketIndex++) {
            $rocket = new CanOfWhoopAss();
            $rocket->id = uniqid();
            $armory[] = json_decode(json_encode($rocket), true);
        }
        $armoryJson = json_encode($armory);
        $insertFortress->bind_param('ssss', $fortressId, $playerId, $name, $armoryJson);
        $insertFortress->execute();
    }
    $insertFortress->close();

    $gameId = $ids['handler'];
    $playerOne = $ids['players'][0];
    $playerTwo = $ids['players'][1];
    $fortressOne = $ids['fortresses'][0];
    $fortressTwo = $ids['fortresses'][1];
    $playerUp = $playerOne;
    $gameLog = '[]';
    $affinities = '{}';
    $targetScore = 250;
    $winnerId = NULL;
    $gameStatus = 'active';
    $insertGame = $db->prepare('INSERT INTO gamehandler (id, basePoints, p1, p2, f1, f2, gameLog, playerUp, affinities, targetScore, winnerId, gameStatus) VALUES (?, 5, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $insertGame->bind_param('sssssssssis', $gameId, $playerOne, $playerTwo, $fortressOne, $fortressTwo, $gameLog, $playerUp, $affinities, $targetScore, $winnerId, $gameStatus);
    $insertGame->execute();
    $insertGame->close();

    $firstPhase = simulateUntilWinner($db, $gameId, 20);
    $firstGame = $firstPhase['game'];
    $firstLog = json_decode($firstGame['gameLog'], true) ?: array();
    $result['first_phase'] = array(
        'attacks' => $firstPhase['attacks'],
        'winner_id' => $firstGame['winnerId'],
        'status' => $firstGame['gameStatus'],
        'winner_log_recorded' => count(array_filter($firstLog,
            // Checks each log `$entry` for the win marker; returns whether it contains `wins!`.
            function($entry){ return strpos($entry, 'wins!') !== false; }
        )) > 0
    );
    if ($firstGame['gameStatus'] !== 'won' || empty($firstGame['winnerId'])) {
        throw new RuntimeException('First target did not produce a winner.');
    }

    $scoreStatement = $db->prepare('SELECT points FROM fortress WHERE id IN (?, ?) ORDER BY id');
    $scoreStatement->bind_param('ss', $fortressOne, $fortressTwo);
    $scoreStatement->execute();
    $scoreResult = $scoreStatement->get_result();
    $scoresBefore = array_map('intval', array_column($scoreResult->fetch_all(MYSQLI_ASSOC), 'points'));
    $scoreStatement->close();

    $targetScore = max($scoresBefore) + 200;
    $updateTarget = $db->prepare("UPDATE gamehandler SET targetScore = ?, winnerId = NULL, gameStatus = 'active' WHERE id = ? AND gameStatus = 'won'");
    $updateTarget->bind_param('is', $targetScore, $gameId);
    $updateTarget->execute();
    $updateTarget->close();

    $scoreStatement = $db->prepare('SELECT points FROM fortress WHERE id IN (?, ?) ORDER BY id');
    $scoreStatement->bind_param('ss', $fortressOne, $fortressTwo);
    $scoreStatement->execute();
    $scoreResult = $scoreStatement->get_result();
    $scoresAfter = array_map('intval', array_column($scoreResult->fetch_all(MYSQLI_ASSOC), 'points'));
    $scoreStatement->close();
    $result['target_extension_preserved_scores'] = $scoresBefore === $scoresAfter;

    $secondPhase = simulateUntilWinner($db, $gameId, 20);
    $secondGame = $secondPhase['game'];
    $secondLog = json_decode($secondGame['gameLog'], true) ?: array();
    $result['second_phase'] = array(
        'target' => (int)$secondGame['targetScore'],
        'attacks' => $secondPhase['attacks'],
        'winner_id' => $secondGame['winnerId'],
        'status' => $secondGame['gameStatus'],
        'winner_log_recorded' => count(array_filter($secondLog,
            // Checks each log `$entry` for the win marker; returns whether it contains `wins!`.
            function($entry){ return strpos($entry, 'wins!') !== false; }
        )) > 0
    );
    if ($secondGame['gameStatus'] !== 'won' || empty($secondGame['winnerId'])) {
        throw new RuntimeException('Second target did not produce a winner.');
    }

    $markEnded = $db->prepare("UPDATE gamehandler SET gameStatus = 'ended' WHERE id = ?");
    $markEnded->bind_param('s', $gameId);
    $markEnded->execute();
    $markEnded->close();
    $turnCheck = new combatHandler();
    $result['ended_game_rejects_turn'] = !$turnCheck->isPlayerTurn($playerOne, $gameId);
    if (!$result['ended_game_rejects_turn']) {
        throw new RuntimeException('Ended game still permits a turn.');
    }
} catch (Throwable $error) {
    $result['error'] = get_class($error) . ': ' . $error->getMessage();
} finally {
    $deleteGame = $db->prepare('DELETE FROM gamehandler WHERE id = ?');
    $deleteGame->bind_param('s', $ids['handler']);
    $deleteGame->execute();
    $deleteGame->close();

    $deleteFortress = $db->prepare('DELETE FROM fortress WHERE id IN (?, ?)');
    $deleteFortress->bind_param('ss', $ids['fortresses'][0], $ids['fortresses'][1]);
    $deleteFortress->execute();
    $deleteFortress->close();

    $deletePlayer = $db->prepare('DELETE FROM players WHERE id IN (?, ?)');
    $deletePlayer->bind_param('ss', $ids['players'][0], $ids['players'][1]);
    $deletePlayer->execute();
    $deletePlayer->close();
    $result['temporary_rows_cleaned'] = true;
}

echo json_encode($result, JSON_PRETTY_PRINT) . PHP_EOL;
