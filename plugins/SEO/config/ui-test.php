<?php

return [
    'observers.global' => \Piwik\DI::add([
        ['Http.sendHttpRequest',\Piwik\DI::value(function ($aUrl, $httpEventParams, &$response, &$status, &$headers) {
            // fake responses for SEO metric requests
            if (strpos($aUrl, 'www.bing.com')) {
                $response = file_get_contents(PIWIK_INCLUDE_PATH . '/plugins/SEO/tests/resources/bing_response.html');
            } elseif (strpos($aUrl, 'archive.org')) {
                $response = '{"timestamp": "19900101", "url": "matomo.org", "archived_snapshots": {"closest": {"timestamp": "20180109155124", "available": true, "status": "200", "url": "http://web.archive.org/web/20180109155124/https://matomo.org"}}}';
            } elseif (strpos($aUrl, 'www.who.is')) {
                $response = file_get_contents(PIWIK_INCLUDE_PATH . '/plugins/SEO/tests/resources/whois_response.html');
            } elseif (strpos($aUrl, 'www.whois.com')) {
                $response = file_get_contents(PIWIK_INCLUDE_PATH . '/plugins/SEO/tests/resources/whoiscom_response.html');
            }
        })]
    ]),
];
