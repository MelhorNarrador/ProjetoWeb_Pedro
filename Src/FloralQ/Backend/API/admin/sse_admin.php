<?php
require_once "../../Utils/init.php";
require_once "../../Middleware/auth.php";

$user = requireAdmin();
$userId = $user["user_id"];

session_write_close();
set_time_limit(0);

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no');

@ob_end_clean();
ob_implicit_flush(true);

function sendEvent(string $event, array $data): void
{
    echo "event: $event\n";
    echo "data: " . json_encode($data) . "\n\n";
    @flush();
}

$startTime = time();
$maxDuration = 5 * 60;
$pollInterval = 5;

$lastSignature = null;

sendEvent('connected', ['user_id' => $userId]);

while (true) {

    if (connection_aborted()) {
        exit;
    }

    if (time() - $startTime > $maxDuration) {
        exit;
    }

    try {
        $stmt = $pdo->prepare("
        SELECT
          (SELECT COUNT(*) FROM user_account) AS u,
          (SELECT COUNT(*) FROM device)AS d,
          (SELECT COUNT(*) FROM plant)AS p
    ");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Assinatura concatenada: "5|12|34"
        $signature = "{$row['u']}|{$row['d']}|{$row['p']}";

        if ($signature !== $lastSignature) {
            sendEvent('admin-update', [
                'users' => (int)$row['u'],
                'devices' => (int)$row['d'],
                'plants' => (int)$row['p'],
            ]);
            $lastSignature = $signature;
        } else {
            echo ": ping\n\n";
            @flush();
        }
    } catch (PDOException $e) {
        error_log("[SSE Admin] " . $e->getMessage());
        exit;
    }

    sleep($pollInterval);
}
