<?php
require __DIR__ . '/../../config/init.php';
require __DIR__ . '/../../config/dbConnection.php';
require_once __DIR__ . '/../../controllers/ReserveController.php';

/* セッションチェック */
if (!isset($_SESSION['student_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

/* Controllerのインスタンスを作成 */
$ctrl = new ReserveController($pdo);

/* リクエストパラメータを取得 */
$student_id = $_SESSION['student_id'];
$store_product_id = $_POST['store_product_id'] ?? null;
$quantity = $_POST['quantity'] ?? null;

/* 予約作成処理をControllerに委譲 */
$result = $ctrl->create($student_id, $store_product_id, $quantity);

/* 結果に応じて処理 */
if ($result['success']) {
    header('Location: my_reservations.php', true, 303);
    exit;
} else {
    die($result['message']);
}
