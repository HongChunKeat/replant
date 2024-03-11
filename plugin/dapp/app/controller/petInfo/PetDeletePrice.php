<?php

namespace plugin\dapp\app\controller\petInfo;

# library
use plugin\dapp\app\controller\Base;
use support\Request;
# database & logic
use app\model\database\UserPetModel;
use app\model\logic\HelperLogic;

class PetDeletePrice extends Base
{
    # [validation-rule]
    protected $rule = [
        "pets" => "require|max:500",
    ];

    # [inputs-pattern]
    protected $patternInputs = [
        "pets",
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
            # [process]
            if (count($cleanVars) > 0) {
                $xtendoTotal = 0;
                $gtendoTotal = 0;

                $normalRefundPrice = HelperLogic::buildAttributeGeneral(["category" => "pet", "code" => "normal_refund_price"]);
                $premiumRefundPrice = HelperLogic::buildAttributeGeneral(["category" => "pet", "code" => "premium_refund_price"]);

                $pets = UserPetModel::defaultWhere()->whereIn("sn", $cleanVars["pets"])->get();

                foreach ($pets as $pet) {
                    if ($pet["quality"] == "premium") {
                        $gtendoTotal += $premiumRefundPrice[0]["value"];
                    } else {
                        $xtendoTotal += $normalRefundPrice[0]["value"];
                    }
                }

                # [result]
                $this->response = [
                    "success" => true,
                    "data" => [
                        "xtendo" => $xtendoTotal,
                        "gtendo" => $gtendoTotal,
                    ]
                ];
            }
        }

        # [standard output]
        return $this->output();
    }

    private function checking(array $params = [])
    {
        # [condition]
        if (isset($params["pets"])) {
            if (count($params["pets"]) > 25) {
                $this->error[] = "pets:only_25_allowed";
            }
        }
    }
}