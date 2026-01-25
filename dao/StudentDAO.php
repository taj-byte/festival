<?php
require_once __DIR__ . '/BaseDAO.php';
require_once __DIR__ . '/../dto/StudentDTO.php';

/**
 * StudentDAO - 生徒管理のDAO（Data Access Object）
 * データベースとの直接的なやり取りを担当し、DTOを使用
 */
class StudentDAO extends BaseDAO {

    /**
     * すべての生徒を取得
     * @return array StudentDTOの配列
     */
    public function getAllStudents() {
        $sql = 'SELECT st_id, class, name, kana, pasc FROM student ORDER BY st_id';
        return $this->fetchAll($sql, [], function($row) {
            return new StudentDTO(
                $row['st_id'],
                $row['class'],
                $row['name'],
                $row['kana'],
                $row['pasc']
            );
        });
    }

    /**
     * 生徒IDで生徒を取得
     * @param int $studentId 生徒ID
     * @return StudentDTO|null 生徒データ
     */
    public function getStudentById($studentId) {
        $sql = 'SELECT st_id, class, name, kana, pasc FROM student WHERE st_id = ?';
        return $this->fetchOne($sql, [$studentId], function($row) {
            return new StudentDTO(
                $row['st_id'],
                $row['class'],
                $row['name'],
                $row['kana'],
                $row['pasc']
            );
        });
    }

    /**
     * クラスで生徒を取得
     * @param string $class クラス
     * @return array StudentDTOの配列
     */
    public function getStudentsByClass($class) {
        $sql = 'SELECT st_id, class, name, kana, pasc FROM student WHERE class = ? ORDER BY st_id';
        return $this->fetchAll($sql, [$class], function($row) {
            return new StudentDTO(
                $row['st_id'],
                $row['class'],
                $row['name'],
                $row['kana'],
                $row['pasc']
            );
        });
    }

    /**
     * パスコードで生徒を認証
     * @param int $studentId 生徒ID
     * @param string $pasc パスコード
     * @return StudentDTO|null 認証成功時は生徒データ、失敗時はnull
     */
    public function authenticateStudent($studentId, $pasc) {
        $sql = 'SELECT st_id, class, name, kana, pasc FROM student WHERE st_id = ? AND pasc = ?';
        return $this->fetchOne($sql, [$studentId, $pasc], function($row) {
            return new StudentDTO(
                $row['st_id'],
                $row['class'],
                $row['name'],
                $row['kana'],
                $row['pasc']
            );
        });
    }

    /**
     * 学生名で検索（部分一致）
     * @param string $name 学生名（部分一致）
     * @return array StudentDTOの配列
     */
    public function searchStudentsByName($name) {
        $sql = 'SELECT st_id, class, name, kana, pasc FROM student WHERE name LIKE ? ORDER BY st_id';
        $searchParam = '%' . $name . '%';
        return $this->fetchAll($sql, [$searchParam], function($row) {
            return new StudentDTO(
                $row['st_id'],
                $row['class'],
                $row['name'],
                $row['kana'],
                $row['pasc']
            );
        });
    }

    /**
     * 学生名（かな）で検索（部分一致）
     * @param string $kana 学生名かな（部分一致）
     * @return array StudentDTOの配列
     */
    public function searchStudentsByKana($kana) {
        $sql = 'SELECT st_id, class, name, kana, pasc FROM student WHERE kana LIKE ? ORDER BY st_id';
        $searchParam = '%' . $kana . '%';
        return $this->fetchAll($sql, [$searchParam], function($row) {
            return new StudentDTO(
                $row['st_id'],
                $row['class'],
                $row['name'],
                $row['kana'],
                $row['pasc']
            );
        });
    }
}
