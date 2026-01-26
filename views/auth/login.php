<?php
require __DIR__ . '/../../config/init.php';
require __DIR__ . '/../../config/dbConnection.php';
require_once __DIR__ . '/../../controllers/ShopController.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'] ?? '';
    $passcode   = $_POST['passcode'] ?? '';

    if ($student_id === '' || $passcode === '') {
        $error = '学生IDとパスコードを入力してください';
    } else {
        $sql = "SELECT * FROM student WHERE st_id = ? AND pasc = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$student_id, $passcode]);
        $student = $stmt->fetch();

        if ($student) {
            $_SESSION['student_id'] = $student['st_id'];
            //$_SESSION['student_name'] = $student['name']; // 任意
            header('Location: /fes/views/auth/store_select.php');
            exit;
        } else {
            $error = '学生IDまたはパスコードが正しくありません';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>学生ログイン</title>
<link rel="stylesheet" href="../css/festival.css">
</head>

<body class="login-body">
<div class="login-box">
    <h2>学生ログイン</h2>

    <?php if ($error): ?>
        <div class="login-error" role="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <label for="student_id">学生ID</label>
        <input type="text" name="student_id" id="student_id" placeholder="例: S001" autocomplete="username" required>

        <label for="passcode">パスコード</label>
        <div class="password-wrapper">
            <input type="password" name="passcode" id="passcode" placeholder="パスコードを入力" autocomplete="current-password" required>
            <button type="button" class="password-toggle" onclick="togglePassword()" aria-label="パスワード表示切替">&#128065;</button>
        </div>

        <button type="submit" class="btn btn-primary">ログイン</button>
    </form>

    <div class="back-link">
        <a href="../common/index.html">← トップページに戻る</a>
    </div>
</div>

<script>
function togglePassword() {
    const input = document.getElementById('passcode');
    const btn = document.querySelector('.password-toggle');
    if (input.type === 'password') {
        input.type = 'text';
        btn.textContent = '🔒';
    } else {
        input.type = 'password';
        btn.textContent = '👁';
    }
}
</script>
</body>
</html>