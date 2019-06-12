<?php

function object_to_array($obj){
    $_arr = is_object($obj)? get_object_vars($obj) :$obj;
    foreach ($_arr as $key => $val){
        $val=(is_array($val)) || is_object($val) ? object_to_array($val) :$val;
        $arr[$key] = $val;
    }
    return $arr;
}

/**
 * 后台日志存储
 * @return LoggerRoot Log4php日志对象
 */
function officeLogger(){
    vendor('log4php.Logger');
    \Logger::configure(APP_PATH.'Office/Conf/log4php.xml');
    $pay = \Logger::getRootLogger();
    return $pay;
}

/**
 * 组合字符串函数
 * @param   $data array OR string   要存储的信息  	 *
 * @return  string   组合完成的字符串
 */
function combineStr($data){
    static $newstr='';

    if(is_object($data)) $data =ob2ar($data);

    if(is_array($data)){
        foreach ($data as $key => $value){
            if(is_array($value)){
                $function = __FUNCTION__;
                $function($value);
            }else{
                $newstr .= "\r\n".$key.':::'.$value;
            }
        }
    }else{
        $newstr = $data;
    }
    return $newstr;
}

function ob2ar($obj) {
    if(is_object($obj)) {
        $obj = (array)$obj;
        $obj = ob2ar($obj);
    } elseif(is_array($obj)) {
        foreach($obj as $key => $value) {
            $obj[$key] = ob2ar($value);
        }
    }
    return $obj;
}

/**
 * 生成顶部导航菜单
 * @param $authority array 用户权限
 * @param $menuOrg array 原始菜单
 * @param $controllerName string 当前控制器
 * @return array
 * @author wrf
 */
function getTopMenu($authority=array(),$menuOrg,$controllerName){
    $menu_array = $menuOrg;
    //待返回的菜单
    $menu = array();
    if(!empty($authority)){
        foreach($menu_array as $k=>$v){
            for($i=0;$i<count($authority);$i++){
                if($v["id"] == $authority[$i]){
                    if(in_array($controllerName,$v['controller'])){

                        $v['active'] = 'active';
                    }
                    $menu[] = $v;
                }
            }
        }
    }else{
        $menu = $menu_array;
    }
    return $menu;
}
/**
 * 生成左侧菜单数据
 * @param $authority array 权限
 * @param $array array 一级菜单
 * @param $controllerName string 控制器名称
 * @return array
 */
function getMenu($authority= array(),$array,$controllerName){

    $menu_array  = $array;
    $authority[] = 1;
    $menu        = array();
    //按照权限筛选一下$menu
    if(!empty($authority)){
        foreach($menu_array as $item){
            //获取topMenu
            if(in_array($controllerName,$item['controller'])) {
                foreach($item['submenu'] as $k=>$v){
                    for($i=0;$i<count($authority);$i++){
                        if($v["id"] == $authority[$i]){
                            $menu[] = $v;
                        }
                    }
                }
            }
        }

    }else{
        $menu = $menu_array[0]['submneu'];
    }
    return $menu;
}

/**
 * 超级后台生成左侧菜单数据
 * @return string
 */
function getAdminMenu($authority="",$array){

    $menu_array = $array;
    $authority[] = 1;
    //按照权限筛选一下$menu
    if(!empty($authority)){
        foreach($menu_array as $k=>$v){
            for($i=0;$i<count($authority);$i++){
                if($v["id"] == $authority[$i]){
                    $menu[] = $v;
                }
            }
        }
    }else{
        $menu = $menu_array;
    }
    return $menu;
}


function openTopMenu($menu){

    $result = '<ul class="layui-nav layui-layout-left">';
    foreach($menu as $item){
        $class = 'nav-link';
        /*if(isset($item['active'])) {
            $class.= ' active';
        }*/
        $result .='<li class="layui-nav-item"><a class="'.$class.'" href="'.C('baseUrl').$item['url'].'">'.$item['name'].'</a></li>';
    }
    $result = $result . '</ul>';
    return $result;
}
function openMenu($menu,$openUrl,$type = "user"){
    $pid = '0';
    $str = '<ul class="layui-nav layui-nav-tree">';
    $flag = '0';

    //获取激活菜单
    $menuLevel = array();
    getMenuActive($menu,$openUrl,$menuLevel,true);

    $result = menu($menu, $pid, $str, $flag, $menuLevel,$type);
    $result = $result . '</ul>';
    return $result;
}


