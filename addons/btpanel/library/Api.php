<?php

namespace addons\btpanel\library;

class Api
{
    use \traits\controller\Jump;
    private $BT_KEY = "2hKwhusgQlWCbjZLLnOMqIwmtKwAM8jV";  //接口密钥
    private $BT_PANEL = "http://10.10.1.13:8888";       //面板地址

    //如果希望多台面板，可以在实例化对象时，将面板地址与密钥传入
    public function __construct($bt_panel = null, $bt_key = null)
    {
        $config = get_addon_config('btpanel');
        if ($config['linux']) {
            if (!PATH_SEPARATOR == ':') {
                $this->error('该插件仅支持Linux版宝塔');
            }
        }
        $this->BT_PANEL = $bt_panel ?: $config['url'] ?: '';
        $this->BT_KEY = $bt_key ?: $config['key'] ?: '';
    }

    /**
     * 获取系统基础统计
     */
    public function getSystemTotal()
    {
        return $this->getData('system?action=GetSystemTotal');
    }

    /**
     * 获取实时状态信息（CPU、内存、网络、负载）
     */
    public function getNetWork()
    {
        return $this->getData('system?action=GetNetWork');
    }

    /**
     * 获取磁盘分区信息
     */
    public function getDiskInfo()
    {
        return $this->getData('system?action=GetDiskInfo');
    }

    /**
     * 检查是否有安装任务
     */
    public function getTaskCount()
    {
        return $this->getData('ajax?action=GetTaskCount');
    }

    /**
     * 检查面板更新
     */
    public function updatePanel()
    {
        return $this->getData('ajax?action=UpdatePanel');
    }

    /**
     * 获取网站列表
     * @param array $params p=>当前分页，limit=>取回行数，type=>分类标识（-1：分布分类，0默认分类），order=>排序规则（id desc）,tojs=>分页JS回调，search=>搜索内容
     */
    public function getWebSite($params)
    {
        return $this->getData('data?action=getData&table=sites', $params);
    }

    /**
     * 获取网站端口及URL
     * @param array $id 网站ID
     */
    public function getWebSiteDomain($id)
    {
        return $this->getData('data?action=getData&table=domain&list=true', ['search' => $id]);
    }

    /**
     * 获取网站分类
     */
    public function getSiteTypes()
    {
        return $this->getData('site?action=get_site_types');
    }

    /**
     * 获取已安装的PHP版本列表
     */
    public function getPHPVersion()
    {
        return $this->getData('site?action=getPHPVersion');
    }

    /**
     * 设置网站到期时间
     * @param array $params id=>网站ID，edate=>到期时间（永久：0000-00-00）
     */
    public function setEdate($params)
    {
        return $this->getData('site?action=setEdate', $params);
    }

    /**
     * 修改网站备注
     * @param array $params id=>网站ID，ps=>备注内容
     */
    public function setPs($params)
    {
        return $this->getData('data?action=setPs&table=sites', $params);
    }

    /**
     * 获取网站备份列表
     * @param array $params p=>当前分页，limit=>取回行数，type=>备份类型（固定传0）,tojs=>分页JS回调，search=>网站ID
     */
    public function getBackupData($params)
    {
        $params['type'] = 0;
        return $this->getData('data?action=getData&table=backup', $params);
    }

    /**
     * 创建网站备份
     * @param string $id 网站ID
     */
    public function toBackup($id)
    {
        return $this->getData('site?action=ToBackup', ['id' => $id]);
    }

    /**
     * 删除网站备份
     * @param string $id 网站ID
     */
    public function delBackup($id)
    {
        return $this->getData('site?action=DelBackup', ['id' => $id]);
    }

    /**
     * 获取网站的域名列表
     * @param string $id 网站ID
     */
    public function getDomainData($id)
    {
        return $this->getData('data?action=getData&table=domain', ['search' => $id, 'list' => true]);
    }

    /**
     * 添加域名
     * @param String $id      网站ID
     * @param String $webname 网站名称
     * @param String $domain  要添加的域名:端口（80端口请忽略端口号）
     */
    public function addDomain($id, $webname, $domain)
    {
        return $this->getData('site?action=AddDomain', [
            'id'      => $id,
            'webname' => $webname,
            'domain'  => $domain
        ]);
    }

    /**
     * 删除域名
     * @param String         $id      网站ID
     * @param String         $webname 网站名称
     * @param String         $domain  要删除的域名
     * @param String|numeric $port    该域名的端口
     */
    public function delDomain($id, $webname, $domain, $port)
    {
        return $this->getData('site?action=DelDomain', [
            'id'      => $id,
            'webname' => $webname,
            'domain'  => $domain,
            'port'    => $port
        ]);
    }

    /**
     * 获取可选的预定义伪静态列表
     * @param string $siteName 网站名称
     */
    public function getRewriteList($siteName)
    {
        return $this->getData('site?action=GetR&table=domain', ['siteName' => $siteName]);
    }

