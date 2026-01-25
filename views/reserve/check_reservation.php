<?php
require __DIR__ . '/../../config/init.php';
require __DIR__ . '/../../config/dbConnection.php';
require_once __DIR__ . '/../../controllers/ReserveController.php';
require_once __DIR__ . '/../../dao/StudentDAO.php';

// JSON形式で返却
header('Content-Type: application/json; charset=utf-8');

// POSTリクエストのみ受け付け
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '不正なリクエストです']);
    exit;
}

// パラメータ取得
$student_name = $_POST['student_name'] ?? null;
$shop_id = $_POST['shop_id'] ?? null;

// バリデーション
if (!$student_name || !$shop_id) {
    echo json_encode(['success' => false, 'message' => 'パラメータが不足しています']);
    exit;
}

// StudentDAOで学生を検索
$studentDAO = new StudentDAO($pdo);
$students = $studentDAO->searchStudentsByName($student_name);

if (empty($students)) {
    echo json_encode([
        'success' => true,
        'has_reservation' => false,
        'message' => '該当する学生が見つかりません'
    ]);
    exit;
}

// 複数の学生が見つかった場合、候補を返す
if (count($students) > 1) {
    $studentList = array_map(function($student) {
        return [
            'st_id' => $student->st_id,
            'name' => $student->name,
            'class' => $student->class
        ];
    }, $students);

    echo json_encode([
        'success' => true,
        'multiple_students' => true,
        'message' => '複数の学生が見つかりました。学生を選択してください',
        'students' => $studentList
    ]);
    exit;
}

// 1人の学生が見つかった場合、予約を検索
$student = $students[0];
$ctrl = new ReserveController($pdo);
$reservations = $ctrl->getByStudentShop($student->st_id, $shop_id);

if (empty($reservations)) {
    echo json_encode([
        'success' => true,
        'has_reservation' => false,
        'student_found' => true,
        'student_name' => $student->name,
        'student_id' => $student->st_id,
        'message' => $student->name . ' さんの予約はありません'
    ]);
} else {
    // 予約がある場合、最初の予約を返す
    $reservation = $reservations[0];
    echo json_encode([
        'success' => true,
        'has_reservation' => true,
        'student_name' => $student->name,
        'student_id' => $student->st_id,
        'message' => $student->name . ' さんの予約が見つかりました',
        'reservation' => [
            'r_id' => $reservation['r_id'],
            'si_id' => $reservation['si_id'],
            'i_name' => $reservation['i_name'],
            'i_price' => $reservation['i_price'],
            'num' => $reservation['num'],
            'datetime' => $reservation['datetime']
        ]
    ]);
}
