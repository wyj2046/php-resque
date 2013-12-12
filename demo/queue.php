<?php
if(empty($argv[1])) {
	die('Specify the name of a job to add. e.g, php queue.php PHP_Job');
}

require dirname(__FILE__).'/../lib/Resque.php';
date_default_timezone_set('Asia/Chongqing');
Resque::setBackend('127.0.0.1:6379');

// 参数必须是个数组
$args = array(
	'time' => time(),
	'array' => array(
		'test' => 'test',
	),
);

$jobId = Resque::enqueue('default', $argv[1], $args, true);
echo "Queued job ".$jobId."\n\n";
?>