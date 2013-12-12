<?php
// 入队文件
// demo/queue.php

$jobId = Resque::enqueue('default', $argv[1], $args, true);

$result = Resque_Job::create($queue, $class, $args, $trackStatus);

Resque::push($queue, array(
                 'class'	=> $class,
                 'args'	=> array($args),
                 'id'	=> $id,
             ));

self::redis()->sadd('queues', $queue);
self::redis()->rpush('queue:' . $queue, json_encode($item));

self::$redis = new Resque_Redis($host, $port);

// public function __call($name, $args) {
$args[0] = $keyCommands;
$args[1][0] = $redis_key;
$args[1][1] = $redis_value;

return parent::__call($name, $args[1]);





// 消费队列
// resque.php
$worker = new Resque_Worker($queues);
$worker->work($interval);
