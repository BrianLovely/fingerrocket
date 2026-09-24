<?php
session_start();
include __DIR__ . '/handler.php';

$ch = new combatHandler();
$login = json_decode($ch->authPlayer('jeff', 'frpass'), true);
if (!$login['playerExists']) {
    fwrite(STDERR, "LOGIN_FAILED\n");
    exit(1);
}

$handlers = $ch->findCombatHandlers('681f762053cb6');
if (count($handlers) === 0) {
    fwrite(STDERR, "NO_HANDLERS_FOUND\n");
    exit(2);
}

$options = $ch->packageGameChoices($handlers, '681f762053cb6');
if (count($options) === 0) {
    fwrite(STDERR, "NO_GAME_OPTIONS\n");
    exit(3);
}

$loaded = json_decode($ch->loadGameFromOptions('681f762053cb6', '68443e5fd233b'), true);
if (!isset($loaded['player']) || !isset($loaded['opponent'])) {
    fwrite(STDERR, "LOAD_GAME_FAILED\n");
    exit(4);
}

echo "LOGIN_OK\n";
echo "HANDLER_COUNT=" . count($handlers) . "\n";
echo "OPTION_COUNT=" . count($options) . "\n";
echo "LOADED_PLAYER=" . $loaded['player']['id'] . "\n";
echo "LOADED_OPPONENT=" . $loaded['opponent']['id'] . "\n";
