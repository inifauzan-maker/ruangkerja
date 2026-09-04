<?php

return [
    'antivirus' => [
        'enabled' => (bool) env('ATTACHMENT_ANTIVIRUS_ENABLED', false),
        'command' => env('ATTACHMENT_ANTIVIRUS_COMMAND', 'clamscan'),
        'timeout' => (int) env('ATTACHMENT_ANTIVIRUS_TIMEOUT', 30),
        'fail_closed' => (bool) env('ATTACHMENT_ANTIVIRUS_FAIL_CLOSED', true),
    ],
];
