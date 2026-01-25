<?php
require_once __DIR__ . '/../dao/ReserveDAO.php';
require_once __DIR__ . '/../dto/ReserveDTO.php';

/**
 * ReserveModel - 予約管理のビジネスロジック層
 * DAOを使ってデータベース操作を行い、ビジネスルールを適用
 */
class ReserveModel {
    private $reserveDAO;

    /**
     * コンストラクタ
     * @param ReserveDAO $reserveDAO
     */
    public function __construct($reserveDAO) {
        $this->reserveDAO = $reserveDAO;
    }

    /**
     * 予約を作成
     * @param int $studentId 学生ID
     * @param int $siId 店舗商品ID
     * @param int $quantity 数量
     * @return array ['success' => bool, 'message' => string, 'reserve_id' => int|null]
     */
    public function create($studentId, $siId, $quantity) {
        // バリデーション
        if (!$studentId || !$siId || !$quantity || $quantity < 1) {
            return [
                'success' => false,
                'message' => '不正なリクエストです',
                'reserve_id' => null
            ];
        }

        // 二重予約チェック
        if ($this->reserveDAO->hasDuplicateReservation($studentId, $siId)) {
            return [
                'success' => false,
                'message' => 'すでにこの商品は予約済みです',
                'reserve_id' => null
            ];
        }

        // 予約DTOを作成
        $reserveDTO = new ReserveDTO(
            null,                    // r_id (自動採番)
            date('Y-m-d H:i:s'),    // datetime
            $studentId,             // st_id
            $siId,                  // si_id
            $quantity,              // num
            0                       // situation (0: 予約中)
        );

        // 予約を登録
        $result = $this->reserveDAO->addReserve($reserveDTO);

        if ($result['success']) {
            return [
                'success' => true,
                'message' => '予約が完了しました',
                'reserve_id' => $result['insert_id']
            ];
        } else {
            return [
                'success' => false,
                'message' => '予約の登録に失敗しました',
                'reserve_id' => null
            ];
        }
    }

    /**
     * 予約をキャンセル
     * @param int $reserveId 予約ID
     * @param int $studentId 学生ID
     * @return array ['success' => bool, 'message' => string]
     */
    public function cancel($reserveId, $studentId) {
        // バリデーション
        if (!$reserveId || !$studentId) {
            return [
                'success' => false,
                'message' => '不正なリクエストです'
            ];
        }

        // 自分の予約かつ予約中か確認
        $reservation = $this->reserveDAO->getReserveOwnerAndStatus($reserveId, $studentId);

        if (!$reservation) {
            return [
                'success' => false,
                'message' => '不正な操作です'
            ];
        }

        if ($reservation['situation'] != 0) {
            return [
                'success' => false,
                'message' => 'この予約は取り消せません'
            ];
        }

        // 状態を取消(2)に更新
        $result = $this->reserveDAO->updReserveStatus($reserveId, 2);

        if ($result) {
            return [
                'success' => true,
                'message' => '予約をキャンセルしました'
            ];
        } else {
            return [
                'success' => false,
                'message' => '予約のキャンセルに失敗しました'
            ];
        }
    }

    /**
     * 学生の予約一覧を取得（詳細情報含む）
     * @param int $studentId 学生ID
     * @return array 予約詳細の配列
     */
    public function getByStudent($studentId) {
        if (!$studentId) {
            return [];
        }
        return $this->reserveDAO->getReserveDetails($studentId);
    }

    /**
     * 店舗の予約一覧を取得（学生情報含む）
     * @param int $shopId 店舗ID
     * @return array 予約詳細の配列
     */
    public function getByShop($shopId) {
        if (!$shopId) {
            return [];
        }
        return $this->reserveDAO->getReserveDetailsForShop($shopId);
    }

    /**
     * 予約状態を更新（店舗側の操作用）
     * @param int $reserveId 予約ID
     * @param int $shopId 店舗ID
     * @param int $newSituation 新しい状態
     * @return array ['success' => bool, 'message' => string]
     */
    public function updStatus($reserveId, $shopId, $newSituation) {
        // バリデーション
        if (!$reserveId || !$shopId || !in_array($newSituation, [1, 3])) {
            return [
                'success' => false,
                'message' => '不正なリクエストです'
            ];
        }

        // 自店舗の予約かつ予約中(0)か確認
        $reservation = $this->reserveDAO->getReservationShopStatus($reserveId, $shopId);

        if (!$reservation) {
            return [
                'success' => false,
                'message' => '不正な操作です'
            ];
        }

        if ($reservation['situation'] != 0) {
            return [
                'success' => false,
                'message' => 'この予約は変更できません'
            ];
        }

        // 状態を更新
        $result = $this->reserveDAO->updReserveStatus($reserveId, $newSituation);

        if ($result) {
            $statusName = $newSituation == 1 ? '来店済み' : '完売';
            return [
                'success' => true,
                'message' => "予約を{$statusName}に更新しました"
            ];
        } else {
            return [
                'success' => false,
                'message' => '予約の更新に失敗しました'
            ];
        }
    }

    /**
     * 学生の店舗別予約を取得
     * @param int $studentId 学生ID
     * @param int $shopId 店舗ID
     * @return array 予約詳細の配列
     */
    public function getByStudentShop($studentId, $shopId) {
        if (!$studentId || !$shopId) {
            return [];
        }
        return $this->reserveDAO->getStudentReservationsByShop($studentId, $shopId);
    }

    /**
     * ステータスコードを日本語に変換
     * @param int $situation ステータスコード
     * @return string ステータス名
     */
    public function getSituationLabel($situation) {
        switch ($situation) {
            case 0: return '予約中';
            case 1: return '来店';
            case 2: return '取消';
            case 3: return '完売';
            default: return '不明';
        }
    }

    /**
     * 学生の予約一覧を取得（ステータスラベル付き）
     * @param int $studentId 学生ID
     * @return array 予約詳細の配列（situation_labelフィールド追加）
     */
    public function getByStudentLabeled($studentId) {
        $reservations = $this->getByStudent($studentId);

        // 各予約にステータスラベルを追加
        foreach ($reservations as &$reservation) {
            $reservation['situation_label'] = $this->getSituationLabel($reservation['situation']);
        }

        return $reservations;
    }
}
