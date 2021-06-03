<?php

namespace app\index\controller;

use app\common\controller\Frontend;

class App extends Frontend
{

    protected $noNeedLogin = '';
    protected $noNeedRight = '*';
    protected $layout = '';

    public function index()
    {
        //$row = \app\common
        $uid = $this->auth->id;
        $row = \app\common\model\App::where('user_id', $uid)->select();
        $this->view->assign('row', $row);

        return $this->view->fetch();
    }
    
    public function edit($id)
    {
        $uid = $this->auth->id;
        
        if($this->request->isAjax()){
            //$row = $this->request->post('row');
            $data = ['az_links'=>input('az_links',''),'by_link'=>input('by_link',''),'remarks'=>input('remarks','')];
            
            $res = \think\Db::name('app')->where('user_id', $uid)->where('id', $id)->update($data);
            if($res){
                return ['code'=>1,'msg'=>'操作成功'];
            }else{
                 return ['code'=>0,'msg'=>'操作失败'];
            }
           
        }
        //$row = \app\common
        
        
        $row = \app\common\model\App::where('user_id', $uid)->where('id', $id)->find();
        $this->view->assign('vo', $row);

        return $this->view->fetch();
    }
}
