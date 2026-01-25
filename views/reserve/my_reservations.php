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

<p style="text-align: right; font-size: 0.9em;">
    ログイン中: <?= htmlspecialchars($_SESSION['student_id'], ENT_QUOTES, 'UTF-8') ?> |
    <a href="../auth/logout.php">ログアウト</a>
</p>

<h2>予約一覧（全店舗）</h2>

<table border="1" cellpadding="5">
<tr>
    <th>日時</th>
    <th>企画名</th>
    <th>場所</th>
    <th>商品名</th>
    <th>数量</th>
    <th>状態</th>
</tr>

<?php foreach ($reservations as $r): ?>
<tr>
    <td><?= htmlspecialchars($r['datetime'], ENT_QUOTES, 'UTF-8') ?></td>
    <td><?= htmlspecialchars($r['pr_name'], ENT_QUOTES, 'UTF-8') ?></td>
    <td><?= htmlspecialchars($r['place'], ENT_QUOTES, 'UTF-8') ?></td>
    <td><?= htmlspecialchars($r['i_name'], ENT_QUOTES, 'UTF-8') ?></td>
    <td><?= htmlspecialchars($r['num'], ENT_QUOTES, 'UTF-8') ?></td>
    <td><?= htmlspecialchars($r['situation_label'], ENT_QUOTES, 'UTF-8') ?></td>
</tr>
<?php endforeach; ?>

</table>

<p>
    <a href="../auth/store_select.php">別の店舗で予約</a> |
    <a href="../common/index.html">メニューに戻る</a>
</p>

<?php require __DIR__ . '/../common/footer.php'; ?>
