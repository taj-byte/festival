<?php require __DIR__ . '/../common/header.php'; ?>

<h2>学生検索</h2>

<p>フリガナを入力してください（ひらがな・カタカナ対応）</p>

<form action="st_search_output.php" method="post">
    <input type="text" name="kana">
    <input type="submit" value="検索">
</form>

<p><a href="../common/index.html">メニューに戻る</a></p>

<?php require __DIR__ . '/../common/footer.php'; ?>
