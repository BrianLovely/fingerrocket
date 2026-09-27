<?php
require __DIR__ . '/handler.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = new mysqli('localhost', 'root', 'root', 'db_fingerrocket');
$ids = array(
    'players' => array(uniqid('ta'), uniqid('tb')),
    'fortresses' => array(uniqid('tfa'), uniqid('tfb')),
    'game' => uniqid('tgame')
);
$result = array();

function postGameAction(array $data): array {
    $body = http_build_query($data);
    $context = stream_context_create(array('http' => array(
        'method' => 'POST',
        'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
        'content' => $body,
        'ignore_errors' => true,
        'timeout' => 10
    )));
    $response = file_get_contents('http://localhost:8000/handler.php', false, $context);
    $decoded = json_decode((string)$response, true);
    if(!is_array($decoded)){
        throw new RuntimeException('Handler returned invalid JSON: ' . substr((string)$response, 0, 200));
    }
    return $decoded;
}

function readTurnTestRow(mysqli $db, string $table, string $id): array {
    $statement = $db->prepare("SELECT * FROM `$table` WHERE id = ?");
    $statement->bind_param('s', $id);
    $statement->execute();
    $row = $statement->get_result()->fetch_assoc();
    $statement->close();
    if($row === NULL){
        throw new RuntimeException('Missing temporary ' . $table . ' row.');
    }
    return $row;
}

