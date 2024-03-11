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
        Route::post("/telegramLogin", [dapp\auth\TelegramLogin::class, "index"]);
        Route::post("/xLogin", [dapp\auth\XLogin::class, "index"]);
        Route::post("/logout", [dapp\auth\Logout::class, "index"])->middleware([
            plugin\dapp\app\middleware\JwtAuthMiddleware::class,
        ]);
    })->middleware([
        plugin\dapp\app\middleware\MaintenanceMiddleware::class,
    ]);

    // user
    Route::group("/user", function () {
        Route::get("/bindTelegram", [dapp\user\BindTelegram::class, "index"]);
        Route::post("/bindX", [dapp\user\BindX::class, "index"]);
        Route::post("/characterCreation", [dapp\user\CharacterCreation::class, "index"]);
        Route::get("/getProfile", [dapp\user\GetProfile::class, "index"]);
        Route::post("/setProfile", [dapp\user\SetProfile::class, "index"]);
        Route::get("/stamina", [dapp\user\Stamina::class, "index"]);
        Route::get("/tutorialProgress", [dapp\user\TutorialProgress::class, "index"]);
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

    // character
    Route::group("/character", function () {
        Route::get("/levelUpInfo", [dapp\character\LevelUpInfo::class, "index"]);
        Route::post("/levelUp", [dapp\character\LevelUp::class, "index"]);
    })->middleware([
        plugin\dapp\app\middleware\JwtAuthMiddleware::class,
        plugin\dapp\app\middleware\MaintenanceMiddleware::class,
    ]);

    // gacha
    Route::group("/gacha", function () {
        Route::post("/draw", [dapp\gacha\Draw::class, "index"]);
        Route::get("/gachaHistory", [dapp\gacha\GachaHistory::class, "index"]);
        Route::get("/gachaList", [dapp\gacha\GachaList::class, "index"]);
        Route::get("/gachaDetail", [dapp\gacha\GachaDetail::class, "index"]);
        Route::get("/itemDropList", [dapp\gacha\ItemDropList::class, "index"]);
    })->middleware([
        plugin\dapp\app\middleware\JwtAuthMiddleware::class,
        plugin\dapp\app\middleware\MaintenanceMiddleware::class,
    ]);

    // inventory
    Route::group("/inventory", function () {
        Route::post("/useItem", [dapp\inventory\UseItem::class, "index"]);
        Route::post("/itemDelete", [dapp\inventory\ItemDelete::class, "index"]);
        Route::post("/buyPage", [dapp\inventory\BuyPage::class, "index"]);
    })->middleware([
        plugin\dapp\app\middleware\JwtAuthMiddleware::class,
        plugin\dapp\app\middleware\MaintenanceMiddleware::class,
    ]);

    // inventory info
    Route::group("/inventoryInfo", function () {
        Route::get("/inventory", [dapp\inventoryInfo\Inventory::class, "index"]);
        Route::get("/inventoryList", [dapp\inventoryInfo\InventoryList::class, "index"]);
        Route::post("/itemList", [dapp\inventoryInfo\ItemList::class, "index"]);
        Route::get("/pagePrice", [dapp\inventoryInfo\PagePrice::class, "index"]);
    })->middleware([
        plugin\dapp\app\middleware\JwtAuthMiddleware::class,
        plugin\dapp\app\middleware\MaintenanceMiddleware::class,
    ]);

    // market
    Route::group("/market", function () {
        Route::post("/buy", [dapp\market\Buy::class, "index"]);
        Route::post("/remove", [dapp\market\Remove::class, "index"]);
        Route::post("/sell", [dapp\market\Sell::class, "index"]);
    })->middleware([
        plugin\dapp\app\middleware\JwtAuthMiddleware::class,
        plugin\dapp\app\middleware\MaintenanceMiddleware::class,
    ]);

    // market info
    Route::group("/marketInfo", function () {
        Route::post("/itemList", [dapp\marketInfo\ItemList::class, "index"]);
        Route::get("/itemMarketList", [dapp\marketInfo\ItemMarketList::class, "index"]);
        Route::get("/onsalesList", [dapp\marketInfo\OnsalesList::class, "index"]);
        Route::post("/petMarketList", [dapp\marketInfo\PetMarketList::class, "index"]);
    })->middleware([
        plugin\dapp\app\middleware\JwtAuthMiddleware::class,
        plugin\dapp\app\middleware\MaintenanceMiddleware::class,
    ]);

    // mission
    Route::group("/mission", function () {
        Route::post("/takeMission", [dapp\mission\TakeMission::class, "index"]);
        Route::post("/claimReward", [dapp\mission\ClaimReward::class, "index"]);
    })->middleware([
        plugin\dapp\app\middleware\JwtAuthMiddleware::class,
        plugin\dapp\app\middleware\MaintenanceMiddleware::class,
    ]);

    // mission info
    Route::group("/missionInfo", function () {
        Route::get("/missionList", [dapp\missionInfo\MissionList::class, "index"]);
    })->middleware([
        plugin\dapp\app\middleware\JwtAuthMiddleware::class,
        plugin\dapp\app\middleware\MaintenanceMiddleware::class,
    ]);

    // onboarding
    Route::group("/onboarding", function () {
        Route::post("/bindUpline", [dapp\onboarding\BindUpline::class, "index"]);
        Route::get("/claimPoint", [dapp\onboarding\ClaimPoint::class, "index"]);
        Route::get("/missionList", [dapp\onboarding\MissionList::class, "index"]);
        Route::get("/nftCheck", [dapp\onboarding\NftCheck::class, "index"]);
        Route::get("/nftPrice", [dapp\onboarding\NftPrice::class, "index"]);
        Route::post("/nftTxid", [dapp\onboarding\NftTxid::class, "index"]);
        Route::get("/pointAndReferral", [dapp\onboarding\PointAndReferral::class, "index"]);
        Route::post("/purchaseNFT", [dapp\onboarding\PurchaseNFT::class, "index"]);
    })->middleware([
        plugin\dapp\app\middleware\JwtAuthMiddleware::class,
        plugin\dapp\app\middleware\MaintenanceMiddleware::class,
    ]);

    // pet
    Route::group("/pet", function () {
        Route::post("/assignPet", [dapp\pet\AssignPet::class, "index"]);
        Route::post("/buySlot", [dapp\pet\BuySlot::class, "index"]);
        Route::post("/miningReward", [dapp\pet\MiningReward::class, "index"]);
        Route::post("/petDelete", [dapp\pet\PetDelete::class, "index"]);
        Route::post("/petUpgrade", [dapp\pet\PetUpgrade::class, "index"]);
    })->middleware([
        plugin\dapp\app\middleware\JwtAuthMiddleware::class,
        plugin\dapp\app\middleware\MaintenanceMiddleware::class,
    ]);

    // pet info
    Route::group("/petInfo", function () {
        Route::get("/minedAmount", [dapp\petInfo\MinedAmount::class, "index"]);
        Route::get("/pet", [dapp\petInfo\Pet::class, "index"]);
        Route::post("/petDeletePrice", [dapp\petInfo\PetDeletePrice::class, "index"]);
        Route::get("/petList", [dapp\petInfo\PetList::class, "index"]);
        Route::get("/petUpgradeInfo", [dapp\petInfo\PetUpgradeInfo::class, "index"]);
        Route::get("/slotPrice", [dapp\petInfo\SlotPrice::class, "index"]);
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
        Route::get("/marketSetting", [dapp\setting\MarketSetting::class, "index"]);
    })->middleware([
        plugin\dapp\app\middleware\JwtAuthMiddleware::class,
        plugin\dapp\app\middleware\MaintenanceMiddleware::class,
    ]);

    // shop
    Route::group("/shop", function () {
        Route::post("/purchase", [dapp\shop\Purchase::class, "index"]);
        Route::get("/shopList", [dapp\shop\ShopList::class, "index"]);
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

    Route::group("/telegram", function () {
        Route::post("/manualload", [dapp\telegram\load\Manual::class, "index"]);
        Route::post("/autoload", [dapp\telegram\load\Auto::class, "index"]);
        Route::get("/connectWebhook", [dapp\telegram\webhook\Connect::class, "index"]);
        Route::get("/disconnectWebhook", [dapp\telegram\webhook\Disconnect::class, "index"]);
    });
});
