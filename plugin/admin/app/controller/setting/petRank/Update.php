<?php

namespace plugin\admin\app\controller\setting\petRank;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\SettingItemModel;
use app\model\database\SettingPetRankModel;
use app\model\logic\HelperLogic;

class Update extends Base
{
    # [validation-rule]
    protected $rule = [
        "quality" => "in:normal,premium",
        "rank" => "",
        "star" => "number|egt:0|max:11",
        "item_required" => "",
        "item_required_quantity" => "",
        "mining_rate" => "float|egt:0|max:11",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "quality",
        "rank",
        "star",
        "item_required",
        "item_required_quantity",
        "mining_rate",
        "remark",
    ];

    public function index(Request $request, int $targetId = 0)
    {
        if ($request->post("item_required") || $request->post("item_required_quantity")) {
            $this->rule["item_required"] .= "|require";
            $this->rule["item_required_quantity"] .= "|require";
        }

        # [validation]
        $this->validation($request->post(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->post(), $this->patternInputs, 1);

        # [checking]
        $this->checking(["id" => $targetId] + $cleanVars);

        # [proceed]
        if (!count($this->error)) {
            $res = "";

            # [process]
            if (count($cleanVars) > 0) {
                if (!empty($cleanVars["item_required"]) && !empty($cleanVars["item_required_quantity"])) {
                    $cleanVars["item_required"] = json_encode(
                        HelperLogic::combineParamsToArray($cleanVars["item_required"], $cleanVars["item_required_quantity"])
                    );
                } else {
                    $cleanVars["item_required"] = null;
                }

                # [unset key]
                unset($cleanVars["item_required_quantity"]);

                $res = SettingPetRankModel::where("id", $targetId)->update($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "update", "setting_pet_rank", $targetId);
                $this->response = [
                    "success" => true,
                ];
            }
        }

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [condition]
        if (!empty($params["quality"]) || !empty($params["rank"]) || !empty($params["star"])) {
            $check = SettingPetRankModel::where("id", $params["id"])->first();

            if (
                SettingPetRankModel::where([
                    "quality" => empty($params["quality"])
                        ? $check["quality"]
                        : $params["quality"],
                    "rank" => empty($params["rank"])
                        ? $check["rank"]
                        : $params["rank"],
                    "star" => empty($params["star"]) && $params["star"] != 0
                        ? $check["star"]
                        : $params["star"],
                ])
                    ->whereNot("id", $params["id"])
                    ->first()
            ) {
                $this->error[] = "entry:exists";
            }
        }

        if (!empty($params["item_required"]) && !empty($params["item_required_quantity"])) {
            $itemQuantityBreak = HelperLogic::explodeParams($params["item_required_quantity"]);

            $checkItem = SettingItemModel::whereIn("id", $params["item_required"])->get();
            if (count($params["item_required"]) != count($checkItem)) {
                $this->error[] = "item_required:invalid";
            }

            foreach ($itemQuantityBreak as $value) {
                if (!is_numeric($value)) {
                    $this->error[] = "item_required_quantity:must_be_number";
                    break;
                }
            }

            if (count($params["item_required"]) != count($itemQuantityBreak)) {
                $this->error[] = "item_required_and_quantity:invalid";
            }
        }
    }
}