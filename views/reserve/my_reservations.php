<?php
require __DIR__ . '/../../config/init.php';
require __DIR__ . '/../../config/dbConnection.php';
require_once __DIR__ . '/../../controllers/ReserveController.php';

/* セッションチェック */
if (!isset($_SESSION['student_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

/* Controllerのインスタンスを作成 */
$ctrl = new ReserveController($pdo);

/* 学生の予約一覧を取得（ステータスラベル付き） */
$student_id = $_SESSION['student_id'];
$reservations = $ctrl->getByStudentLabeled($student_id);
?>

<?php require __DIR__ . '/../common/header.php'; ?>

<p class="login-info-bar">
    ログイン中: <?= htmlspecialchars($_SESSION['student_id'], ENT_QUOTES, 'UTF-8') ?> |
    <a href="../auth/logout.php">ログアウト</a>
</p>

<h2>予約一覧（全店舗）</h2>

<table class="data-table card-table">
    <thead>
        <tr>
            <th scope="col">日時</th>
            <th scope="col">企画名</th>
            <th scope="col">場所</th>
            <th scope="col">商品名</th>
            <th scope="col">数量</th>
            <th scope="col">状態</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($reservations as $r): ?>
        <tr>
            <td data-label="日時"><?= htmlspecialchars($r['datetime'], ENT_QUOTES, 'UTF-8') ?></td>
            <td data-label="企画名"><?= htmlspecialchars($r['pr_name'], ENT_QUOTES, 'UTF-8') ?></td>
            <td data-label="場所"><?= htmlspecialchars($r['place'], ENT_QUOTES, 'UTF-8') ?></td>
            <td data-label="商品名"><?= htmlspecialchars($r['i_name'], ENT_QUOTES, 'UTF-8') ?></td>
            <td data-label="数量"><?= htmlspecialchars($r['num'], ENT_QUOTES, 'UTF-8') ?></td>
            <td data-label="状態"><?= htmlspecialchars($r['situation_label'], ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<nav class="page-nav" aria-label="ページナビゲーション">
    <a href="../auth/store_select.php">別の店舗で予約</a>
    <a href="../common/index.html">メニューに戻る</a>
</nav>

<?php require __DIR__ . '/../common/footer.php'; ?>
