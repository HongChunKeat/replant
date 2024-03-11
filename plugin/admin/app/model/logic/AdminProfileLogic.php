<?php

namespace plugin\admin\app\model\logic;

# system lib
use support\Redis;
use Tinywan\Jwt\JwtToken;
# database & logic
use plugin\admin\app\model\logic\SecureLogic;
use app\model\logic\HelperLogic;

class AdminProfileLogic
{
    public static function newAuthKey(string $address = "")
    {
        $_response = false;
        $address= strtolower($address);

        $_response = "authentication message for login:" . HelperLogic::randomCode();
        Redis::setEx("admin_authkey:{$address}", 30, $_response);

        return $_response;
    }

    public static function verifyAuthKey(string $address = "", string $sign = "", bool $isDebug = false)
    {
        $_response = false;
        $getAuthKey = Redis::get("admin_authkey:{$address}");

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
        return Redis::del("admin_authkey:{$address}");
    }

    public static function newAccessToken(string $admin_id = "", array $info = [])
    {
        $newToken = JwtToken::generateToken($info);

        // 3 hours
        Redis::setEx("admin_accessToken:{$admin_id}", 10800, $newToken["access_token"]);

        return $newToken;
    }

    public static function getAccessToken(string $admin_id = "")
    {
        return Redis::get("admin_accessToken:{$admin_id}");
    }

    public static function logout(string $admin_id = "")
    {
        JwtToken::clear();
        return Redis::del("admin_accessToken:{$admin_id}");
    }
}