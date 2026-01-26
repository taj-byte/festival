<?php
require_once __DIR__ . '/../models/StudentModel.php';
require_once __DIR__ . '/../dao/StudentDAO.php';

/**
 * StudentController - 生徒管理のコントローラー層
 * リクエスト処理を担当
 */
class StudentController {
    private $studentModel;

    public function __construct($pdo) {
        if ($pdo !== null) {
            $studentDAO = new StudentDAO($pdo);
            $this->studentModel = new StudentModel($studentDAO);
        }
    }

    /**
     * 生徒一覧を取得（並び替え対応）
     * @return array StudentDTOの配列
     */
    public function display() {
        // リクエストパラメータ取得
        $order = $_GET['order'] ?? 'class';
        $dir = $_GET['dir'] ?? 'asc';

        // 許可された値のみ受け付ける
        $allowedOrders = ['class', 'id', 'kana'];
        $allowedDirs = ['asc', 'desc'];

        if (!in_array($order, $allowedOrders)) {
            $order = 'class';
        }
        if (!in_array($dir, $allowedDirs)) {
            $dir = 'asc';
        }

        return [
            'students' => $this->studentModel->getAll($order, $dir),
            'order' => $order,
            'dir' => $dir
        ];
    }

    /**
     * フリガナで生徒検索
     * @return array 検索結果
     */
    public function search() {
        $kana = $_POST['kana'] ?? '';
        return $this->studentModel->searchByKana($kana);
    }

    /**
     * 生徒IDで生徒を取得
     * @param int $studentId 生徒ID
     * @return StudentDTO|null 生徒データ
     */
    public function findById($studentId) {
        return $this->studentModel->findById($studentId);
    }
}
