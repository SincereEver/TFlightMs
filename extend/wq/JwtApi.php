<?php

namespace wq;

use \GuzzleHttp\Client;

class JwtApi
{
    public function __construct($authorization)
    {
        $this->http = new Client(['headers' => ['Authorization' => 'Bearer ' . $authorization], 'verify' => false, 'http_errors' => false, 'base_uri' => 'https://api.appstoreconnect.apple.com/v1/']);
    }
    public function getUserList()
    {
        $res = $this->http->get('users');
        if ($res->getStatusCode() != 200) {
            throw new \think\Exception('获取用户列表失败');
        } {
            return true;
        }
    }
    /**
     * 列出在App Store Connect中的应用程序
     */
    public function getApps($q = '')
    {
        $res = $this->http->get('apps' . $q);
        $code = $this->code($res);
        //die($res->getStatusCode());
        //die($res->getBody());
        if ($code != 200) throw new \think\Exception('获取app列表异常');
        return $this->arr($res)['data'];
    }

    /**
     * 列出在App中builds信息
     *
     * @param string $appid
     * @param string $q 查询参数
     * @return void
     */
    public function getAppsBuilds(string $appid, $q = '')
    {
        $res = $this->http->get('apps/' . $appid . '/builds' . $q);
        $code = $this->code($res);
        if ($code != 200) throw new \think\Exception('JWT异常');
        return $this->arr($res)['data'];
    }

    /**
     * 获取在App中内部测试组
     *
     * @param string $appid
     * @param string $q 查询参数
     * @return void
     */
    public function getAppsBuildGroup(string $appid, $q = '?fields[betaGroups]=isInternalGroup')
    {
        $res = $this->http->get('apps/' . $appid . '/betaGroups' . $q);
        $code = $this->code($res);
        if ($code != 200) throw new \think\Exception('JWT异常');
        $list = $this->arr($res)['data'];
        foreach ($list as $l) {
            if ($l['attributes']['isInternalGroup'] == true) {
                return $l['id'];
            }
        }
    }

    /**
     * 添加测试人员
     *
     * @param string $gid
     * @param array $arr
     * @return void
     */
    public function addTesters(string $gid, array $arr)
    {
        $json = [
            'data' => $arr
        ];

        $res = $this->http->post("betaGroups/$gid/relationships/betaTesters", ['json' => $json]);
        $code = $this->code($res);
        if ($code != 204) throw new \think\Exception('添加测试人员失败' . $res->getBody());
        return true;
    }

    /**
     * 删除测试组测试人员
     *
     * @param string $gid
     * @param array $arr
     * @return void
     */
    public function delGroupTesters(string $gid, string $appid)
    {
        $arr = $this->getGroupTesters($gid, '?fields[betaTesters]=firstName&limit=100');
        if (empty($arr)) {
            return true;
        }
        //die(json_encode($arr));
        foreach ($arr as $v) {
            $testersData[] = array_visible(['id', 'type'], $v);
        }



        $json = [
            'data' => $testersData
        ];
        $res = $this->http->delete("apps/$appid/relationships/betaTesters", ['json' => $json]);
        $code = $this->code($res);
        if ($code != 204) throw new \think\Exception('删除测试人员失败');
        return true;
    }
    /**
     * 列出Beta组中的所有Beta测试人员
     *
     * @param string $gid
     * @return void
     */
    public function getGroupTesters(string $gid, string $q = '')
    {
        $res = $this->http->get("betaGroups/$gid/betaTesters" . $q);
        $code = $this->code($res);
        //die($res->getBody());
        if ($code != 200) throw new \think\Exception('列出Beta组中的所有Beta测试人员失败');

        return  $this->arr($res)['data'];
    }




    protected function code($res)
    {
        return $res->getStatusCode();
    }
    protected function arr($res)
    {
        return json_decode($res->getBody(), true);
    }
}
