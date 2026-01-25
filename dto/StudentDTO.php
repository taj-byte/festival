<?php
/**
 * StudentDTO - 生徒データを保持するクラス
 */
class StudentDTO {
    public $st_id;     // 生徒ID
    public $class;     // クラス
    public $name;      // 名前
    public $kana;      // カナ
    public $pasc;      // パスコード

    public function __construct($st_id = null, $class = null, $name = null, $kana = null, $pasc = null) {
        $this->st_id = $st_id;
        $this->class = $class;
        $this->name = $name;
        $this->kana = $kana;
        $this->pasc = $pasc;
    }
}
