<?php
require_once __DIR__ . '/../models/ReserveModel.php';
require_once __DIR__ . '/../dao/ReserveDAO.php';

/**
 * ReserveController - 予約管理のコントローラー層
 * リクエストを受け取り、Modelに処理を依頼し、レスポンスを返す
 */
class ReserveController {
    private $reserveModel;
    private $pdo;

    /**
     * コンストラクタ
     * @param PDO|null $pdo PDOインスタンス
     */
    public function __construct($pdo = null) {
        $this->pdo = $pdo;
        if ($pdo !== null) {
            $reserveDAO = new ReserveDAO($pdo);
            $this->reserveModel = new ReserveModel($reserveDAO);
        }
    }

    /**
     * 予約を作成
     * @param int $studentId 学生ID
     * @param int $siId 店舗商品ID
     * @param int $quantity 数量
     * @return array ['success' => bool, 'message' => string, 'reserve_id' => int|null]
     */
    public function create($studentId, $siId, $quantity) {
        return $this->reserveModel->create($studentId, $siId, $quantity);
    }

    /**
     * 予約をキャンセル
     * @param int $reserveId 予約ID
     * @param int $studentId 学生ID
     * @return array ['success' => bool, 'message' => string]
     */
    public function cancel($reserveId, $studentId) {
        return $this->reserveModel->cancel($reserveId, $studentId);
    }

    /**
     * 学生の予約一覧を取得
     * @param int $studentId 学生ID
     * @return array 予約詳細の配列
     */
    public function getByStudent($studentId) {
        return $this->reserveModel->getByStudent($studentId);
    }

    /**
     * 学生の予約一覧を取得（ステータスラベル付き）
     * @param int $studentId 学生ID
     * @return array 予約詳細の配列（situation_labelフィールド追加）
     */
    public function getByStudentLabeled($studentId) {
        return $this->reserveModel->getByStudentLabeled($studentId);
    }

    /**
     * 店舗の予約一覧を取得
     * @param int $shopId 店舗ID
     * @return array 予約詳細の配列
     */
    public function getByShop($shopId) {
        return $this->reserveModel->getByShop($shopId);
    }

    /**
     * 予約状態を更新（店舗側の操作用）
     * @param int $reserveId 予約ID
     * @param int $shopId 店舗ID
     * @param int $newSituation 新しい状態
     * @return array ['success' => bool, 'message' => string]
     */
    public function updStatus($reserveId, $shopId, $newSituation) {
        return $this->reserveModel->updStatus($reserveId, $shopId, $newSituation);
    }

    /**
     * 学生の店舗別予約を取得
     * @param int $studentId 学生ID
     * @param int $shopId 店舗ID
     * @return array 予約詳細の配列
     */
    public function getByStudentShop($studentId, $shopId) {
        return $this->reserveModel->getByStudentShop($studentId, $shopId);
    }

    /**
     * セッションチェック（学生用）
     * @return int|null 学生ID、未ログインの場合null
     */
    public function checkStudentSession() {
        if (!isset($_SESSION['student_id'])) {
            return null;
        }
        return $_SESSION['student_id'];
    }

    /**
     * セッションチェック（店舗用）
     * @return int|null 店舗ID、未ログインの場合null
     */
    public function checkShopSession() {
        if (!isset($_SESSION['shop_id'])) {
            return null;
        }
        return $_SESSION['shop_id'];
    }
}
