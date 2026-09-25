<?php
require __DIR__ . '/handler.php';

function item($typeId, $material = NULL){
    return array('id' => uniqid(), 'typeId' => $typeId, 'name' => 'test', 'material' => $material);
}

function blueprint($module){
    return array('id' => uniqid(), 'typeId' => 32, 'name' => $module, 'module' => $module);
}

function createPlayer($items){
    $player = new player();
    $player->setId(uniqid());
    $player->setUserName('craft-test');
    $player->setPass('craft-test');
    $player->itemArray = $items;
    $player->setFortress(new Fortress());
    return $player;
}

function repeatItems($typeId, $count, $material = NULL){
    $items = array();
    for($index = 0; $index < $count; $index++){
        $items[] = item($typeId, $material);
    }
    return $items;
}

$rocketItems = [[], [], [blueprint('Finger Rocket')], []];
$rocketItems[3] = array_merge(repeatItems(1000, 1), repeatItems(1001, 1), repeatItems(1002, 1), repeatItems(18, 1));
$rocketPlayer = createPlayer($rocketItems);
$rocketResult = $rocketPlayer->craftBlueprint($rocketItems[2][0]['id']);

$moduleItems = [[], [], [blueprint('Bulwark')], []];
$moduleItems[1] = array_merge(repeatItems(29, 6, 'Iron'), repeatItems(34, 4, 'Iron'), repeatItems(30, 3, 'Iron'), repeatItems(31, 3, 'Iron'), repeatItems(35, 3, 'Iron'));
$modulePlayer = createPlayer($moduleItems);
$moduleResult = $modulePlayer->craftBlueprint($moduleItems[2][0]['id']);

$claddingItems = [[], [], [blueprint('Cladding')], []];
$claddingItems[1] = array_merge(repeatItems(1005, 1, 'Iron'), repeatItems(1006, 2, 'Iron'), repeatItems(1003, 4, 'Iron'), repeatItems(34, 20, 'Iron'), repeatItems(29, 50, 'Iron'), repeatItems(30, 20, 'Iron'), repeatItems(31, 20, 'Iron'), repeatItems(35, 20, 'Iron'));
$claddingPlayer = createPlayer($claddingItems);
$claddingResult = $claddingPlayer->craftBlueprint($claddingItems[2][0]['id']);

echo json_encode([
    'finger_rocket' => $rocketResult,
    'bulwark' => $moduleResult,
    'cladding' => $claddingResult,
    'crafted_module_count' => count($modulePlayer->itemArray[3]),
    'cladding_type' => $claddingPlayer->fortress->getCladding()
], JSON_PRETTY_PRINT) . PHP_EOL;