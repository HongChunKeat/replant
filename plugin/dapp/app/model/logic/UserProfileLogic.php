<?php

namespace plugin\dapp\app\model\logic;

# system lib
use support\Redis;
use Tinywan\Jwt\JwtToken;
# database & logic
use app\model\database\AccountUserModel;
use app\model\database\NetworkSponsorModel;
use app\model\database\UserInviteCodeModel;
use app\model\database\UserSeedModel;
use plugin\dapp\app\model\logic\SecureLogic;
use app\model\logic\HelperLogic;
use app\model\logic\SettingLogic;

class UserProfileLogic
{
    public static function newAuthKey(string $address = "")
    {
        $_response = false;
        $address = strtolower($address);

        $_response = "authentication message for login:" . HelperLogic::randomCode();
        Redis::setEx("user_authkey:{$address}", 30, $_response);

        return $_response;
    }

    public static function verifyAuthKey(string $address = "", string $sign = "", bool $isDebug = false)
    {
        $_response = false;
        $getAuthKey = Redis::get("user_authkey:{$address}");

        // test msg sign
        if ($isDebug) {
            $address = "0x70997970c51812dc3a010c7d01b50e0d17dc79c8";
            $getAuthKey = "Message for signing";
            $sign = "0xf3bf58813ed135d610db573848fcdc25eb3f7370e2d7cd9f6eb1855579a616b4595ba719bd7115bb207782bb2d06c743791f1b9cd15a39a603f3b995d16071731c";
        }

        if ($getAuthKey) {
            // verify the signature content
            if (SecureLogic::verifyEthSign($getAuthKey, $sign, $address)) {
                self::removeAuthKey($address);
                $_response = true;
            }
        }

        return $_response;
    }

    public static function removeAuthKey(string $address = "")
    {
        return Redis::del("user_authkey:{$address}");
    }

    public static function newAccessToken(string $user_id = "", array $info = [])
    {
        $newToken = JwtToken::generateToken($info);

        // 3 hours
        Redis::setEx("user_accessToken:{$user_id}", 10800, $newToken["access_token"]);

        return $newToken;
    }

    public static function getAccessToken(string $user_id = "")
    {
        return Redis::get("user_accessToken:{$user_id}");
    }

    public static function logout(string $user_id = "")
    {
        JwtToken::clear();
        return Redis::del("user_accessToken:{$user_id}");
    }

    public static function checkTelegramAuthorization($authData)
    {
        $response = false;
        $err = "";

        $botToken = ENV("TELEGRAM_API_SECRET");
        $checkHash = $authData["hash"];
        unset($authData["hash"]);

        $dataCheckArr = [];
        foreach ($authData as $key => $value) {
            $dataCheckArr[] = $key . "=" . $value;
        }

        sort($dataCheckArr);
        $dataCheckString = implode("\n", $dataCheckArr);

        $secretKey = hash("sha256", $botToken, true);
        $hash = hash_hmac("sha256", $dataCheckString, $secretKey);

        if (strcmp($hash, $checkHash) !== 0) {
            $err = "data_is_not_from_telegram";
        } else if ((time() - $authData["auth_date"]) > 86400) {
            $err = "data_is_outdated";
        } else {
            $response = true;
        }

        return [
            "success" => $response,
            "data" => $authData,
            "msg" => $err
        ];
    }

    public static function checkXAuthorization($code = null, $toInternal = false)
    {
        $response = false;
        $data = "";
        $err = "";

        $dapp = SettingLogic::get("general", ["category" => "website", "code" => "dapp_website"]);
        $dapp = ($toInternal)
            ? $dapp["value"] . "/profile"
            : $dapp["value"];

        $token = base64_encode(ENV("X_API_CLIENT_ID") . ":" . ENV("X_API_SECRET"));

        // auth to get access token first
        $auth = HelperLogic::httpSend(
            "POST",
            "https://api.twitter.com/2/oauth2/token",
            [
                "client_id" => ENV("X_API_CLIENT_ID"),
                "code_verifier" => "8KxxO-RPl0bLSxX5AWwgdiFbMnry_VOKzFeIlVA7NoA",
                "redirect_uri" => $dapp,
                "grant_type" => "authorization_code",
                "code" => $code,
            ],
            ["Authorization: Basic " . $token]
        );

        // if got access token then get user info
        if (isset($auth["access_token"])) {
            $user = HelperLogic::httpSend(
                "GET",
                "https://api.twitter.com/2/users/me",
                [
                    "user.fields" => "profile_image_url,username"
                ],
                ["Authorization: Bearer " . $auth["access_token"]]
            );

            if (isset($user["data"])) {
                $response = true;
                $data = $user["data"];
            } else {
                $err = isset($user["title"])
                    ? $user["title"]
                    : "failed";
            }
        } else {
            $err = $auth["error"];
        }

        return [
            "success" => $response,
            "data" => $data,
            "msg" => $err
        ];
    }

    public static function multiSearch($search)
    {
        $response = false;

        if (str_starts_with($search, "0x") !== false) {
            $response = AccountUserModel::where(["web3_address" => $search, "status" => "active"])->first();
        } else if (strlen($search) == 16) {
            $response = AccountUserModel::where(["user_id" => $search, "status" => "active"])->first();
        } else {
            $response = AccountUserModel::where(["login_id" => strtoupper($search), "status" => "active"])->first();
        }

        return $response;
    }

    public static function init($id)
    {
        UserInviteCodeModel::create([
            "uid" => $id,
            "code" => HelperLogic::generateUniqueSN("user_invite_code", 6, "int"),
            "usage" => 5
        ]);

        UserSeedModel::create([
            "uid" => $id,
            "claimable" => 1,
        ]);
    }

    public static function delete($id)
    {
        UserInviteCodeModel::where("uid", $id)->delete();
    }

    public static function getCountdown($interval)
    {
        $currentHour = date("G"); // 24-hour format
        $currentMinute = date("i");

        // Convert to minutes since midnight
        $minutesSinceMidnight = ($currentHour * 60) + $currentMinute;

        // Calculate the time left until the next minute interval
        $timeLeft = date("Y-m-d H:i:00", strtotime("+" . ($interval - ($minutesSinceMidnight % $interval)) . " minutes"));
        $countdown = strtotime($timeLeft) - time();

        return $countdown;
    }

    public static function bindUpline(int $userId = 0, int $uplineId = null)
    {
        $_response = false;

        //default null
        if ($uplineId !== null) {
            //get upline
            $uplineNetwork = NetworkSponsorModel::where("uid", $uplineId)->first();

            //if upline is verfied
            if ($uplineNetwork) {
                //check user exist in network or not
                $userNetwork = NetworkSponsorModel::where("uid", $userId)->first();

                //if exist then update, else create
                if ($userNetwork) {
                    $_response = NetworkSponsorModel::where("uid", $userId)->update(["upline_uid" => $uplineId]);
                } else {
                    $_response = NetworkSponsorModel::create(["uid" => $userId, "upline_uid" => $uplineId]);
                }
            }
        } else {
            // for root user, if got 1 person only then is root because already created
            if (AccountUserModel::count("id") == 1) {
                NetworkSponsorModel::create(["uid" => $userId, "upline_uid" => 0]);
            }

            $_response = true;
        }

        return $_response;
    }
}
