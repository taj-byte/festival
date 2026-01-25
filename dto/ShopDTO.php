<?php
/**
 * ShopDTO - 店舗データを保持するクラス
 */
class ShopDTO {
    public $sh_id;      // 店舗ID
    public $fy;         // 年度
    public $class;      // クラス
    public $pr_name;    // プロジェクト名
    public $place;      // 場所

    public function __construct($sh_id = null, $fy = null, $class = null, $pr_name = null, $place = null) {
        $this->sh_id = $sh_id;
        $this->fy = $fy;
        $this->class = $class;
        $this->pr_name = $pr_name;
        $this->place = $place;
    }
}
