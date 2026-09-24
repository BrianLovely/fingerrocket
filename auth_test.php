<?php
$_POST = [
    'username' => 'jeff',
    'pass' => 'frpass',
];
$_SERVER['REQUEST_METHOD'] = 'POST';
session_start();
include __DIR__ . '/handler.php';
