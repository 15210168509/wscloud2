<?php
    $shell = "/usr/install/bsdiff-4.3/bsdiff ";
	$dir = "/usr/install/bsdiff-4.3/app-version/";
	$version = "vshiFaceCC_v1.0.2.apk";
	
    echo "<pre>";
    system('cd /usr/install/bsdiff-4.3; ./', $status);
    echo "</pre>";
    //注意shell命令的执行结果和执行返回的状态值的对应关系
    $shell = "<font color='red'>$shell</font>";
    if( $status ){
        echo "shell命令{$shell}执行失败";
    } else {
        echo "shell命令{$shell}成功执行";
    }
?>
 