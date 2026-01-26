<?php
require __DIR__ . '/../../config/dbConnection.php';
require_once __DIR__ . '/../../controllers/StudentController.php';

// Controllerのインスタンスを作成
$studentController = new StudentController($pdo);

// Controller層に処理を委譲
$result = $studentController->search();
$kana = $_POST['kana'] ?? '';
?>

<?php require __DIR__ . '/../common/header.php'; ?>

<h2>検索結果</h2>

<?php if (!$result['success']): ?>
    <p class="error" style="color: red; font-weight: bold;">
        <?= htmlspecialchars($result['message'], ENT_QUOTES, 'UTF-8') ?>
    </p>
    <p><a href="st_search.php">← 検索画面に戻る</a></p>
<?php else: ?>
    <p>検索条件：フリガナ「<?= htmlspecialchars($kana, ENT_QUOTES, 'UTF-8') ?>」</p>

    <?php if (!empty($result['data'])): ?>
        <table border="1" cellpadding="5">
            <tr>
                <th>学籍番号</th>
                <th>クラス</th>
                <th>氏名</th>
                <th>フリガナ</th>
            </tr>
            <?php foreach ($result['data'] as $student): ?>
            <tr>
                <td><?= htmlspecialchars($student->st_id, ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($student->class, ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($student->name, ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($student->kana, ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>該当する学生はいません。</p>
    <?php endif; ?>

    <br>
    <p><a href="st_search.php">← 検索画面に戻る</a></p>
<?php endif; ?>

<?php require __DIR__ . '/../common/footer.php'; ?>
