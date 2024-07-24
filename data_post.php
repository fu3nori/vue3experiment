<?php
// データベース接続情報
$dsn = 'mysql:host=localhost;dbname=myprofile_form;charset=utf8';
$username = 'root';
$password = '';

// POSTデータをサニタイズする関数
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// ログファイルにエラーを書き込む関数
function log_error($message) {
    $logFile = __DIR__ . '/log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] ERROR: $message" . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// クライアントのドメインを取得
$clientDomain = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);

// サーバーのドメインを取得
$serverDomain = $_SERVER['SERVER_NAME'];

// ドメインを比較
if ($clientDomain === $serverDomain) {
    // 送信されたデータを受け取り、サニタイズ
    $zip = isset($_POST['zip']) ? sanitize_input($_POST['zip']) : null;
    $address1 = isset($_POST['address1']) ? sanitize_input($_POST['address1']) : null;
    $address2 = isset($_POST['address2']) ? sanitize_input($_POST['address2']) : null;
    $address3 = isset($_POST['address3']) ? sanitize_input($_POST['address3']) : null;
    $address4 = isset($_POST['address4']) ? sanitize_input($_POST['address4']) : null;
    $full_name = isset($_POST['full_name']) ? sanitize_input($_POST['full_name']) : null;
    $mail = isset($_POST['mail']) ? sanitize_input($_POST['mail']) : null;
    $message = isset($_POST['message']) ? sanitize_input($_POST['message']) : null;

    // 全てのデータが揃っているかチェック
    if ($zip && $address1 && $address2 && $address3 && $address4 && $full_name && $mail && $message) {
        try {
            // データベースに接続
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            // SQL文を準備
            $stmt = $pdo->prepare("INSERT INTO forms (zip, address1, address2, address3, address4, full_name, mail, message) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

            // SQLを実行
            if ($stmt->execute([$zip, $address1, $address2, $address3, $address4, $full_name, $mail, $message])) {
                echo "データが正常に保存されました。";
            } else {
                $errorInfo = $stmt->errorInfo();
                log_error('データの保存に失敗しました: ' . implode(' ', $errorInfo));
                echo "データの保存に失敗しました。";
            }
        } catch (PDOException $e) {
            log_error('データベースエラー: ' . $e->getMessage());
            echo "データベースエラーが発生しました。";
        }
    } else {
        echo "全てのデータが揃っていません。";
    }
} else {
    echo "許可されていないドメインからの送信です。";
}
?>
