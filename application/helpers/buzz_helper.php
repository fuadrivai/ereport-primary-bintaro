<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('get_buzz_token')) {
    function get_buzz_token()
    {
        $cacheFile = APPPATH . "cache/buzz_token.json";

        // cek apakah token masih valid
        if (file_exists($cacheFile)) {
            $data = json_decode(file_get_contents($cacheFile), true);
            if ($data && isset($data['token']) && isset($data['expires']) && $data['expires'] > time()) {
                return $data['token'];
            }
        }

        // request baru kalau expired
        $loginData = [
            "request" => [
                "cmd" => "login2",
                "username" => "mhisplus/A001",
                "password" => "JavaJava1414"
            ]
        ];

        $ch = curl_init("http://103.247.217.153:8000");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $loginResponse = curl_exec($ch);
        curl_close($ch);

        $xml = simplexml_load_string($loginResponse);
        $tokenNodes = $xml->xpath('//user');
        if (!$tokenNodes || !isset($tokenNodes[0]['token'])) {
            return null;
        }

        $token = (string) $tokenNodes[0]['token'];

        // simpan 3 menit
        $data = [
            'token'   => $token,
            'expires' => time() + 600
        ];
        file_put_contents($cacheFile, json_encode($data));

        return $token;
    }
}
