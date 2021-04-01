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
    ]
];
