<?php

namespace app\common\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class CheckMail extends Command
{

    protected function configure()
    {
        //max_execution_time(60);
        //print_r(get_loaded_extensions());
        $this->t1 = time();

        if (!in_array('imap', get_loaded_extensions())) die('没有安装imap扩展');
        ini_set("max_execution_time", 30);
        $this->setName('CheckMail');
    }
    protected function getInfo()
    {

        $imap_host = config('site.imap_host');
        $imap_port = config('site.imap_port');
        $imap_user = config('site.imap_user');
        $imap_pass = config('site.imap_pass');
        $server = new \Ddeboer\Imap\Server(
            $imap_host, // required
            $imap_port,     // defaults to '993'
            '/imap/novalidate-cert'
        );
        $connection = $server->authenticate($imap_user, $imap_pass);
        $mailbox = $connection->getMailbox('INBOX');
        $messages = $mailbox->getMessages(new \Ddeboer\Imap\Search\Email\From('no_reply@email.apple.com'));
        $c = [];
        $apps = \think\Db::name('app')->where('id', '<>', 0)->field('id,name')->select();
        foreach ($apps as $app) {
            $c[$app['name']] = $app['id'];
        }

        $newCount = 0;
        //print_r(count($messages));
        //die(count($messages) . '条');
        foreach ($messages as $message) {
            die($message->getBodyText());
            preg_match('/(https:\/\/testflight\.apple\.com\/v1\/invite\/.*?platform=ios)[\s\S]*By using (.*?), you agree/i', $message->getBodyHtml(), $res);
            return;
            if (!empty($res)) {
                $Address =  $message->getTo();
                $info = ['email' => $Address[0]->getAddress(), 'name' => $res[2], 'app_id' => $c[$res[2]], 'link' => $res[1], 'update_time' => time()];

                echo var_export($info, true) . PHP_EOL;
                if ($newCount == 5) return;

                // $linksCount = \think\Db::name('links')->where(['app_id' => $info['app_id'], 'email' => $info['email']])->data($info)->update();
                // if ($linksCount == 0) {
                //     \think\Db::name('links')->insert($info);
                // }
                // $message->delete();
                // $connection->expunge();
            } else {
                // $message->getBodyHtml();
                //die();
            }
            $newCount++;
        }

        return $newCount;
    }

    protected function execute(Input $input, Output $output)
    {

        $file = fopen(__DIR__ . '/lock', 'w+');
        //加锁
        if (flock($file, LOCK_EX | LOCK_NB)) {
            echo '---------------------------' . PHP_EOL;
            echo 'start...' . PHP_EOL . PHP_EOL;
            sleep(10);
            //$count = $this->getInfo();
            flock($file, LOCK_UN); //解锁
            fclose($file);
            echo '执行完毕，用时 ' . (time() - $this->t1) . ' 秒，获取链接 ' . $count . ' 条' . PHP_EOL . PHP_EOL;

            echo 'end...' . PHP_EOL;
            echo '---------------------------' . PHP_EOL;
        } else {
            //TODO 执行业务代码 返回系统繁忙等错误提示
            echo 'task is in progress...';
        }

        //关闭文件
    }
}
