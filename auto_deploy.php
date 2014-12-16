<?php
/*
 * @warning phpからコマンドを実行した際、cdコマンドが実行できないことに注意が必要
 */

$app = isset($_POST['repository']['slug']) ? $_POST['repository']['slug'] : '';
$env = isset($_POST['commits']['branch']) ? $_POST['commits']['branch'] : '';

$log_path = 'deploy.log';
$content = '';

if($app && $env){
	$file_nm = "deploy_{$app}_{$env}";
	if((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')){
		$prefix = '';
		$ext = 'cmd';
	}else{
		$prefix = 'sh ';
		$ext = 'sh';
	}
	$cmd = "{$prefix}{$file_nm}.{$ext}";

	list($output, $status) = exec_cmd($cmd);
	if($status){
		$content  = "Failure: ".basename($cmd).', ';
		$content .= "Return Values: ".implode(", ", $output);
	}else{
		$content  = "Success: ".basename($cmd);
	}
}else{
	$content = "Failure. Please set 'app' and 'env' parameters".PHP_EOL;
}
write_log($log_path, $content, file_remake_validate($log_path) ? true : false);

//////////////////////////////////////////////////////////
function file_remake_validate($file_path){
	$is_new = true;
	if(file_exists($file_path)){
		if(filesize($file_path) < 1024){
			$is_new = false;
		}
	}
	return $is_new;
}

function write_log($log_path, $content, $is_new = false){
	if(file_exists($log_path) && $is_new)
		unlink($log_path);

	$fp = fopen($log_path, 'a');
	fwrite($fp, date('c').' ');
	fwrite($fp, $content.PHP_EOL);
	fclose($fp);
}

/*
 * @return
 * status →0:成功、1:失敗
 */
function exec_cmd($cmd){
	$cmd .= ' 2>&1';
	exec($cmd, $output, $status);
	return array($output, $status);
}
