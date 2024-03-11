<?php

namespace plugin\admin\app\model\logic;

# system lib
use support\Db;
# database & logic
use app\model\database\AccountUserModel;
use app\model\database\NetworkSponsorModel;
use app\model\database\StatSponsorModel;

class StatSponsorLogic
{
    # point
    public static function statRecord(int $fromUid, string $type, $amount)
    {
        $_response = false;

        if (AccountUserModel::where("id", $fromUid)->first()) {
            $date = date("Y-m-d H:i:s");
            $usedAt = date("Ymd");
            $bulk = [];

            $curUser = $fromUid;
            do {
                //get user network
                $user = NetworkSponsorModel::where("uid", $curUser)->first();

                if ($user) {
                    $bulk[] = [
                        "uid" => $user["uid"],
                        "from_uid" => $fromUid,
                        "stat_type" => strtolower($type),
                        "amount" => $amount,
                        "created_at" => $date,
                        "updated_at" => $date,
                        "used_at" => $usedAt,
                        "is_personal" => ($user["uid"] == $fromUid) ? 1 : 0,
                    ];

                    //change current user to user's upline
                    //keep on looping upward until hit root
                    $curUser = $user["upline_uid"];
                } else {
                    $curUser = false;
                }
            } while ($curUser);

            if (count($bulk) > 0) {
                StatSponsorModel::insert($bulk);
            }
        }

        return $_response;
    }

    public static function statCumulative($execDate = null)
    {
        if ($execDate == null) {
            $usedAt = date("Ymd", strtotime("-1 day"));
        } else {
            // 2023-01-01 00:00:00
            $usedAt = date("Ymd", strtotime("-1 day", strtotime($execDate)));
        }

        // get personal yesterday stats
        $personalStats = StatSponsorModel::where(["is_personal" => 1])
            ->where("used_at", $usedAt)
            ->select("uid", "stat_type", Db::raw("SUM(amount) as amount"), "is_cumulative")
            ->groupBy("uid", "stat_type", "is_cumulative")
            ->get();

        self::processStats($personalStats, 1, $usedAt);

        // get team yesterday stats
        $teamStats = StatSponsorModel::where(["is_personal" => 0])
            ->where("used_at", $usedAt)
            ->select("uid", "stat_type", Db::raw("SUM(amount) as amount"), "is_cumulative")
            ->groupBy("uid", "stat_type", "is_cumulative")
            ->get();

        self::processStats($teamStats, 0, $usedAt);

        return true;
    }

    public static function processStats($stats, $personal, $usedAt)
    {
        $bulk = [];

        foreach ($stats as $row) {
            if ($row["is_cumulative"] == 1) {
                // cumulative carry over
                // get the non cumulative stat of that type
                $nonCumulative = StatSponsorModel::Where([
                    "uid" => $row["uid"],
                    "stat_type" => $row["stat_type"],
                    "is_personal" => $personal,
                    "is_cumulative" => 0,
                    "used_at" => $usedAt,
                ])->first();

                // only insert if no cf 0 stat on that day
                if (!$nonCumulative) {
                    $bulk[] = [
                        "uid" => $row["uid"],
                        "stat_type" => $row["stat_type"],
                        "amount" => $row["amount"],
                        "used_at" => date("Ymd", strtotime("+1 day", strtotime($usedAt))),
                        "is_personal" => $personal,
                        "is_cumulative" => 1,
                        "created_at" => date("Y-m-d H:i:s"),
                        "updated_at" => date("Y-m-d H:i:s"),
                    ];
                }
            } else {
                // non cumulative
                // get user yesterday latest cf stat
                $cumulative = StatSponsorModel::where([
                    "uid" => $row["uid"],
                    "stat_type" => $row["stat_type"],
                    "is_personal" => $personal,
                    "is_cumulative" => 1,
                    "used_at" => $usedAt,
                ])->first();

                // sum yesterday stat with yesterday latest cf stat
                $amount = $cumulative
                    ? $row["amount"] + $cumulative["amount"]
                    : $row["amount"];

                $bulk[] = [
                    "uid" => $row["uid"],
                    "stat_type" => $row["stat_type"],
                    "amount" => $amount,
                    "used_at" => date("Ymd", strtotime("+1 day", strtotime($usedAt))),
                    "is_personal" => $personal,
                    "is_cumulative" => 1,
                    "created_at" => date("Y-m-d H:i:s"),
                    "updated_at" => date("Y-m-d H:i:s"),
                ];
            }
        }

        if (count($bulk) > 0) {
            StatSponsorModel::insert($bulk);
        }
    }

    public static function statList(int $uid = 0, $personal = true)
    {
        $statTypes = [
            "point",
        ];

        $totals = [];
        foreach ($statTypes as $type) {
            $totals[$type] = self::kpi($uid, $personal, $type);
        }

        return $totals;
    }

    public static function kpi(int $uid = 0, $personal = true, $type = "point")
    {
        $statType[] = $type;

        // get today stat : new user stat will be in here
        $currentStats = StatSponsorModel::where([
            "uid" => $uid,
            "is_personal" => $personal,
            "is_cumulative" => 0,
            "used_at" => date("Ymd")
        ])
            ->whereIn("stat_type", $statType)
            ->sum("amount");

        // get latest cf stat : if none then is new user
        $cumulativeStats = StatSponsorModel::where([
            "uid" => $uid,
            "is_personal" => $personal,
            "is_cumulative" => 1
        ])
            ->whereIn("stat_type", $statType)
            ->orderBy("id", "desc")
            ->first();

        $total = ($cumulativeStats)
            ? $currentStats + $cumulativeStats["amount"]
            : $currentStats;

        return $total;
    }
}
