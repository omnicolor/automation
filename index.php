<?php

require 'IntentInterface.php';
require 'HouseMusicIntent.php';
require 'QuietHouseIntent.php';
require 'HouseMusicVolumeIntent.php';
require 'TimeIntent.php';
require 'TvIntent.php';

$config = require 'config.php';

/**
 * Send a response back to Alexa.
 * @param string $responseText Text to send back to Alexa
 */
function sendResponse($responseText)
{
    header('Content-Type: application/json;charset=UTF-8');
    $response = [
        'version' => '1.0',
        'response' => [
            'shouldEndSession' => true,
            'outputSpeech' => [
                'type' => 'PlainText',
                'text' => $responseText,
            ],
        ],
    ];

    echo json_encode($response);
}

$input = json_decode(file_get_contents('php://input'));

$request = $input->request;

if ('LaunchRequest' === $request->type) {
    error_log('Mordor received LaunchRequest');
    sendResponse('You didn\'t tell me what to ask Mordor.');
    exit();
}

if (!isset($request->intent->slots)) {
    $request->intent->slots = new \StdClass();
}

try {
    $intent = new $request->intent->name($config);
    $responseText = $intent->run($request->intent->slots);
} catch (\RuntimeException $e) {
    $responseText = $e->getMessage();
}

sendResponse($responseText);
