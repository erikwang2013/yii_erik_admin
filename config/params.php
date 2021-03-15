<?php

return [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    'page'=>1,
    'limit'=>25,
    //雪花算法配置
    'snowflake'=>[
        'data_center_id'=>0,   //数据中心编号
        'unix_id'=>0      //机器编号 
    ],
    'controller_cors'=>[
        'admin'=>[
            'cors'=>[
                'origin'=>['*'],                 //允许来源的数组
                'request'=>['GET', 'POST', 'PUT','DELETE'],           //允许动作
            ],
            'actions'=>[
                //控制器方法
                'login' => [
                             'Access-Control-Allow-Credentials' => true,  //前请求是否使用证书，可为 true，false 或 null（不设置）
                         ]
            ]
        ],
        'admin-authority'=>[
            'cors'=>[
                'origin'=>['*'],                 //允许来源的数组
                'request'=>['GET', 'POST', 'PUT','DELETE'],           //允许动作
            ],
            'actions'=>[
                //控制器方法
                'login' => [
                             'Access-Control-Allow-Credentials' => true,  //前请求是否使用证书，可为 true，false 或 null（不设置）
                         ]
            ]
        ],
    ],
];
