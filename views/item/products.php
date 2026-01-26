<?php
require __DIR__ . '/../../config/init.php';
require __DIR__ . '/../../config/dbConnection.php';
require_once __DIR__ . '/../../controllers/ShopController.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$store_id = $_POST['store_id'] ?? null;

if (!$store_id) {
    header("Location: /fes/views/auth/store_select.php");
    exit;
}

// 店舗情報を取得
$sql_shop = "SELECT class, pr_name, place FROM shop WHERE sh_id = ?";
$stmt_shop = $pdo->prepare($sql_shop);
$stmt_shop->execute([$store_id]);
$shop = $stmt_shop->fetch();

if (!$shop) {
    header("Location: /fes/views/auth/store_select.php");
    exit;
}

// 商品一覧を取得
$sql = "
SELECT
    si.si_id,
    i.i_name,
    i.i_price
FROM shopitem si
JOIN item i ON si.i_id = i.i_id
WHERE si.sh_id = ?
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$store_id]);
$products = $stmt->fetchAll();
?>

<?php require __DIR__ . '/../common/header.php'; ?>

<p class="login-info-bar">
    ログイン中: <?= htmlspecialchars($_SESSION['student_id'], ENT_QUOTES, 'UTF-8') ?> |
    <a href="../auth/logout.php">ログアウト</a>
</p>

<div class="shop-info-box">
    <strong>選択中の店舗:</strong>
    <?= htmlspecialchars($shop['class'], ENT_QUOTES, 'UTF-8') ?>
    <?= htmlspecialchars($shop['pr_name'], ENT_QUOTES, 'UTF-8') ?>
    (<?= htmlspecialchars($shop['place'], ENT_QUOTES, 'UTF-8') ?>)
</div>

<h2>商品一覧</h2>
<?php foreach ($products as $p): ?>
<form method="post" action="../reserve/reserve.php">
    <input type="hidden" name="store_product_id" value="<?= htmlspecialchars($p['si_id'], ENT_QUOTES, 'UTF-8') ?>">
    <?= htmlspecialchars($p['i_name'], ENT_QUOTES, 'UTF-8') ?>（<?= htmlspecialchars($p['i_price'], ENT_QUOTES, 'UTF-8') ?>円）
    数量：
    <input type="number" name="quantity" min="1" required>
    <button type="submit">予約</button>
</form>
<?php endforeach; ?>

<nav class="page-nav" aria-label="ページナビゲーション">
    <a href="../auth/store_select.php">店舗選択に戻る</a>
    <a href="../common/index.html">メニューに戻る</a>
</nav>

<?php require __DIR__ . '/../common/footer.php'; ?>
