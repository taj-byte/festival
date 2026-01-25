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
<table border="1">
    <tr>
        <th>店舗ID</th>
        <th>年度</th>
        <th>クラス</th>
        <th>企画名</th>
        <th>場所</th>
    </tr>
    <?php foreach($shops as $shop): ?>
    <tr>
        <td><?= htmlspecialchars($shop->sh_id, ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars($shop->fy, ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars($shop->class, ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars($shop->pr_name, ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars($shop->place, ENT_QUOTES, 'UTF-8') ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<p><a href="../common/index.html">メニューに戻る</a></p>

<?php require __DIR__ . '/../common/footer.php'; ?>