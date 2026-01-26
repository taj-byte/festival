<?php
require __DIR__ . '/../../config/dbConnection.php';
require_once __DIR__ . '/../../controllers/StudentController.php';

// Controllerのインスタンスを作成
$studentController = new StudentController($pdo);

// Controller層に処理を委譲
$result = $studentController->display();
$students = $result['students'];
$order = $result['order'];
$dir = $result['dir'];
?>

<?php require __DIR__ . '/../common/header.php'; ?>

<h2>学生一覧</h2>

<form method="get">
    並び替え：
    <select name="order" onchange="this.form.submit();">
        <option value="class" <?= $order === 'class' ? 'selected' : '' ?>>クラス</option>
        <option value="id" <?= $order === 'id' ? 'selected' : '' ?>>学籍番号</option>
        <option value="kana" <?= $order === 'kana' ? 'selected' : '' ?>>フリガナ</option>
    </select>

    <select name="dir" onchange="this.form.submit();">
        <option value="asc" <?= $dir === 'asc' ? 'selected' : '' ?>>昇順</option>
        <option value="desc" <?= $dir === 'desc' ? 'selected' : '' ?>>降順</option>
    </select>
</form>

<br>

<table border="1" cellpadding="5">
    <tr>
        <th>クラス</th>
        <th>学籍番号</th>
        <th>フリガナ</th>
        <th>氏名</th>
    </tr>

    <?php foreach ($students as $s): ?>
    <tr>
        <td><?= htmlspecialchars($s->class, ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars($s->st_id, ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars($s->kana, ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars($s->name, ENT_QUOTES, 'UTF-8') ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<p><a href="../common/index.html">メニューに戻る</a></p>

<?php require __DIR__ . '/../common/footer.php'; ?>
