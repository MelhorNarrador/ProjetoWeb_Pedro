<?php
require_once "../Utils/init.php";
require_once "../Middleware/auth.php";

$user = requireAuth();
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

$lastMaxTimestamp = null;

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
            SELECT MAX(sr.sensor_reading_recorded_at) AS max_ts
            FROM sensor_reading sr
            JOIN device d ON d.device_id = sr.device_id
            WHERE d.user_account_id = :uid
        ");
        $stmt->execute(['uid' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $maxTs = $row['max_ts'] ?? null;

        if ($maxTs && $maxTs !== $lastMaxTimestamp) {
            sendEvent('plant-update', ['timestamp' => $maxTs]);
            $lastMaxTimestamp = $maxTs;
        } else {
            echo ": ping\n\n";
            @flush();
        }
    } catch (PDOException $e) {
        error_log("[SSE] " . $e->getMessage());
        exit;
    }

    sleep($pollInterval);
}
