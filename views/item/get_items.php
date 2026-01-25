<?php
require __DIR__ . '/../../config/dbConnection.php';
require_once __DIR__ . '/../../controllers/ItemController.php';

// Controllerのインスタンスを作成
$itemController = new ItemController($pdo);

// Controller層に処理を委譲
$itemController->api();
