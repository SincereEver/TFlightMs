<?php

namespace wq;

use \GuzzleHttp\Client;
use \think\Cache;

class AppleLogin
{
    public $LoginInfo = [];
    public $Key = '';
    protected $headers = [];
    public function __construct($appid, $password)
    {
        $this->LoginInfo['appid'] = $appid;
        $this->LoginInfo['password'] = $password;
        $this->Key = 'e0b80c3bf78523bfe80974d320935bfa30add02e1bff88ec2166c6bd5a706c42';
        //$this->http = Client
        //X-Apple-WidgetKey
    }
    /**
     * 发送验证码
     *
     * @return void
     */
    public function sendCode()
    {

        $this->http = new Client(['headers' => ['Accept' => 'application/json, text/javascript', 'Accept-Language' => 'zh-CN,zh;q=0.9', 'X-Apple-Widget-Key' => $this->Key], 'base_uri' => 'https://idmsa.apple.com/appleauth/', 'verify' => false, 'http_errors' => false]);
        //验证账号密码
        $res = $this->http->post(
            'auth/signin?isRememberMeEnabled=true',
            ['json' => [
                'accountName' => $this->LoginInfo['appid'],
                'password' => $this->LoginInfo['password'],
                'rememberMe' => true
            ]]
        );
        $statusCode = $res->getStatusCode();
        if ($statusCode == 401) throw new \think\Exception('App ID 或密码错误', $statusCode);
        if ($statusCode != 409) throw new \think\Exception('账号密码验证失败', $statusCode);
        $headers = array_visible(['X-Apple-ID-Session-Id', 'scnt'], $res->getHeaders());
        //获取验证码header信息
        $res = $this->http->get('auth', ['headers' => $headers]);
        $statusCode = $res->getStatusCode();
        if ($statusCode != 200 && $statusCode != 201) throw new \think\Exception('获取验证码失败，请稍后再试', $statusCode);
        $headers = array_visible(['X-Apple-ID-Session-Id', 'scnt'], $res->getHeaders());
        $headers['type'] = 1;
        $this->headers($headers);
        return true;
    }

    /**
     * 重新获取验证码
     *
     * @return void
     */
    public function reSendCode()
    {
        $headers = $this->headers();

        if (empty($headers)) throw new \think\Exception('请先获取验证码');
        $this->http = new Client(['headers' => $headers, 'base_uri' => 'https://idmsa.apple.com/appleauth/', 'verify' => false, 'http_errors' => false]);
        $json = ['mode' => 'sms', 'phoneNumber' => ['id' => 2]];
        $res = $this->http->put('auth/verify/phone', ['json' => $json]);
        $statusCode = $res->getStatusCode();

        if ($statusCode == 401) throw new \think\Exception('登录会话过期，请重新获取验证码', $statusCode);
        if ($statusCode != 200) throw new \think\Exception('获取验证码失败，请稍后再试', $statusCode);

        $headers = array_visible(['X-Apple-ID-Session-Id', 'scnt'], $res->getHeaders());
        $headers['type'] = 2;
        $this->headers($headers);
        return true;
    }
    /**
     * 效验验证码返回cookie
     *
     * @param [type] $code
     * @return void
     */
    public function verifyCode($code)
    {
        $headers = $this->headers();

        $type = $headers['type'];
        unset($headers['type']);
        if (empty($headers)) throw new \think\Exception('请先获取验证码');
        $this->http = new Client(['headers' => $headers, 'base_uri' => 'https://idmsa.apple.com/appleauth/', 'verify' => false, 'http_errors' => false]);
        /*if ($type == 1) {
            $json = ['securityCode' => ['code' => $code]];
            $res = $this->http->post('auth/verify/trusteddevice/securitycode', ['json' => $json]);
        } else {
            $json = ['mode' => 'sms', 'phoneNumber' => ['id' => 2], 'securityCode' => ['code' => $code]];
            $res = $this->http->post('auth/verify/phone/securitycode', ['json' => $json]);
        }*/
        $json = ['mode' => 'sms', 'phoneNumber' => ['id' => 2], 'securityCode' => ['code' => $code]];
        $res = $this->http->post('auth/verify/phone/securitycode', ['json' => $json]);
        $statusCode = $res->getStatusCode();

        if ($statusCode != 200 && $statusCode != 204) {

            throw new \think\Exception('验证码不正确 验证码方式' . $type, $statusCode);
        }
        $res = $this->http->get('auth/2sv/trust', ['headers' => $this->headers()]);
        $httpCode = $res->getStatusCode();
        if ($httpCode != 204) {

            throw new \think\Exception('获取cookie异常', $httpCode);
        }
        $setCookies = $res->getHeaders()['Set-Cookie'];
        $cookie = '';
        foreach ($setCookies as $setCookie) {
            $cookie = $cookie . ' ' . explode(' ', $setCookie)[0];
        }
        return $cookie;
    }
    /**
     * 获取账号/保存账号 headers信息
     *
     * @param array $headers
     * @return void
     */
    protected function headers($headers = [])
    {
        $key = $this->LoginInfo['appid'] . $this->LoginInfo['password'] . 'login';

        if (!empty($headers)) {
            //保存数组
            //'Accept' => 'application/json, text/javascript', 'Accept-Language' => 'zh-CN,zh;q=0.9',

            $headers['X-Apple-Widget-Key'] = $this->Key;
            $headers['Accept'] = 'application/json, text/javascript';
            $headers['Accept-Language'] = 'zh-CN,zh;q=0.9';
            Cache::set($key, json_encode($headers), 300);
        } else {
            //获取数组
            $headers = Cache::get($key);

            if (!$headers) return [];
            return json_decode($headers, true);
        }
    }
}
