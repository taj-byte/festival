<?php
require_once __DIR__ . '/BaseDAO.php';
require_once __DIR__ . '/../dto/ItemDTO.php';

/**
 * ItemDAO - 商品管理のDAO（Data Access Object）
 * データベースとの直接的なやり取りを担当し、DTOを使用
 */
class ItemDAO extends BaseDAO {

    /**
     * 商品を追加
     * @param ItemDTO $dto 商品データ
     * @return bool 成功した場合true
     */
    public function addItem($dto) {
        $sql = 'INSERT INTO item VALUES(null, ?, ?)';
        return $this->executeInsert($sql, [$dto->i_name, $dto->i_price]);
    }

    /**
     * すべての商品を取得
     * @return array ItemDTOの配列
     */
    public function getAllItems() {
        $sql = 'SELECT * FROM item';
        return $this->fetchAll($sql, [], function($row) {
            return new ItemDTO($row['i_id'], $row['i_name'], $row['i_price']);
        });
    }

    /**
     * 店舗IDに紐づく商品を取得（si_id付き）
     * @param int $shopId 店舗ID
     * @return array 商品データの配列（si_id, i_id, i_name, i_price）
     */
    public function getItemsByShop($shopId) {
        $sql = 'SELECT si.si_id, i.i_id, i.i_name, i.i_price
                FROM item i
                INNER JOIN shopitem si ON i.i_id = si.i_id
                WHERE si.sh_id = ?
                ORDER BY i.i_name';
        return $this->fetchAssoc($sql, [$shopId]);
    }

    /**
     * 商品名で存在チェック
     * @param string $name 商品名
     * @return bool 存在する場合true
     */
    public function existsItem($name) {
        $sql = 'SELECT COUNT(*) FROM item WHERE i_name = ?';
        return $this->fetchColumn($sql, [$name]) > 0;
    }
}
