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

class Create extends Base
{
    # [validation-rule]
    protected $rule = [
        "quality" => "require|in:normal,premium",
        "rank" => "require",
        "star" => "require|number|egt:0|max:11",
        "item_required" => "",
        "item_required_quantity" => "",
        "mining_rate" => "require|float|egt:0|max:11",
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

    public function index(Request $request)
    {
        if ($request->post("item_required") || $request->post("item_required_quantity")) {
            $this->rule["item_required"] .= "|require";
            $this->rule["item_required_quantity"] .= "|require";
        }
        # [validation]
        $this->validation($request->post(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->post(), $this->patternInputs);

        # [checking]
        $this->checking($cleanVars);

        # [proceed]
        if (!count($this->error)) {
            $res = "";

            # [process]
            if (count($cleanVars) > 0) {
                if (isset($cleanVars["item_required"]) && isset($cleanVars["item_required_quantity"])) {
                    $cleanVars["item_required"] = json_encode(
                        HelperLogic::combineParamsToArray($cleanVars["item_required"], $cleanVars["item_required_quantity"])
                    );
                }

                # [unset key]
                unset($cleanVars["item_required_quantity"]);

                $res = SettingPetRankModel::create($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "create", "setting_pet_rank", $res["id"]);
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
        if (isset($params["quality"]) && isset($params["rank"]) && isset($params["star"])) {
            if (SettingPetRankModel::where(["quality" => $params["quality"], "rank" => $params["rank"], "star" => $params["star"]])->first()) {
                $this->error[] = "entry:exists";
            }
        }

        if (isset($params["item_required"]) && isset($params["item_required_quantity"])) {
            $itemQuantityBreak = HelperLogic::explodeParams($params["item_required_quantity"]);

            $checkItem = SettingItemModel::whereIn("id", $params["item_required"])->get();
            if (count($params["item_required"]) != count($checkItem)) {
                $this->error[] = "item_required:invalid";
            }

            foreach($itemQuantityBreak as $value) {
                if(!is_numeric($value)) {
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