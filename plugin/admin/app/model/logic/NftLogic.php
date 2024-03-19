<?php

namespace plugin\admin\app\model\logic;

# database & logic
use app\model\database\AccountUserModel;
use app\model\logic\SettingLogic;
use app\model\logic\EvmLogic;

class NftLogic
{
    public static function countMultiplier($uid)
    {
        $response = 0;

        $user = AccountUserModel::where("id", $uid)->first();
        if ($user) {
            $multiplier1 = SettingLogic::get("general", ["category" => "reward", "code" => "gen1_nft_multiplier"]);
            $multiplier2 = SettingLogic::get("general", ["category" => "reward", "code" => "gen2_nft_multiplier"]);

            // count gen 1 nft
            $gen1Nft = SettingLogic::get("nft", ["name" => "gen1_nft"]);
            $gen1Network = SettingLogic::get("blockchain_network", ["id" => $gen1Nft["network"]]);
            $gen1NftCount = EvmLogic::getBalance("nft", $gen1Network["rpc_url"], $gen1Nft["token_address"], $user["web3_address"]);
            $gen1Multiplier = $gen1NftCount * $multiplier1["value"];

            // count gen 2 nft
            $gen2Nft = SettingLogic::get("nft", ["name" => "gen2_nft"]);
            $gen2Network = SettingLogic::get("blockchain_network", ["id" => $gen2Nft["network"]]);
            $gen2NftCount = EvmLogic::getBalance("nft", $gen2Network["rpc_url"], $gen2Nft["token_address"], $user["web3_address"]);
            $gen2Multiplier = $gen2NftCount * $multiplier2["value"];

            $response = $gen1Multiplier + $gen2Multiplier;
        }

        return $response;
    }

    public static function findTokenId($uid)
    {
        $response = 0;

        $user = AccountUserModel::where("id", $uid)->first();
        if ($user) {
            // get seed token id array
            $plantNft = SettingLogic::get("nft", ["name" => "plant"]);
            $plantNetwork = SettingLogic::get("blockchain_network", ["id" => $plantNft["network"]]);
            $plantTokenId = EvmLogic::getBalance("nft", $plantNetwork["rpc_url"], $plantNft["token_address"], $user["web3_address"]);

            // get gen 1 token id array
            $gen1Nft = SettingLogic::get("nft", ["name" => "gen1_nft"]);
            $gen1Network = SettingLogic::get("blockchain_network", ["id" => $gen1Nft["network"]]);
            $gen1TokenId = EvmLogic::getBalance("nft", $gen1Network["rpc_url"], $gen1Nft["token_address"], $user["web3_address"]);

            // get gen 2 token id array
            $gen2Nft = SettingLogic::get("nft", ["name" => "gen2_nft"]);
            $gen2Network = SettingLogic::get("blockchain_network", ["id" => $gen2Nft["network"]]);
            $gen2TokenId = EvmLogic::getBalance("nft", $gen2Network["rpc_url"], $gen2Nft["token_address"], $user["web3_address"]);

            $response = [
                "plant" => $plantTokenId,
                "gen1" => $gen1TokenId,
                "gen2" => $gen2TokenId
            ];
        }

        return $response;
    }
}
