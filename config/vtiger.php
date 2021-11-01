<?php

return [
    'url'               => env( 'VTIGER_URL' ),
    'username'          => env( 'VTIGER_USERNAME' ),
    'accesskey'         => env( 'VTIGER_KEY' ),
    'persistconnection' => env( 'VTIGER_PERSISTENT', true ),
    'max_retries'       => env( 'VTIGER_RETRIES', 10 ),
];
