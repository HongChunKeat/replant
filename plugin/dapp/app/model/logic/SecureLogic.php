<?php

declare(strict_types=1);

namespace plugin\dapp\app\model\logic;

use Elliptic\EC as EllEC;
use kornrunner\Keccak;

final class SecureLogic
{
    public static function verifyEthSign($message, $signature, $address): bool
    {
        $msglen = strlen($message);
        $hash = Keccak::hash("\x19Ethereum Signed Message:\n{$msglen}{$message}", 256);
        $sign = ["r" => substr($signature, 2, 64), "s" => substr($signature, 66, 64)];
        $recid = ord(hex2bin(substr($signature, 130, 2))) - 27;
        if ($recid != ($recid & 1)) {
            return false;
        }

        $ec = new EllEC("secp256k1");
        $pubkey = $ec->recoverPubKey($hash, $sign, $recid);

        return strtolower($address) == self::pubKeyToAddress($pubkey);
    }

    private static function pubKeyToAddress($pubkey)
    {
        return strtolower("0x" . substr(Keccak::hash(substr(hex2bin($pubkey->encode("hex")), 1), 256), 24));
    }

    public static function encrypt($txt, $key = "str")
    {
        $txt = $txt . $key;
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-=+_#%&!@";
        $nh = rand(0, 64);
        $ch = $chars[$nh];
        $mdKey = md5($key . $ch);
        $mdKey = substr($mdKey, $nh % 8, ($nh % 8) + 7);
        $txt = base64_encode($txt);
        $tmp = "";
        $i = 0;
        $j = 0;
        $k = 0;
        for ($i = 0; $i < strlen($txt); $i++) {
            $k = $k == strlen($mdKey) ? 0 : $k;
            $j = ($nh + strpos($chars, $txt[$i]) + ord($mdKey[$k++])) % 64;
            $tmp .= $chars[$j];
        }
        return urlencode(base64_encode($ch . $tmp));
    }

    public static function decrypt($txt, $key = "str")
    {
        $txt = base64_decode(urldecode($txt));
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-=+_#%&!@";
        $ch = $txt[0];
        $nh = strpos($chars, $ch);
        $mdKey = md5($key . $ch);
        $mdKey = substr($mdKey, $nh % 8, ($nh % 8) + 7);
        $txt = substr($txt, 1);
        $tmp = "";
        $i = 0;
        $j = 0;
        $k = 0;
        for ($i = 0; $i < strlen($txt); $i++) {
            $k = $k == strlen($mdKey) ? 0 : $k;
            $j = strpos($chars, $txt[$i]) - $nh - ord($mdKey[$k++]);
            while ($j < 0) {
                $j += 64;
            }
            $tmp .= $chars[$j];
        }
        return trim(base64_decode($tmp), $key);
    }
}
?>
