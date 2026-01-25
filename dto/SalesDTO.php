<?php
/**
 * SalesDTO - 売上データを保持するクラス
 */
class SalesDTO {
    public $s_id;      // 売上ID
    public $s_date;    // 売上日時
    public $sh_id;     // 店舗ID
    public $situation; // 状況

    public function __construct($s_id = null, $s_date = null, $sh_id = null, $situation = null) {
        $this->s_id = $s_id;
        $this->s_date = $s_date;
        $this->sh_id = $sh_id;
        $this->situation = $situation;
    }
}
