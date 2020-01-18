<?php
/**
 * Created by PhpStorm.
 * User: Django
 * Date: 1/18/2020
 * Time: 9:07 AM
 */

namespace App\Http\Controllers\helper;


use Illuminate\Http\JsonResponse;

class Reusable
{
    public const SHA_256 = 'sha256';
    /**
     * @param $string
     * @param $secret_key
     * @param $secret_iv
     * @return string
     */
    public static function encrypt($string, $secret_key, $secret_iv)
    {
        $encrypt_method = "cast5-cfb";
        $password = hash(self::SHA_256, $secret_key);
        $iv = substr(hash(self::SHA_256, $secret_iv), 0, 8);
        return base64_encode(openssl_encrypt($string, $encrypt_method, $password, 0, $iv));
    }

    /**
     * @param $string
     * @param $secret_key
     * @param $secret_iv
     * @return string
     */
    public static function decrypt($string, $secret_key, $secret_iv)
    {
        $encrypt_method = "cast5-cfb";
        $password = hash(self::SHA_256, $secret_key);
        $iv = substr(hash(self::SHA_256, $secret_iv), 0, 8);
        return openssl_decrypt(base64_decode($string), $encrypt_method, $password, 0, $iv);
    }


    /**
     * @param $body
     * @param $message
     * @param $statusCode
     * @param $statusMessage
     * @return JsonResponse
     */
    static function returnDataInJson($body, $message, $statusCode, $statusMessage)
    {
        $data = [
            "body" => $body,
            "message" => $message,
        ];
        return (new JsonResponse($data))->setStatusCode($statusCode, $statusMessage);
    }
}
