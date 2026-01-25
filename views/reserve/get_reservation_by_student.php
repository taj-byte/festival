<?php
require __DIR__ . '/../../config/init.php';
require __DIR__ . '/../../config/dbConnection.php';
require_once __DIR__ . '/../../controllers/ReserveController.php';

// JSON形式で返却
header('Content-Type: application/json; charset=utf-8');

// POSTリクエストのみ受け付け
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '不正なリクエストです']);
    exit;
}

// パラメータ取得
$student_id = $_POST['student_id'] ?? null;
$shop_id = $_POST['shop_id'] ?? null;

// バリデーション
if (!$student_id || !$shop_id) {
    echo json_encode(['success' => false, 'message' => 'パラメータが不足しています']);
    exit;
}

// 予約を検索
$ctrl = new ReserveController($pdo);
$reservations = $ctrl->getByStudentShop($student_id, $shop_id);

if (empty($reservations)) {
    echo json_encode([
        'success' => true,
        'has_reservation' => false,
        'message' => 'この店舗の予約はありません'
    ]);
} else {
    // 予約がある場合、最初の予約を返す（予約中のもの優先）
    $activeReservation = null;
    foreach ($reservations as $reservation) {
        if ($reservation['situation'] == 0) { // 予約中
            $activeReservation = $reservation;
            break;
        }
    }

    // 予約中のものがなければ最初の予約
    if (!$activeReservation) {
        $activeReservation = $reservations[0];
    }

    echo json_encode([
        'success' => true,
        'has_reservation' => true,
        'student_name' => $activeReservation['student_name'],
        'student_id' => $student_id,
        'message' => '予約が見つかりました',
        'reservation' => [
            'r_id' => $activeReservation['r_id'],
            'si_id' => $activeReservation['si_id'],
            'i_name' => $activeReservation['i_name'],
            'i_price' => $activeReservation['i_price'],
            'num' => $activeReservation['num'],
            'datetime' => $activeReservation['datetime']
        ]
    ]);
}
