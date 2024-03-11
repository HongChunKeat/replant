<?php

use Webman\Route;
use plugin\admin\app\controller as admin;

/**
 * 执行操作: 前端访客
 *    List = GET /tickets/list - 列出所有
 *    List = GET /tickets - 列出 paging
 *    Read = GET /tickets/{id} - 列出 id
 *    Create = POST /tickets - 创建
 *    Update = PUT /tickets/{id} - 更新信息
 *    UpdatePartial = PATCH /tickets/{id} - 部分修改, 例如修改状态
 *    Delete = DELETE /tickets/{id} - 删掉 9839 这张车票
 */ 

Route::group("/admin", function () {
    // global
    Route::group("/global", function () {
        Route::post("/redisFlush", [admin\GlobalController::class, "redisFlush"]);
        Route::post("/redis", [admin\GlobalController::class, "redis"]);
    });

    // auth
    Route::group("/auth", function () {
        Route::get("/request", [admin\auth\Ask::class, "index"]);
        Route::post("/verify", [admin\auth\Verify::class, "index"]);
        Route::post("/logout", [admin\auth\Logout::class, "index"])->middleware([
            plugin\admin\app\middleware\JwtAuthMiddleware::class,
        ]);
        Route::get("/rule", [admin\auth\Rule::class, "index"])->middleware([
            plugin\admin\app\middleware\JwtAuthMiddleware::class,
        ]);
    })->middleware([
        plugin\admin\app\middleware\MaintenanceMiddleware::class,
    ]);

    // enum list
    Route::group("/enumList", function () {
        Route::get("/list", [admin\enumList\Listing::class, "index"]);
    })->middleware([
        plugin\admin\app\middleware\JwtAuthMiddleware::class,
        plugin\admin\app\middleware\PermissionControlMiddleware::class,
        plugin\admin\app\middleware\MaintenanceMiddleware::class,
    ]);

    // account
    Route::group("/account", function () {
        Route::group("/admin", function () {
            Route::get("/list", [admin\account\admin\Listing::class, "index"]);
            Route::get("", [admin\account\admin\Paging::class, "index"]);
            Route::get("/{id:\d+}", [admin\account\admin\Read::class, "index"]);
            Route::post("", [admin\account\admin\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\account\admin\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\account\admin\Delete::class, "index"]);
        });

        Route::group("/user", function () {
            Route::get("/list", [admin\account\user\Listing::class, "index"]);
            Route::get("", [admin\account\user\Paging::class, "index"]);
            Route::get("/{id:\d+}", [admin\account\user\Read::class, "index"]);
            Route::post("", [admin\account\user\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\account\user\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\account\user\Delete::class, "index"]);
            Route::get("/details", [admin\account\user\Details::class, "index"]);
            Route::get("/viewBalance/{id:\d+}", [admin\account\user\ViewBalance::class, "index"]);
            Route::put("/addBalance/{id:\d+}", [admin\account\user\AddBalance::class, "index"]);
            Route::put("/deductBalance/{id:\d+}", [admin\account\user\DeductBalance::class, "index"]);
        });
    })->middleware([
        plugin\admin\app\middleware\JwtAuthMiddleware::class,
        plugin\admin\app\middleware\PermissionControlMiddleware::class,
        plugin\admin\app\middleware\MaintenanceMiddleware::class,
    ]);

    // log
    Route::group("/log", function () {
        Route::group("/admin", function () {
            Route::get("/list", [admin\log\admin\Listing::class, "index"]);
            Route::get("", [admin\log\admin\Paging::class, "index"]);
            Route::get("/{id:\d+}", [admin\log\admin\Read::class, "index"]);
            Route::post("", [admin\log\admin\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\log\admin\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\log\admin\Delete::class, "index"]);
        });

        Route::group("/api", function () {
            Route::get("/list", [admin\log\api\Listing::class, "index"]);
            Route::get("", [admin\log\api\Paging::class, "index"]);
            Route::get("/{id:\d+}", [admin\log\api\Read::class, "index"]);
            Route::post("", [admin\log\api\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\log\api\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\log\api\Delete::class, "index"]);
        });

        Route::group("/cronjob", function () {
            Route::get("/list", [admin\log\cronjob\Listing::class, "index"]);
            Route::get("", [admin\log\cronjob\Paging::class, "index"]);
            Route::get("/{id:\d+}", [admin\log\cronjob\Read::class, "index"]);
            Route::post("", [admin\log\cronjob\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\log\cronjob\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\log\cronjob\Delete::class, "index"]);
        });

        Route::group("/user", function () {
            Route::get("/list", [admin\log\user\Listing::class, "index"]);
            Route::get("", [admin\log\user\Paging::class, "index"]);
            Route::get("/{id:\d+}", [admin\log\user\Read::class, "index"]);
            Route::post("", [admin\log\user\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\log\user\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\log\user\Delete::class, "index"]);
        });        
    })->middleware([
        plugin\admin\app\middleware\JwtAuthMiddleware::class,
        plugin\admin\app\middleware\PermissionControlMiddleware::class,
        plugin\admin\app\middleware\MaintenanceMiddleware::class,
    ]);

    // network
    Route::group("/network", function () {
        Route::group("/sponsor", function () {
            Route::get("/list", [admin\network\sponsor\Listing::class, "index"]);
            Route::get("", [admin\network\sponsor\Paging::class, "index"]);
            Route::get("/{id:\d+}", [admin\network\sponsor\Read::class, "index"]);
            Route::post("", [admin\network\sponsor\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\network\sponsor\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\network\sponsor\Delete::class, "index"]);
        });       
    })->middleware([
        plugin\admin\app\middleware\JwtAuthMiddleware::class,
        plugin\admin\app\middleware\PermissionControlMiddleware::class,
        plugin\admin\app\middleware\MaintenanceMiddleware::class,
    ]);

    // permission
    Route::group("/permission", function () {
        Route::group("/admin", function () {
            Route::get("/list", [admin\permission\admin\Listing::class, "index"]);
            Route::get("", [admin\permission\admin\Paging::class, "index"]);
            Route::get("/{id:\d+}", [admin\permission\admin\Read::class, "index"]);
            Route::post("", [admin\permission\admin\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\permission\admin\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\permission\admin\Delete::class, "index"]);
        });

        Route::group("/template", function () {
            Route::get("/list", [admin\permission\template\Listing::class, "index"]);
            Route::get("", [admin\permission\template\Paging::class, "index"]);
            Route::get("/{id:\d+}", [admin\permission\template\Read::class, "index"]);
            Route::post("", [admin\permission\template\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\permission\template\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\permission\template\Delete::class, "index"]);
        });

        Route::group("/warehouse", function () {
            Route::get("/list", [admin\permission\warehouse\Listing::class, "index"]);
            Route::get("", [admin\permission\warehouse\Paging::class, "index"]);
            Route::get("/{id:\d+}", [admin\permission\warehouse\Read::class, "index"]);
            Route::post("", [admin\permission\warehouse\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\permission\warehouse\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\permission\warehouse\Delete::class, "index"]);
        });
    })->middleware([
        plugin\admin\app\middleware\JwtAuthMiddleware::class,
        plugin\admin\app\middleware\PermissionControlMiddleware::class,
        plugin\admin\app\middleware\MaintenanceMiddleware::class,
    ]);

    Route::group("/reward", function () {
        Route::group("/record", function () {
            Route::get("/list", [admin\reward\record\Listing::class, "index"]);
            Route::get("", [admin\reward\record\Paging::class, "index"]);
            Route::get("/{id:\d+}", [admin\reward\record\Read::class, "index"]);
            Route::post("", [admin\reward\record\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\reward\record\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\reward\record\Delete::class, "index"]);
        });
    })->middleware([
        plugin\admin\app\middleware\JwtAuthMiddleware::class,
        plugin\admin\app\middleware\PermissionControlMiddleware::class,
        plugin\admin\app\middleware\MaintenanceMiddleware::class,
    ]);

    // setting
    Route::group("/setting", function () {
        Route::group("/announcement", function () {
            Route::get("/list", [admin\setting\announcement\Listing::class, "index"]);
            Route::get("/{id:\d+}", [admin\setting\announcement\Read::class, "index"]);
            Route::get("", [admin\setting\announcement\Paging::class, "index"]);
            Route::post("", [admin\setting\announcement\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\setting\announcement\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\setting\announcement\Delete::class, "index"]);
        });

        Route::group("/attribute", function () {
            Route::get("/list", [admin\setting\attribute\Listing::class, "index"]);
            Route::get("/{id:\d+}", [admin\setting\attribute\Read::class, "index"]);
            Route::get("", [admin\setting\attribute\Paging::class, "index"]);
            Route::post("", [admin\setting\attribute\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\setting\attribute\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\setting\attribute\Delete::class, "index"]);
        });

        Route::group("/blockchainNetwork", function () {
            Route::get("/list", [admin\setting\blockchainNetwork\Listing::class, "index"]);
            Route::get("/{id:\d+}", [admin\setting\blockchainNetwork\Read::class, "index"]);
            Route::get("", [admin\setting\blockchainNetwork\Paging::class, "index"]);
            Route::post("", [admin\setting\blockchainNetwork\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\setting\blockchainNetwork\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\setting\blockchainNetwork\Delete::class, "index"]);
        });

        Route::group("/coin", function () {
            Route::get("/list", [admin\setting\coin\Listing::class, "index"]);
            Route::get("/{id:\d+}", [admin\setting\coin\Read::class, "index"]);
            Route::get("", [admin\setting\coin\Paging::class, "index"]);
            Route::post("", [admin\setting\coin\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\setting\coin\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\setting\coin\Delete::class, "index"]);
        });

        Route::group("/deposit", function () {
            Route::get("/list", [admin\setting\deposit\Listing::class, "index"]);
            Route::get("/{id:\d+}", [admin\setting\deposit\Read::class, "index"]);
            Route::get("", [admin\setting\deposit\Paging::class, "index"]);
            Route::post("", [admin\setting\deposit\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\setting\deposit\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\setting\deposit\Delete::class, "index"]);
        });

        Route::group("/gacha", function () {
            Route::get("/list", [admin\setting\gacha\Listing::class, "index"]);
            Route::get("/{id:\d+}", [admin\setting\gacha\Read::class, "index"]);
            Route::get("", [admin\setting\gacha\Paging::class, "index"]);
            Route::post("", [admin\setting\gacha\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\setting\gacha\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\setting\gacha\Delete::class, "index"]);
        });

        Route::group("/gachaItem", function () {
            Route::get("/list", [admin\setting\gachaItem\Listing::class, "index"]);
            Route::get("/{id:\d+}", [admin\setting\gachaItem\Read::class, "index"]);
            Route::get("", [admin\setting\gachaItem\Paging::class, "index"]);
            Route::post("", [admin\setting\gachaItem\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\setting\gachaItem\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\setting\gachaItem\Delete::class, "index"]);
        });

        Route::group("/general", function () {
            Route::get("/list", [admin\setting\general\Listing::class, "index"]);
            Route::get("/{id:\d+}", [admin\setting\general\Read::class, "index"]);
            Route::get("", [admin\setting\general\Paging::class, "index"]);
            Route::post("", [admin\setting\general\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\setting\general\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\setting\general\Delete::class, "index"]);
        });

        Route::group("/item", function () {
            Route::get("/list", [admin\setting\item\Listing::class, "index"]);
            Route::get("/{id:\d+}", [admin\setting\item\Read::class, "index"]);
            Route::get("", [admin\setting\item\Paging::class, "index"]);
            Route::post("", [admin\setting\item\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\setting\item\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\setting\item\Delete::class, "index"]);
        });

        Route::group("/itemAttribute", function () {
            Route::get("/list", [admin\setting\itemAttribute\Listing::class, "index"]);
            Route::get("/{id:\d+}", [admin\setting\itemAttribute\Read::class, "index"]);
            Route::get("", [admin\setting\itemAttribute\Paging::class, "index"]);
            Route::post("", [admin\setting\itemAttribute\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\setting\itemAttribute\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\setting\itemAttribute\Delete::class, "index"]);
        });

        Route::group("/lang", function () {
            Route::get("/list", [admin\setting\lang\Listing::class, "index"]);
            Route::get("/{id:\d+}", [admin\setting\lang\Read::class, "index"]);
            Route::get("", [admin\setting\lang\Paging::class, "index"]);
            Route::post("", [admin\setting\lang\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\setting\lang\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\setting\lang\Delete::class, "index"]);
        });

        Route::group("/level", function () {
            Route::get("/list", [admin\setting\level\Listing::class, "index"]);
            Route::get("/{id:\d+}", [admin\setting\level\Read::class, "index"]);
            Route::get("", [admin\setting\level\Paging::class, "index"]);
            Route::post("", [admin\setting\level\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\setting\level\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\setting\level\Delete::class, "index"]);
        });

        Route::group("/mission", function () {
            Route::get("/list", [admin\setting\mission\Listing::class, "index"]);
            Route::get("/{id:\d+}", [admin\setting\mission\Read::class, "index"]);
            Route::get("", [admin\setting\mission\Paging::class, "index"]);
            Route::post("", [admin\setting\mission\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\setting\mission\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\setting\mission\Delete::class, "index"]);
        });

        Route::group("/nft", function () {
            Route::get("/list", [admin\setting\nft\Listing::class, "index"]);
            Route::get("/{id:\d+}", [admin\setting\nft\Read::class, "index"]);
            Route::get("", [admin\setting\nft\Paging::class, "index"]);
            Route::post("", [admin\setting\nft\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\setting\nft\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\setting\nft\Delete::class, "index"]);
        });

        Route::group("/operator", function () {
            Route::get("/list", [admin\setting\operator\Listing::class, "index"]);
            Route::get("/{id:\d+}", [admin\setting\operator\Read::class, "index"]);
            Route::get("", [admin\setting\operator\Paging::class, "index"]);
            Route::post("", [admin\setting\operator\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\setting\operator\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\setting\operator\Delete::class, "index"]);
        });

        Route::group("/payment", function () {
            Route::get("/list", [admin\setting\payment\Listing::class, "index"]);
            Route::get("/{id:\d+}", [admin\setting\payment\Read::class, "index"]);
            Route::get("", [admin\setting\payment\Paging::class, "index"]);
            Route::post("", [admin\setting\payment\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\setting\payment\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\setting\payment\Delete::class, "index"]);
        });

        Route::group("/pet", function () {
            Route::get("/list", [admin\setting\pet\Listing::class, "index"]);
            Route::get("/{id:\d+}", [admin\setting\pet\Read::class, "index"]);
            Route::get("", [admin\setting\pet\Paging::class, "index"]);
            Route::post("", [admin\setting\pet\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\setting\pet\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\setting\pet\Delete::class, "index"]);
        });

        Route::group("/petAttribute", function () {
            Route::get("/list", [admin\setting\petAttribute\Listing::class, "index"]);
            Route::get("/{id:\d+}", [admin\setting\petAttribute\Read::class, "index"]);
            Route::get("", [admin\setting\petAttribute\Paging::class, "index"]);
            Route::post("", [admin\setting\petAttribute\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\setting\petAttribute\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\setting\petAttribute\Delete::class, "index"]);
        });

        Route::group("/petRank", function () {
            Route::get("/list", [admin\setting\petRank\Listing::class, "index"]);
            Route::get("/{id:\d+}", [admin\setting\petRank\Read::class, "index"]);
            Route::get("", [admin\setting\petRank\Paging::class, "index"]);
            Route::post("", [admin\setting\petRank\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\setting\petRank\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\setting\petRank\Delete::class, "index"]);
        });

        Route::group("/reward", function () {
            Route::get("/list", [admin\setting\reward\Listing::class, "index"]);
            Route::get("/{id:\d+}", [admin\setting\reward\Read::class, "index"]);
            Route::get("", [admin\setting\reward\Paging::class, "index"]);
            Route::post("", [admin\setting\reward\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\setting\reward\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\setting\reward\Delete::class, "index"]);
        });

        Route::group("/rewardAttribute", function () {
            Route::get("/list", [admin\setting\rewardAttribute\Listing::class, "index"]);
            Route::get("/{id:\d+}", [admin\setting\rewardAttribute\Read::class, "index"]);
            Route::get("", [admin\setting\rewardAttribute\Paging::class, "index"]);
            Route::post("", [admin\setting\rewardAttribute\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\setting\rewardAttribute\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\setting\rewardAttribute\Delete::class, "index"]);
        });

        Route::group("/wallet", function () {
            Route::get("/list", [admin\setting\wallet\Listing::class, "index"]);
            Route::get("/{id:\d+}", [admin\setting\wallet\Read::class, "index"]);
            Route::get("", [admin\setting\wallet\Paging::class, "index"]);
            Route::post("", [admin\setting\wallet\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\setting\wallet\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\setting\wallet\Delete::class, "index"]);
        });

        Route::group("/walletAttribute", function () {
            Route::get("/list", [admin\setting\walletAttribute\Listing::class, "index"]);
            Route::get("/{id:\d+}", [admin\setting\walletAttribute\Read::class, "index"]);
            Route::get("", [admin\setting\walletAttribute\Paging::class, "index"]);
            Route::post("", [admin\setting\walletAttribute\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\setting\walletAttribute\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\setting\walletAttribute\Delete::class, "index"]);
        });

        Route::group("/withdraw", function () {
            Route::get("/list", [admin\setting\withdraw\Listing::class, "index"]);
            Route::get("/{id:\d+}", [admin\setting\withdraw\Read::class, "index"]);
            Route::get("", [admin\setting\withdraw\Paging::class, "index"]);
            Route::post("", [admin\setting\withdraw\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\setting\withdraw\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\setting\withdraw\Delete::class, "index"]);
        });
    })->middleware([
        plugin\admin\app\middleware\JwtAuthMiddleware::class,
        plugin\admin\app\middleware\PermissionControlMiddleware::class,
        plugin\admin\app\middleware\MaintenanceMiddleware::class,
    ]);

    // user
    Route::group("/user", function () {
        Route::group("/battle", function () {
            Route::get("/list", [admin\user\battle\Listing::class, "index"]);
            Route::get("", [admin\user\battle\Paging::class, "index"]);
            Route::get("/{id:\d+}", [admin\user\battle\Read::class, "index"]);
            Route::post("", [admin\user\battle\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\user\battle\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\user\battle\Delete::class, "index"]);
        });

        Route::group("/deposit", function () {
            Route::get("/list", [admin\user\deposit\Listing::class, "index"]);
            Route::get("", [admin\user\deposit\Paging::class, "index"]);
            Route::get("/{id:\d+}", [admin\user\deposit\Read::class, "index"]);
            Route::post("", [admin\user\deposit\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\user\deposit\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\user\deposit\Delete::class, "index"]);
        });

        Route::group("/gacha", function () {
            Route::get("/list", [admin\user\gacha\Listing::class, "index"]);
            Route::get("", [admin\user\gacha\Paging::class, "index"]);
            Route::get("/{id:\d+}", [admin\user\gacha\Read::class, "index"]);
            Route::post("", [admin\user\gacha\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\user\gacha\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\user\gacha\Delete::class, "index"]);
        });

        Route::group("/inventory", function () {
            Route::get("/list", [admin\user\inventory\Listing::class, "index"]);
            Route::get("", [admin\user\inventory\Paging::class, "index"]);
            Route::get("/{id:\d+}", [admin\user\inventory\Read::class, "index"]);
            Route::post("", [admin\user\inventory\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\user\inventory\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\user\inventory\Delete::class, "index"]);
        });

        Route::group("/level", function () {
            Route::get("/list", [admin\user\level\Listing::class, "index"]);
            Route::get("", [admin\user\level\Paging::class, "index"]);
            Route::get("/{id:\d+}", [admin\user\level\Read::class, "index"]);
            Route::post("", [admin\user\level\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\user\level\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\user\level\Delete::class, "index"]);
        });

        Route::group("/market", function () {
            Route::get("/list", [admin\user\market\Listing::class, "index"]);
            Route::get("", [admin\user\market\Paging::class, "index"]);
            Route::get("/{id:\d+}", [admin\user\market\Read::class, "index"]);
            Route::post("", [admin\user\market\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\user\market\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\user\market\Delete::class, "index"]);
        });

        Route::group("/mission", function () {
            Route::get("/list", [admin\user\mission\Listing::class, "index"]);
            Route::get("", [admin\user\mission\Paging::class, "index"]);
            Route::get("/{id:\d+}", [admin\user\mission\Read::class, "index"]);
            Route::post("", [admin\user\mission\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\user\mission\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\user\mission\Delete::class, "index"]);
        });

        Route::group("/nft", function () {
            Route::get("/list", [admin\user\nft\Listing::class, "index"]);
            Route::get("", [admin\user\nft\Paging::class, "index"]);
            Route::get("/{id:\d+}", [admin\user\nft\Read::class, "index"]);
            Route::post("", [admin\user\nft\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\user\nft\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\user\nft\Delete::class, "index"]);
        });

        Route::group("/pet", function () {
            Route::get("/list", [admin\user\pet\Listing::class, "index"]);
            Route::get("", [admin\user\pet\Paging::class, "index"]);
            Route::get("/{id:\d+}", [admin\user\pet\Read::class, "index"]);
            Route::post("", [admin\user\pet\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\user\pet\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\user\pet\Delete::class, "index"]);
        });

        Route::group("/point", function () {
            Route::get("/list", [admin\user\point\Listing::class, "index"]);
            Route::get("", [admin\user\point\Paging::class, "index"]);
            Route::get("/{id:\d+}", [admin\user\point\Read::class, "index"]);
            Route::post("", [admin\user\point\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\user\point\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\user\point\Delete::class, "index"]);
        });

        Route::group("/remark", function () {
            Route::get("/list", [admin\user\remark\Listing::class, "index"]);
            Route::get("", [admin\user\remark\Paging::class, "index"]);
            Route::get("/{id:\d+}", [admin\user\remark\Read::class, "index"]);
            Route::post("", [admin\user\remark\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\user\remark\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\user\remark\Delete::class, "index"]);
        });

        Route::group("/stamina", function () {
            Route::get("/list", [admin\user\stamina\Listing::class, "index"]);
            Route::get("", [admin\user\stamina\Paging::class, "index"]);
            Route::get("/{id:\d+}", [admin\user\stamina\Read::class, "index"]);
            Route::post("", [admin\user\stamina\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\user\stamina\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\user\stamina\Delete::class, "index"]);
        });

        Route::group("/withdraw", function () {
            Route::get("/list", [admin\user\withdraw\Listing::class, "index"]);
            Route::get("", [admin\user\withdraw\Paging::class, "index"]);
            Route::get("/{id:\d+}", [admin\user\withdraw\Read::class, "index"]);
            Route::post("", [admin\user\withdraw\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\user\withdraw\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\user\withdraw\Delete::class, "index"]);
        });
    })->middleware([
        plugin\admin\app\middleware\JwtAuthMiddleware::class,
        plugin\admin\app\middleware\PermissionControlMiddleware::class,
        plugin\admin\app\middleware\MaintenanceMiddleware::class,
    ]);

    // wallet
    Route::group("/wallet", function () {
        Route::group("/transaction", function () {
            Route::get("/list", [admin\wallet\transaction\Listing::class, "index"]);
            Route::get("", [admin\wallet\transaction\Paging::class, "index"]);
            Route::get("/{id:\d+}", [admin\wallet\transaction\Read::class, "index"]);
            Route::post("", [admin\wallet\transaction\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\wallet\transaction\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\wallet\transaction\Delete::class, "index"]);
        });

        Route::group("/transactionDetail", function () {
            Route::get("/list", [admin\wallet\transactionDetail\Listing::class, "index"]);
            Route::get("", [admin\wallet\transactionDetail\Paging::class, "index"]);
            Route::get("/{id:\d+}", [admin\wallet\transactionDetail\Read::class, "index"]);
            Route::post("", [admin\wallet\transactionDetail\Create::class, "index"]);
            Route::put("/{id:\d+}", [admin\wallet\transactionDetail\Update::class, "index"]);
            Route::delete("/{id:\d+}", [admin\wallet\transactionDetail\Delete::class, "index"]);
        });
    })->middleware([
        plugin\admin\app\middleware\JwtAuthMiddleware::class,
        plugin\admin\app\middleware\PermissionControlMiddleware::class,
        plugin\admin\app\middleware\MaintenanceMiddleware::class,
    ]);

    // summary
    Route::group("/dashboard", function () {
        // Route::get("/activeUser", [admin\dashboard\ActiveUser::class, "index"]);
    })->middleware([
        plugin\admin\app\middleware\JwtAuthMiddleware::class,
        plugin\admin\app\middleware\PermissionControlMiddleware::class,
        plugin\admin\app\middleware\MaintenanceMiddleware::class,
    ]);
});
