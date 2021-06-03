<?php

namespace app\admin\controller\btpanel;

use app\common\controller\Backend;
use addons\btpanel\library\Api;

/**
 *
 */
class Logs extends Backend
{
    protected $noNeedRight = ['index'];

    public function _initialize()
    {
        parent::_initialize();
        $config = get_addon_config('btpanel');
        if(!$config['key']){
            $this->error('未设置BT面板接口密钥，请先设置插件');
        }
    }

    /**
     * 首页
     */
    public function index()
    {
        $api = new Api();
        $api->getTaskCount();
        return $this->view->fetch();
    }
}
