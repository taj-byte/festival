<?php
/**
 * ShopItemDTO - 店舗商品関連データを保持するクラス
 */
class ShopItemDTO {
    public $si_id;     // 店舗商品ID
    public $sh_id;     // 店舗ID
    public $i_id;      // 商品ID

    public function __construct($si_id = null, $sh_id = null, $i_id = null) {
        $this->si_id = $si_id;
        $this->sh_id = $sh_id;
        $this->i_id = $i_id;
    }
}
