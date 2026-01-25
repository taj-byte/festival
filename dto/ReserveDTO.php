<?php
/**
 * ReserveDTO - 予約データを保持するクラス
 */
class ReserveDTO {
    public $r_id;      // 予約ID
    public $datetime;  // 日時
    public $st_id;     // 生徒ID
    public $si_id;     // 店舗商品ID
    public $num;       // 人数
    public $situation; // 状況

    public function __construct($r_id = null, $datetime = null, $st_id = null, $si_id = null, $num = null, $situation = null) {
        $this->r_id = $r_id;
        $this->datetime = $datetime;
        $this->st_id = $st_id;
        $this->si_id = $si_id;
        $this->num = $num;
        $this->situation = $situation;
    }
}
