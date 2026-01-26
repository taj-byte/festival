<?php
require __DIR__ . '/../../config/dbConnection.php';
require_once __DIR__ . '/../../controllers/ShopItemController.php';

// Controllerのインスタンスを作成
$ctrl = new ShopItemController($pdo);

// Controller層に処理を委譲
$result = $ctrl->add();

// エラー表示（成功時はController内でリダイレクト）
?>
<?php require __DIR__ . '/../common/header.php'; ?>

<h2>店舗商品登録結果</h2>

<p class="text-error"><?= htmlspecialchars($result['message'], ENT_QUOTES, 'UTF-8') ?></p>

<?php if (isset($result['added']) && isset($result['skipped'])): ?>
    <ul>
        <li>追加件数: <?= $result['added'] ?>件</li>
        <li>スキップ件数: <?= $result['skipped'] ?>件</li>
    </ul>
<?php endif; ?>

<p><a href="inp_shopitem.php">戻る</a> | <a href="dsp_shopitem.php">店舗商品一覧</a></p>

<?php require __DIR__ . '/../common/footer.php'; ?>
