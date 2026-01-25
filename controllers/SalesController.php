<?php
require_once __DIR__ . '/../models/SalesModel.php';
require_once __DIR__ . '/../models/ShopModel.php';
require_once __DIR__ . '/../dao/SalesDAO.php';
require_once __DIR__ . '/../dao/ShopDAO.php';
require_once __DIR__ . '/../utils/CsrfToken.php';
require_once __DIR__ . '/../config/settings.php';

/**
 * SalesController - 売上管理のコントローラー層
 * リクエスト処理、レスポンス生成を担当
 */
class SalesController {
    private $salesModel;
    private $shopModel;
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $salesDAO = new SalesDAO($pdo);
        $this->salesModel = new SalesModel($salesDAO);
        $shopDAO = new ShopDAO($pdo);
        $this->shopModel = new ShopModel($shopDAO);
    }

    /**
     * 売上追加処理
     */
    public function addSales() {
        // リクエストパラメータ取得
        $shopId = $_POST['shop_id'] ?? '';
        $salesData = $_POST;
        $reserveId = $_POST['reserve_id'] ?? null;

        try {
            // トランザクション開始
            $this->pdo->beginTransaction();

            // 1. 売上登録
            $result = $this->salesModel->createSales($shopId, $salesData);

            if (!$result['success']) {
                throw new Exception($result['message'] ?? '売上登録に失敗しました');
            }

            // 2. 予約IDがある場合、予約状態を「来店(1)」に更新
            if ($reserveId) {
                require_once __DIR__ . '/ReserveController.php';
                $reserveController = new ReserveController($this->pdo);

                $updateResult = $reserveController->updStatus(
                    $reserveId,
                    $shopId,
                    1  // situation = 1 (来店)
                );

                // 予約更新が失敗したらロールバック
                if (!$updateResult['success']) {
                    throw new Exception('予約状態の更新に失敗しました: ' . $updateResult['message']);
                }
            }

            // 両方成功したらコミット
            $this->pdo->commit();

            header('Location: dsp_sales.php?s_id=' . $result['sales_id']);
            exit();

        } catch (Exception $e) {
            // エラー時はロールバック
            $this->pdo->rollBack();

            // エラーメッセージをセッションに保存
            $_SESSION['error_message'] = $e->getMessage();

            // エラー画面またはフォームにリダイレクト
            header('Location: add_sales.php?shop_id=' . urlencode($shopId));
            exit();
        }
    }


    /**
     * 売上一覧表示処理
     */
    public function listSales() {
        // リクエストから年度パラメータを取得（デフォルトは現在年度）
        $fy = isset($_GET['fy']) ? ($_GET['fy'] !== '' ? (int)$_GET['fy'] : null) : CURRENT_FY;

        // 店舗ごとの売上合計を取得（降順）
        $salesSummary = $this->salesModel->getShopSum($fy);

        // 全店舗の総売上を計算
        $grandTotal = $this->salesModel->calcGrandTotal($fy);

        // 利用可能な年度一覧を取得（キャッシュ付き）
        $availableYears = $this->prepareYearFilter();

        // 現在選択中の年度
        $selectedYear = $fy;

        // ビューにデータを渡す
        return compact('salesSummary', 'grandTotal', 'availableYears', 'selectedYear');
    }

    /**
     * 売上詳細表示処理
     */
    public function showDetail() {
        // リクエストパラメータから店舗IDを取得
        $shopId = $_GET['sh_id'] ?? null;

        if (empty($shopId)) {
            // 店舗IDが指定されていない場合はエラー
            return [
                'error' => true,
                'message' => '店舗IDが指定されていません',
                'shopInfo' => null,
                'details' => [],
                'total' => 0,
                'reservations' => []
            ];
        }

        // 店舗情報を取得（キャッシュ付き）
        $shopInfo = $this->showShop($shopId);

        // 特定店舗の売上明細を取得
        $details = $this->salesModel->getShopDetails($shopId);

        // 売上合計を計算（明細データから）
        $total = 0;
        foreach ($details as $detail) {
            $total += $detail['subtotal'];
        }

        // 店舗の予約情報を取得
        require_once __DIR__ . '/ReserveController.php';
        $reserveController = new ReserveController($this->pdo);
        $reservations = $reserveController->getByShop($shopId);

        // ビューにデータを渡す
        return compact('shopId', 'shopInfo', 'details', 'total', 'reservations');
    }

    /**
     * 注文納品状況表示処理
     */
    public function showPending() {
        // リクエストパラメータから店舗IDを取得
        $shopId = $_GET['sh_id'] ?? null;

        if (empty($shopId)) {
            // 店舗IDが指定されていない場合はエラー
            return [
                'error' => true,
                'message' => '店舗IDが指定されていません',
                'shopInfo' => null,
                'pendingOrders' => []
            ];
        }

        // 店舗情報を取得（キャッシュ付き）
        $shopInfo = $this->showShop($shopId);

        // 納品待ち注文を取得
        $pendingOrders = $this->salesModel->getPending($shopId);

        // ビューにデータを渡す
        return compact('shopId', 'shopInfo', 'pendingOrders');
    }

    /**
     * 納品処理
     */
    public function deliverOrder() {
        // CSRFトークンの検証
        $csrfToken = $_POST['csrf_token'] ?? null;
        if (!CsrfToken::validate($csrfToken)) {
            $_SESSION['error_message'] = '不正なリクエストです（CSRFトークンが無効）';
            header('Location: order_status.php?sh_id=' . ($_POST['sh_id'] ?? ''));
            exit();
        }

        // リクエストパラメータから売上IDと店舗IDを取得
        $salesId = $_POST['s_id'] ?? null;
        $shopId = $_POST['sh_id'] ?? null;

        if (empty($salesId) || empty($shopId)) {
            $_SESSION['error_message'] = '必要なパラメータが不足しています';
            header('Location: order_status.php?sh_id=' . $shopId);
            exit();
        }

        try {
            // トランザクション開始
            $this->pdo->beginTransaction();

            // 売上IDと店舗IDの整合性チェック
            $sales = $this->salesModel->getSalesById($salesId);

            if (empty($sales)) {
                throw new Exception('指定された売上IDが存在しません');
            }

            // 売上が指定された店舗のものか確認
            if ($sales[0]->sh_id != $shopId) {
                throw new Exception('不正な操作です（店舗IDが一致しません）');
            }

            // すでに納品済みかチェック
            if ($sales[0]->situation == 1) {
                throw new Exception('この注文はすでに納品済みです');
            }

            // 納品済みに更新
            $result = $this->salesModel->markDelivered($salesId);

            if (!$result) {
                throw new Exception('データベースの更新に失敗しました');
            }

            // 成功したらコミット
            $this->pdo->commit();

            // 成功したら納品状況画面にリダイレクト
            header('Location: order_status.php?sh_id=' . $shopId);
            exit();

        } catch (Exception $e) {
            // エラー時はロールバック
            $this->pdo->rollBack();

            // エラーメッセージをセッションに保存
            $_SESSION['error_message'] = $e->getMessage();

            // エラー画面にリダイレクト
            header('Location: order_status.php?sh_id=' . $shopId);
            exit();
        }
    }

    /**
     * 店舗別の午前・午後・合計売上を表示
     */
    public function showSalesByPeriod() {
        // リクエストから年度パラメータを取得（デフォルトは現在年度）
        $fy = isset($_GET['fy']) ? ($_GET['fy'] !== '' ? (int)$_GET['fy'] : null) : CURRENT_FY;

        $shopSales = $this->salesModel->getByPeriod($fy);

        // 全店舗の合計を計算
        $totalMorning = 0;
        $totalAfternoon = 0;
        $grandTotal = 0;

        foreach ($shopSales as $shop) {
            $totalMorning += $shop['morning_sales'] ?? 0;
            $totalAfternoon += $shop['afternoon_sales'] ?? 0;
            $grandTotal += $shop['total_sales'] ?? 0;
        }

        // 利用可能な年度一覧を取得（キャッシュ付き）
        $availableYears = $this->prepareYearFilter();

        // 現在選択中の年度
        $selectedYear = $fy;

        return compact('shopSales', 'totalMorning', 'totalAfternoon', 'grandTotal', 'availableYears', 'selectedYear');
    }

    /**
     * 利用可能な年度一覧をキャッシュ付きで取得
     * @return array 年度の配列
     */
    private function prepareYearFilter() {
        // セッションにキャッシュがあり、有効期限内なら返す
        if (isset($_SESSION['fiscal_years']) &&
            isset($_SESSION['fiscal_years_expires']) &&
            time() < $_SESSION['fiscal_years_expires']) {
            return $_SESSION['fiscal_years'];
        }

        // キャッシュがないか期限切れならDBから取得
        $years = $this->salesModel->getYears();

        // セッションにキャッシュ（1時間有効、年度は変更頻度が低いため長め）
        $_SESSION['fiscal_years'] = $years;
        $_SESSION['fiscal_years_expires'] = time() + 3600;

        return $years;
    }

    /**
     * 年度一覧のキャッシュをクリア
     */
    public function clearFiscalYearCache() {
        unset($_SESSION['fiscal_years']);
        unset($_SESSION['fiscal_years_expires']);
    }

    /**
     * 個別店舗情報をキャッシュ付きで取得
     * @param int $shopId 店舗ID
     * @return array|null 店舗情報
     */
    private function showShop($shopId) {
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
}
