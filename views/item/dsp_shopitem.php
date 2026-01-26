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
            <h3 class="shop-header">
                <?= htmlspecialchars($shopData['shop']->pr_name, ENT_QUOTES, 'UTF-8') ?>
                <span class="shop-header-count">
                    (<?= count($shopData['items']) ?>件)
                </span>
            </h3>

            <?php if (empty($shopData['items'])): ?>
                <p class="no-items-text">登録されている商品はありません</p>
            <?php else: ?>
                <table class="data-table card-table">
                    <thead>
                        <tr>
                            <th scope="col" class="col-id">商品ID</th>
                            <th scope="col">商品名</th>
                            <th scope="col" class="col-price">価格</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($shopData['items'] as $shopItem): ?>
                            <tr>
                                <td class="text-center" data-label="商品ID">
                                    <?= htmlspecialchars($shopItem['item']->i_id, ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td data-label="商品名">
                                    <?= htmlspecialchars($shopItem['item']->i_name, ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td class="text-right" data-label="価格">
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

<nav class="page-nav" aria-label="ページナビゲーション">
    <a href="inp_shopitem.php">店舗商品追加</a>
    <a href="../common/index.html">メニューに戻る</a>
</nav>

<?php require __DIR__ . '/../common/footer.php'; ?>
