<?php

namespace app\admin\controller\btpanel;

use app\common\controller\Backend;
use addons\btpanel\library\Api;
use think\Config;
/**
 *
 */
class Index extends Backend
{

    protected $noNeedRight = ['index'];

    public function _initialize()
    {
        parent::_initialize();
        $config = get_addon_config('btpanel');
        $this->assignconfig('btpanel',$config);
        if(!$config['key']){
            $this->error('未设置BT面板接口密钥，请先设置插件');
        }
        if($config['admin'] && $this->auth->username !== 'admin'){
            $this->error('仅限管理员访问');
        }
    }

    /**
     * 首页
     */
    public function index()
    {
        $api = new Api();
        $m_SystemTotal = $api->getSystemTotal();
        $m_SiteTypes = $api->getSiteTypes();
        $this->view->assign('SystemTotal',$m_SystemTotal);
        $this->view->assign('SiteTypes',$m_SiteTypes);
        return $this->view->fetch();
    }
}
