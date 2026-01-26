<?php
/**
 * 共通初期化ファイル
 * セッション開始前にDTOクラスをロードする
 */

// アプリケーション設定を読み込み
require_once __DIR__ . '/settings.php';

// DTOクラスを事前ロード（セッションのシリアライズ/アンシリアライズに必要）
require_once __DIR__ . '/../dto/ItemDTO.php';
require_once __DIR__ . '/../dto/ShopDTO.php';
require_once __DIR__ . '/../dto/SalesDTO.php';
require_once __DIR__ . '/../dto/SalesDetailDTO.php';
require_once __DIR__ . '/../dto/StudentDTO.php';
require_once __DIR__ . '/../dto/ReserveDTO.php';
require_once __DIR__ . '/../dto/ShopItemDTO.php';

// セッション開始
session_start();
