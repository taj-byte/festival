<?php
require_once __DIR__ . '/BaseDAO.php';
require_once __DIR__ . '/../dto/ShopItemDTO.php';

/**
 * ShopItemDAO - 店舗商品関連管理のDAO（Data Access Object）
 * データベースとの直接的なやり取りを担当し、DTOを使用
 */
class ShopItemDAO extends BaseDAO {

    /**
     * 店舗商品関連を追加
     * @param ShopItemDTO $shopItemDTO 店舗商品関連データ
     * @return bool 成功した場合true
     */
    public function addShopItem($shopItemDTO) {
        $sql = 'INSERT INTO shopitem (sh_id, i_id) VALUES (?, ?)';
        return $this->executeInsert($sql, [$shopItemDTO->sh_id, $shopItemDTO->i_id]);
    }

    /**
     * すべての店舗商品関連を取得
     * @return array ShopItemDTOの配列
     */
    public function getAllShopItems() {
        $sql = 'SELECT si_id, sh_id, i_id FROM shopitem ORDER BY sh_id';
        return $this->fetchAll($sql, [], function($row) {
            return new ShopItemDTO($row['si_id'], $row['sh_id'], $row['i_id']);
        });
    }

    /**
     * 店舗IDで商品を取得
     * @param int $shopId 店舗ID
     * @return array ShopItemDTOの配列
     */
    public function fetchItemsByShop($shopId) {
        $sql = 'SELECT si_id, sh_id, i_id FROM shopitem WHERE sh_id = ?';
        return $this->fetchAll($sql, [$shopId], function($row) {
            return new ShopItemDTO($row['si_id'], $row['sh_id'], $row['i_id']);
        });
    }

    /**
     * 店舗商品関連の存在チェック
     * @param int $shopId 店舗ID
     * @param int $itemId 商品ID
     * @return bool 存在する場合true
     */
    public function exists($shopId, $itemId) {
        $sql = 'SELECT COUNT(*) FROM shopitem WHERE sh_id = ? AND i_id = ?';
        $count = $this->fetchColumn($sql, [$shopId, $itemId]);
        return $count > 0;
    }

    /**
     * 店舗商品関連を削除
     * @param int $shopId 店舗ID
     * @param int $itemId 商品ID
     * @return bool 成功した場合true
     */
    public function delShopItem($shopId, $itemId) {
        $sql = 'DELETE FROM shopitem WHERE sh_id = ? AND i_id = ?';
        return $this->executeDelete($sql, [$shopId, $itemId]);
    }
}
