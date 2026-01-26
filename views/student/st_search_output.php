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
    <p class="text-error-bold">
        <?= htmlspecialchars($result['message'], ENT_QUOTES, 'UTF-8') ?>
    </p>
    <nav class="page-nav" aria-label="ページナビゲーション">
        <a href="st_search.php">検索画面に戻る</a>
    </nav>
<?php else: ?>
    <p>検索条件：フリガナ「<?= htmlspecialchars($kana, ENT_QUOTES, 'UTF-8') ?>」</p>

    <?php if (!empty($result['data'])): ?>
        <table class="data-table card-table">
            <thead>
                <tr>
                    <th scope="col">学籍番号</th>
                    <th scope="col">クラス</th>
                    <th scope="col">氏名</th>
                    <th scope="col">フリガナ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result['data'] as $student): ?>
                <tr>
                    <td data-label="学籍番号"><?= htmlspecialchars($student->st_id, ENT_QUOTES, 'UTF-8') ?></td>
                    <td data-label="クラス"><?= htmlspecialchars($student->class, ENT_QUOTES, 'UTF-8') ?></td>
                    <td data-label="氏名"><?= htmlspecialchars($student->name, ENT_QUOTES, 'UTF-8') ?></td>
                    <td data-label="フリガナ"><?= htmlspecialchars($student->kana, ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>該当する学生はいません。</p>
    <?php endif; ?>

    <nav class="page-nav" aria-label="ページナビゲーション">
        <a href="st_search.php">検索画面に戻る</a>
    </nav>
<?php endif; ?>

<?php require __DIR__ . '/../common/footer.php'; ?>
