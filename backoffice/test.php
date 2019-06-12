<?php
/**
 * Created by PhpStorm.
 * User: dev
 * Date: 2018/5/10
 * Time: 13:06
 */
$file = "D:/geojson/cities.json";

if (file_exists($file)) {
    $str = file_get_contents($file);//将整个文件内容读入到一个字符串中
    $tmp = json_decode($str, true);

  foreach($tmp as $province){
      if(count($province['c'])>0){
         foreach($province['c'] as $city){

             if($city['name'] == '省直辖') {
                 continue;
             }
             $url = 'http://59.63.188.53:9993/SafeSystemWeb/static/js/high/geoJson/china-main-city/'.$city['adcode'].'.json';

             if(url_exists($url)) {
                 $res = file_get_contents($url);
                 $fileName = 'D:/geojson/cities/'.$city['adcode'].'.json';
                 $tmpFile = fopen($fileName,'w');
                 $txt = substr($res,3);
                 //$txt = 'test test';
                 fwrite($tmpFile, $txt);
                 fclose($tmpFile);
             }


         }
      }
  }

}

function url_exists($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_NOBODY, 1); // 不下载
    curl_setopt($ch, CURLOPT_FAILONERROR, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    return (curl_exec($ch)!==false) ? true : false;
}
