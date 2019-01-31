<?php

function request_url($uri, $params = null, $header = null)
{
    $source = config('app.OLD_OD_API_SOURCE');

    $requestUrl = $source . $uri;
    $ch = curl_init($requestUrl);

    if ($header) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    }

    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // grab URL and pass it to the browser
    $response = curl_exec($ch);
    $response = json_decode($response,true);
    curl_close($ch);

    return $response;
}
