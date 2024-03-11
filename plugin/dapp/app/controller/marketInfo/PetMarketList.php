<?php

namespace plugin\dapp\app\controller\marketInfo;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\AccountUserModel;
use app\model\database\UserMarketModel;
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;
use plugin\admin\app\model\logic\PetLogic;

class PetMarketList extends Base
{
    # [validation-rule]
    protected $rule = [
        "size" => "require|number",
        "page" => "require|number",
        "quality" => "",
        "rank" => "",
        "star" => "",
        "payment" => "",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "quality",
        "rank",
        "star",
        "payment",
    ];

    protected $patternOutputs = [
        "sn",
        "seller",
        "image",
        "name",
        "quality",
        "rank",
        "star",
        "mining_rate",
        "status",
        "price",
        "payment",
        "locked",
    ];

    public function index(Request $request)
    {
        # [validation]
        $this->validation($request->post(), $this->rule);

        # [clean variables]
        $cleanVars = HelperLogic::cleanParams($request->post(), $this->patternInputs);

        # user id
        $uid = $request->visitor["id"];

        # [proceed]
        if (!count($this->error)) {
            # [paging query]
            $table = UserMarketModel::defaultWhere("user_market")
                ->leftJoin("user_pet", "user_market.ref_id", "=", "user_pet.id")
                ->where("ref_table", "user_pet")
                ->select(
                    "user_market.sn",
                    "user_market.seller_uid",
                    "user_market.amount",
                    "user_market.amount_wallet_id",
                    "user_market.removed_at",
                    "user_market.sold_at",
                    "user_pet.quality",
                    "user_pet.rank",
                    "user_pet.star",
                    "user_pet.mining_rate",
                    "user_pet.pet_id",
                    "user_pet.id"
                );

            if (isset($cleanVars["payment"])) {
                $payment = [];
                foreach ($cleanVars["payment"] as $name) {
                    $wallet = SettingLogic::get("wallet", ["code" => $name]);
                    if ($wallet) {
                        $payment[] = $wallet["id"];
                    }
                }
                $table->whereIn("amount_wallet_id", $payment);
            }

            if (isset($cleanVars["quality"])) {
                $table->whereIn("quality", $cleanVars["quality"]);
            }

            if (isset($cleanVars["rank"])) {
                $table->whereIn("rank", $cleanVars["rank"]);
            }

            if (isset($cleanVars["star"])) {
                $table->whereBetween("star", [$cleanVars["star"][0] ?? 0, $cleanVars["star"][1] ?? 4]);
            }

            $paginator = $table->orderBy("user_market.amount", "asc")->paginate($request->get("size"), ["*"], "page", $request->get("page"));
            $res = ["items" => $paginator->items(), "count" => $paginator->total()];

            # [result]
            if ($res) {
                foreach ($res["items"] as $row) {
                    $row["image"] = null;
                    $row["name"] = null;

                    $seller = AccountUserModel::where("id", $row["seller_uid"])->first();
                    $row["seller"] = $seller
                        ? $seller["nickname"] ?? $seller["user_id"]
                        : "";

                    $pet = SettingLogic::get("pet", ["id" => $row["pet_id"]]);
                    if ($pet) {
                        $row["image"] = $pet["image"];
                        $row["name"] = $pet["name"];
                    }

                    // payment
                    $row["price"] = $row["amount"] * 1;
                    $wallet = SettingLogic::get("wallet", ["id" => $row["amount_wallet_id"]]);
                    $row["payment"] = $wallet["code"] ?? "";

                    // health
                    $health = PetLogic::countHealth($row["id"]);
                    $row["status"] = PetLogic::checkHealth($health);

                    // locked if is self
                    $row["locked"] = $row["seller_uid"] == $uid ? true : false;
                }

                $this->response = [
                    "success" => true,
                    "data" => [
                        "data" => HelperLogic::formatOutput($res["items"], $this->patternOutputs, 1),
                        "count" => $res["count"],
                        "last_page" => ceil($res["count"] / 25),
                    ],
                ];
            }
        }

        # [standard output]
        return $this->output();
    }
}