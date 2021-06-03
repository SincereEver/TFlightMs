<?php

namespace app\common\library;

use \app\common\model\App;
use \app\common\model\DeveloperAccount;
use \wq\JwtApi;
use \think\Cache;

class Wq
{
    public static function createLinks($appid)
    {

        $app = App::find($appid);
        $dev = DeveloperAccount::field('id,lssuer_id,key_id,p8_base64,is_testers,appid')->find($app->developer_account_id);
        //$key = Cache::get($dev['lssuer_id'] . $dev['key_id']);
        $key = false;
        if (!$key) {
            $key = \wq\P8JWT::encode($dev['lssuer_id'], $dev['key_id'], $dev['p8_base64']);
            Cache::set($dev['lssuer_id'] . $dev['key_id'], $dev, 5 * 60);
        }
        $api = new JwtApi($key);
        if (!$app->group_id) {
            //isInternalGroup
            $gid = $api->getAppsBuildGroup($appid);
            $app->group_id = $gid;
            $app->save();
        }
        $api->delGroupTesters($app->group_id, $appid);
        //die('ok');
        $testers = \think\Db::name('testers')->where('account', $dev['appid'])->field('id,type')->limit(100)->select();
        if (empty($testers)) throw new \think\Exception('暂无内测人员');
        $api->addTesters($app->group_id, $testers);
        \think\Db::name('app')->where(['id' =>$appid])->data(['links_update'=>time()])->update();



        return true;
    }
    protected function getGroupId()
    {
    }
}
