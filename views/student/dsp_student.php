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

<form method="get" class="filter-form">
    <label class="filter-label">並び替え：</label>
    <select name="order" class="filter-select" onchange="this.form.submit();">
        <option value="class" <?= $order === 'class' ? 'selected' : '' ?>>クラス</option>
        <option value="id" <?= $order === 'id' ? 'selected' : '' ?>>学籍番号</option>
        <option value="kana" <?= $order === 'kana' ? 'selected' : '' ?>>フリガナ</option>
    </select>

    <select name="dir" class="filter-select" onchange="this.form.submit();">
        <option value="asc" <?= $dir === 'asc' ? 'selected' : '' ?>>昇順</option>
        <option value="desc" <?= $dir === 'desc' ? 'selected' : '' ?>>降順</option>
    </select>
</form>

<table class="data-table card-table">
    <thead>
        <tr>
            <th scope="col">クラス</th>
            <th scope="col">学籍番号</th>
            <th scope="col">フリガナ</th>
            <th scope="col">氏名</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($students as $s): ?>
        <tr>
            <td data-label="クラス"><?= htmlspecialchars($s->class, ENT_QUOTES, 'UTF-8') ?></td>
            <td data-label="学籍番号"><?= htmlspecialchars($s->st_id, ENT_QUOTES, 'UTF-8') ?></td>
            <td data-label="フリガナ"><?= htmlspecialchars($s->kana, ENT_QUOTES, 'UTF-8') ?></td>
            <td data-label="氏名"><?= htmlspecialchars($s->name, ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<nav class="page-nav" aria-label="ページナビゲーション">
    <a href="../common/index.html">メニューに戻る</a>
</nav>

<?php require __DIR__ . '/../common/footer.php'; ?>
