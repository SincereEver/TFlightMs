<?php
namespace app\common\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class Receive extends Command
{
    protected function configure()
    {
        $this->setName('receive')
        	->addArgument('emailName', Argument::OPTIONAL, "email data")
        	->setDescription('Say Hello');
    }

    protected function execute(Input $input, Output $output)
    {
        $apps = \think\Db::name('app')->where('id', '<>', 0)->field('id,name')->select();
        foreach ($apps as $app) {
            $c[$app['name']] = $app['id'];
        }
        //$email_domain='bigefuli.com';
       // \think\Db::name('user_token')->insert(['user_id'=>mt_rand(1,99999999),'token'=>mt_rand(1,99999999),'createtime'=>time()]);
    	$enameName = trim($input->getArgument('emailName'));
    	//if($enameName){
    	    //\think\Db::name('links')->insert(['tester'=>$enameName]);
    	//}
    	$data=quoted_printable_decode(stream_get_contents(fopen('php://stdin','r')));
    	//$data = preg_replace('//s*/', '', $data); 
    	$data = str_replace(array("\r\n", "\r", "\n"), "", $data);
    	//file_put_contents(ROOT_PATH.'res.log',$data,FILE_APPEND);
    	//$data=file_get_contents('/www/wwwroot/www.ttfloor.com/postfix.log');
    	//$re = "/Content-Type: text\/html[\s\S]*?Content-Transfer-Encoding:\\sbase64\\s\n([\s\S]*)(?=\s--)/"; 
    	//$re = "/(testers\d*@testflightdev\.com)[\s\S]*?(https:+\/\/testflight\.apple\.com\/v1\/invite[\s\S]*?)' alt=\"Start Testing\" ari[\s\S]*?By using (.*?), you agree/"; 
    	$re = "/(testers\d*@testflight\.wiki)[\s\S]*?(https:+\/\/testflight\.apple\.com\/v1\/invite[\s\S]*?)' alt=\"Start Testing\" ari[\s\S]*?By using (.*?), you agree/"; 
    	if(preg_match_all($re,$data, $mc)){
    	    
    	    $link =$mc[2][0];
    	    $name = $mc[3][0];
    	    $email = $mc[1][0];
    	    
    	    $dbData = ['app_id' => $c[$name], 'email' =>$email,'name'=>$name,'update_time'=>time(),'link'=>$link];
    	    $linksCount = \think\Db::name('links')->where(['app_id' => $c[$name], 'email' =>$email])->data($dbData)->update();
            if ($linksCount == 0) {
                \think\Db::name('links')->insert($dbData);
            }
            
    	    //$name = 's';
    	    //$link = 'sssss';
    	    //file_put_contents(ROOT_PATH.'public/postfix.html',var_export($mc,true) );
    	    
    	    //file_put_contents(ROOT_PATH.'public/postfix.html',"接收邮件 $email<br> 应用名称 $name<br>测试链接 $link<br>------------------------------------------------------<br><br>",FILE_APPEND);
    	    //FILE_APPEND
    	    
    	    //$html_base64 =$mc[1][0];
    	    //$html=$html_base64;
    	    //$html = base64_decode(trim($html_base64,'\n'));
    	    
    	} else {
    	     file_put_contents(ROOT_PATH.'public/postfix.html',"<br>$data 获取失败  <br>------------------------------------------------------<br><br>");
    	    
    	}
    	
    
    //其余操作
        

    	
    	
    	//die();
    	
    	
    	//receive
    	//\think\Db::name('tests')->insert(['content'=>$data]);
        
        
        $output->writeln("enameName: " . $enameName);
    }
}