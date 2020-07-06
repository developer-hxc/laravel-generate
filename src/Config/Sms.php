<?php
return [
    // HTTP 请求的超时时间（秒）
    'timeout' => 5.0,

    // 默认发送配置
    'default' => [
        // 默认可用的发送网关
        'gateways' => [
            'QTTXGateway'
        ],
    ],
    // 可用的网关配置
    'gateways' => [
        'errorlog' => [
            'file' => '/tmp/easy-sms.log',
        ],
        'QTTXGateway' => [
            'account' => env('SMS_QTTX_ACCOUNT'),
            'password' => env('SMS_QTTX_PASSWORD'),
        ]
    ],
];
