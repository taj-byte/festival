<?php
/**
 * CsrfToken - CSRF対策トークンの生成と検証を行うユーティリティクラス
 */
class CsrfToken {

    /**
     * CSRFトークンを生成してセッションに保存
     * @return string 生成されたトークン
     */
    public static function generate() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // ランダムなトークンを生成
        $token = bin2hex(random_bytes(32));

        // セッションに保存
        $_SESSION['csrf_token'] = $token;

        return $token;
    }

    /**
     * CSRFトークンを検証
     * @param string $token 検証するトークン
     * @return bool トークンが有効な場合true
     */
    public static function validate($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // セッションにトークンが存在しない場合は無効
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }

        // トークンが一致するか確認（タイミング攻撃対策でhash_equals使用）
        $isValid = hash_equals($_SESSION['csrf_token'], $token);

        // 使用済みトークンを削除（ワンタイムトークン）
        if ($isValid) {
            unset($_SESSION['csrf_token']);
        }

        return $isValid;
    }

    /**
     * セッションに保存されているトークンを取得
     * @return string|null トークン（存在しない場合はnull）
     */
    public static function get() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return $_SESSION['csrf_token'] ?? null;
    }
}
