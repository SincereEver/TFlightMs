<?php

namespace app\admin\controller\btpanel;

use app\common\controller\Backend;
use addons\btpanel\library\Api;

/**
 *
 */
class Ajax extends Backend
{
    protected $noNeedRight = ['*'];
    protected $api = null;
    public function _initialize()
    {
        $config = get_addon_config('btpanel');
        if (!$config['key']) {
            $this->error('未设置BT面板接口密钥，请先设置插件');
        }
        parent::_initialize();
        $this->api=new Api();
    }
    public function getWebSiteDomain()
    {
        $id = $this->request->request('id', -1);
        return json($this->api->getWebSiteDomain($id));
    }

    public function updatePanel()
    {
        return json($this->api->updatePanel());
    }

    public function getNetWork()
    {
        return json($this->api->getNetWork());
    }
    public function getLogs()
    {
        $type = $this->request->request('type', '');
        if ($type == 'crontab') {
            return json($this->api->getLogs($this->request->request(), $type));
        } else {
            return json($this->api->getLogs($this->request->request()));
        }
    }
    public function getPanelErrorLogs()
    {
        return json($this->api->getPanelErrorLogs());
    }
    public function getSiteData()
    {
        $type = $this->request->request('type', -1);
        return json($this->api->getWebSite(['limit'=>9999,'type'=>$type])) ;
    }
    public function setControl()
    {
        $type = $this->request->request('type', -1);
        $day = $this->request->request('day', 30);
        return json($this->api->setControl($type, $day)) ;
    }
    public function getMonitorData()
    {
        $action = $this->request->request('action', null);
        $start = $this->request->request('start', null);
        $end = $this->request->request('end', null);
        switch ($action) {
            case 'getLoadAverage':
                return json($this->api->getLoadAverage($start, $end));
            case 'getCpuIo':
                return json($this->api->getCpuIo($start, $end));
            case 'getDiskIo':
                return json($this->api->getDiskIo($start, $end));
            case 'GetNetWorkIo':
                return json($this->api->GetNetWorkIo($start, $end));
            default:
            return json([]);
        }
    }
    public function getCrontab()
    {
        return json($this->api->getCrontab()) ;
    }
    public function getDataList()
    {
        $type = $this->request->request('type', 'sites');
        return json($this->api->getDataList($type)) ;
    }
    public function startTask()
    {
        $id = $this->request->request('id', null);
        return json($this->api->startTask($id)) ;
    }
    public function getCrontabFind()
    {
        $id = $this->request->request('id', null);
        return json($this->api->getCrontabFind($id)) ;
    }
    public function modifyCrond()
    {
        $params = $this->request->request();
        return json($this->api->modifyCrond($params)) ;
    }
    public function setCronStatus()
    {
        $id = $this->request->request('id', null);
        return json($this->api->setCronStatus($id)) ;
    }
    public function addCrontab()
    {
        $params = $this->request->request();
        return json($this->api->addCrontab($params)) ;
    }
    public function delCrontab()
    {
        $id = $this->request->request('id', null);
        return json($this->api->delCrontab($id)) ;
    }
    public function delLogs()
    {
        $id = $this->request->request('id', null);
        return json($this->api->delLogs($id)) ;
    }
}
