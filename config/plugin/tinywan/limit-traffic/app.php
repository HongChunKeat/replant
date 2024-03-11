<?php

return [
    "enable" => true,
    "limit" => [
        "limit" => 100, // 请求次数
        "window_time" => 30, // 窗口时间，单位：秒
        "status" => 429,  // HTTP 状态码
        "body" => [  // 响应信息
            "success" => false,
            "data" => ["too_many_requests_please_try_again_later"],
            "msg" => ""
        ]
    ]
];