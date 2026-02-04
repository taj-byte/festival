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

}
