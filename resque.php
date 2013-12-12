<?php
// 1. 指定队列
$QUEUE = getenv('QUEUE');
if(empty($QUEUE)) {
	die("Set QUEUE env var containing the list of queues to work.\n");
}

require_once 'lib/Resque.php';
require_once 'lib/Resque/Worker.php';

// 2. 指定Redis
$REDIS_BACKEND = getenv('REDIS_BACKEND');
if(!empty($REDIS_BACKEND)) {
	Resque::setBackend($REDIS_BACKEND);
}

// 3. 设置日志级别
$logLevel = 0;
$LOGGING = getenv('LOGGING');
$VERBOSE = getenv('VERBOSE');
$VVERBOSE = getenv('VVERBOSE');
if(!empty($LOGGING) || !empty($VERBOSE)) {
	$logLevel = Resque_Worker::LOG_NORMAL;
}
else if(!empty($VVERBOSE)) {
	$logLevel = Resque_Worker::LOG_VERBOSE;
}

// 4. app?
$APP_INCLUDE = getenv('APP_INCLUDE');
if($APP_INCLUDE) {
	if(!file_exists($APP_INCLUDE)) {
		die('APP_INCLUDE ('.$APP_INCLUDE.") does not exist.\n");
	}

	require_once $APP_INCLUDE;
}

// 5. 间隔时间(秒): 用来检查队列
$interval = 5;
$INTERVAL = getenv('INTERVAL');
if(!empty($INTERVAL)) {
	$interval = $INTERVAL;
}

// 6. 多进程控制
$count = 1;
$COUNT = getenv('COUNT');
if(!empty($COUNT) && $COUNT > 1) {
	$count = $COUNT;
}

// 7. run
if($count > 1) {
	for($i = 0; $i < $count; ++$i) {
		$pid = pcntl_fork();
        // 父进程和子进程都会执行下面的代码
        var_dump($pid);
		if($pid == -1) {
			die("Could not fork worker ".$i."\n");
		}
		// Child, start the worker
		else if(!$pid) {  // 子进程得到的$pid为0, 所以这里是子进程执行的逻辑
			$queues = explode(',', $QUEUE);
			$worker = new Resque_Worker($queues);
			$worker->logLevel = $logLevel;
			fwrite(STDOUT, '*** Starting worker '.$worker."\n");
			$worker->work($interval);
			break; // 子进程中断, 避免进一步循环
		}
	}
}
// Start a single worker
else {
	$queues = explode(',', $QUEUE);
	$worker = new Resque_Worker($queues);
	$worker->logLevel = $logLevel;
	
	$PIDFILE = getenv('PIDFILE');
	if ($PIDFILE) {
		file_put_contents($PIDFILE, getmypid()) or
			die('Could not write PID information to ' . $PIDFILE);
	}

	fwrite(STDOUT, '*** Starting worker '.$worker."\n");
	$worker->work($interval);
}
?>
