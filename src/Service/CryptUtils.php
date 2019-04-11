<?php

namespace App\Service;

/**
 * Created by PhpStorm.
 * User: ThanhDT
 * Date: 8/21/2018
 * Time: 2:25 PM
 */
class CryptUtils
{
    const ENCRYPT_METHOD = 'AES-128-CBC';
    private $cryptInfo;

    public function __construct($cryptInfo)
    {
        $this->cryptInfo = $cryptInfo;
    }

    /**
     * Encrypt data
     * author: ThanhDT
     * date:   2018-11-09 11:26 AM
     * @param $data : String only
     * @return string
     */
    public function encrypt($data)
    {
        $token = self::AES_encrypt($data, $this->cryptInfo['key'], $this->cryptInfo['iv']);

        return $token;
    }

    /**
     * Decrypt data
     * author: ThanhDT
     * date:   2018-11-09 11:26 AM
     * @param $encryptedData
     * @return bool
     */
    public function decrypt($encryptedData)
    {
        try {
            $data = self::AES_decrypt($encryptedData, $this->cryptInfo['key'], $this->cryptInfo['iv']);
        } catch (\Exception $ex) {
            return false;
        }

        return $data;
    }

    /**
     * Generate token with time expire
     * author: ThanhDT
     * date:   2018-08-21 03:55 PM
     * @return string
     */
    public function encryptToken()
    {
        $data = [
            'checksum' => $this->cryptInfo['token'],
            'current_time' => date('Y-m-d h:i:s')
        ];
        $token = self::AES_encrypt(json_encode($data), $this->cryptInfo['key']);

        return $token;
    }

    /**
     * Validate token string
     * @param $encryptString : Encrypted token from query string
     * @return bool
     */
    public function validateToken($encryptString)
    {
        $tokenInfo = self::decryptToken($encryptString);
        if (!$tokenInfo) {
            return false;
        }

        return $tokenInfo['checksum'] == $this->cryptInfo['token'];
    }

    /**
     * Validate token
     * author: ThanhDT
     * date:   2018-08-21 03:56 PM
     * @param $encryptString
     * @return bool|string
     */
    public function decryptToken($encryptString)
    {
        try {
            $token = self::AES_decrypt($encryptString, $this->cryptInfo['key']);
        } catch (\Exception $ex) {
            return false;
        }
        $arrInfo = json_decode($token, true);
        if (!count($arrInfo)) {
            return false;
        }
        $encryptTime = strtotime($arrInfo['current_time']);
        $expireTime = strtotime('-1 day');
        if ($encryptTime < $expireTime) {
            return false;
        }

        return $arrInfo;
    }

    public function AES_encrypt($string, $AES_Key, $AES_IV = null)
    {
        $key = base64_decode($AES_Key);
        if ($AES_IV != null) {
            $iv = base64_decode($AES_IV);
        } else {
            $ivSize = openssl_cipher_iv_length(self::ENCRYPT_METHOD);
            $iv = openssl_random_pseudo_bytes($ivSize);
        }
        $cipherText = openssl_encrypt($string, self::ENCRYPT_METHOD, $key, OPENSSL_RAW_DATA, $iv);

        return $AES_IV == null ? base64_encode($iv . $cipherText) : base64_encode($cipherText);
    }

    public function AES_decrypt($string, $AES_Key, $AES_IV = null)
    {
        $key = base64_decode($AES_Key);
        //$iv = $AES_IV != null ? base64_decode($AES_IV) : null;
        $string = base64_decode($string);
        if ($AES_IV != null) {
            $iv = base64_decode($AES_IV);
        } else {
            $ivSize = openssl_cipher_iv_length(self::ENCRYPT_METHOD);
            $iv = substr($string, 0, $ivSize);
            $string = substr($string, $ivSize);
        }

        $decryptString = openssl_decrypt($string, self::ENCRYPT_METHOD, $key, OPENSSL_RAW_DATA, $iv);
        return $decryptString;
    }
}