    /**
     * 获取指定域名伪静态规则内容（获取文件内容）
     * @param string $domain 网站域名
     */
    public function getFileBody($domain)
    {
        return $this->getData('files?action=GetFileBody', ['path' => "/www/server/panel/vhost/rewrite/nginx/" . $domain . ".conf"]);
    }

    /**
     * 获取指定域名伪静态规则内容（获取文件内容）
     * @param String $domain   网站域名
     * @param String $data     规则内容
     * @param String $encoding 文件编码，固定为'utf-8'
     */
    public function saveFileBody($domain, $data, $encoding = "utf-8")
    {
        return $this->getData('files?action=SaveFileBody', [
            'path'     => "/www/server/panel/vhost/rewrite/nginx/" . $domain . ".conf",
            'data'     => $data,
            'encoding' => $encoding
        ]);
    }

    /**
     * 取回指定网站的跟目录
     * @param string $id 网站ID
     */
    public function getSitesPath($id)
    {
        return $this->getData('data?action=getKey&table=sites&key=path', ['id' => $id]);
    }

    /**
     * 取回防跨站配置/运行目录/日志开关状态/可设置的运行目录列表/密码访问状态
     * @param string $id 网站ID
     */
    public function getDirUserINI($id)
    {
        return $this->getData('site?action=GetDirUserINI', [
            'id'   => $id,
            'path' => $this->getSitesPath($id)
        ]);
    }

    /**
     * 设置防跨站状态（自动取反）
     * @param string $id 网站ID
     */
    public function setDirUserINI($id)
    {
        return $this->getData('site?action=setDirUserINI', ['path' => $this->getSitesPath($id)]);
    }

    /**
     * 设置是否防写访问日志
     * @param string $id 网站ID
     */
    public function logsOpen($id)
    {
        return $this->getData('site?action=logsOpen', ['id' => $id]);
    }

    /**
     * 修改网站根目录
     * @param string $id   网站ID
     * @param string $path 新的网站根目录
     */
    public function setPath($id, $path)
    {
        return $this->getData('site?action=SetPath', ['id' => $id, 'path' => $path]);
    }

    /**
     * 设置是否写访问日志
     * @param string $id      网站ID
     * @param string $runPath 基于网站跟目录的运行目录
     */
    public function setSiteRunPath($id, $runPath)
    {
        return $this->getData('site?action=SetSiteRunPath', ['id' => $id, 'runPath' => $runPath]);
    }

    /**
     * 设置密码访问
     * @param string $id       网站ID
     * @param string $username 用户名
     * @param string $password 密码
     */
    public function setHasPwd($id, $username, $password)
    {
        return $this->getData('site?action=SetHasPwd', ['id' => $id, 'username' => $username, 'password' => $password]);
    }

    /**
     * 关闭密码访问
     * @param string $id 网站ID
     */
    public function closeHasPwd($id)
    {
        return $this->getData('site?action=CloseHasPwd', ['id' => $id]);
    }

    /**
     * 获取流量限制相关配置（仅支持nginx）
     * @param string $id 网站ID
     */
    public function getLimitNet($id)
    {
        return $this->getData('site?action=GetLimitNet', ['id' => $id]);
    }

    /**
     * 开启或保存流量限制相关配置（仅支持nginx）
     * @param string $id         网站ID
     * @param number $perserver  并发限制
     * @param number $perip      单IP限制
     * @param number $limit_rate 流量限制
     */
    public function setLimitNet($id, $perserver, $perip, $limit_rate)
    {
        return $this->getData('site?action=SetLimitNet', [
            'id'         => $id,
            'perserver'  => $perserver,
            'perip'      => $perip,
            'limit_rate' => $limit_rate
        ]);
    }

    /**
     * 关闭流量限制（仅支持nginx）
     * @param string $id 网站ID
     */
    public function closeLimitNet($id)
    {
        return $this->getData('site?action=CloseLimitNet', ['id' => $id]);
    }

    /**
     * 取默认文档信息
     * @param string $id 网站ID
     */
    public function getIndex($id)
    {
        return $this->getData('site?action=GetIndex', ['id' => $id]);
    }

    /**
     * 设置默认文档
     * @param string $id    网站ID
     * @param string $Index 默认文档，用逗号隔开
     */
    public function setIndex($id, $Index)
    {
        return $this->getData('site?action=SetIndex', ['id' => $id, 'Index' => $Index]);
    }

    /**
     * 获取面板日志
     */
    public function getLogs($params, $type = "")
    {
        if ($type == "crontab") {
            return $this->getData('crontab?action=GetLogs', $params);
        } else {
            return $this->getData('data?action=getData', [
                'table' => 'logs',
                'limit' => $params['limit'] ?: 10,
                'p'     => $params['p'] ?: 1,
                'tojs'  => $params['tojs'] ?: 'pageTo'
            ]);
        }
    }

    /**
     * 获取面板配置信息
     *
     */
    public function getPanelErrorLogs()
    {
        return $this->getData('config?action=get_panel_error_logs');
    }

    /**
     * 获取面板配置信息
     *
     */
    public function getConfig()
    {
        return $this->getData('config?action=get_config');
    }

