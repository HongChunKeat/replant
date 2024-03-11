<?php

/**
 * 当前没用上，未必适合所有场景
 * 适合当一个操作需要同时通知各模块的时候才需要使用 event
 */
namespace plugin\admin\app\event;

# library
use support\Request;
use support\Response;
use support\Log;
// use Tinywan\Validate\Validate;
# database

class StatSponsorEvent
{
    function create($params)
    {
        // loop to top account
        // update table:stat_sponsor
        var_export($params);
    }
}
