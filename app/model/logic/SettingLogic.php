<?php

namespace app\model\logic;

use app\model\database\SettingAnnouncementModel;
use app\model\database\SettingAttributeModel;
use app\model\database\SettingBlockchainNetworkModel;
use app\model\database\SettingCoinModel;
use app\model\database\SettingDepositModel;
use app\model\database\SettingGachaModel;
use app\model\database\SettingGachaItemModel;
use app\model\database\SettingGeneralModel;
use app\model\database\SettingItemModel;
use app\model\database\SettingItemAttributeModel;
use app\model\database\SettingLangModel;
use app\model\database\SettingLevelModel;
use app\model\database\SettingMissionModel;
use app\model\database\SettingNftModel;
use app\model\database\SettingOperatorModel;
use app\model\database\SettingPaymentModel;
use app\model\database\SettingPetModel;
use app\model\database\SettingPetAttributeModel;
use app\model\database\SettingPetRankModel;
use app\model\database\SettingRewardModel;
use app\model\database\SettingRewardAttributeModel;
use app\model\database\SettingWalletModel;
use app\model\database\SettingWalletAttributeModel;
use app\model\database\SettingWithdrawModel;

class SettingLogic
{
    public static function get(string $table = "", array $params = [], bool $list = false)
    {
        $_response = false;

        switch ($table) {
            case "announcement":
                $_response = SettingAnnouncementModel::where($params);
                break;
            case "attribute":
                $_response = SettingAttributeModel::where($params);
                break;
            case "blockchain_network":
                $_response = SettingBlockchainNetworkModel::where($params);
                break;
            case "coin":
                $_response = SettingCoinModel::where($params);
                break;
            case "deposit":
                $_response = SettingDepositModel::where($params);
                break;
            case "gacha":
                $_response = SettingGachaModel::where($params);
                break;
            case "gacha_item":
                $_response = SettingGachaItemModel::where($params);
                break;
            case "general":
                $_response = SettingGeneralModel::where($params)->where("is_show", 1);
                break;
            case "item":
                $_response = SettingItemModel::where($params);
                break;
            case "item_attribute":
                $_response = SettingItemAttributeModel::where($params);
                break;
            case "lang":
                $_response = SettingLangModel::where($params);
                break;
            case "level":
                $_response = SettingLevelModel::where($params);
                break;
            case "mission":
                $_response = SettingMissionModel::where($params);
                break;
            case "nft":
                $_response = SettingNftModel::where($params);
                break;
            case "operator":
                $_response = SettingOperatorModel::where($params);
                break;
            case "payment":
                $_response = SettingPaymentModel::where($params);
                break;
            case "pet":
                $_response = SettingPetModel::where($params);
                break;
            case "pet_attribute":
                $_response = SettingPetAttributeModel::where($params);
                break;
            case "pet_rank":
                $_response = SettingPetRankModel::where($params);
                break;
            case "reward":
                $_response = SettingRewardModel::where($params);
                break;
            case "reward_attribute":
                $_response = SettingRewardAttributeModel::where($params);
                break;
            case "wallet":
                $_response = SettingWalletModel::where($params);
                break;
            case "wallet_attribute":
                $_response = SettingWalletAttributeModel::where($params);
                break;
            case "withdraw":
                $_response = SettingWithdrawModel::where($params);
                break;
        }

        if ($list) {
            $_response = $_response->get();
        } else {
            $_response = $_response->first();
        }

        return $_response;
    }

    public static function getDeposit(array $params = [])
    {
        $_response = false;

        $_response = SettingDepositModel::where($params)
            ->inRandomOrder()
            ->first();

        return $_response;
    }

    public static function getWithdraw(array $params = [])
    {
        $_response = false;

        $_response = SettingWithdrawModel::where($params)
            ->inRandomOrder()
            ->first();

        return $_response;
    }
}
