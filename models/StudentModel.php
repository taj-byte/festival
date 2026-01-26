<?php
require_once __DIR__ . '/../dto/StudentDTO.php';
require_once __DIR__ . '/../dao/StudentDAO.php';

/**
 * StudentModel - 生徒管理のビジネスロジック層
 * バリデーション、データ加工などを担当
 */
class StudentModel {
    private $studentDAO;

    public function __construct($studentDAO) {
        $this->studentDAO = $studentDAO;
    }

    /**
     * すべての生徒を取得
     * @param string $order 並び替え項目 (class, id, kana)
     * @param string $dir 並び順 (asc, desc)
     * @return array StudentDTOの配列
     */
    public function getAll($order = 'class', $dir = 'asc') {
        $students = $this->studentDAO->getAllStudents();
        return $this->sortStudents($students, $order, $dir);
    }

    /**
     * 生徒IDで生徒を取得
     * @param int $studentId 生徒ID
     * @return StudentDTO|null 生徒データ
     */
    public function findById($studentId) {
        return $this->studentDAO->getStudentById($studentId);
    }

    /**
     * クラスで生徒を取得
     * @param string $class クラス
     * @return array StudentDTOの配列
     */
    public function findByClass($class) {
        return $this->studentDAO->getStudentsByClass($class);
    }

    /**
     * フリガナで検索（バリデーション付き）
     * @param string $kana フリガナ（カタカナまたはひらがな）
     * @return array ['success' => bool, 'data' => array|null, 'message' => string]
     */
    public function searchByKana($kana) {
        // バリデーション
        $validation = $this->validateKanaSearch($kana);
        if (!$validation['valid']) {
            return ['success' => false, 'data' => null, 'message' => $validation['message']];
        }

        // ひらがなをカタカナに変換して検索
        $kanaForSearch = mb_convert_kana($kana, 'C', 'UTF-8');

        $students = $this->studentDAO->searchStudentsByKana($kanaForSearch);
        return ['success' => true, 'data' => $students, 'message' => ''];
    }

    /**
     * フリガナ検索のバリデーション（カタカナ・ひらがな対応）
     * @param string $kana フリガナ
     * @return array ['valid' => bool, 'message' => string]
     */
    private function validateKanaSearch($kana) {
        if (empty($kana)) {
            return ['valid' => false, 'message' => 'フリガナが入力されていません'];
        }

        // カタカナ（ァ-ヶー）またはひらがな（ぁ-ん）のみ許可
        if (!preg_match('/^[ァ-ヶーぁ-ん]+$/u', $kana)) {
            return ['valid' => false, 'message' => 'エラー：ひらがな・カタカナ以外の文字が含まれています'];
        }

        return ['valid' => true, 'message' => ''];
    }

    /**
     * 生徒リストをソート
     * @param array $students StudentDTOの配列
     * @param string $order 並び替え項目
     * @param string $dir 並び順
     * @return array ソート済みStudentDTOの配列
     */
    private function sortStudents($students, $order, $dir) {
        $dir = strtolower($dir) === 'desc' ? -1 : 1;

        usort($students, function($a, $b) use ($order, $dir) {
            switch ($order) {
                case 'id':
                    $cmp = $a->st_id <=> $b->st_id;
                    if ($cmp !== 0) return $cmp * $dir;
                    $cmp = $a->class <=> $b->class;
                    if ($cmp !== 0) return $cmp;
                    return $a->kana <=> $b->kana;

                case 'kana':
                    $cmp = strcmp($a->kana, $b->kana);
                    if ($cmp !== 0) return $cmp * $dir;
                    $cmp = $a->class <=> $b->class;
                    if ($cmp !== 0) return $cmp;
                    return $a->st_id <=> $b->st_id;

                case 'class':
                default:
                    $cmp = strcmp($a->class, $b->class);
                    if ($cmp !== 0) return $cmp * $dir;
                    $cmp = $a->st_id <=> $b->st_id;
                    if ($cmp !== 0) return $cmp;
                    return strcmp($a->kana, $b->kana);
            }
        });

        return $students;
    }
}
