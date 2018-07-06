<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/7/5
 * Time: 14:59
 */

for ($i = 0; $i < 2; $i++) {
    $ppid = posix_getpid();
    $pid = pcntl_fork();
    if ($pid == -1) {
        throw new Exception('fork子进程失败!');
    } elseif ($pid > 0) {
        cli_set_process_title("我是父进程,我的进程id是{$ppid}.");
        echo "我是父进程,我的进程id是{$ppid}.\r\n";
        sleep(1); // 保持30秒，确保能被ps查到
    } else {
        $cpid = posix_getpid();
        cli_set_process_title("我是{$ppid}的子进程,我的进程id是{$cpid}.");
        echo "我是{$ppid}的子进程,我的进程id是{$cpid}.\r\n";
        sleep(1);
        exit;
    }
}

