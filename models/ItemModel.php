<?php
require_once __DIR__ . '/../dto/ItemDTO.php';
require_once __DIR__ . '/../dao/ItemDAO.php';

/**
 * ItemModel - 商品管理のビジネスロジック層
 * バリデーション、データ加工などを担当
 */
class ItemModel {
    private $itemDAO;

    public function __construct($itemDAO) {
        $this->itemDAO = $itemDAO;
    }

    /**
     * 商品名を正規化
     * - 前後の空白を削除
     * - 全角括弧・数字を半角に統一
     * @param string $name 商品名
     * @return string 正規化された商品名
     */
    private function normalizeName($name) {
        $name = trim($name);

        // 全角括弧を半角に
        $name = str_replace(['（', '）'], ['(', ')'], $name);

        // 全角数字を半角に
        $name = mb_convert_kana($name, 'n', 'UTF-8');

        // 括弧の前後のスペースを除去（全角・半角）
        $name = preg_replace('/\s*\(\s*/', '(', $name);
        $name = preg_replace('/\s*\)\s*/', ')', $name);

        return $name;
    }

    /**
     * 商品名に年度フォーマット (YYYY) が含まれているかチェック
     * @param string $name 正規化済みの商品名
     * @return array ['valid' => bool, 'message' => string, 'year' => int|null]
     */
    private function checkYear($name) {
        // (4桁の数字) を探す
        if (!preg_match('/\((\d{4})\)/u', $name, $matches)) {
            return [
                'valid' => false,
                'message' => '商品名に年度を含めてください（例: タピオカミルクティー(2025)）',
                'year' => null
            ];
        }

        // 年度部分を除いた商品名が空でないかチェック
        $nameWithoutYear = trim(preg_replace('/\(\d{4}\)/', '', $name));
        if (empty($nameWithoutYear)) {
            return [
                'valid' => false,
                'message' => '商品名を入力してください（年度だけでは登録できません）',
                'year' => null
            ];
        }

        $year = (int)$matches[1];

        // 年度の範囲チェック
        if ($year < 2000 || $year > 2100) {
            return [
                'valid' => false,
                'message' => '年度は2000〜2100の範囲で入力してください',
                'year' => null
            ];
        }

        return [
            'valid' => true,
            'message' => '',
            'year' => $year
        ];
    }

    /**
     * 商品を追加（バリデーション付き）
     * @param string $name 商品名
     * @param string $price 価格
     * @return array ['success' => bool, 'message' => string]
     */
    public function create($name, $price) {
        $name = $this->normalizeName($name);

        // バリデーション
        $validation = $this->validateItem($name, $price);
        if (!$validation['valid']) {
            return ['success' => false, 'message' => $validation['message']];
        }

        // 年度チェック
        $yearCheck = $this->checkYear($name);
        if (!$yearCheck['valid']) {
            return ['success' => false, 'message' => $yearCheck['message']];
        }

        // 重複チェック
        if ($this->itemDAO->existsItem($name)) {
            $msg = sprintf('「%s」は既に登録されています', $name);
            return ['success' => false, 'message' => $msg];
        }

        $dto = new ItemDTO(null, $name, $price);

        if ($this->itemDAO->addItem($dto)) {
            return ['success' => true, 'message' => '商品を追加しました'];
        }
        return ['success' => false, 'message' => '追加に失敗しました'];
    }

    /**
     * すべての商品を取得
     * @return array ItemDTOの配列
     */
    public function getAll() {
        return $this->itemDAO->getAllItems();
    }

    /**
     * 商品を年度別にグループ化
     * @param array $items ItemDTOの配列
     * @return array 年度をキーとした商品の配列
     */
    public function groupByYear($items) {
        $byYear = [];

        foreach ($items as $item) {
            if (preg_match('/\((\d{4})\)/u', $item->i_name, $m)) {
                $year = $m[1];
            } else {
                $year = '未分類';
            }

            if (!isset($byYear[$year])) {
                $byYear[$year] = [];
            }
            $byYear[$year][] = $item;
        }

        krsort($byYear);
        return $byYear;
    }

    /**
     * 店舗IDに紐づく商品を取得
     * @param int $shopId 店舗ID
     * @return array ItemDTOの配列
     */
    public function getByShop($shopId) {
        return $this->itemDAO->getItemsByShop($shopId);
    }

    /**
     * API用: 店舗の商品を取得
     * @param int $shopId 店舗ID
     * @return array ['success' => bool, 'items' => array, 'count' => int, 'message' => string]
     */
    public function getForApi($shopId) {
        if (empty($shopId)) {
            return [
                'success' => false,
                'items' => [],
                'count' => 0,
                'message' => '店舗IDが指定されていません'
            ];
        }

        $items = $this->itemDAO->getItemsByShop($shopId);
        $result = [];

        foreach ($items as $i) {
            $result[] = [
                'si_id' => $i['si_id'],
                'i_id' => $i['i_id'],
                'i_name' => $i['i_name'],
                'i_price' => $i['i_price']
            ];
        }

        return [
            'success' => true,
            'items' => $result,
            'count' => count($result),
            'message' => ''
        ];
    }

    /**
     * 商品データのバリデーション
     * @param string $name 商品名
     * @param string $price 価格
     * @return array ['valid' => bool, 'message' => string]
     */
    private function validateItem($name, $price) {
        if (empty($name)) {
            return ['valid' => false, 'message' => '商品名を入力してください'];
        }

        if (empty($price) || !is_numeric($price) || $price < 0) {
            return ['valid' => false, 'message' => '価格は0以上の数値で入力してください'];
        }

        return ['valid' => true, 'message' => ''];
    }
}
