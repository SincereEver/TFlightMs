<?php

namespace app\common\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class Mail extends Command
{
    protected function configure()
    {
        $this->setName('mail');
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

        foreach ($messages as $message) {


            preg_match('/(https:\/\/testflight\.apple\.com\/v1\/invite\/.*?platform=ios)[\s\S]*By using (.*?), you agree/i', $message->getBodyHtml(), $res);
            $Address =  $message->getTo();
            $info = ['tester' => $Address[0]->getAddress(), 'name' => $res[2], 'link' => $res[1], 'exist' => 1];

            $links = \think\Db::name('links')
                ->where([
                    'name' => $info['name'],
                    'tester' => $info['tester']
                ])
                ->inc('num')
                ->data($info)
                ->update();


            if ($links == 0) {
                echo "appName:" . $info['name'] . '影响条数：' . $links . ' 创建新的注册表' . PHP_EOL;
                \think\Db::name('links')->insert($info);
            } else {
                echo "appName:" . $info['name'] . ' 更新操作' . PHP_EOL;
            }
            echo "appName:" . $info['name'] . ' 测试员：' . $info['tester'] . ' 入库成功' . PHP_EOL;

            $message->delete();
        }
        $connection->expunge();
    }

    protected function execute(Input $input, Output $output)
    {
        $file = fopen(__DIR__ . '/lock', 'w+');
        //加锁
        if (flock($file, LOCK_EX | LOCK_NB)) {
            echo '正在执行中...';
            $this->getInfo();
            flock($file, LOCK_UN); //解锁
            fclose($file);
            echo '任务执行完毕';
        } else {
            //TODO 执行业务代码 返回系统繁忙等错误提示
            echo '当前有任务在执行,自动退出';
        }
        die();
        //关闭文件





    }
}
