<?php
require_once __DIR__ . '/BaseDAO.php';
require_once __DIR__ . '/../dto/ReserveDTO.php';

/**
 * ReserveDAO - 予約管理のDAO（Data Access Object）
 * データベースとの直接的なやり取りを担当し、DTOを使用
 */
class ReserveDAO extends BaseDAO {

    /**
     * 予約を追加
     * @param ReserveDTO $reserveDTO 予約データ
     * @return array ['success' => bool, 'insert_id' => int|null] 成功した場合はtrue、挿入IDを含む
     */
    public function addReserve($reserveDTO) {
        $sql = 'INSERT INTO reserve (datetime, st_id, si_id, num, situation) VALUES (?, ?, ?, ?, ?)';
        $result = $this->executeInsert($sql, [
            $reserveDTO->datetime,
            $reserveDTO->st_id,
            $reserveDTO->si_id,
            $reserveDTO->num,
            $reserveDTO->situation
        ]);

        if ($result) {
            return [
                'success' => true,
                'insert_id' => $this->getLastInsertId()
            ];
        } else {
            return [
                'success' => false,
                'insert_id' => null
            ];
        }
    }

    /**
     * すべての予約を取得
     * @return array ReserveDTOの配列
     */
    public function getAllReserves() {
        $sql = 'SELECT r_id, datetime, st_id, si_id, num, situation FROM reserve ORDER BY datetime DESC';
        return $this->fetchAll($sql, [], function($row) {
            return new ReserveDTO(
                $row['r_id'],
                $row['datetime'],
                $row['st_id'],
                $row['si_id'],
                $row['num'],
                $row['situation']
            );
        });
    }

    /**
     * 予約IDで予約を取得
     * @param int $reserveId 予約ID
     * @return ReserveDTO|null 予約データ
     */
    public function getReserveById($reserveId) {
        $sql = 'SELECT r_id, datetime, st_id, si_id, num, situation FROM reserve WHERE r_id = ?';
        return $this->fetchOne($sql, [$reserveId], function($row) {
            return new ReserveDTO(
                $row['r_id'],
                $row['datetime'],
                $row['st_id'],
                $row['si_id'],
                $row['num'],
                $row['situation']
            );
        });
    }

    /**
     * 生徒IDで予約を取得
     * @param int $studentId 生徒ID
     * @return array ReserveDTOの配列
     */
    public function getReservesByStudentId($studentId) {
        $sql = 'SELECT r_id, datetime, st_id, si_id, num, situation FROM reserve WHERE st_id = ? ORDER BY datetime DESC';
        return $this->fetchAll($sql, [$studentId], function($row) {
            return new ReserveDTO(
                $row['r_id'],
                $row['datetime'],
                $row['st_id'],
                $row['si_id'],
                $row['num'],
                $row['situation']
            );
        });
    }

    /**
     * 予約の状況を更新
     * @param int $reserveId 予約ID
     * @param int $situation 状況
     * @return bool 成功した場合true
     */
    public function updReserveStatus($reserveId, $situation) {
        $sql = 'UPDATE reserve SET situation = ? WHERE r_id = ?';
        return $this->executeUpdate($sql, [$situation, $reserveId]);
    }

    /**
     * 二重予約チェック
     * @param int $studentId 学生ID
     * @param int $siId 店舗商品ID
     * @return bool 既に予約済みの場合true
     */
    public function hasDuplicateReservation($studentId, $siId) {
        $sql = 'SELECT COUNT(*) FROM reserve WHERE st_id = ? AND si_id = ? AND situation = 0';
        $count = $this->fetchColumn($sql, [$studentId, $siId]);
        return $count > 0;
    }