try {
    $playerOne = $ids['players'][0];
    $playerTwo = $ids['players'][1];
    $fortressOne = $ids['fortresses'][0];
    $fortressTwo = $ids['fortresses'][1];
    $gameId = $ids['game'];

    $itemsOne = json_encode(array(
        array(),
        array(),
        array(array('id' => 'bp-test', 'name' => 'Finger Rocket', 'module' => 'Finger Rocket', 'typeId' => 32, 'type' => 2)),
        array(
            array('id' => 'cone-test', 'typeId' => 1000, 'name' => 'Nose Cone'),
            array('id' => 'payload-test', 'typeId' => 1001, 'name' => 'Payload Module'),
            array('id' => 'propulsion-test', 'typeId' => 1002, 'name' => 'Propulsion Module'),
            array('id' => 'explosive-test', 'typeId' => 18, 'name' => 'Explosive')
        )
    ));
    $itemsTwo = json_encode(array(array(), array(), array(), array()));
    $statement = $db->prepare('INSERT INTO players (id, fId, username, pass, items) VALUES (?, ?, ?, ?, ?)');
    $username = 'testa';
    $password = 'test';
    $statement->bind_param('sssss', $playerOne, $fortressOne, $username, $password, $itemsOne);
    $statement->execute();
    $username = 'testb';
    $statement->bind_param('sssss', $playerTwo, $fortressTwo, $username, $password, $itemsTwo);
    $statement->execute();
    $statement->close();

    $rocket = new FingerRocket();
    $rocket->id = uniqid('rocket');
    $rocket->toHit = 0;
    $rocket->dieType = 4;
    $armoryOne = json_encode(array(json_decode(json_encode($rocket), true)));
    $armoryTwo = json_encode(array());
    $statement = $db->prepare("INSERT INTO fortress (flak, damageResistance, hitResistance, points, id, pId, cladding, storedCladding, name, damage, armory) VALUES (0, 0, ?, 100, ?, ?, 0, 0, ?, 'undamaged', ?)");
    $hitResistance = 0;
    $name = 'Turn Test A';
    $statement->bind_param('issss', $hitResistance, $fortressOne, $playerOne, $name, $armoryOne);
    $statement->execute();
    $hitResistance = 1000;
    $name = 'Turn Test B';
    $statement->bind_param('issss', $hitResistance, $fortressTwo, $playerTwo, $name, $armoryTwo);
    $statement->execute();
    $statement->close();

    $basePoints = 5;
    $gameLog = '[]';
    $playerUp = $playerOne;
    $affinities = '{}';
    $targetScore = 100000;
    $winnerId = NULL;
    $gameStatus = 'active';
    $statement = $db->prepare('INSERT INTO gamehandler (id, basePoints, p1, p2, f1, f2, gameLog, playerUp, affinities, targetScore, winnerId, gameStatus) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $statement->bind_param('sisssssssiss', $gameId, $basePoints, $playerOne, $playerTwo, $fortressOne, $fortressTwo, $gameLog, $playerUp, $affinities, $targetScore, $winnerId, $gameStatus);
    $statement->execute();
    $statement->close();

    $initialArmory = json_decode(readTurnTestRow($db, 'fortress', $fortressOne)['armory'], true);
    $initialPlayerPoints = (int)readTurnTestRow($db, 'fortress', $fortressOne)['points'];
    $initialOpponentPoints = (int)readTurnTestRow($db, 'fortress', $fortressTwo)['points'];
    $rejectedAttack = postGameAction(array(
        'attack_pId' => $playerTwo,
        'attack_hId' => $gameId,
        'player_rockets' => 'invalid-rocket'
    ));
    $result['out_of_turn_attack_rejected'] = isset($rejectedAttack['error']);
    $result['rejected_attack_preserved_armory'] = count(json_decode(readTurnTestRow($db, 'fortress', $fortressOne)['armory'], true)) === count($initialArmory);

    $rocketId = $initialArmory[0]['id'];
    $attack = postGameAction(array(
        'attack_pId' => $playerOne,
        'attack_hId' => $gameId,
        'player_rockets' => $rocketId
    ));
    $result['attack_response_has_game_state'] = isset($attack['player'], $attack['f1'], $attack['f2']);
    $pointsAfterAttack = (int)readTurnTestRow($db, 'fortress', $fortressTwo)['points'];
    $playerPointsAfterAttack = (int)readTurnTestRow($db, 'fortress', $fortressOne)['points'];
    $storedGame = readTurnTestRow($db, 'gamehandler', $gameId);
    $result['attack_awarded_points'] = $pointsAfterAttack > $initialOpponentPoints || $playerPointsAfterAttack > $initialPlayerPoints;
    $result['defender_score_delta'] = $pointsAfterAttack - $initialOpponentPoints;
    $result['attacker_score_delta'] = $playerPointsAfterAttack - $initialPlayerPoints;
    $result['attack_log'] = $storedGame['gameLog'];
    $result['attack_passed_turn_to_b'] = readTurnTestRow($db, 'gamehandler', $gameId)['playerUp'] === $playerTwo;

    $armoryBeforeBuy = count(json_decode(readTurnTestRow($db, 'fortress', $fortressTwo)['armory'], true));
    $buy = postGameAction(array(
        'action_playerId' => $playerTwo,
        'action_handlerId' => $gameId,
        'player_gunShop' => 0,
        'player_quan' => 1
    ));
    $result['buy_response_has_game_state'] = isset($buy['player'], $buy['f1'], $buy['f2']);
    $result['buy_added_rocket'] = count(json_decode(readTurnTestRow($db, 'fortress', $fortressTwo)['armory'], true)) === $armoryBeforeBuy + 1;
    $result['buy_passed_turn_to_a'] = readTurnTestRow($db, 'gamehandler', $gameId)['playerUp'] === $playerOne;

    $armoryAfterBuy = count(json_decode(readTurnTestRow($db, 'fortress', $fortressTwo)['armory'], true));
    $rejectedBuy = postGameAction(array(
        'action_playerId' => $playerTwo,
        'action_handlerId' => $gameId,
        'player_gunShop' => 0,
        'player_quan' => 1
    ));
    $result['out_of_turn_buy_rejected'] = isset($rejectedBuy['error']);
    $result['rejected_buy_preserved_armory'] = count(json_decode(readTurnTestRow($db, 'fortress', $fortressTwo)['armory'], true)) === $armoryAfterBuy;

    $craft = postGameAction(array(
        'action_playerId' => $playerOne,
        'action_handlerId' => $gameId,
        'craft_blueprint_id' => 'bp-test'
    ));
    $result['craft_succeeded'] = !empty($craft['craft']['success']);
    $result['craft_passed_turn_to_b'] = readTurnTestRow($db, 'gamehandler', $gameId)['playerUp'] === $playerTwo;

    foreach($result as $check => $passed){
        if(!$passed){
            throw new RuntimeException('Failed check: ' . $check);
        }
    }
} catch (Throwable $error) {
    $result['error'] = get_class($error) . ': ' . $error->getMessage();
} finally {
    foreach(array('gamehandler' => array($ids['game']), 'fortress' => $ids['fortresses'], 'players' => $ids['players']) as $table => $rowIds){
        foreach($rowIds as $rowId){
            try{
                $statement = $db->prepare("DELETE FROM `$table` WHERE id = ?");
                $statement->bind_param('s', $rowId);
                $statement->execute();
                $statement->close();
            } catch (Throwable $cleanupError){
                $result['cleanup_error'] = $cleanupError->getMessage();
            }
        }
    }
    $result['temporary_rows_cleaned'] = !isset($result['cleanup_error']);
}

echo json_encode($result, JSON_PRETTY_PRINT) . PHP_EOL;
