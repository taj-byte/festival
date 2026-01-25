<?php
require_once __DIR__ . '/BaseDAO.php';
require_once __DIR__ . '/../dto/SalesDTO.php';
require_once __DIR__ . '/../dto/SalesDetailDTO.php';

/**
 * SalesDAO - 売上管理のDAO（Data Access Object）
 * データベースとの直接的なやり取りを担当し、DTOを使用
 */
class SalesDAO extends BaseDAO {

    /**
     * 売上を追加（トランザクション処理）
     * @param SalesDTO $salesDTO 売上データ
     * @param array $detailDTOs SalesDetailDTOの配列
     * @return array ['success' => bool, 'sales_id' => int, 'message' => string]
     */
    public function addSales($salesDTO, $detailDTOs) {
        try {
            // 売上データをsalesテーブルに挿入
            $sql_insert_sales = 'INSERT INTO sales (s_date, sh_id, situation) VALUES (NOW(), ?, ?)';
            $this->executeInsert($sql_insert_sales, [$salesDTO->sh_id, $salesDTO->situation ?? 0]);

            // 挿入された売上IDを取得
            $new_s_id = $this->getLastInsertId();

            // 売上明細をdetailテーブルに挿入
            $sql_insert_detail = 'INSERT INTO detail (si_id, s_id, num, price, disc) VALUES (?, ?, ?, ?, ?)';

            foreach ($detailDTOs as $detailDTO) {
                $this->executeInsert($sql_insert_detail, [
                    $detailDTO->si_id,
                    $new_s_id,
                    $detailDTO->num,
                    $detailDTO->price,
                    $detailDTO->disc
                ]);
            }

            return ['success' => true, 'sales_id' => $new_s_id, 'message' => ''];

        } catch (Exception $e) {
            return ['success' => false, 'sales_id' => null, 'message' => $e->getMessage()];
        }
    }

    /**
     * すべての売上を取得
     * @return array SalesDTOの配列
     */
    public function getAllSales() {
        $sql = 'SELECT s.s_id, s.s_date, s.sh_id, s.situation FROM sales s ORDER BY s.s_date DESC, s.s_id DESC';
        return $this->fetchAll($sql, [], function($row) {
            return new SalesDTO($row['s_id'], $row['s_date'], $row['sh_id'], $row['situation']);
        });
    }

    /**
     * 売上IDで売上を取得
     * @param int $salesId 売上ID
     * @return array SalesDTOの配列
     */
    public function getSalesById($salesId) {
        $sql = 'SELECT s.s_id, s.s_date, s.sh_id, s.situation FROM sales s WHERE s.s_id = ?';
        return $this->fetchAll($sql, [$salesId], function($row) {
            return new SalesDTO($row['s_id'], $row['s_date'], $row['sh_id'], $row['situation']);
        });
    }

    /**
     * 店舗IDで売上を取得
     * @param int $shopId 店舗ID
     * @return array SalesDTOの配列
     */
    public function getSalesByShop($shopId) {
        $sql = 'SELECT s.s_id, s.s_date, s.sh_id, s.situation FROM sales s WHERE s.sh_id = ? ORDER BY s.s_date DESC, s.s_id DESC';
        return $this->fetchAll($sql, [$shopId], function($row) {
            return new SalesDTO($row['s_id'], $row['s_date'], $row['sh_id'], $row['situation']);
        });
    }

    /**
     * 売上明細を取得
     * @param int $salesId 売上ID
     * @return array 売上明細データの配列（商品情報含む）
     */
    public function getSalesDetails($salesId) {
        $sql = 'SELECT d.si_id, si.i_id, i.i_name, d.num, d.price, d.disc, (d.num * (d.price - d.disc)) as subtotal
                FROM detail d
                INNER JOIN shopitem si ON d.si_id = si.si_id
                INNER JOIN item i ON si.i_id = i.i_id
                WHERE d.s_id = ?
                ORDER BY d.si_id';
        return $this->fetchAssoc($sql, [$salesId]);
    }

    /**
     * 総売上金額を計算
     * @param int|null $salesId 売上ID（nullの場合は全体）
     * @param int|null $shopId 店舗ID（nullの場合は全体）
     * @param int|null $fy 年度（nullの場合は全年度）
     * @return float 総売上金額
     */
    public function getSalesSum($salesId = null, $shopId = null, $fy = null) {
        $sql = 'SELECT SUM(d.num * (d.price - d.disc))
                FROM detail d
                INNER JOIN sales s ON d.s_id = s.s_id';

        // 年度フィルターが必要な場合はJOIN追加
        if ($fy !== null) {
            $sql .= ' INNER JOIN shopitem si ON d.si_id = si.si_id
                      INNER JOIN item i ON si.i_id = i.i_id';
        }

        $params = [];
        $where_conditions = [];

        if ($salesId !== null) {
            $where_conditions[] = 's.s_id = ?';
            $params[] = $salesId;
        } elseif ($shopId !== null) {
            $where_conditions[] = 's.sh_id = ?';
            $params[] = $shopId;
        }

        if ($fy !== null) {
            $where_conditions[] = 'i.i_name REGEXP ?';
            $params[] = '\\(' . $fy . '\\)';
        }

        if (count($where_conditions) > 0) {
            $sql .= ' WHERE ' . implode(' AND ', $where_conditions);
        }

        $result = $this->fetchColumn($sql, $params);
        return $result ?? 0;
    }