/**
 * 生成层次<ul>
 * @param $arr
 * @param $id
 * @param $str
 * @param $flag
 * @return string
 */
function menu($arr,$id,$str,$flag,$menuLevel,$type){
    //在数据库（$arr数组）找$id的子栏目

    $clist=array();
    foreach($arr as $k => $v ){
        if( $v['pid'] == $id ){
            $clist[]=$v;
        }
    }

    if(!empty($clist)){   //如果$id有子栏目，则把每个子栏目处理一次。
        foreach($clist as $k => $v){
            $id2=$v['id'];
            $subcat=array();
            foreach($arr as $k2 => $v2 ){
                if( $v2['pid'] == $id2 ){
                    $subcat[]=$v2;
                }
            }
            $active = "";
            $menuLength = count($menuLevel);
            if(!empty($subcat)){

                for($i=$menuLength-1;$i>=0;$i--){
                    if($v['id'] == $menuLevel[$i]["self"]){
                        $active = "layui-nav-itemed";
                    }
                }

                if ($v['url'][0] == '#') {
                    $str=$str.'<li id="menu_'.$v['id'].'" class="'.$active.' layui-nav-item"><a class="" href="javascript:;">'.$v['name'].'</a><ul class="layui-nav-child" style="'.(in_array($v['id'], $menuLevel[0]["self"]) ? 'display: block;' : '').'">'; //如果此栏目有子栏目，那么就是有一个<ul>。
                } else {
                    $str=$str.'<li id="menu_'.$v['id'].'" class="'.$active.' layui-nav-item"><a class="" href="'.C('baseUrl').$v['url'][0].'">'.$v['name'].'</a><ul class="layui-nav-child" style="'.(in_array($v['id'], $menuLevel[0]["self"]) ? 'display: block;' : '').'">'; //如果此栏目有子栏目，那么就是有一个<ul>。
                }

                $flag=$flag+1;
                $str=menu($arr,$id2,$str,$flag,$menuLevel,$type); //如果$id2有子栏目，就要递归。
            }else{
                for($i=$menuLength-1;$i>=0;$i--){
                    if($v['id'] == $menuLevel[$i]["self"]){
                        $active = "layui-nav-itemed";
                    }
                }
                if ($v['openNew']) {
                    $str=$str.'<li id="menu_'.$v['id'].'" class="'.$active.'"><a target="_blank" href="'.C('baseUrl').$v['url'][0].'"><i class="'.$v["icon"].'"></i><span class="menu-text">'.$v['name'].'</span></a></li>'; //一个栏目递归到最底层子栏目就走这儿，写一个<li>项，再继续for循环，处理下一个栏目。

                } else {
                    $str=$str.'<li id="menu_'.$v['id'].'" class="'.$active.'"><a href="'.C('baseUrl').$v['url'][0].'"><i class="'.$v["icon"].'"></i><span class="menu-text">'.$v['name'].'</span></a></li>'; //一个栏目递归到最底层子栏目就走这儿，写一个<li>项，再继续for循环，处理下一个栏目。

                }
                //$str=$str.'<li id="menu_'.$v['id'].'" class="'.$active.'"><a href="'.$v['url'][0].'"><i class="'.$v["icon"].'"></i><span class="menu-text">'.$v['name'].'</span></a></li>'; //一个栏目递归到最底层子栏目就走这儿，写一个<li>项，再继续for循环，处理下一个栏目。
            }
        }
        //对于第一次执行来说，for循环结束之后，所有顶级栏目的递归完成，也就是所有栏目都处理过了，返回$str即可。
        if($id == '0' || $flag == '0' ){
            return $str;
        }
        //如果flag != 0，说明不是第一次递归，是中间递归过程，中间每递归一次要添加</ul>，与<ul>成对。
        if($flag != '0' ){
            $str=$str.'</ul></li>';
            return $str;
        }
    }
    return $str;
}

