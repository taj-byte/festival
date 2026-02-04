<?php
require_once __DIR__ . '/../dto/ShopDTO.php';
require_once __DIR__ . '/../dao/ShopDAO.php';

/**
 * ShopModel - 店舗管理のビジネスロジック層
 * バリデーション、データ加工などを担当
 */
class ShopModel {
    private $shopDAO;

    public function __construct($shopDAO) {
        $this->shopDAO = $shopDAO;
    }
    /**
     * 店舗を追加（バリデーション付き）
     * @param string $fy 年度
     * @param string $class クラス
     * @param string $pr_name 企画名
     * @param string $place 場所
     * 
     * @return array ['success' => bool, 'message' => string]
     */
    public function addshop($fy, $class, $pr_name, $place) {
        $validation = $this->validateShop($fy, $class, $pr_name, $place);
        if (!$validation['valid']) {
            return ['success' => false, 'message' => $validation['message']];
        }

        $shopDTO = new ShopDTO(null, $fy, $class, $pr_name, $place);
        return $this->shopDAO->addShop($shopDTO)
            ? ['success' => true, 'message' => '店舗を追加しました']
            : ['success' => false, 'message' => '追加に失敗しました'];
    }
    /**
     * 店舗入力バリデーション
     * @return array ['valid' => bool, 'message' => string]
     */
    private function validateShop($fy, $class, $pr_name, $place) {
        if ($fy === '') {
            return ['valid' => false, 'message' => '年度を入力してください'];
        }
        if ($class === '') {
            return ['valid' => false, 'message' => 'クラスを入力してください'];
        }
        if (!preg_match('/^[A-Za-z0-9]+$/', $class)) {
            return ['valid' => false, 'message' => 'クラスは半角英数字で入力してください'];
        }
        if ($pr_name === '') {
            return ['valid' => false, 'message' => '企画名を入力してください'];
        }
        if ($place === '') {
            return ['valid' => false, 'message' => '場所を入力してください'];
        }

        return ['valid' => true, 'message' => ''];
    }

    /**
     * すべての店舗を取得
     * @return array ShopDTOの配列
     */
    public function getAll() {
        return $this->shopDAO->getAllShops();
    }

    /**
     * 年度で店舗を取得
     * @param int $fy 年度
     * @return array ShopDTOの配列
     */
    public function getByFy($fy) {
        return $this->shopDAO->getShopsByFy($fy);
    }

    /**
     * 店舗IDで店舗情報を取得
     * @param int $shopId 店舗ID
     * @return ShopDTO|null 店舗DTO
     */
    public function findShop($shopId) {
        return $this->shopDAO->fetchShopById($shopId);
    }
}
