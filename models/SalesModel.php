<?php
require_once __DIR__ . '/../dto/SalesDTO.php';
require_once __DIR__ . '/../dto/SalesDetailDTO.php';
require_once __DIR__ . '/../dao/SalesDAO.php';

/**
 * SalesModel - 売上管理のビジネスロジック層
 * バリデーション、データ加工などを担当
 */
class SalesModel {
    private $salesDAO;

    public function __construct($salesDAO) {
        $this->salesDAO = $salesDAO;
    }

    /**
     * 売上を追加（バリデーション付き）
     * @param string $shopId 店舗ID
     * @param array $salesData 売上データ（['si_id_0' => 'si_id', 'num_0' => '', 'i_price_0' => '', ...]）
     * @return array ['success' => bool, 'sales_id' => int, 'message' => string]
     */
    public function createSales($shopId, $salesData) {
        // 店舗IDのバリデーション
        if (empty($shopId)) {
            return ['success' => false, 'sales_id' => null, 'message' => '店舗IDが選択されていません'];
        }

        // 売上明細データの処理
        $detailDTOs = [];
        for ($i = 0; $i < 4; $i++) {
            // フォームからsi_id（店舗商品ID）、数量、単価、値引き額を取得
            $si_id = $salesData["si_id_$i"] ?? '';
            $num = $salesData["num_$i"] ?? '';
            $price = $salesData["i_price_$i"] ?? '';
            $disc = $salesData["disc_$i"] ?? 0;

            // 空のデータはスキップ
            if (empty($si_id) || $num === '' || $num === null || $price === '' || $price === null) {
                continue;
            }

            // バリデーション
            $validation = $this->validateDetail($num, $price, $disc, $i + 1);
            if (!$validation['valid']) {
                return ['success' => false, 'sales_id' => null, 'message' => $validation['message']];
            }

            // DTOを作成（si_idを渡す）
            $detailDTOs[] = new SalesDetailDTO($si_id, null, $num, $price, $disc);
        }

        // 最低1つの商品が必要
        if (count($detailDTOs) == 0) {
            return ['success' => false, 'sales_id' => null, 'message' => '少なくとも1つの商品を入力してください'];
        }

        // SalesDTOを作成
        $salesDTO = new SalesDTO(null, null, $shopId);

        // DAOを通じてDBに追加
        $result = $this->salesDAO->addSales($salesDTO, $detailDTOs);

        if ($result['success']) {
            return ['success' => true, 'sales_id' => $result['sales_id'], 'message' => ''];
        } else {
            return ['success' => false, 'sales_id' => null, 'message' => '登録に失敗しました: ' . $result['message']];
        }
    }

    /**
     * すべての売上を取得
     * @return array SalesDTOの配列
     */
    public function getAllSales() {
        return $this->salesDAO->getAllSales();
    }

    /**
     * 売上IDで売上を取得
     * @param int $salesId 売上ID
     * @return array SalesDTOの配列
     */
    public function getSalesById($salesId) {
        return $this->salesDAO->getSalesById($salesId);
    }

    /**
     * 店舗IDで売上を取得
     * @param int $shopId 店舗ID
     * @return array SalesDTOの配列
     */
    public function getSalesByShop($shopId) {
        return $this->salesDAO->getSalesByShop($shopId);
    }

    /**
     * 売上明細を取得
     * @param int $salesId 売上ID
     * @return array 売上明細データの配列
     */
    public function getSalesDetails($salesId) {
        return $this->salesDAO->getSalesDetails($salesId);
    }

    /**
     * 総売上金額を計算
     * @param int|null $salesId 売上ID
     * @param int|null $shopId 店舗ID
     * @return float 総売上金額
     */
    public function calcTotal($salesId = null, $shopId = null) {
        return $this->salesDAO->getSalesSum($salesId, $shopId);
    }

    /**
     * 売上明細データのバリデーション
     * @param string $num 数量
     * @param string $price 価格
     * @param string $disc 値引き額
     * @param int $rowNumber 行番号
     * @return array ['valid' => bool, 'message' => string]
     */
    private function validateDetail($num, $price, $disc, $rowNumber) {
        if (!is_numeric($num) || $num < 1) {
            return ['valid' => false, 'message' => "数量は1以上の数値で入力してください（行{$rowNumber}）"];
        }

        if (!is_numeric($price) || $price < 0) {
            return ['valid' => false, 'message' => "単価は0以上の数値で入力してください（行{$rowNumber}）"];
        }

        // 値引き額のバリデーション
        if (!is_numeric($disc) || $disc < 0) {
            return ['valid' => false, 'message' => "値引き額は0以上の数値で入力してください（行{$rowNumber}）"];
        }

        // 値引き額が単価を超えないかチェック
        if ($disc > $price) {
            return ['valid' => false, 'message' => "値引き額が単価を超えています（行{$rowNumber}）"];
        }

        return ['valid' => true, 'message' => ''];
    }

    /**
     * 店舗ごとの売上合計を取得
     * @param int|null $fy 年度（nullの場合は全年度）
     * @return array 店舗ごとの売上合計データ（降順）
     */
    public function getShopSum($fy = null) {
        return $this->salesDAO->getShopSum($fy);
    }

    /**
     * 全店舗の総売上を計算
     * @param int|null $fy 年度（nullの場合は全年度）
     * @return float 総売上金額
     */
    public function calcGrandTotal($fy = null) {
        return $this->salesDAO->getSalesSum(null, null, $fy);
    }

    /**
     * 特定店舗の売上明細を取得
     * @param int $shopId 店舗ID
     * @return array 売上明細データの配列
     */
    public function getShopDetails($shopId) {
        return $this->salesDAO->getShopDetails($shopId);
    }

    /**
     * 特定店舗の納品待ち注文を取得
     * @param int $shopId 店舗ID
     * @return array 納品待ち注文データの配列
     */
    public function getPending($shopId) {
        return $this->salesDAO->getPending($shopId);
    }

    /**
     * 売上を納品済みにする
     * @param int $salesId 売上ID
     * @return bool 成功したかどうか
     */
    public function markDelivered($salesId) {
        return $this->salesDAO->updStatus($salesId, 1);
    }

    /**
     * 店舗別の午前・午後・合計売上を取得
     * @param int|null $fy 年度（nullの場合は全年度）
     * @return array 店舗別売上データ
     */
    public function getByPeriod($fy = null) {
        return $this->salesDAO->getByPeriod($fy);
    }

    /**
     * 利用可能な年度一覧を取得
     * @return array 年度の配列
     */
    public function getYears() {
        return $this->salesDAO->getYears();
    }

    /**
     * 商品名から年度を抽出
     * @param string $itemName 商品名
     * @return int|null 年度（抽出できない場合はnull）
     */
    public function extractYear($itemName) {
        if (preg_match('/\((\d{4})\)/u', $itemName, $matches)) {
            $year = (int)$matches[1];
            if ($year >= 2000 && $year <= 2100) {
                return $year;
            }
        }
        return null;
    }
}
