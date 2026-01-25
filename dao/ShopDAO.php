<?php
require_once __DIR__ . '/BaseDAO.php';
require_once __DIR__ . '/../dto/ShopDTO.php';

/**
 * ShopDAO - 店舗管理のDAO（Data Access Object）
 * データベースとの直接的なやり取りを担当し、DTOを使用
 */
class ShopDAO extends BaseDAO {

    /**
     * すべての店舗を取得
     * @return array ShopDTOの配列
     */
    public function getAllShops() {
        $sql = 'SELECT sh_id, fy, class, pr_name, place FROM shop ORDER BY sh_id';
        return $this->fetchAll($sql, [], function($row) {
            return new ShopDTO(
                $row['sh_id'],
                $row['fy'],
                $row['class'],
                $row['pr_name'],
                $row['place']
            );
        });
    }
    /**
     * 店舗を追加
     * @param ShopDTO $ShopDTO 店舗データ
     * @return bool 成功した場合true
     */
    public function addShop($shopDTO) {
        $sql = 'INSERT INTO shop VALUES(null, ?, ?, ?, ?)';
        return $this->executeInsert($sql, [$shopDTO->fy, $shopDTO->class, $shopDTO->pr_name, $shopDTO->place]);
    }

    /**
     * 年度で店舗を取得
     * @param int $fy 年度
     * @return array ShopDTOの配列
     */
    public function getShopsByFy($fy) {
        $sql = 'SELECT sh_id, fy, class, pr_name, place FROM shop WHERE fy = ? ORDER BY sh_id';
        return $this->fetchAll($sql, [$fy], function($row) {
            return new ShopDTO(
                $row['sh_id'],
                $row['fy'],
                $row['class'],
                $row['pr_name'],
                $row['place']
            );
        });
    }

    /**
     * 店舗IDで店舗情報を取得
     * @param int $shopId 店舗ID
     * @return ShopDTO|null 店舗DTO
     */
    public function fetchShopById($shopId) {
        $sql = 'SELECT sh_id, fy, class, pr_name, place FROM shop WHERE sh_id = ?';
        $result = $this->fetchAll($sql, [$shopId], function($row) {
            return new ShopDTO(
                $row['sh_id'],
                $row['fy'],
                $row['class'],
                $row['pr_name'],
                $row['place']
            );
        });
        return $result[0] ?? null;
    }
}
