<?php
require __DIR__ . '/../../config/dbConnection.php';
require_once __DIR__ . '/../../controllers/SalesController.php';

// Controllerのインスタンスを作成
$ctrl = new SalesController($pdo);

// Controller層に処理を委譲（成功時・エラー時ともにリダイレクトされる）
$ctrl->deliverOrder();

// 通常はここには到達しない（Controller内でリダイレクトされるため）
?>
