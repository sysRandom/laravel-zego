<?php

namespace Sysrandom\Zego;

use Sysrandom\Zego\Exceptions\ZegoException;
use Sysrandom\Zego\Support\ZegoErrorCodes;

class Zego
{
    /**
     * Generate Zego Token
     *
     * @param string $user_id User ID of user
     * @param array $payload Payload data
     *
     * @return string returns Zego Token
     */
    public function generate_token(string $user_id, array $payload = []): string
    {
        $appId = config('zego.app_id');
        $appSecret = config('zego.app_secret');
        $expiry = config('zego.token_expiry');
        if ($appId == 0) {
            throw new ZegoException(ZegoErrorCodes::appIDInvalid);
        }
        if ($expiry <= 0) {
            throw new ZegoException(ZegoErrorCodes::effectiveTimeInSecondsInvalid);
        }
        if ($user_id == '') {
            throw new ZegoException(ZegoErrorCodes::userIDInvalid);
        }
        $cipher = 'aes-256-cbc';
        $keyLen = strlen($appSecret);
        switch ($keyLen) {
            case 16:
                $cipher = 'aes-128-cbc';
            case 24:
                $cipher = 'aes-192-cbc';
            case 32:
                $cipher = 'aes-256-cbc';
            default:
                throw new ZegoException(ZegoErrorCodes::secretInvalid);
        }
        $timestamp = time();
        $data_package = [
            'app_id' => $appId,
            'user_id' => $user_id,
            'nonce' => unpack('I', openssl_random_pseudo_bytes(20))[1],
            'ctime' => $timestamp,
            'expire' => $timestamp + $expiry,
            'payload' => empty($payload) ? '' : json_encode($payload),
        ];
        $plaintext = json_encode($data_package, JSON_BIGINT_AS_STRING);
        $iv = $this->makeIv();
        $encrypted = openssl_encrypt($plaintext, $cipher, $appSecret, OPENSSL_RAW_DATA, $iv);
        $packData = [
            strlen($iv),$iv,strlen($encrypted),$encrypted
        ];
        $binary = pack('J', $data_package['expire']);
        $binary .= pack('na*na*', ...$packData);
        return '04' . base64_encode($binary);
    }

    private function makeIv($length = 16): string
    {
        $str = explode(' ','0 1 2 3 4 5 6 7 8 9 a b c d e f g h i j k l m n o p q r s t u v w x y z');
        $strLen = count($str);
        $return = [];
        for ($i=0; $i < $length; $i++) {
            $result[] = $str[rand(0,$strLen-1)];
        }
        return implode('', $result);
    }

}
