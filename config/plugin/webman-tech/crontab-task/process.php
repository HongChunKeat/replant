<?php

use WebmanTech\CrontabTask\Schedule;

// 添加多个定时任务，在同个进程中（注意会存在阻塞）
//    ->addTasks("task2", [
//        ["*/1 * * * * *", \WebmanTech\CrontabTask\Tasks\SampleTask::class],
//        ["*/1 * * * * *", \WebmanTech\CrontabTask\Tasks\SampleTask::class],
//    ])

return (new Schedule())
    ->addTask("deposit_check_status", "*/15 * * * * *", \app\crontab\tasks\deposit\DepositCheckStatus::class)
    ->addTask("withdraw_approve", "*/15 * * * * *", \app\crontab\tasks\withdraw\WithdrawApprove::class)
    ->addTask("withdraw_check_status", "*/15 * * * * *", \app\crontab\tasks\withdraw\WithdrawCheckStatus::class)
    ->addTask("nft_check_status", "*/15 * * * * *", \app\crontab\tasks\nft\NftCheckStatus::class)
    // ->addTask("test", "0 0 * * *", \app\crontab\tasks\MiscTask::class)
    ->buildProcesses();