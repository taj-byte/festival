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

<div style="background-color: #d1ecf1; padding: 10px; margin-bottom: 15px; border-left: 4px solid #0c5460;">
    <strong>ℹ 店舗商品登録について</strong><br>
    店舗で販売する商品を登録します。1つの店舗に複数の商品を一度に登録できます。<br>
    <small style="color: #666;">※既に登録済みの商品は自動的にスキップされます</small>
</div>

<form action="add_shopitem.php" method="post">
    <div style="margin-bottom: 15px;">
        <label for="shop_id"><strong>店舗を選択:</strong></label><br>
        <select name="shop_id" id="shop_id" required style="padding: 5px; font-size: 14px; width: 300px;">
            <option value="">-- 店舗を選択してください --</option>
            <?php foreach ($shops as $shop): ?>
                <option value="<?= htmlspecialchars($shop->sh_id, ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($shop->pr_name, ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div style="margin-bottom: 15px;">
        <label><strong>販売する商品を選択:</strong></label><br>
        <small style="color: #666;">複数選択可能です（Ctrl/Cmdキーを押しながらクリック、またはチェックボックスで選択）</small>
        <div style="margin-top: 10px; max-height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background-color: #f9f9f9;">
            <?php foreach ($itemsByYear as $year => $yearItems): ?>
                <fieldset style="margin-bottom: 15px; border: 1px solid #ccc; padding: 10px;">
                    <legend style="font-weight: bold; color: #007bff;">
                        <?= htmlspecialchars($year, ENT_QUOTES, 'UTF-8') ?>年度の商品
                    </legend>
                    <?php foreach ($yearItems as $item): ?>
                        <div style="margin-bottom: 5px;">
                            <label style="cursor: pointer;">
                                <input type="checkbox"
                                       name="item_ids[]"
                                       value="<?= htmlspecialchars($item->i_id, ENT_QUOTES, 'UTF-8') ?>"
                                       style="margin-right: 5px;">
                                <?= htmlspecialchars($item->i_name, ENT_QUOTES, 'UTF-8') ?>
                                <span style="color: #666;">(¥<?= number_format($item->i_price) ?>)</span>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </fieldset>
            <?php endforeach; ?>
        </div>
    </div>

    <div style="margin-top: 15px;">
        <input type="submit" value="登録" style="padding: 10px 20px; font-size: 14px; cursor: pointer;">
        <a href="dsp_shopitem.php" style="margin-left: 10px;">キャンセル</a>
    </div>
</form>

<p style="margin-top: 20px;">
    <a href="dsp_shopitem.php">店舗商品一覧</a> | <a href="../common/index.html">メニューに戻る</a>
</p>

<?php require __DIR__ . '/../common/footer.php'; ?>
