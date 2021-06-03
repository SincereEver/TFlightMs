<?php

namespace app\api\controller;

use app\common\controller\Api;

/**
 * 首页接口
 */
class Index extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function index()
    {
        $config = array(
            'config' => ROOT_PATH . '/openssl.cnf',
            'digest_alg' => 'sha512',
            'private_key_bits' => 512,                     //字节数    512 1024  2048   4096 等
            'private_key_type' => OPENSSL_KEYTYPE_RSA,     //加密类型
        );
        echo sys_get_temp_dir();

        $private_key = openssl_pkey_new($config);
        if ($private_key) {
            $public_key_pem = openssl_pkey_get_details($private_key)['key'];
            echo $public_key_pem;
        } else {
            echo '失败';
        }
    }
}
