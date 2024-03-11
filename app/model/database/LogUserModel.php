<?php

namespace app\model\database;

use support\Request;
use app\model\database\DbBase;
use zjkal\TimeHelper;

class LogUserModel extends DbBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "log_user";

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

    public static function log(Request $request, string $remark = null, string $refTable = null, int $refId = 0)
    {
        $uid = $request->visitor["id"] ?? 0;

        LogUserModel::create([
            "uid" => $uid,
            "by_uid" => $uid,
            "ip" => $request->header("x-forwarded-for") ?? "0.0.0.0",
            "ref_table" => $refTable,
            "ref_id" => $refId,
            "remark" => $remark,
        ]);
    }
}
