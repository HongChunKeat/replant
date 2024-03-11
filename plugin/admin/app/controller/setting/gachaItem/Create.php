<?php

namespace plugin\admin\app\controller\setting\gachaItem;

# library
use plugin\admin\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\LogAdminModel;
use app\model\database\SettingGachaItemModel;
use app\model\database\SettingGachaModel;
use app\model\database\SettingItemModel;
use app\model\database\SettingPetModel;
use app\model\database\SettingWalletModel;
use app\model\logic\HelperLogic;

class Create extends Base
{
    # [validation-rule]
    protected $rule = [
        "gacha" => "require|number|max:11",
        "ref_table" => "require",
        "ref_id" => "require|number|max:11",
        "token_reward" => "float|max:11",
        "occurrence" => "require|number|max:11",
        "remark" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "gacha",
        "ref_table",
        "ref_id",
        "token_reward",
        "occurrence",
        "remark",
    ];

    public function index(Request $request)
    {
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
                if (isset($cleanVars["gacha"])) {
                    $cleanVars["gacha_id"] = $cleanVars["gacha"];
                }

                # [unset key]
                unset($cleanVars["gacha"]);

                $res = SettingGachaItemModel::create($cleanVars);
            }

            # [result]
            if ($res) {
                LogAdminModel::log($request, "create", "setting_gacha_item", $res["id"]);
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
        if (isset($params["gacha"])) {
            if (!SettingGachaModel::where("id", $params["gacha"])->first()) {
                $this->error[] = "gacha:invalid";
            }
        }

        if (isset($params["ref_table"]) && isset($params["ref_id"])) {
            if ($params["ref_table"] == "setting_pet") {
                if (!SettingPetModel::where("id", $params["ref_id"])->first()) {
                    $this->error[] = "ref_id:invalid";
                } else {
                    if (isset($params["token_reward"])) {
                        $this->error[] = "token_reward:must_be_empty_for_this_table";
                    }
                }
            } else if ($params["ref_table"] == "setting_item") {
                if (!SettingItemModel::where("id", $params["ref_id"])->first()) {
                    $this->error[] = "ref_id:invalid";
                } else {
                    if (isset($params["token_reward"])) {
                        $this->error[] = "token_reward:must_be_empty_for_this_table";
                    }
                }
            } else if ($params["ref_table"] == "setting_wallet") {
                if (!SettingWalletModel::where("id", $params["ref_id"])->first()) {
                    $this->error[] = "ref_id:invalid";
                } else {
                    if (empty($params["token_reward"])) {
                        $this->error[] = "token_reward:is_required_for_this_table";
                    }
                }
            } else {
                $this->error[] = "ref_table:invalid";
            }
        }

        if (isset($params["gacha"]) && isset($params["ref_table"]) && isset($params["ref_id"])) {
            if (SettingGachaItemModel::where([
                    "gacha_id" => $params["gacha"],
                    "ref_table" => $params["ref_table"],
                    "ref_id" => $params["ref_id"]
                ])->first()
            ) {
                $this->error[] = "entry:exists";
            }
        }
    }
}