/**
 * @param $menu         //菜单数组
 * @param $openUrl      //打开的网页url
 * @param $menuLevel    //打开的等级
 * @param $bool         //几次
 */
function getMenuActive($menu,$openUrl,&$menuLevel,$bool){
    if($bool){
        foreach($menu as $k=>$v){
            if(in_array($openUrl,$v["url"])){
                $menuLevel[] = array("self"=>$v["id"],"pid"=>$v["pid"]);
            }
        }
    }
    $pid = $menuLevel[count($menuLevel)-1]["pid"];
    if($pid!=0){
        foreach($menu as $k=>$v){
            if($v["id"] == $pid){
                $menuLevel[] = array("self"=>$v["id"],"pid"=>$v["pid"]);
            }
        }
        getMenuActive($menu,$openUrl,$menuLevel,false);
    }
}

/**
 * 权限复选框HTML生成
 * @param  array  $arr          配置数组
 * @param  string $type         权限类型
 * @param  int    $depth        层级深度
 * @param  string $fatherRight  父级权限
 * @return string HTML字符串
 */
function rightHtml($arr=array(), $type='children', $depth=1, $fatherRight='') {
    if ($depth == 1) {
        $html = '<div id="r_1_0" depth="1" style="padding-top:2px; max-height:350px;overflow: auto;padding-left: 5px">';
    } else {
        $html = '<div id="r_'.$depth.'_'.$fatherRight.'" depth="'.$depth.'" style="padding-top: 2px;margin-left: 30px; display: none;">';
    }
    if (!$arr) {
        $arr = C('RIGHT');
    }
    foreach ($arr as $key => $value) {
        $html .= '<input type="checkbox" name="right" value="'.$value['right'].'" id="r_'.$value['right'].'" right="'.$value['right'].'"><label for="r_'.$value['right'].'" style="padding-left: 4px;">'.$value['name'].'</label>';
        $html .= '<br>';
        if (count($value['children'])>0) {
            $html .= rightHtml($value['children'], 'children', $depth+1, $value['right']);
        }
        if (count($value['operations'])>0) {
            $html .= rightHtml($value['operations'], 'operations', $depth+1, $value['right']);
        }
    }
    $html .= '</div>';
    return $html;
}

function getRightAll($arr = array())
{
    $rightStr = '';
    if (empty($arr)) {
        $arr = C('RIGHT');
        $start = true;
    } else {
        $start = false;
    }
    foreach ($arr as $key => $val) {
        if (is_array($val) && !empty($val)) {
            if ($start) {
                $rightStr .= $val['right'];
                $start = false;
            } else {
                $rightStr .= ',' . $val['right'];
            }
            if (is_array($val['children']) && !empty($val['children'])) {
                $rightStr .= getRightAll($val['children']);
            }
            if (is_array($val['operations']) && !empty($val['operations'])) {
                $rightStr .= getRightAll($val['operations']);
            }
        }
    }
    return $rightStr;
}

/**
 * 将对象转换成数组
 * @param $obj
 * @return array
 */
function objectToArray($obj){
    $arr = is_object($obj) ? get_object_vars($obj) : $obj;
    if(is_array($arr)){
        return array_map(__FUNCTION__, $arr);
    }else{
        return $arr;
    }
}

/**
 * 阿里云文件路径转换
 */
function getOssFileUrl($path, $fileType=null) {
    switch ($fileType) {
        case 'img':
            $url = C('IMG_FILE_SERVER') . '/' . $path; break;
        case 'nv21':
            $url = C('NV21_FILE_SERVER') . '/' . $path; break;
        case 'video':
            $url = C('VIDEO_FILE_SERVER') . '/' . $path; break;
        default:
            $url = C('PRO_FILE_SERVER') . '/' . $path; break;
    }
    return $url;
}

