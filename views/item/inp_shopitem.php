<?php
require __DIR__ . '/../../config/init.php';
require __DIR__ . '/../../config/dbConnection.php';
require_once __DIR__ . '/../../controllers/ShopItemController.php';

// Controllerのインスタンスを作成
$ctrl = new ShopItemController($pdo);

// 入力用データを取得（年度別グループ化付き）
$data = $ctrl->prepareInput();
$shops = $data['shops'];
$itemsByYear = $data['itemsByYear'];
?>

<?php require __DIR__ . '/../common/header.php'; ?>

<h2>店舗商品登録</h2>

<div class="info-box">
    <strong>ℹ 店舗商品登録について</strong><br>
    店舗で販売する商品を登録します。1つの店舗に複数の商品を一度に登録できます。<br>
    <small class="hint-text">※既に登録済みの商品は自動的にスキップされます</small>
</div>

<form action="add_shopitem.php" method="post">
    <div class="form-group-lg">
        <label for="shop_id"><strong>店舗を選択:</strong></label><br>
        <select name="shop_id" id="shop_id" required class="select-wide">
            <option value="">-- 店舗を選択してください --</option>
            <?php foreach ($shops as $shop): ?>
                <option value="<?= htmlspecialchars($shop->sh_id, ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($shop->pr_name, ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group-lg">
        <label><strong>販売する商品を選択:</strong></label><br>
        <small class="hint-text">複数選択可能です（Ctrl/Cmdキーを押しながらクリック、またはチェックボックスで選択）</small>
        <div class="item-scroll-area">
            <?php foreach ($itemsByYear as $year => $yearItems): ?>
                <fieldset class="year-fieldset">
                    <legend class="year-legend">
                        <?= htmlspecialchars($year, ENT_QUOTES, 'UTF-8') ?>年度の商品
                    </legend>
                    <?php foreach ($yearItems as $item): ?>
                        <div class="checkbox-item">
                            <label class="checkbox-label">
                                <input type="checkbox"
                                       name="item_ids[]"
                                       value="<?= htmlspecialchars($item->i_id, ENT_QUOTES, 'UTF-8') ?>"
                                       class="checkbox-input">
                                <?= htmlspecialchars($item->i_name, ENT_QUOTES, 'UTF-8') ?>
                                <span class="price-text">(¥<?= number_format($item->i_price) ?>)</span>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </fieldset>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="form-actions">
        <input type="submit" value="登録" class="submit-btn">
        <a href="dsp_shopitem.php" class="cancel-link">キャンセル</a>
    </div>
</form>

<nav class="page-nav" aria-label="ページナビゲーション">
    <a href="dsp_shopitem.php">店舗商品一覧</a>
    <a href="../common/index.html">メニューに戻る</a>
</nav>

<?php require __DIR__ . '/../common/footer.php'; ?>
