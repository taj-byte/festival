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
$reservation_id = $_POST['reservation_id'] ?? null;

/* 予約キャンセル処理をControllerに委譲 */
$result = $ctrl->cancel($reservation_id, $student_id);

/* 結果に応じて処理 */
if ($result['success']) {
    header('Location: my_reservations.php');
    exit;
} else {
    die($result['message']);
}