    /**
     * 店舗ごとの売上合計を取得（降順）
     * @param int|null $fy 年度（nullの場合は全年度）
     * @return array 店舗ごとの売上合計データ
     */
    public function getShopSum($fy = null) {
        $sql = 'SELECT s.sh_id, sh.pr_name, sh.class, SUM(d.num * (d.price - d.disc)) as total_sales
                FROM sales s
                INNER JOIN detail d ON s.s_id = d.s_id
                INNER JOIN shop sh ON s.sh_id = sh.sh_id
                INNER JOIN shopitem si ON d.si_id = si.si_id
                INNER JOIN item i ON si.i_id = i.i_id';

        $params = [];

        if ($fy !== null) {
            $sql .= ' WHERE i.i_name REGEXP ?';
            $params[] = '\\(' . $fy . '\\)';
        }

        $sql .= ' GROUP BY s.sh_id, sh.pr_name, sh.class
                  ORDER BY total_sales DESC';

        return $this->fetchAssoc($sql, $params);
    }

    /**
     * 特定店舗の売上明細を全て取得
     * @param int $shopId 店舗ID
     * @return array 売上明細データの配列
     */
    public function getShopDetails($shopId) {
        $sql = 'SELECT s.s_id, s.s_date, si.i_id, i.i_name, i.i_price as original_price,
                       d.num, d.price, d.disc, (d.num * (d.price - d.disc)) as subtotal
                FROM sales s
                INNER JOIN detail d ON s.s_id = d.s_id
                INNER JOIN shopitem si ON d.si_id = si.si_id
                INNER JOIN item i ON si.i_id = i.i_id
                WHERE s.sh_id = ?
                ORDER BY s.s_date DESC, s.s_id DESC, d.si_id';

        return $this->fetchAssoc($sql, [$shopId]);
    }

    /**
     * 特定店舗の納品待ち売上を取得
     * @param int $shopId 店舗ID
     * @return array 納品待ち売上データの配列（売上IDでグループ化）
     */
    public function getPending($shopId) {
        $sql = 'SELECT s.s_id, s.s_date, s.situation,
                       GROUP_CONCAT(CONCAT(i.i_name, " x", d.num) SEPARATOR ", ") as items,
                       SUM(d.num * (d.price - d.disc)) as total
                FROM sales s
                INNER JOIN detail d ON s.s_id = d.s_id
                INNER JOIN shopitem si ON d.si_id = si.si_id
                INNER JOIN item i ON si.i_id = i.i_id
                WHERE s.sh_id = ? AND s.situation = 0
                GROUP BY s.s_id, s.s_date, s.situation
                ORDER BY s.s_id ASC';

        return $this->fetchAssoc($sql, [$shopId]);
    }

    /**
     * 売上の状態を更新（納品済みにする）
     * @param int $salesId 売上ID
     * @return bool 成功したかどうか
     */
    public function updStatus($salesId, $situation = 1) {
        $sql = 'UPDATE sales SET situation = ? WHERE s_id = ?';
        return $this->executeUpdate($sql, [$situation, $salesId]);
    }

    /**
     * 店舗別の午前・午後・合計売上を取得
     * @param int|null $fy 年度（nullの場合は全年度）
     * @return array 店舗別売上データ（午前・午後・合計）
     */
    public function getByPeriod($fy = null) {
        $sql = 'SELECT
                    s.sh_id,
                    sh.pr_name,
                    sh.class,
                    SUM(CASE WHEN HOUR(s.s_date) < 12 THEN d.num * (d.price - d.disc) ELSE 0 END) as morning_sales,
                    SUM(CASE WHEN HOUR(s.s_date) >= 12 THEN d.num * (d.price - d.disc) ELSE 0 END) as afternoon_sales,
                    SUM(d.num * (d.price - d.disc)) as total_sales
                FROM sales s
                INNER JOIN detail d ON s.s_id = d.s_id
                INNER JOIN shop sh ON s.sh_id = sh.sh_id
                INNER JOIN shopitem si ON d.si_id = si.si_id
                INNER JOIN item i ON si.i_id = i.i_id';

        $params = [];

        if ($fy !== null) {
            $sql .= ' WHERE i.i_name REGEXP ?';
            $params[] = '\\(' . $fy . '\\)';
        }

        $sql .= ' GROUP BY s.sh_id, sh.pr_name, sh.class
                  ORDER BY total_sales DESC';

        return $this->fetchAssoc($sql, $params);
    }

    /**
     * データベースから利用可能な年度一覧を取得
     * @return array 年度の配列（降順）
     */
    public function getYears() {
        $sql = 'SELECT DISTINCT
                    CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(i.i_name, "(", -1), ")", 1) AS UNSIGNED) as fy
                FROM item i
                WHERE i.i_name REGEXP "\\([0-9]{4}\\)"
                ORDER BY fy DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        $years = [];
        while ($row = $stmt->fetch()) {
            if ($row['fy'] >= 2000 && $row['fy'] <= 2100) {
                $years[] = (int)$row['fy'];
            }
        }

        return $years;
    }

    /**
     * 二重登録チェック（同じ店舗・商品・時間帯の売上）
     * @param int $shopId 店舗ID
     * @param int $siId 店舗商品ID
     * @param int $minutes 何分以内をチェックするか（デフォルト5分）
     * @return bool 重複がある場合true
     */
    public function hasDuplicateSales($shopId, $siId, $minutes = 5) {
        $sql = "
        SELECT COUNT(*) FROM sales s
        JOIN detail d ON s.s_id = d.s_id
        WHERE s.sh_id = ?
        AND d.si_id = ?
        AND s.s_date >= DATE_SUB(NOW(), INTERVAL ? MINUTE)
        ";
        $count = $this->fetchColumn($sql, [$shopId, $siId, $minutes]);
        return $count > 0;
    }
}
