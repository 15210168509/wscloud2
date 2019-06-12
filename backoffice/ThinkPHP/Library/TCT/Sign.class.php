<?php
namespace TCT;
class Sign
{
	/**
	http://127.0.0.1:8080/stores/stores/BsStoresEmployeesService/existCode/code1?SIGN=4ec07892e2ae55b1e866be3418f9ef7
加密源：/stores/BsStoresEmployeesService/existCode/code1
说明:加密源不包括contextPath部分，也不包括?及其后面的内容
加密算法：md5
加密值：4ec07892e2ae55b1e866be3418f9ef7
参数名称：SIGN
	*/
	function get($url)
	{
		$u=parse_url($url);
		$path=$u['path'];
		$tmp=explode('/',$path);
		unset($tmp[1]);
		$path=implode($tmp,'/');
		$sign=$this->encode($path);
		$r=$url.'?SIGN='.$sign;

		return $r;
	}


	/**
	http://192.168.0.142:8080/stores/md5.jsp
	
	81dc9bdb52d04dc2036dbd8313ed055
	
	c4ca4238a0b92382dcc509a6f75849b
	*/
	
	function byteToHexString($b) {
		static $hexDigits= array("0", "1", "2", "3", "4", "5", "6", "7",
            "8", "9", "a", "b", "c", "d", "e", "f");
			
         $n = $b;
		
        if ($n < 0) {
            $n = 256 + $n;
        }
        if($n<16){
        	return $hexDigits[$n];
        }else{
        	$d1 = intval($n / 16);
             $d2 =intval($n % 16);
              $a1=$a2='';
 
            return $hexDigits[$d1] . $hexDigits[$d2];
        }
    
        
    }
	
	/*
	
	7651 3ad7 2485 7c09 8d1e d0e3 3173 cd68
	7651 3ad7 2485 7c 9 8d1e d0e3 3173 cd68
	
	奇数
	15
	*/
	
function Hex2StringPPD2($hex){
    $string='';
	$k=0;
    for ($i=0; $i < strlen($hex)-1; $i+=2){
     	$tt='';
		$t=chr(hexdec($hex[$i].$hex[$i+1]));

		if($t=='0' && $k%2==0 )
		{
		$tt='xxxx';
		$t='';		
		}
		
		$string .= $t;
		
		$k++;
    }
    return $string;
}


	
	function Hex2StringDDP($hex){
    $string='';
    for ($i=0; $i < strlen($hex)-1; $i+=2){

	   $tt=$this->byteToHexString(hexdec($hex[$i].$hex[$i+1]));

	   $string .= chr($tt);

	   $string1 .= $tt;
	   
    }
	// 7b933abc5b57b8884bb998b674bccbca
	
	 $ss=$this->Hex2StringPPD2($string1);

	 return $ss;
	 
	
}

	
	/**
	
	**/
	function encode($s)
	{

		$s=md5($s);
		$rr='';
		$hex=String2Hex($s);
		return $this->Hex2StringDDP($hex);
		
	}
	
	
	function unicode_decode($unistr, $encoding = 'UTF-8', $ishex = false, $prefix = '&#', $postfix = ';') {
    $arruni = explode($prefix, $unistr);
    $unistr = '';
 
    for($i = 1, $len = count($arruni); $i < $len; $i++) {
        if (strlen($postfix) > 0) {
            $arruni[$i] = substr($arruni[$i], 0, strlen($arruni[$i]) - strlen($postfix));
        }
        $temp = $ishex ? hexdec($arruni[$i]) : intval($arruni[$i]);
        $unistr .= ($temp < 256) ? chr(0) . chr($temp) : chr($temp / 256) . chr($temp % 256);
    }
 
    return iconv('UCS-2', $encoding, $unistr);
}

	
	
	/**
	post方式：
http://127.0.0.1:8080/stores/stores/BsStoresEmployeesService/insertEmployee?SIGN=fa5117f2d9ceac55c3cc8c57943d461a

加密源：
{"id":null,"employeeState":null,"employeePosition":"总经理","employeePwd":"3213232","employeeTel":"21323123","employeeRoleId":null,"storeId":null,"employeeCode":"wangwu","employeeName":"王五"}
注意：加密源中不能换行。
加密值：fa5117f2d9ceac55c3cc8c57943d461a
	*/
	function post($url,$ds)
	{
		if(is_array($ds))
		{
            arrayRecursive($ds, 'strval', true);
            $keyValue = keyValue($ds);
            $arr['data'] = md5($keyValue);
			$j=JSON($arr);
		}
		else
		{
			$j=$ds;
		}

		$sign=$this->encode($j);
		$r=$url.'?SIGN='.$sign;
		return $r;

	}

    function http_post_data($url, $data_string) {
  
        $ch = curl_init();  
        curl_setopt($ch, CURLOPT_POST, 1);  
        curl_setopt($ch, CURLOPT_URL, $url);  
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);  
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(  
            'Content-Type: application/json; charset=utf-8',  
            'Content-Length: ' . strlen($data_string))  
        );  
        ob_start();  
        curl_exec($ch);  
        $return_content = ob_get_contents();  
        ob_end_clean();  
  
        $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);  
        return array($return_code, $return_content);  
    }  
	
	
	
	
}

/**
 * 连接key=》value
 */
function keyValue($array){
    $keyVluey = "";
    foreach ($array as $k=>$value){
        if (is_array($value)){
            keyValue($value);
        }
        $keyVluey .= ($k.$value);
    }
    return $keyVluey;
}

/**************************************************************
* 
*
使用特定function对数组中所有元素做处理 
*	@param	string	&$array	要处理的字符串
*	@param	string	$function	要执行的函数 
*	@return boolean	$apply_to_keys_also	是否也应用到key上 
*	@access public 
* 
*************************************************************/ 
function arrayRecursive(&$array, $function, $apply_to_keys_also = false) 
{
	static $recursive_counter = 0;
	if(++$recursive_counter > 1000){        
		die('possible deep recursion attack');    
	}
	foreach($array as $key => $value){
        if(is_array($value)){
			arrayRecursive($array[$key], $function, $apply_to_keys_also);
		}
		else{            
			$array[$key] = $function($value);        
		}        
		if($apply_to_keys_also && is_string($key)){             
			$new_key = $function($key);            
			if($new_key != $key){                
				$array[$new_key] = $array[$key];                
				unset($array[$key]);            
			}        
		}    
	}    
	$recursive_counter--; 
} 
/************************************************************** 
* 
*	将数组转换为JSON字符串（兼容中文）
*	@param	array	$array	要转换的数组 
*	@return string	转换得到的json字符串
*	@access public 
* 
*************************************************************/
function JSON($array) 
{
arrayRecursive($array, 'urlencode', true); 
$json = json_encode($array);
 $r= urldecode($json);
 //fix null
 // ugly 
 //fck 
 $r=str_replace('""','null',$r);
 return $r;
} 

 function String2Hex($string){
    $hex='';
    for ($i=0; $i < strlen($string); $i++){
        $hex .= dechex(ord($string[$i]));
    }
    return $hex;
}

function Hex2String($hex){
    $string='';
    for ($i=0; $i < strlen($hex)-1; $i+=2){
        //$string .= chr(hexdec($hex[$i].$hex[$i+1]));
		$string .= chr(hexdec($hex[$i].$hex[$i+1]));
		//var_dump(chr(hexdec($hex[$i].$hex[$i+1])));
    }
    return $string;
}

