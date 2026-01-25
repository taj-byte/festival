<?php require __DIR__ . '/../common/header.php'; ?>

<h2>店舗登録</h2>
<form action="add_shop.php" method="POST">

    年度: <input type="number" name="sh_year" required><br>
    クラス名: <input type="text" name="sh_class" required><br>
    店舗名: <input type="text" name="sh_name" required><br>
    場所: <input type="text" name="i_place" required><br>

    <input type="submit" value="追加">

</form>

<p><a href="../common/index.html">メニューに戻る</a></p>

<?php require __DIR__ . '/../common/footer.php'; ?>