    /**
     * 店舗IDで予約を取得
     * @param int $shopId 店舗ID
     * @return array ReserveDTOの配列
     */
    public function getReservesByShopId($shopId) {
        $sql = 'SELECT r.r_id, r.datetime, r.st_id, r.si_id, r.num, r.situation
                FROM reserve r
                JOIN shopitem si ON r.si_id = si.si_id
                WHERE si.sh_id = ?
                ORDER BY r.datetime DESC';
        return $this->fetchAll($sql, [$shopId], function($row) {
            return new ReserveDTO(
                $row['r_id'],
                $row['datetime'],
                $row['st_id'],
                $row['si_id'],
                $row['num'],
                $row['situation']
            );
        });
    }

    /**
     * 予約の詳細情報を取得（JOIN含む）
     * @param int $studentId 学生ID
     * @return array 予約詳細の配列
     */
    public function getReserveDetails($studentId) {
        $sql = "
        SELECT
            r.r_id,
            r.datetime,
            r.num,
            r.situation,
            i.i_name,
            i.i_price,
            sh.pr_name,
            sh.place
        FROM reserve r
        JOIN shopitem si ON r.si_id = si.si_id
        JOIN item i ON si.i_id = i.i_id
        JOIN shop sh ON si.sh_id = sh.sh_id
        WHERE r.st_id = ?
        ORDER BY r.datetime DESC
        ";
        return $this->fetchAll($sql, [$studentId], function($row) {
            return $row;
        });
    }

    /**
     * 予約の所有者と状態を確認
     * @param int $reserveId 予約ID
     * @param int $studentId 学生ID
     * @return array|false ['situation' => int] または false
     */
    public function getReserveOwnerAndStatus($reserveId, $studentId) {
        $sql = 'SELECT situation FROM reserve WHERE r_id = ? AND st_id = ?';
        return $this->fetchAssocOne($sql, [$reserveId, $studentId]);
    }

    /**
     * 店舗の予約詳細を取得（学生情報含む）
     * @param int $shopId 店舗ID
     * @return array 予約詳細の配列
     */
    public function getReserveDetailsForShop($shopId) {
        $sql = "
        SELECT
            r.r_id,
            r.datetime,
            r.num,
            r.situation,
            s.st_id,
            s.name AS st_name,
            s.class AS st_class,
            i.i_name,
            i.i_price,
            si.si_id
        FROM reserve r
        JOIN student s ON r.st_id = s.st_id
        JOIN shopitem si ON r.si_id = si.si_id
        JOIN item i ON si.i_id = i.i_id
        WHERE si.sh_id = ?
        ORDER BY r.datetime DESC
        ";
        return $this->fetchAll($sql, [$shopId], function($row) {
            return $row;
        });
    }

    /**
     * 学生の予約を店舗商品IDで検索
     * @param int $studentId 学生ID
     * @param int $shopId 店舗ID
     * @return array 予約詳細の配列
     */
    public function getStudentReservationsByShop($studentId, $shopId) {
        $sql = "
        SELECT
            r.r_id,
            r.datetime,
            r.num,
            r.situation,
            r.si_id,
            i.i_name,
            i.i_price,
            s.name AS student_name
        FROM reserve r
        JOIN shopitem si ON r.si_id = si.si_id
        JOIN item i ON si.i_id = i.i_id
        JOIN student s ON r.st_id = s.st_id
        WHERE r.st_id = ?
        AND si.sh_id = ?
        AND r.situation = 0
        ORDER BY r.datetime DESC
        ";
        return $this->fetchAll($sql, [$studentId, $shopId], function($row) {
            return $row;
        });
    }

    /**
     * 予約が自店舗のものか、かつ予約中かを確認
     * @param int $reserveId 予約ID
     * @param int $shopId 店舗ID
     * @return array|false ['situation' => int] または false
     */
    public function getReservationShopStatus($reserveId, $shopId) {
        $sql = "
        SELECT r.situation
        FROM reserve r
        JOIN shopitem si ON r.si_id = si.si_id
        WHERE r.r_id = ?
        AND si.sh_id = ?
        ";
        return $this->fetchAssocOne($sql, [$reserveId, $shopId]);
    }
}
