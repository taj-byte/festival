<?php
require_once __DIR__ . '/../models/ItemModel.php';
require_once __DIR__ . '/../dao/ItemDAO.php';
require_once __DIR__ . '/../config/settings.php';

/**
 * ItemController - 商品管理のコントローラー層
 * リクエスト処理、レスポンス生成を担当
 */
class ItemController {
    private $itemModel;

    public function __construct($pdo) {
        $itemDAO = new ItemDAO($pdo);
        $this->itemModel = new ItemModel($itemDAO);
    }

    /**
     * 商品追加処理
     */
    public function add() {
        $name = $_POST['i_name'] ?? '';
        $price = $_POST['i_price'] ?? '';

        $result = $this->itemModel->create($name, $price);

        if ($result['success']) {
            header('Location: dsp_item.php', true, 303);
            exit();
        }

        return $result;
    }

    /**
     * 商品一覧表示処理
     */
    public function list() {
        $items = $this->itemModel->getAll();
        return ['items' => $items];
    }

    /**
     * 商品一覧を年度別にグループ化して表示
     */
    public function listByYear() {
        // デフォルトは現在年度（パラメータがない場合）
        $year = isset($_GET['year']) ? $_GET['year'] : CURRENT_FY;
        $items = $this->itemModel->getAll();
        $byYear = $this->itemModel->groupByYear($items);

        $years = array_keys($byYear);
        rsort($years);

        if ($year !== 'all') {
            $byYear = array_filter($byYear, function($y) use ($year) {
                return $y == $year;
            }, ARRAY_FILTER_USE_KEY);
        }

        return [
            'byYear' => $byYear,
            'years' => $years,
            'year' => $year,
            'currentFy' => CURRENT_FY
        ];
    }

    /**
     * API用: 店舗の商品を取得
     */
    public function api() {
        $shopId = $_GET['shop_id'] ?? '';
        $result = $this->itemModel->getForApi($shopId);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        exit();
    }

}
