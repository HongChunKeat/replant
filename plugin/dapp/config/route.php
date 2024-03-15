<?php

use Webman\Route;
use plugin\dapp\app\controller as dapp;

/**
 * 执行操作: 前端访客
 *    List = GET /listing - 列出所有
 *    List = GET /tickets - 列出 paging
 *    Read = GET /tickets/{id} - 列出 id
 *    Create = POST /tickets - 创建
 *    Update = PUT /tickets/{id} - 更新信息
 *    UpdatePartial = PATCH /tickets/{id} - 部分修改, 例如修改状态
 *    Delete = DELETE /tickets/{id} - 删掉 9839 这张车票
 */

Route::group("/dapp", function () {
    // auth
    Route::group("/auth", function () {
        Route::get("/request", [dapp\auth\Ask::class, "index"]);
        Route::post("/verify", [dapp\auth\Verify::class, "index"]);
        Route::post("/logout", [dapp\auth\Logout::class, "index"])->middleware([
            plugin\dapp\app\middleware\JwtAuthMiddleware::class,
        ]);
    })->middleware([
        plugin\dapp\app\middleware\MaintenanceMiddleware::class,
    ]);

    // user
    Route::group("/user", function () {
        Route::get("/getProfile", [dapp\user\GetProfile::class, "index"]);
        Route::post("/setProfile", [dapp\user\SetProfile::class, "index"]);
    })->middleware([
        plugin\dapp\app\middleware\JwtAuthMiddleware::class,
        plugin\dapp\app\middleware\MaintenanceMiddleware::class,
    ]);

    // wallet
    Route::group("/wallet", function () {
        // Route::post("/transfer", [dapp\wallet\Transfer::class, "index"]);
        // Route::post("/swap", [dapp\wallet\Swap::class, "index"]);
        Route::post("/deposit", [dapp\wallet\Deposit::class, "index"]);
        Route::post("/withdraw", [dapp\wallet\Withdraw::class, "index"]);
    })->middleware([
        plugin\dapp\app\middleware\JwtAuthMiddleware::class,
        plugin\dapp\app\middleware\MaintenanceMiddleware::class,
    ]);

    // wallet info
    Route::group("/walletInfo", function () {
        Route::get("/wallet", [dapp\walletInfo\Wallet::class, "index"]);
        Route::get("/walletList", [dapp\walletInfo\WalletList::class, "index"]);
        Route::get("/transaction", [dapp\walletInfo\Transaction::class, "index"]);
    })->middleware([
        plugin\dapp\app\middleware\JwtAuthMiddleware::class,
        plugin\dapp\app\middleware\MaintenanceMiddleware::class,
    ]);

    // invite code - no need auth
    Route::group("/inviteCode", function () {
        Route::post("/check", [dapp\inviteCode\Check::class, "index"]);
        Route::post("/sendInfo", [dapp\inviteCode\SendInfo::class, "index"]);
    })->middleware([
        plugin\dapp\app\middleware\MaintenanceMiddleware::class,
    ]);

    // seed
    Route::group("/seed", function () {
        Route::get("/claimPoint", [dapp\seed\ClaimPoint::class, "index"]);
    })->middleware([
        plugin\dapp\app\middleware\JwtAuthMiddleware::class,
        plugin\dapp\app\middleware\MaintenanceMiddleware::class,
    ]);

    // team
    Route::group("/team", function () {
        Route::post("/bindUpline", [dapp\team\BindUpline::class, "index"]);
        Route::get("/listing", [dapp\team\Listing::class, "index"]);
    })->middleware([
        plugin\dapp\app\middleware\JwtAuthMiddleware::class,
        plugin\dapp\app\middleware\MaintenanceMiddleware::class,
    ]);

    // tree
    Route::group("/tree", function () {
        Route::get("/levelUpInfo", [dapp\tree\LevelUpInfo::class, "index"]);
        Route::post("/levelUp", [dapp\tree\LevelUp::class, "index"]);
    })->middleware([
        plugin\dapp\app\middleware\JwtAuthMiddleware::class,
        plugin\dapp\app\middleware\MaintenanceMiddleware::class,
    ]);

    // setting
    Route::group("/setting", function () {
        Route::get("/announcement", [dapp\setting\Announcement::class, "index"]);
        Route::post("/announcementDetails", [dapp\setting\AnnouncementDetails::class, "index"]);
        Route::get("/coinList", [dapp\setting\CoinList::class, "index"]);
        Route::get("/deposit", [dapp\setting\Deposit::class, "index"]);
        Route::get("/depositSetting", [dapp\setting\DepositSetting::class, "index"]);
        Route::get("/withdraw", [dapp\setting\Withdraw::class, "index"]);
        Route::get("/withdrawSetting", [dapp\setting\WithdrawSetting::class, "index"]);
    })->middleware([
        plugin\dapp\app\middleware\JwtAuthMiddleware::class,
        plugin\dapp\app\middleware\MaintenanceMiddleware::class,
    ]);
});
