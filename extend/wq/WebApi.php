<?php

namespace wq;

use \GuzzleHttp\Client;

class WebApi
{

    /**
     * 初始化
     *
     * @param [type] $cookie 
     * @param [type] $check 是否验证cookie
     */
    public function __construct($cookie, $check = false)
    {
        $this->http = new Client(['headers' => ['cookie' => $cookie], 'verify' => false, 'http_errors' => false, 'base_uri' => 'https://appstoreconnect.apple.com/']);
        if ($check) {
            //https://appstoreconnect.apple.com/olympus/v1/session
            $res = $this->http->get('olympus/v1/session');
            if ($res->getStatusCode() != 200) {
                throw new \think\Exception('账号会话过期', 8888);
            }
        }
    }

    public function getUserInfo()
    {
        $res = $this->http->get('olympus/v1/session');
        $code = $res->getStatusCode();
        if ($code != 200) throw new \think\Exception('获取用户数据失败');
        $data = json_decode($res->getBody(), true);
        $info = [
            'lssuer_id' => $data['availableProviders'][0]['publicProviderId'],
            'team_name' => $data['availableProviders'][0]['name'],
            'user_name' => $data['user']['lastName'] . $data['user']['firstName'],
        ];
        return $info;
    }

    /**
     * 添加测试人员
     *
     * @param string $groups 应用组id
     * @param array $mail_arr 测试人员邮件地址 ['email'=>'1150383838@qq.com']
     * @return void
     */
    public function createTestets(string $groupsId, array $mail_arr)
    {
        $json = [
            'data' => [
                'attributes' => [
                    'betaTesters' => $mail_arr
                ],
                'relationships' => [
                    'betaGroup' => [
                        'data' => [
                            'id' => $groupsId,
                            'type' => 'betaGroups'
                        ]
                    ]
                ],
                'type' => 'bulkBetaTesterAssignments'
            ]
        ];
        $res = $this->http->post('iris/v1/bulkBetaTesterAssignments', ['json' => $json]);
        $code = $this->code($res);
        if ($code != 201) throw new \think\Exception('添加测试人员失败', $code);
        return $this->arr($res)['data']['attributes']['betaTesters'];
    }


    /**
     * 创建应用组
     *
     * @param string $app_id 应用ID
     * @param string $name 组名称
     * @return string 返回应用组ID
     */
    public function createGroups(string $app_id, string $name = '网圈专用组')
    {
        $json = [
            'data' => [
                'type' => 'betaGroups',
                'attributes' => [
                    'name' => $name
                ],
                'relationships' => [
                    'app' => [
                        'data' => [
                            'type' => 'apps',
                            'id' => $app_id
                        ]
                    ]
                ]
            ]
        ];

        $res = $this->http->post('iris/v1/betaGroups', ['json' => $json]);
        $code = $this->code($res);
        if ($code != 201) throw new \think\Exception('创建应用组失败', $code);
        return $this->arr($res)['data']['id'];
    }

    /**
     * 创建P8证书
     *
     * @param string $name 
     * @return void
     */
    public function create_p8($name = '平台专用')
    {
        $json['data'] = [
            'attributes' => [
                'allAppsVisible' => true,
                'keyType' => 'PUBLIC_API',
                'nickname' => $name,
                'roles' => [0 => 'ADMIN'],
            ],
            'type' => 'apiKeys'
        ];
        $res = $this->http->post('iris/v1/apiKeys', ['json' => $json]);
        $code = $res->getStatusCode();
        if ($code == 201) {
            $keyid = json_decode($res->getBody(), true)['data']['id'];
            $url = 'iris/v1/apiKeys/' . $keyid . '?fields[apiKeys]=privateKey';
            $res = $this->http->get($url);
            if ($res->getStatusCode() == 200) {
                $p8_base64 = json_decode($res->getBody(), true)['data']['attributes']['privateKey'];
                return ['key_id' => $keyid, 'p8_base64' => $p8_base64];
            } else {
                throw new \think\Exception('获取p8失败，请重试');
            }
        } else {
            throw new \think\Exception('创建p8异常，请检查开发者账号');
        }
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
