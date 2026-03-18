<?php
function validateDeviceCode($device_code)
{
    if (!preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $device_code)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Invalid device_code format"
        ]);
        exit;
    }
}
