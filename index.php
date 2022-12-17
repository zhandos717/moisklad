<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once 'lib.php';

$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['REQUEST_URI'];


loginfo("MOYSKLAD => APP", "Received: method=$method, path=$path");

$pp = explode('/', $path);


$n = count($pp);
$appId = $pp[$n - 2];
$accountId = $pp[$n - 1];

loginfo("MOYSKLAD => APP", "Extracted: appId=$appId, accountId=$accountId");

$app = AppInstance::load($appId, $accountId);


$replyStatus = true;

switch ($method) {
    case 'PUT':
        $requestBody = file_get_contents('php://input');

        loginfo("MOYSKLAD => APP", "Request body: " . print_r($requestBody, true));

        $data = json_decode($requestBody);

        $appUid = $data->appUid;
        $accessToken = $data->access[0]->access_token;

        loginfo("MOYSKLAD => APP", "Received access_token: appUid=$appUid, access_token=$accessToken)");

        if (!$app->getStatusName()) {
            $app->accessToken = $accessToken;
            $app->status = AppInstance::SETTINGS_REQUIRED;
            $app->persist();
        }
        break;
    case 'GET':
        break;
    case 'DELETE':
        $app->delete();
        $replyStatus = false;
        break;
}

if (!$app->getStatusName()) {
    http_response_code(404);
} else if ($replyStatus) {
    header("Content-Type: application/json");
    echo '{"status": "' . $app->getStatusName() . '"}';
}


