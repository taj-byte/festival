<?php
/**
 * BaseDAO - すべてのDAOクラスの基底クラス
 * データベース操作の共通機能を提供し、コードの重複を排除
 */
abstract class BaseDAO {
    protected $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * SELECT文を実行してDTOの配列を返す（共通処理）
     * @param string $sql SQL文
     * @param array $params パラメータの配列
     * @param callable $dtoMapper 行データをDTOに変換する関数
     * @return array DTOの配列
     */
    protected function fetchAll($sql, $params, $dtoMapper) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $results = [];
        foreach ($stmt->fetchAll() as $row) {
            $results[] = $dtoMapper($row);
        }
        return $results;
    }

    /**
     * INSERT文を実行する（共通処理）
     * @param string $sql SQL文
     * @param array $params パラメータの配列
     * @return bool 成功した場合true
     */
    protected function executeInsert($sql, $params) {
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * UPDATE文を実行する（共通処理）
     * @param string $sql SQL文
     * @param array $params パラメータの配列
     * @return bool 成功した場合true
     */
    protected function executeUpdate($sql, $params) {
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * DELETE文を実行する（共通処理）
     * @param string $sql SQL文
     * @param array $params パラメータの配列
     * @return bool 成功した場合true
     */
    protected function executeDelete($sql, $params) {
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * 単一行を取得してDTOを返す
     * @param string $sql SQL文
     * @param array $params パラメータの配列
     * @param callable $dtoMapper 行データをDTOに変換する関数
     * @return object|null DTO、または見つからない場合null
     */
    protected function fetchOne($sql, $params, $dtoMapper) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $row = $stmt->fetch();
        if ($row === false) {
            return null;
        }

        return $dtoMapper($row);
    }

    /**
     * 最後に挿入されたIDを取得
     * @return string 最後に挿入されたID
     */
    protected function getLastInsertId() {
        return $this->pdo->lastInsertId();
    }

    /**
     * 単一の値を取得（COUNT, SUM, 単一カラム取得用）
     * @param string $sql SQL文
     * @param array $params パラメータの配列
     * @param int $columnIndex カラムインデックス（デフォルト: 0）
     * @return mixed 取得した値、見つからない場合はfalse
     */
    protected function fetchColumn($sql, $params = [], $columnIndex = 0) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn($columnIndex);
    }

    /**
     * 生の連想配列を返す（JOIN結果など、DTOに変換しない場合）
     * @param string $sql SQL文
     * @param array $params パラメータの配列
     * @return array 連想配列の配列
     */
    protected function fetchAssoc($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * 単一行を連想配列として取得
     * @param string $sql SQL文
     * @param array $params パラメータの配列
     * @return array|false 連想配列、見つからない場合はfalse
     */
    protected function fetchAssocOne($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
}
