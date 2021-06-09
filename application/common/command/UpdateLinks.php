<?php

namespace app\common\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class UpdateLinks extends Command
{
    protected function configure()
    {
        $this->setName('UpdateLinks')->addArgument('appid', Argument::OPTIONAL, "appid")->setDescription('appid');
    }

    protected function execute(Input $input, Output $output)
    {
        $appid = trim($input->getArgument('appid'));
        $file = fopen(__DIR__ . '/lock/UpdateLinks.lock', 'w+');
        //加锁
        if (flock($file, LOCK_EX | LOCK_NB)) {

            $this->st();
            flock($file, LOCK_UN); //解锁
            fclose($file);
            //echo '执行完毕'.PHP_EOL;
        } else {
            //TODO 执行业务代码 返回系统繁忙等错误提示
            echo 'task is in progress...' . PHP_EOL;
        }


        //其余操作

        //die();

        //receive
        //\think\Db::name('tests')->insert(['content'=>$data]);

        $output->writeln("---监控任务执行完毕");
    }

    protected function st()
    {
        $apps = \think\Db::name('app')->where('is_check', 1)->where('links_update', '<', (time() - 60))->select();
        if (empty($apps)) {
            echo '当前没有可执行的任务' . PHP_EOL;
            return;
        }
        $i = 0;
        foreach ($apps as $app) {
            $num = \think\Db::name('links')->where('app_id', $app['id'])->count();
            if ($num < config('site.config_update_count')) {
                echo '正在执行APP：' . $app['name'] . PHP_EOL;
                $i++;
                try {
                    \app\common\library\Wq::createLinks($app['id']);
                } catch (\Exception $e) {
                    echo $app['name'] . $e->getMessage() . PHP_EOL;
                }
                echo $app['name'] . '----执行完成' . PHP_EOL;
            }
        }
        if ($i = 0) {
            echo '当前没有可执行的任务' . PHP_EOL;
            return;
        }

    }


}