<?php

require(__DIR__ . '/../vendor/autoload.php');

use PHPsize\codeSizer;

if(isset($argv)){
	$class = new codeSizer();
	echo call_user_func_array(array($class, 'init'), $argv);
	die;
}else{
	die('not isset argv');
}

