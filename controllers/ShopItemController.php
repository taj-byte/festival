<?php
require_once __DIR__ . '/../models/ShopItemModel.php';
require_once __DIR__ . '/../dao/ShopItemDAO.php';
require_once __DIR__ . '/../dao/ShopDAO.php';
require_once __DIR__ . '/../dao/ItemDAO.php';

/**
 * ShopItemController - 店舗商品管理のコントローラー層
 * リクエスト処理、レスポンス生成を担当
 */
class ShopItemController {
    private $shopItemModel;
    private $shopDAO;
    private $itemDAO;
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $shopItemDAO = new ShopItemDAO($pdo);
        $this->shopItemModel = new ShopItemModel($shopItemDAO);
        $this->shopDAO = new ShopDAO($pdo);
        $this->itemDAO = new ItemDAO($pdo);
    }

    /**
     * 店舗商品追加処理（複数対応）
     */
    public function add() {
        // リクエストパラメータ取得
        $shopId = $_POST['shop_id'] ?? '';
        $itemIds = $_POST['item_ids'] ?? [];

        // 商品が選択されていない場合
        if (empty($itemIds)) {
            return ['success' => false, 'message' => '商品を少なくとも1つ選択してください'];
        }

        // モデルで処理
        $result = $this->shopItemModel->createMulti($shopId, $itemIds);

        // 結果に応じて画面遷移
        if ($result['success']) {
            header('Location: dsp_shopitem.php', true, 301);
            exit();
        }

        // エラー時はビューにデータを渡す
        return $result;
    }

    /**
     * 店舗商品一覧表示処理
     */
    public function list() {
        $shopItems = $this->shopItemModel->getAll();

        // 店舗情報と商品情報を結合（キャッシュ使用）
        require_once __DIR__ . '/ShopController.php';
        require_once __DIR__ . '/ItemController.php';
        $shopController = new ShopController($this->pdo);
        $itemController = new ItemController($this->pdo);
        $shops = $shopController->getCache();
        $items = $itemController->getCache();

        // 店舗ごとにグループ化
        $shopItemsGrouped = [];
        foreach ($shopItems as $shopItem) {
            $shopId = $shopItem->sh_id;
            if (!isset($shopItemsGrouped[$shopId])) {
                $shopItemsGrouped[$shopId] = [
                    'shop' => null,
                    'items' => []
                ];
            }

            // 店舗情報を設定
            foreach ($shops as $shop) {
                if ($shop->sh_id == $shopId) {
                    $shopItemsGrouped[$shopId]['shop'] = $shop;
                    break;
                }
            }

            // 商品情報を追加
            foreach ($items as $item) {
                if ($item->i_id == $shopItem->i_id) {
                    $shopItemsGrouped[$shopId]['items'][] = [
                        'si_id' => $shopItem->si_id,
                        'item' => $item
                    ];
                    break;
                }
            }
        }

        return [
            'shopItemsGrouped' => $shopItemsGrouped
        ];
    }

    /**
     * 入力画面用データ取得（年度別グループ化付き）
     */
    public function prepareInput() {
        // キャッシュを使用してデータ取得
        require_once __DIR__ . '/ShopController.php';
        require_once __DIR__ . '/ItemController.php';
        $shopController = new ShopController($this->pdo);
        $itemController = new ItemController($this->pdo);
        $shops = $shopController->getCache();
        $items = $itemController->getCache();

        // 既に店舗商品が登録されている店舗IDを取得
        $shopItems = $this->shopItemModel->getAll();
        $registeredShopIds = array_unique(array_map(function($si) {
            return $si->sh_id;
        }, $shopItems));

        // 店舗商品未登録の店舗のみにフィルタリング
        $shops = array_filter($shops, function($shop) use ($registeredShopIds) {
            return !in_array($shop->sh_id, $registeredShopIds);
        });

        // 商品を年度別にグループ化
        require_once __DIR__ . '/../models/ItemModel.php';
        $itemDAO = new ItemDAO($this->pdo);
        $itemModel = new ItemModel($itemDAO);
        $itemsByYear = $itemModel->groupByYear($items);

        return [
            'shops' => $shops,
            'itemsByYear' => $itemsByYear
        ];
    }

    /**
     * 店舗商品削除処理
     */
    public function delete() {
        $shopId = $_POST['shop_id'] ?? '';
        $itemId = $_POST['item_id'] ?? '';

        $result = $this->shopItemModel->delete($shopId, $itemId);

        if ($result['success']) {
            header('Location: dsp_shopitem.php', true, 301);
            exit();
        }

        return $result;
    }
}
