<?php

use Webman\Route;
use plugin\development\app\controller as oxDev;

Route::group("/development", function () {
    // test
    Route::group("/test", function () {
        Route::get("/testing", [oxDev\test\Test::class, "testing"]);
        // Route::get("/testing2", [oxDev\test\Test::class, "testing2"]);
        // Route::get("/testing3", [oxDev\test\Test::class, "testing3"]);
        // Route::get("/cron", [app\crontab\tasks\reward\Reward::class, "handle"]);
    });
});
