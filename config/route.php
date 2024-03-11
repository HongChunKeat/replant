<?php
/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

use Webman\Route;
use support\Request;

/**
 * 执行操作模板:
 *    List = GET /tickets - 列出所有
 *    Read = GET /tickets/{id} - 列出 id
 *    Create = POST /tickets - 创建
 *    Update = PUT /tickets/{id} - 更新信息
 *    UpdatePartial = PATCH /tickets/{id} - 部分修改, 例如修改状态
 *    Delete = DELETE /tickets/{id} - 删掉 9839 这张车票
 */

// turn off auto route
Route::disableDefaultRoute();

// error
Route::fallback(function (Request $request) {
    return response($request->uri() . " not found", 404);
}, "admin");

// cors
Route::options("[{path:.+}]", function () {
    return response("");
});

// start self-defined route path
Route::get("/", function () {
    return response("When nothing goes right, go left.");
});