    /**
     * 系统监控设置
     *
     * @param bool    $type 是否开启
     * @param integer $day  保存天数
     * @return void
     */
    public function setControl($type = -1, $day = '')
    {
        return $this->getData('config?action=SetControl', ['type' => $type, 'day' => $day]);
    }

    /**
     * 释放内存
     *
     * @return void
     */
    public function reMemory()
    {
        return $this->getData('system?action=ReMemory');
    }

    /**
     * 获取平均负载使用率
     *
     * @param string $start 开始时间
     * @param string $end   结束时间
     * @return void
     */
    public function getLoadAverage($start, $end)
    {
        return $this->getData('ajax?action=get_load_average', ['start' => $start, 'end' => $end]);
    }

    /**
     * 获取CPU利用率
     *
     * @param string $start 开始时间
     * @param string $end   结束时间
     * @return void
     */
    public function getCpuIo($start, $end)
    {
        return $this->getData('ajax?action=GetCpuIo', ['start' => $start, 'end' => $end]);
    }

    /**
     * 获取磁盘IO
     *
     * @param string $start 开始时间
     * @param string $end   结束时间
     * @return void
     */
    public function getDiskIo($start, $end)
    {
        return $this->getData('ajax?action=GetDiskIo', ['start' => $start, 'end' => $end]);
    }

    /**
     * 获取磁盘IO
     *
     * @param string $start 开始时间
     * @param string $end   结束时间
     * @return void
     */
    public function getNetWorkIo($start, $end)
    {
        return $this->getData('ajax?action=GetNetWorkIo', ['start' => $start, 'end' => $end]);
    }

    /**
     * 获取当前PHP版本
     */
    public function getCliPhpVersion()
    {
        return $this->getData('config?action=get_cli_php_version');
    }

    /**
     * 获取计划任务列表
     */
    public function getCrontab()
    {
        return $this->getData('crontab?action=GetCrontab');
    }

    /**
     * 获取存储空间
     */
    public function getDataList($type = 'sites')
    {
        return $this->getData('crontab?action=GetDataList', ['type' => $type]);
    }

    /**
     * 执行任务脚本
     * @param string $id 任务ID
     */
    public function startTask($id)
    {
        return $this->getData('crontab?action=StartTask', ['id' => $id]);
    }

    /**
     * 查询任务信息
     * @param string $id 任务ID
     */
    public function getCrontabFind($id)
    {
        return $this->getData('crontab?action=get_crond_find', ['id' => $id]);
    }

    /**
     * 修改定时任务
     * @param array $params 任务参数
     */
    public function modifyCrond($params)
    {
        return $this->getData('crontab?action=modify_crond', $params);
    }

    /**
     * 新建定时任务
     * @param array $params 任务参数
     */
    public function addCrontab($params)
    {
        return $this->getData('crontab?action=AddCrontab', $params);
    }

    /**
     * 删除定时任务
     * @param string $id 任务ID
     */
    public function delCrontab($id)
    {
        return $this->getData('crontab?action=DelCrontab', ['id' => $id]);
    }

    /**
     * 切换任务状态
     * @param string $id 任务ID
     */
    public function setCronStatus($id)
    {
        return $this->getData('crontab?action=set_cron_status', ['id' => $id]);
    }

    /**
     * 清空计划任务日志
     * @param string $id 任务ID
     */
    public function delLogs($id)
    {
        return $this->getData('crontab?action=DelLogs', ['id' => $id]);
    }

    /**
     * 构造带有签名的关联数组
     */
    private function GetKeyData()
    {
        $now_time = time();
        $p_data = array(
            'request_token' => md5($now_time . '' . md5($this->BT_KEY)),
            'request_time'  => $now_time
        );
        return $p_data;
    }

    private function getData($url, $data = [])
    {
        //拼接URL地址
        $url = $this->BT_PANEL . '/' . $url;
        //准备POST数据
        $p_data = $this->GetKeyData();        //取签名
        $p_data = array_merge($p_data, $data);
        //请求面板接口
        $result = $this->HttpPostCookie($url, $p_data);
        //解析JSON数据
        $data = json_decode($result, true);
        if ($data === null) {
            $this->error('未能获取到数据,请开启宝塔API');
        }
        if (isset($data['status']) && $data['status'] == false) {
            $this->error(is_string($data['msg']) ? $data['msg'] : '连接到宝塔服务器异常');
        }
        return $data;
    }

    /**
     * 发起POST请求
     * @param String       $url  目标网填，带http://
     * @param Array|String $data 欲提交的数据
     * @return string
     */
    private function HttpPostCookie($url, $data, $timeout = 60)
    {
        //定义cookie保存位置
        $cookie_file = './' . md5($this->BT_PANEL) . '.cookie';
        if (!file_exists($cookie_file)) {
            $fp = fopen($cookie_file, 'w+');
            fclose($fp);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        if (curl_exec($ch) === false) {
            $this->error(curl_error($ch));
        }
        curl_close($ch);
        return $output;
    }
}
