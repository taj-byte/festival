<?php
require_once __DIR__ . '/../models/ShopModel.php';
require_once __DIR__ . '/../dao/ShopDAO.php';

/**
 * ShopController - 店舗管理のコントローラー層
 * リクエスト処理、セッション管理を担当
 */
class ShopController {
    private $shopModel;

    public function __construct($pdo) {
        if ($pdo !== null) {
            $shopDAO = new ShopDAO($pdo);
            $this->shopModel = new ShopModel($shopDAO);
        }
    }
    /**
     * 店舗追加処理
     */
    public function addshop() {
        // リクエストパラメータ取得
        $fy = $_POST['sh_year'] ?? '';
        $class = $_POST['sh_class'] ?? '';
        $pr_name = $_POST['sh_name'] ?? '';
        $place = $_POST['i_place'] ?? '';

        // モデルで処理
        $result = $this->shopModel->addshop($fy, $class, $pr_name, $place);

        // 結果に応じて画面遷移
        if ($result['success']) {
            header('Location: dsp_shop.php', true, 303);
            exit();
        }

        // エラー時はビューにデータを渡す
        return $result;
    }

    public function display() {
        return $this->shopModel->getAll();
    }

    /**
     * 年度で店舗一覧を取得
     * @param int $fy 年度
     * @return array ShopDTOの配列
     */
    public function displayByFy($fy) {
        return $this->shopModel->getByFy($fy);
    }

    /**
     * 店舗ID選択処理（セッション設定）
     */
    public function selectShopId() {
        try {
            // モデルから店舗データを取得
            $shopDTOs = $this->shopModel->getAll();

            if (count($shopDTOs) > 0) {
                // DTOを配列に変換してセッションに保存
                $shops = [];
                foreach ($shopDTOs as $shopDTO) {
                    $shops[] = ['sh_id' => $shopDTO->sh_id];
                }

                // セッションに店舗データを保存
                $_SESSION['sh_id'] = $shops;

                // セッションの有効期限を設定（30分）
                $_SESSION['sh_id_expires'] = time() + 1800;

                header('Location: inp_sales.php', true, 301);
                exit;
            } else {
                return ['error' => '店舗データが見つかりませんでした。'];
            }

        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            return ['error' => 'データベースエラーが発生しました。'];
        }
    }

    /**
     * セッション有効期限チェック
     */
    public function checkSessionExpiry() {
        if (isset($_SESSION['sh_id_expires']) && time() > $_SESSION['sh_id_expires']) {
            unset($_SESSION['sh_id']);
            unset($_SESSION['sh_id_expires']);
            // セッション期限切れの場合、現在のページをリロード
            // inp_sales.phpが自動的に店舗選択処理を実行する
            header('Location: ' . $_SERVER['PHP_SELF'], true, 301);
            exit;
        }
    }

    /**
     * セッションから店舗データを取得
     */
    public function getShopsFromSession() {
        return $_SESSION['sh_id'] ?? [];
    }

    /**
     * 個別店舗情報をキャッシュ付きで取得
     * @param int $shopId 店舗ID
     * @return array|null 店舗情報
     */
    public function showShop($shopId) {
        $cacheKey = 'shop_info_' . $shopId;

        // セッションにキャッシュがあり、有効期限内なら返す
        if (isset($_SESSION[$cacheKey]) &&
            isset($_SESSION[$cacheKey . '_expires']) &&
            time() < $_SESSION[$cacheKey . '_expires']) {
            return $_SESSION[$cacheKey];
        }

        // キャッシュがないか期限切れならDBから取得
        $shopInfo = $this->shopModel->findShop($shopId);

        // セッションにキャッシュ（30分有効）
        $_SESSION[$cacheKey] = $shopInfo;
        $_SESSION[$cacheKey . '_expires'] = time() + 1800;

        return $shopInfo;
    }

    /**
     * 全店舗一覧をキャッシュ付きで取得
     * @return array 店舗DTOの配列
     */
    public function getCache() {
        // セッションにキャッシュがあり、有効期限内なら返す
        if (isset($_SESSION['all_shops']) &&
            isset($_SESSION['all_shops_expires']) &&
            time() < $_SESSION['all_shops_expires']) {
            return $_SESSION['all_shops'];
        }

        // キャッシュがないか期限切れならDBから取得
        $shops = $this->shopModel->getAll();

        // セッションにキャッシュ（30分有効）
        $_SESSION['all_shops'] = $shops;
        $_SESSION['all_shops_expires'] = time() + 1800;

        return $shops;
    }

    /**
     * 店舗情報のキャッシュをクリア
     * @param int|null $shopId 特定店舗のみクリア（nullで全クリア）
     */
    public function clearShopCache($shopId = null) {
        if ($shopId !== null) {
            // 特定店舗のキャッシュをクリア
            $cacheKey = 'shop_info_' . $shopId;
            unset($_SESSION[$cacheKey]);
            unset($_SESSION[$cacheKey . '_expires']);
        } else {
            // 全店舗のキャッシュをクリア
            unset($_SESSION['all_shops']);
            unset($_SESSION['all_shops_expires']);

            // 個別キャッシュもクリア
            foreach ($_SESSION as $key => $value) {
                if (strpos($key, 'shop_info_') === 0) {
                    unset($_SESSION[$key]);
                    unset($_SESSION[$key . '_expires']);
                }
            }
        }
    }
}
