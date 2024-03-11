<?php

namespace app\model\database;

use app\model\database\DbBase;
use app\model\logic\HelperLogic;
use zjkal\TimeHelper;

class LogApiModel extends DbBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "log_api";

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = "id";

    /**
     * All fields inside the $guarded array are not mass-assignable
     *
     * @var string
     */
    protected $guarded = ["id", "deleted_at"];

    /**
     * Get the formatted created_at timestamp.
     *
     * @var string
     */
    public function getCreatedAtAttribute($value)
    {
        return TimeHelper::format("Y-m-d H:i:s", $value);
    }

    /**
     * Get the formatted updated_at timestamp.
     *
     * @var string
     */
    public function getUpdatedAtAttribute($value)
    {
        return TimeHelper::format("Y-m-d H:i:s", $value);
    }

    public static function log(
        string $ip = null,
        string $name = null,
        string $url = null,
        string $group = null,
        $response = null,
        string $refTable = null,
        $refId = 0,
        string $sn = null,
        string $byPass = null,
        string $remark = null
    ) {
        $res = LogApiModel::create([
            "sn" => $sn ?? HelperLogic::generateUniqueSN("log_api"),
            "name" => $name,
            "url" => $url,
            "group" => $group,
            "ip" => $ip ?? "0.0.0.0",
            "response" => json_encode($response),
            "ref_table" => $refTable,
            "ref_id" => $refId,
            "by_pass" => $byPass,
            "remark" => $remark
        ]);

        return $res;
    }
}
