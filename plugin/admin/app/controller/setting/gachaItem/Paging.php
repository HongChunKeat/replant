<?php

namespace plugin\admin\app\controller\setting\gachaItem;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\SettingGachaItemModel;
use app\model\database\SettingGachaModel;
use app\model\database\SettingItemModel;
use app\model\database\SettingPetModel;
use app\model\database\SettingWalletModel;
use app\model\logic\HelperLogic;
use plugin\admin\app\model\logic\ItemLogic;

class Paging extends Base
{
    # [validation-rule]
    protected $rule = [
        "size" => "require|number",
        "page" => "require|number",
        "id" => "number|max:11",
        "gacha" => "number|max:11",
        "ref_table" => "",
        "ref_id" => "number|max:11",
        "token_reward" => "float|max:11",
        "occurrence" => "number|max:11",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "id",
        "gacha",
        "ref_table",
        "ref_id",
        "token_reward",
        "occurrence",
        "remark"
    ];

    # [outputs-pattern]
    protected $patternOutputs = [
        "id",
        "gacha",
        "ref_table",
        "ref_id",
        "ref_name",
        "token_reward",
        "occurrence",
        "drop_rate",
        "remark"
    ];


    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->get(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->get(), $this->patternInputs);

        # [proceed]
        if (!count($this->error)) {

            if (isset($cleanVars["gacha"])) {
                $cleanVars["gacha_id"] = $cleanVars["gacha"];
            }

            # [unset key]
            unset($cleanVars["gacha"]);

            # [paging query]
            $res = SettingGachaItemModel::paging(
                $cleanVars,
                $request->get("page"),
                $request->get("size"),
                ["*"],
                ["id", "desc"]
            );

            # [result]
            if ($res) {
                # [add and edit column using for loop]
                foreach ($res["items"] as $row) {
                    $gacha = SettingGachaModel::where("id", $row["gacha_id"])->first();
                    $row["gacha"] = $gacha ? $gacha["name"] : "";

                    if($row["ref_table"] == "setting_pet") {
                        $item = SettingPetModel::where("id", $row["ref_id"])->first();
                        $row["ref_name"] = $item ? $item["name"] : "";
                    } else if ($row["ref_table"] == "setting_item") {
                        $item = SettingItemModel::where("id", $row["ref_id"])->first();
                        $row["ref_name"] = $item ? $item["name"] : "";
                    } else if ($row["ref_table"] == "setting_wallet") {
                        $item = SettingWalletModel::where("id", $row["ref_id"])->first();
                        $row["ref_name"] = $item ? $item["code"] : "";
                    }

                    $itemDetails = ItemLogic::itemDropRate($row["gacha_id"], $row["id"]);
                    $row["drop_rate"] = $itemDetails["drop_rate"];
                }

                $this->response = [
                    "success" => true,
                    "data" => [
                        "data" => HelperLogic::formatOutput($res["items"], $this->patternOutputs, 1),
                        "count" => $res["count"],
                        "last_page" => ceil($res["count"] / $request->get("size")),
                    ],
                ];
            }
        }

        # [standard output]
        return $this->output();
    }
}
