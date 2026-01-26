<?php require __DIR__ . '/../common/header.php'; ?>

<h2>学生検索</h2>

<form action="st_search_output.php" method="post" class="filter-form">
    <div class="form-group">
        <label for="kana">フリガナを入力してください（ひらがな・カタカナ対応）</label><br>
        <input type="text" name="kana" id="kana" placeholder="例: タナカ" class="input-wide">
    </div>
    <input type="submit" value="検索" class="btn btn-primary">
</form>

<nav class="page-nav" aria-label="ページナビゲーション">
    <a href="../common/index.html">メニューに戻る</a>
</nav>

<?php require __DIR__ . '/../common/footer.php'; ?>
