<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Db;
/**
 * 开发者账号
 *
 * @icon fa fa-circle-o
 */
class DeveloperAccount extends Backend
{

    /**
     * DeveloperAccount模型对象
     * @var \app\common\model\DeveloperAccount
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\common\model\DeveloperAccount;
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
                $row['login_human_date'] = human_date($row['logintime']);

                $row->visible(['id', 'user_name', 'team_name', 'appid', 'lssuer_id', 'key_id', 'p8_file', 'certificate_file', 'status_switch', 'createtime', 'updatetime', 'logintime', 'login_human_date']);
            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

    public function sendCode()
    {
        $appid = input('appid', '');
        $password = input('password', '');
        if (!$appid || !$password) return $this->error('Appid或密码不能为空');
        try {
            // 发送验证码
            $apple = new \wq\AppleLogin($appid, $password);
            $apple->sendCode();
        } catch (\Exception $e) {
            // 这是进行异常捕获
            //$error_text = $e->getMessage() . $e->getCode() ? ',错误代码:' . $e->getCode() : '';
            return $this->error($e->getMessage() . ($e->getCode() ? '  错误代码: ' . $e->getCode() : ''));
        }
        return $this->success('验证码已发送到您的设备');
    }

    public function reSendCode()
    {
        $appid = input('appid', '');
        $password = input('password', '');
        if (!$appid || !$password) return $this->error('Appid或密码不能为空');
        try {
            // 发送验证码
            $apple = new \wq\AppleLogin($appid, $password);
            $apple->reSendCode();
        } catch (\Exception $e) {
            // 这是进行异常捕获
            return $this->error($e->getMessage() . ($e->getCode() ? '  错误代码: ' . $e->getCode() : ''));
        }
        return $this->success('验证码已发送到您的设备');
    }

    public function addAccount()
    {
        $appid = input('appid', '');
        $password = input('password', '');
        $code = input('code', '');
        if (!$appid || !$password || !$code) return $this->error('信息不能为空');
        $res = \app\common\model\DeveloperAccount::where('appid', $appid)->find();
        if ($res) return $this->error('添加错误 ' . $appid . ' 已经存在');
        try {

            $apple = new \wq\AppleLogin($appid, $password);
            //$apple->verifyCode($code);
            $cookie = $apple->verifyCode($code);
            $info = ['appid' => $appid, 'password' => $password, 'web_cookie_content' => $cookie];
            // 获取cookie


            //获取p8
            $webApi = new \wq\WebApi($cookie);
            $userInfo = $webApi->getUserInfo();
            $info = $info + $userInfo;
            $p8Info = $webApi->create_p8();
            $info = $info + $p8Info;
            $info['logintime'] = time();

            $res_db = \app\common\model\DeveloperAccount::create($info);
        } catch (\Exception $e) {
            // 这是进行异常捕获
            return $this->error($e->getMessage() . ($e->getCode() ? '  错误代码: ' . $e->getCode() : ''));
        }
        if ($res_db->appid) {
            return $this->success('添加成功');
        } else {
            return $this->error('添加失败');
        }
    }
    public function repeat($ids)
    {
        $dev = new \app\common\model\DeveloperAccount;
        $row = $dev->find($ids);
        if ($this->request->isAjax()) {
            $code = input('code', '');
            if (!$code) return $this->error('验证码不能为空');
            try {
                // 这里是主体代码
                $apple = new \wq\AppleLogin($row->appid, $row->password);
                //$apple->verifyCode($code);
                $cookie = $apple->verifyCode($code);
            } catch (\Exception $e) {
                // 这是进行异常捕获
                return $this->error($e->getMessage() . ($e->getCode() ? '  错误代码: ' . $e->getCode() : ''));
            }

            $row->web_cookie_content = $cookie;
            $row->logintime = time();
            $row->save();
            return $this->success('更新成功');
        }
        $this->view->assign('row', $row);
        return $this->view->fetch();
    }
    
    /**
     * 删除
     */
    public function del($ids = "")
    {
        if (!$this->request->isPost()) {
            $this->error(__("Invalid parameters"));
        }
        $ids = $ids ? $ids : $this->request->post("ids");
        if ($ids) {
            $resData=$this->model->get($ids);
            $pk = $this->model->getPk();
            $adminIds = $this->getDataLimitAdminIds();
            if (is_array($adminIds)) {
                $this->model->where($this->dataLimitField, 'in', $adminIds);
            }
            $list = $this->model->where($pk, 'in', $ids)->select();

            $count = 0;
            Db::startTrans();
            try {
                foreach ($list as $k => $v) {
                    $count += $v->delete();
                }
                Db::commit();
            } catch (PDOException $e) {
                Db::rollback();
                $this->error($e->getMessage());
            } catch (Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            if ($count) {
                Db::name('testers')->where('account',$resData['appid'])->delete();
                $this->success();
            } else {
                $this->error(__('No rows were deleted'));
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }
}
