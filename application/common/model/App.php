<?php

namespace app\common\model;

use think\Model;


class App extends Model
{





    // 表名
    protected $name = 'app';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [];

    public static function saveApps($arr)
    {

        foreach ($arr as $app) {
            $id = $app['id'];
            $app['update'] = time();
            unset($app['id']);
            $res = self::where('id', $id)->update($app);
            if (!$res) {
                $app['id'] = $id;
                $app['download_key'] = $id;
                self::create($app);
            }
        }
    }

    public function userName()
    {
        return $this->hasOne('User', 'id', 'user_id')->field('username')->find()->username;
    }
    public function dev()
    {
        return $this->hasOne('DeveloperAccount', 'id', 'developer_account_id')->find();
    }
    public function appId()
    {
        $res = $this->hasOne('DeveloperAccount', 'id', 'developer_account_id')->field('appid')->find();
        if($res){
           return $res->appid;
        }else{
           return '已删除'; 
        }
        //return $this->hasOne('DeveloperAccount', 'id', 'developer_account_id')->field('appid')->find()->appid;
    }
}
