<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 应用列管理
 *
 * @icon fa fa-circle-o
 */
class App extends Backend
{

    /**
     * App模型对象
     * @var \app\common\model\App
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\common\model\App;
    }

    public function import()
    {
        parent::import();
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    /**
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = false;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $list = $this->model

                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);

            foreach ($list as $row) {
                $row['links_count'] = \think\Db::name('links')->where('app_id', $row['id'])->count();
                $row['down_url'] = config('site.config_url') . '/app/' . $row['download_key'];
                $row['username'] = $row->userName();
                $row['dev_id'] = $row->appId();
                $row['qr_link']='https://wenhairu.com/static/api/qr/?size=200&text='.$row['down_url'];
                $row->visible(['is_check','links_count', 'dev_id', 'username', 'down_url', 'id', 'developer_account_id', 'user_id', 'name', 'icon_image', 'bid', 'download_count', 'view_count', 'qr_link', 'download_key', 'remarks', 'status_switch']);
            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        $update_time = \think\Cache::get('apps_update');
        $update_time = $update_time ? '上次更新: ' . human_date((int)$update_time) : '同步AppStore数据';
        $this->view->assign('update_time', $update_time);
        return $this->view->fetch();
    }

    public function syncList()
    {
        $devList = \app\common\model\DeveloperAccount::field('id,lssuer_id,key_id,p8_base64,is_testers,appid,web_cookie_content')->where('status_switch',1)->select();
        $appList = [];
        $data = [];

        foreach ($devList as $dev) {
            try {
                // 获取key
                $key = \think\Cache::get($dev['lssuer_id'] . $dev['key_id']);
                $key = false;
                if (!$key) {
                    $key = \wq\P8JWT::encode($dev['lssuer_id'], $dev['key_id'], $dev['p8_base64']);
                    \think\Cache::set($dev['lssuer_id'] . $dev['key_id'], $dev, 5 * 60);
                }
                //
                $api = new \wq\JwtApi($key);
                $appList = $api->getApps('?fields[apps]=bundleId,name');

                if (!empty($appList)) {
                    $e = config('site.config_domain');
                    $count = \think\Db::name('testers')->where('account', $dev['appid'])->where('email', 'like', '%' . $e)->count();
                    if ($count < 90) {

                        //die($appList[0]['id']);
                        //die('不够' . $count);
                        $webApi = new \wq\WebApi($dev['web_cookie_content'], true);
                        $gid = $webApi->createGroups($appList[0]['id']);
                        for ($i = 0; $i <= 110; $i++) {
                            $email_arr[] = ['email' => 'testers' . $i . '@' . $e];
                        }
                        $testers = $webApi->createTestets($gid, $email_arr);
                        foreach ($testers as $k => $tester) {
                            $tdb[] = ['account' => $dev['appid'], 'id' => $tester['id'], 'email' => $tester['email']];
                        }
                        $testers = [];
                        \think\Db::name('testers')->insertAll($tdb, true);
                    }
                }
                foreach ($appList as $v) {
                    $data[] = ['id' => (int) $v['id'], 'developer_account_id' => $dev['id'], 'bid' => $v['attributes']['bundleId'], 'name' => $v['attributes']['name']];
                }
                //$appList = array_merge_recursive($api->getApps('?fields[apps]=bundleId,name'), $appList);
            } catch (\Exception $e) {
                // 这是进行异常捕获
                if ($e->getCode() == 8888) {
                    return $this->error('开发者账号: ' . $dev['appid'] . '需要登录验证');
                }
                return $this->error($e->getMessage() . ($e->getCode() ? '  错误代码: ' . $e->getCode() : ''));
            }
        }
        \app\common\model\App::saveApps($data);
        \think\Cache::set('apps_update', time());


        return $this->success('同步完成', null, $data);
    }




    public function syncIcon($ids)
    {
        try {
            // 这里是主体代码
            $app = $this->model->find($ids);
            $dev = \app\common\model\DeveloperAccount::where('id', $app['developer_account_id'])->find();
            $key = \wq\P8JWT::encode($dev['lssuer_id'], $dev['key_id'], $dev['p8_base64']);
            $api = new \wq\JwtApi($key);
            $list = $api->getAppsBuilds($ids, '?fields[builds]=iconAssetToken,version');
        } catch (\Exception $e) {
            // 这是进行异常捕获
            return $this->error($e->getMessage());
        }

        if (empty($list)) {
            return $this->error('应用还未上传构建版本');
        } else {
            if(count($list)==1){
                $icon = $list[0]['attributes']['iconAssetToken']['templateUrl'];
            $icon  = str_replace(array("{w}", "{h}", '{f}'), array("200", "200", 'png'), $icon);
            $app->icon_image = $icon;
            $app->save();
            return $this->success('同步成功', null, $list);
            }else{
                $maxKey = 0;
                $maxNum = 0;
                foreach ($list as $key=>$arr){
                    $v = (int)$arr['attributes']['version'];
                    if($v>$maxNum){
                        $maxNum=$v;
                        $maxKey=$key;
                    }
                }
                $icon = $list[$maxKey]['attributes']['iconAssetToken']['templateUrl'];
            $icon  = str_replace(array("{w}", "{h}", '{f}'), array("200", "200", 'png'), $icon);
            $app->icon_image = $icon;
            $app->save();
            return $this->success('同步成功', null, $list);
                
            }
            
            
        }
    }
    protected function addTesters()
    {
    }
    public function tfLinks($ids)
    {
        $row = \think\Db::name('links')->where('app_id', $ids)->limit(100)->order('update_time', 'desc')->select();
        $this->view->assign('row', $row);
        $this->view->assign('ids', $ids);
        return $this->view->fetch();
    }
    public function delLink($ids)
    {
        try {
            // 这里是主体代码
            $row = \think\Db::name('links')->delete($ids);
        } catch (\Exception $e) {
            // 这是进行异常捕获
            return $this->error($e->getMessage());
        }

        $this->success('操作成功');
    }

    public function createLinks($ids)
    {

        try {
            // 这里是主体代码
            $data = \app\common\library\Wq::createLinks($ids);
        } catch (\Exception $e) {
            // 这是进行异常捕获
            return $this->error($e->getMessage());
        }

        return $this->success('获取成功', null, $data);
    }
}
