<?php

namespace app\down\controller;

use app\common\controller\Frontend;

class Index extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';

    public function alias($alias = '', $lang = '')
    {
        $this->app('', $lang, $alias);
    }

    public function app($key = '', $lang = '', $alias = '')
    {
        if (!empty($alias))
            $app = \app\common\model\App::where('alias', $alias)->find();
        else
            $app = \app\common\model\App::where('download_key', $key)->find();

        if ($lang != 'en') $lang = 'zh-cn';

        if ($app) {
            $dkey = md5(time() . mt_rand(1, 660000));
            \think\Db::name('downKey')->insert(['key' => $dkey, 'appid' => $app->id, 'lang' => $lang]);
            $app->view_count = $app->view_count + 1;
            $app->save();
        } else {
            $dkey = '404';
        }
        $urls = explode("\n", config('site.config_down_urls'));
        shuffle($urls);
        $lang = ($lang == 'en' ? '?lang=' . $lang : '');
        $url = trim($urls[0]) . "/down/" . $dkey . $lang;
        header("Location: " . $url);
        exit;
    }

    public function down($key)
    {
        $lang = 'cn';
        if (strpos($_SERVER['HTTP_ACCEPT_LANGUAGE'], 'en') !== false || $this->request->get('lang') == 'en') {
            $lang = 'en';
        }
        \think\Lang::load(APP_PATH . $lang == 'en' ? 'down\lang\en.php' : 'down\lang\zh-cn.php');
        $this->view->assign('lang', $lang);


        if ($key == '404') {
            header('HTTP/1.1 404 Not Found');
            header('status: 404 Not Found');
            exit;
        }
        $vtime = '-' . config('site.config_validity') . ' minutes';

        //whereTime('createtime',$vtime)
        $w[] = ['key', '=', $key];
        $w[] = ['createtime', '=', $key];
        $res = \think\Db::name('downKey')->where('key', $key)->whereTime('createtime', $vtime)->find();
        if (empty($res)) {
            header('HTTP/1.1 404 Not Found');
            header("status: 404 Not Found");
            exit;
        }

        $isiOS = $this->isiOS();

        $w = $this->isWxQq();
        if ($w) {
            if ($isiOS) {
                return $this->view->fetch('nzios');
            } else {
                return $this->view->fetch('nzaz');
            }
        }

        //
        $appid = $res['appid'];
        $app = \app\common\model\App::where('id', $appid)->find();
        if (empty($app)) {
            die('<h1>应用不存在</h>');
            exit;
        }

        if ($res['lang'] = 'en') {
            $app->name = $app->name_en ?: $app->name;
        }
        $this->view->assign('row', $app);
        if (!$app->status_switch) {
            return $this->view->fetch('stop');
        }
        if (!$isiOS) {
            if ($app->az_links) {
                header("Location: " . $app->az_links);
                exit;
            } else {
                if ($this->isAz()) {
                    return $this->view->fetch('az');
                } else {
                    $urls = explode("\n", config('site.config_down_urls'));
                    //var_export($urls);
                    //die();
                    shuffle($urls);
                    $this->view->assign('qru', $urls[0] . '/down/' . $key);
                    return $this->view->fetch('pc');
                }
            }
        }
        return $this->view->fetch();
    }

    protected function updateLinks($ids = '')
    {
        $count = \think\Db::name('links')->where('app_id', $ids)->count();
        $res = \think\Db::name('app')->where('id', $ids)->find();

        if ($count <= 1) {
            if ($res['links_update'] < time() - 30) {
                \think\Db::name('app')->where('id', $ids)->update(['links_update' => time()]);
                try {
                    \app\common\library\Wq::createLinks($ids);
                } catch (\Exception $e) {
                    //die('服务器内部错误');
                    die($e->getMessage());
                }
            }
        }
    }

    public function loading($ids, $isAjax = false)
    {
        if (!$isAjax) {
            $app = \app\common\model\App::where('id', $ids)->find();
            $this->view->assign('row', $app);
            return $this->view->fetch('loading');
        } else {
            $row = \think\Db::name('links')->where('app_id', $ids)->order('update_time desc')->find();
            if ($row) {
                \think\Db::name('app')->where('id', $ids)->inc('download_count')->update();
                \think\Db::name('links')->delete($row['id']);
                //die('');
                die(str_replace('https', 'itms-beta', $row['link']));
            } else {
                $this->updateLinks($ids);
                die('');
            }
        }

    }

    public function testflight()
    {
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        if (!empty($_SERVER['HTTP_REFERER'])) {
            if (strpos($_SERVER['HTTP_REFERER'], 'tang118.com') === false) {
                die();
            }
        }

        //分析数据
        $is_iphone = (strpos($agent, 'iphone')) ? true : false;
        $is_ipad = (strpos($agent, 'ipad')) ? true : false;

        if ($is_iphone || $is_ipad) {
            $ids = input('ids', '');
            \think\Db::name('app')->where('id', $ids)->inc('download_count')->update();
            $row = \think\Db::name('links')->where('app_id', $ids)->order('update_time desc')->find();
            if ($row) {
                //die(str_replace('https', 'itms-beta', $row['link']));
                \think\Db::name('links')->delete($row['id']);
                $this->updateLinks($ids);
                header("Location: " . str_replace('https', 'itms-beta', $row['link']));
            } else {
                header("Location: /loading/ids/" . $ids);
                die();
            }
        } else {
            header('HTTP/1.1 404 Not Found');
            header("status: 404 Not Found");
            die();
        }
    }


    protected function isiOS()
    {
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        //分别进行判断
        if (strpos($agent, 'iphone') || strpos($agent, 'ipad') || strpos($agent, 'Macintosh')) {
            return true;
        } else {
            return false;
        }
    }

    protected function isWxQq()
    {
        //判断是不是微信
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
            return "wx";
        }

        //判断是不是QQ
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'QQ') !== false) {
            return "qq";
        }
        //哪个都不是
        return false;
    }

    protected function isAz()
    {
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        if (strpos($agent, 'android')) {
            return true;
        } else {
            return false;
        }
    }
}
