<?php
require __DIR__ . '/../../config/init.php';
require __DIR__ . '/../../config/dbConnection.php';
require_once __DIR__ . '/../../controllers/ShopItemController.php';

// Controllerのインスタンスを作成
$ctrl = new ShopItemController($pdo);

// Controller層に処理を委譲
$data = $ctrl->list();
$shopItemsGrouped = $data['shopItemsGrouped'];
?>

<?php require __DIR__ . '/../common/header.php'; ?>

<h2>店舗商品一覧</h2>

<?php if (empty($shopItemsGrouped)): ?>
    <p>店舗商品が登録されていません。</p>
<?php else: ?>
    <?php foreach ($shopItemsGrouped as $shopId => $shopData): ?>
        <?php if ($shopData['shop']): ?>
            <h3 style="background-color: #e9ecef; padding: 8px; margin-top: 20px; border-left: 4px solid #28a745;">
                <?= htmlspecialchars($shopData['shop']->pr_name, ENT_QUOTES, 'UTF-8') ?>
                <span style="font-size: 14px; font-weight: normal; color: #666;">
                    (<?= count($shopData['items']) ?>件)
                </span>
            </h3>

            <?php if (empty($shopData['items'])): ?>
                <p style="margin-left: 20px; color: #666;">登録されている商品はありません</p>
            <?php else: ?>
                <table border="1" style="border-collapse: collapse; width: 100%; margin-bottom: 20px;">
                    <thead>
                        <tr>
                            <th style="width: 80px;">商品ID</th>
                            <th>商品名</th>
                            <th style="width: 120px;">価格</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($shopData['items'] as $shopItem): ?>
                            <tr>
                                <td style="text-align: center;">
                                    <?= htmlspecialchars($shopItem['item']->i_id, ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($shopItem['item']->i_name, ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td style="text-align: right;">
                                    ¥<?= number_format($shopItem['item']->i_price) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>

<p>
    <a href="inp_shopitem.php">店舗商品追加</a> |
    <a href="../common/index.html">メニューに戻る</a>
</p>

<?php require __DIR__ . '/../common/footer.php'; ?>
