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
<title>学生ログイン</title>
<style>
body {
    font-family: sans-serif;
    background: #f5f5f5;
}
.login-box {
    width: 350px;
    margin: 100px auto;
    background: #fff;
    padding: 20px;
    border-radius: 6px;
}
h2 {
    text-align: center;
}
label {
    display: block;
    margin-top: 10px;
}
input {
    width: 100%;
    padding: 8px;
    margin-top: 5px;
}
button {
    width: 100%;
    margin-top: 15px;
    padding: 10px;
}
.error {
    color: red;
    margin-top: 10px;
    text-align: center;
}
.back-link {
    text-align: center;
    margin-top: 15px;
}
.back-link a {
    color: #666;
    text-decoration: none;
}
.back-link a:hover {
    text-decoration: underline;
}
</style>
</head>

<body>
<div class="login-box">
    <h2>学生ログイン</h2>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <label>学生ID</label>
        <input type="text" name="student_id" required>

        <label>パスコード</label>
        <input type="password" name="passcode" required>

        <button type="submit">ログイン</button>
    </form>

    <div class="back-link">
        <a href="../common/index.html">← トップページに戻る</a>
    </div>
</div>
</body>
</html>