<?php require __DIR__ . '/../common/header.php'; ?>

<h2>店舗登録</h2>
<form action="add_shop.php" method="POST">
    <div class="form-group">
        <label for="sh_year">年度:</label><br>
        <input type="number" name="sh_year" id="sh_year" required>
    </div>
    <div class="form-group">
        <label for="sh_class">クラス名:</label><br>
        <input type="text" name="sh_class" id="sh_class" required
               pattern="[A-Z0-9]+" title="半角英数字で入力してください">
    </div>
    <div class="form-group">
        <label for="sh_name">店舗名:</label><br>
        <input type="text" name="sh_name" id="sh_name" required>
    </div>
    <div class="form-group">
        <label for="i_place">場所:</label><br>
        <input type="text" name="i_place" id="i_place" required>
    </div>

    <input type="submit" value="追加" class="btn btn-primary">
</form>

<nav class="page-nav" aria-label="ページナビゲーション">
    <a href="../common/index.html">メニューに戻る</a>
</nav>

<?php require __DIR__ . '/../common/footer.php'; ?>