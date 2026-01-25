<?php
try {
    $dsn = 'mysql:host=localhost;dbname=festival;charset=utf8mb4';
    $username = 'root';
    $password = '';
    
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,  // 例外をスロー
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,  // デフォルトで連想配列
        PDO::ATTR_EMULATE_PREPARES => false,  // プリペアドステートメントをエミュレートしない
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    
} catch (PDOException $e) {
    error_log("Connection failed: " . $e->getMessage());
    die("データベース接続エラー");
}