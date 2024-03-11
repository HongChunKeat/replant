<?php

return [
    "" => [
        plugin\admin\app\middleware\PathDetectorMiddleware::class,
        plugin\admin\app\middleware\CorsMiddleware::class,
        Tinywan\LimitTraffic\Middleware\LimitTrafficMiddleware::class,
    ],
];
