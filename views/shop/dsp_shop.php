<?php
require __DIR__ . '/../../config/dbConnection.php';
require __DIR__ . '/../../config/settings.php';
require_once __DIR__ . '/../../controllers/ShopController.php';

// Controllerのインスタンスを作成
$ShopController = new ShopController($pdo);

// Controller層に処理を委譲（現在年度のみ）
$shops = $ShopController->displayByFy(CURRENT_FY);
?>

<?php require __DIR__ . '/../common/header.php'; ?>

<h2>店舗一覧</h2>
<table class="data-table card-table">
    <thead>
        <tr>
            <th scope="col">店舗ID</th>
            <th scope="col">年度</th>
            <th scope="col">クラス</th>
            <th scope="col">企画名</th>
            <th scope="col">場所</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($shops as $shop): ?>
        <tr>
            <td data-label="店舗ID"><?= htmlspecialchars($shop->sh_id, ENT_QUOTES, 'UTF-8') ?></td>
            <td data-label="年度"><?= htmlspecialchars($shop->fy, ENT_QUOTES, 'UTF-8') ?></td>
            <td data-label="クラス"><?= htmlspecialchars($shop->class, ENT_QUOTES, 'UTF-8') ?></td>
            <td data-label="企画名"><?= htmlspecialchars($shop->pr_name, ENT_QUOTES, 'UTF-8') ?></td>
            <td data-label="場所"><?= htmlspecialchars($shop->place, ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<nav class="page-nav" aria-label="ページナビゲーション">
    <a href="../common/index.html">メニューに戻る</a>
</nav>

<?php require __DIR__ . '/../common/footer.php'; ?>