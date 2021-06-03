<?php

namespace app\admin\controller\btpanel;

use app\common\controller\Backend;
use addons\btpanel\library\Api;

/**
 *
 */
class Crontab extends Backend
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
        $m_SetControl = $api->setControl();
        $this->assignconfig('SetControl', $m_SetControl);
        return $this->view->fetch();
    }
    public function add(){
        $data = $this->getBaseData();
        $this->assignconfig('crontab', $data);
        $this->view->assign('data', $data);
        return $this->view->fetch();
    }
    public function edit($id=null){
        $api = new Api(); 
        $data = $this->getBaseData();
        $m_getCrontabFind = $api->getCrontabFind($id);
        $this->view->assign('row', $m_getCrontabFind);
        $this->assignconfig('row', $m_getCrontabFind);
        $this->assignconfig('crontab', $data);
        $this->view->assign('data', $data);
        return $this->view->fetch();
    }
    private function getBaseData(){
        $data=[];
        $api = new Api(); 
        $m_DataList_Sites = $api->getDataList('sites');
        $m_DataList_Database = $api->getDataList('databases');
        $data['dataListSites']=[];
        $data['dataListDatabases']=[];
        if (isset($m_DataList_Sites['data'])) {
            $data['dataListSites']['sNameArray'][] = ['name'=>'所有','value'=>'ALL'];
            foreach ($m_DataList_Sites['data'] as $item) {
                $data['dataListSites']['sNameArray'][]=['name'=>$item['name'].' ['.$item['ps'].']','value'=>$item['name']];
            }
            $data['dataListSites']['backupsArray'][]=['name'=>'服务器磁盘','value'=>'localhost'];
            foreach ($m_DataList_Sites['orderOpt'] as $item) {
                $data['dataListSites']['backupsArray'][]=['name'=>$item['name'] ,'value'=>$item['value']];
            }
        }
        if (isset($m_DataList_Database['data'])) {
            $data['dataListDatabases']['sNameArray'][] = ['name'=>'所有','value'=>'ALL'];
            foreach ($m_DataList_Database['data'] as $item) {
                $data['dataListDatabases']['sNameArray'][]=['name'=>$item['name'].' ['.$item['ps'].']','value'=>$item['name']];
            }
            $data['dataListDatabases']['backupsArray'][]=['name'=>'服务器磁盘','value'=>'localhost'];
            foreach ($m_DataList_Database['orderOpt'] as $item) {
                $data['dataListDatabases']['backupsArray'][]=['name'=>$item['name'] ,'value'=>$item['value']];
            }
        }
       // echo "<pre>";print_r($data);echo "<pre>";die;

        $data['sTypeArray']=[
            'toShell'=>'Shell脚本',
            'site'=>'备份网站',
            'database'=>'备份数据库',
            'logs'=>'日志切割',
            'path'=>'备份目录',
            'syncTime'=>'同步时间',
            'rememory'=>'释放内存',
            'toUrl'=>'访问URL'
        ];
        $data['cycleArray']=[
            'day'=>'每天',
            'day-n'=>'N天',
            'hour'=>'每小时',
            'hour-n'=>'N小时',
            'minute-n'=>'N分钟',
            'week'=>'每星期',
            'month'=>'每月'
        ];
        $data['weekArray']= [
            1=> '周一',
            2=>'周二',
            3=>'周三',
            4=>'周四',
            5=>'周五',
            6=>'周六',
            7=>'周日'
        ];
        return $data;
    }
}
