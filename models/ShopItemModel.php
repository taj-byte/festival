<?php
require_once __DIR__ . '/../dto/ShopItemDTO.php';
require_once __DIR__ . '/../dao/ShopItemDAO.php';

/**
 * ShopItemModel - 店舗商品管理のビジネスロジック層
 * バリデーション、データ加工、重複チェックなどを担当
 */
class ShopItemModel {
    private $shopItemDAO;

    public function __construct($shopItemDAO) {
        $this->shopItemDAO = $shopItemDAO;
    }

    /**
     * 店舗商品を追加（単一）
     * @param int $shopId 店舗ID
     * @param int $itemId 商品ID
     * @return array ['success' => bool, 'message' => string]
     */
    public function create($shopId, $itemId) {
        // バリデーション
        $validation = $this->validateShopItem($shopId, $itemId);
        if (!$validation['valid']) {
            return ['success' => false, 'message' => $validation['message']];
        }

        // 重複チェック
        if ($this->shopItemDAO->exists($shopId, $itemId)) {
            return ['success' => false, 'message' => 'この商品は既にこの店舗に登録されています'];
        }

        // DTOを作成
        $shopItemDTO = new ShopItemDTO(null, $shopId, $itemId);

        // DAOを通じてDBに追加
        $result = $this->shopItemDAO->addShopItem($shopItemDTO);

        if ($result) {
            return ['success' => true, 'message' => '店舗商品を追加しました'];
        } else {
            return ['success' => false, 'message' => '追加に失敗しました'];
        }
    }

    /**
     * 店舗商品を複数追加
     * @param int $shopId 店舗ID
     * @param array $itemIds 商品IDの配列
     * @return array ['success' => bool, 'message' => string, 'added' => int, 'skipped' => int]
     */
    public function createMulti($shopId, $itemIds) {
        $added = 0;
        $skipped = 0;
        $errors = [];

        foreach ($itemIds as $itemId) {
            $result = $this->create($shopId, $itemId);
            if ($result['success']) {
                $added++;
            } else {
                $skipped++;
                if (strpos($result['message'], '既に登録') === false) {
                    $errors[] = $result['message'];
                }
            }
        }

        if (count($errors) > 0) {
            return [
                'success' => false,
                'message' => implode(', ', $errors),
                'added' => $added,
                'skipped' => $skipped
            ];
        }

        if ($added === 0) {
            return [
                'success' => false,
                'message' => '選択された商品は既に登録されています',
                'added' => 0,
                'skipped' => $skipped
            ];
        }

        return [
            'success' => true,
            'message' => "{$added}件の商品を追加しました" . ($skipped > 0 ? "（{$skipped}件は既に登録済みのためスキップ）" : ''),
            'added' => $added,
            'skipped' => $skipped
        ];
    }

    /**
     * 店舗IDで商品を取得
     * @param int $shopId 店舗ID
     * @return array ShopItemDTOの配列
     */
    public function findItemsByShop($shopId) {
        return $this->shopItemDAO->fetchItemsByShop($shopId);
    }

    /**
     * すべての店舗商品を取得
     * @return array ShopItemDTOの配列
     */
    public function getAll() {
        return $this->shopItemDAO->getAllShopItems();
    }

    /**
     * 店舗商品を削除
     * @param int $shopId 店舗ID
     * @param int $itemId 商品ID
     * @return array ['success' => bool, 'message' => string]
     */
    public function delete($shopId, $itemId) {
        $result = $this->shopItemDAO->delShopItem($shopId, $itemId);

        if ($result) {
            return ['success' => true, 'message' => '店舗商品を削除しました'];
        } else {
            return ['success' => false, 'message' => '削除に失敗しました'];
        }
    }

    /**
     * 店舗商品データのバリデーション
     * @param int $shopId 店舗ID
     * @param int $itemId 商品ID
     * @return array ['valid' => bool, 'message' => string]
     */
    private function validateShopItem($shopId, $itemId) {
        if (empty($shopId) || !is_numeric($shopId)) {
            return ['valid' => false, 'message' => '店舗を選択してください'];
        }

        if (empty($itemId) || !is_numeric($itemId)) {
            return ['valid' => false, 'message' => '商品を選択してください'];
        }

        return ['valid' => true, 'message' => ''];
    }
}
