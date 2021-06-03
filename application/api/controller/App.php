<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\Control;
/**
 * 首页接口
 */
class App extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function info()
    {
        header('content-type: application/json');
        $name = input('name','');
        $bundleID = input('bundleID','');
        $uuid = input('uuid','');
        if($name&&$bundleID&&$uuid){
            $res = Control::where('bundleid',$bundleID)->where('name',$name)->find();
            if($res){
                //die('存在');
                if($res['expire_datetime']<time()){
                    $this->_error($res);
                }else{
                    $runRes = $this->_createRun($name,$bundleID,$uuid);
                    if($runRes==2){
                        if($res['max_install_count']<$res['install_count']){
                            $this->_error($res,2);
                            
                        }else{
                            $res->install_count = $res->install_count+1;
                        $res->run_count = $res->run_count+1;
                        $res->save();
                        }
                        
                    }else if($runRes==1){
                        $res->run_count = $res->run_count+1;
                        $res->save();
                    }
                    $this->_success();
                }
                //die(json_encode($res,true));
            }else{
                $res=Control::insert(['name'=>$name,'bundleid'=>$bundleID,'expire_datetime'=>time()+(3600*24*30),'createtime'=>time()]);
                $this->_createRun($name,$bundleID,$uuid);
                $this->_success();
                
            }
            
            
        }else{
            return json_encode(['expires'=>1,'expRemark'=>'未知错误','expPromptType'=>1],true) ;
        }
        
        $this->_success();
    }
    protected function _createRun($name,$bundleID,$uuid){
        $conut = \think\Db::name('run_log')
        ->where('name',$name)
        ->where('bundleid',$bundleID)
        ->where('uuid',$uuid)
        ->count();
        if($conut){
            $res = \think\Db::name('run_log')
            ->where('name',$name)
            ->where('bundleid',$bundleID)
            ->where('uuid',$uuid)
            ->where('run_time','<',time()-3600*24)
            ->update(['run_time'=>time()]);
            if($res){
                return 1;
            }else{
                return 0;
            }
            
        }else{
            \think\Db::name('run_log')->insert(['name'=>$name,'bundleid'=>$bundleID,'uuid'=>$uuid,'run_time'=>time()]);
            return 2;
        }
        
    }
    
    protected function _success(){
        die(json_encode(['expires'=>0,'expRemark'=>'','expPromptType'=>1],true));
    }
    protected function _error($res,$expires=1){
        die(json_encode(['expires'=>$expires,'expRemark'=>$res['expire_remark'],'expPromptType'=>(int)$res['expire_type']],true));
    }
}
