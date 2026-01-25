<?php
/**
 * ItemDTO - 商品データを保持するクラス
 */
class ItemDTO {
    public $i_id;      // 商品ID
    public $i_name;    // 商品名
    public $i_price;   // 価格

    public function __construct($i_id = null, $i_name = null, $i_price = null) {
        $this->i_id = $i_id;
        $this->i_name = $i_name;
        $this->i_price = $i_price;
    }
}
