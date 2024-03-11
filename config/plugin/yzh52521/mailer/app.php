<?php

return [
    "enable" => true,
    "mailer" => [
        "scheme"   => "smtp",// "smtps": using TLS, "smtp": without using TLS.
        "host"     => "smtp.gmail.com", // 服务器地址
        "username" => "system@iwinfund.com", //用户名
        "password" => "ewfa eiwp zbbp sunc", // 密码
        "port"     => 587, // SMTP服务器端口号,一般为25
        "options"  => [], // See: https://symfony.com/doc/current/mailer.html#tls-peer-verification
        //"dsn"      => "",
    ],
    "from"   => [
        "address" => "system@iwinfund.com",
        "name"    => "system",
    ],
];