<?php
/**
 * SalesDetailDTO - 売上明細データを保持するクラス
 */
class SalesDetailDTO {
    public $si_id;           // 店舗商品ID
    public $s_id;            // 売上ID
    public $num;             // 数量
    public $price;           // 単価
    public $disc;            // 値引き額

    public function __construct($si_id = null, $s_id = null, $num = null, $price = null, $disc = 0) {
        $this->si_id = $si_id;
        $this->s_id = $s_id;
        $this->num = $num;
        $this->price = $price;
        $this->disc = $disc;
    }
}
