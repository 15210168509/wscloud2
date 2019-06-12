<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-12-16
 * Time: 下午2:48
 */

namespace TCT;

use Think\Controller;
use Admin\Controller\Context;
/**
 * Class BaseController
 * @package DDP
 */
class BaseMerchantController extends Controller {

    /**
     * @var array list of css files
     */
    public $css_files = array();

    /**
     * @var array list of javascript files
     */
    public $js_files = array();

    public $js_all_files = array();



    /**
     * 添加css样式到页面
     * @param $css_uri
     * @param string $css_media_type
     * @param null $offset
     */
    public function addCSS($css_uri, $css_media_type = 'all', $offset = null)
    {
        if (!is_array($css_uri))
            $css_uri = array($css_uri=>$css_media_type);

        if (!is_array($css_uri))
            $css_uri = array($css_uri);

        foreach ($css_uri as $css_file => $media)
        {
            if (!isset($this->css_files[$css_file]))
            {
                $size = count($this->css_files);
                if ($offset === null || $offset > $size || $offset < 0 || !is_numeric($offset))
                    $offset = $size;
                $this->css_files = array_merge(array_slice($this->css_files, 0, $offset), array($css_file=>$media), array_slice($this->css_files, $offset));
            }
        }
    }

    /**
     * 移除css样式
     * @param $css_uri
     * @param string $css_media_type
     */
    public function removeCSS($css_uri, $css_media_type = 'all')
    {
        if (!is_array($css_uri))
            $css_uri = array($css_uri=>$css_media_type);

        foreach ($css_uri as $css_file => $media)
        {
            if (isset($this->css_files[key($css_file)]))
                unset($this->css_files[key($css_file)]);
        }
    }

    /**
     *  添加js文件到页面
     * @param mixed $js_uri
     * @return void
     */
    public function addJS($js_uri)
    {
        if (is_array($js_uri)){
            foreach ($js_uri as $js_file)
            {
                $key = is_array($js_file) ? key($js_file) : $js_file;
                if (!in_array($js_file, $this->js_files))
                    $this->js_files[] = $js_file;
            }
        }
        else
        {
            if ($js_uri && !in_array($js_uri, $this->js_files))
                $this->js_files[] = $js_uri;
        }
    }

    /**
     * @param $js_uri
     */
    public function removeJS($js_uri)
    {
        if (is_array($js_uri))
            foreach ($js_uri as $js_file)
            {
                if ($js_file && in_array($js_file, $this->js_files))
                    unset($this->js_files[array_search($js_file,$this->js_files)]);
            }
        else
        {
            if ($js_uri)
                unset($this->js_files[array_search($js_uri,$this->js_files)]);
        }
    }



    /**
     * Add a new javascript file in page header.
     *
     * @param mixed $js_uri
     * @return void
     */
    public function addAllJS($js_uri)
    {
        if (is_array($js_uri)){
            foreach ($js_uri as $js_file)
            {
                $key = is_array($js_file) ? key($js_file) : $js_file;
                if (!in_array($js_file, $this->js_all_files))
                    $this->js_all_files[] = $js_file;
            }
        }
        else
        {
            if ($js_uri && !in_array($js_uri, $this->js_all_files))
                $this->js_all_files[] = $js_uri;
        }
    }

    /**
     * @param string $templateFile
     * @param string $charset
     * @param string $contentType
     * @param string $content
     * @param string $prefix
     */
    public function display($templateFile='',$charset='',$contentType='',$content='',$prefix=''){



        $this->initHeader();
        //输出页面
        parent::display($templateFile,$charset,$contentType,$content,$prefix);

    }
    public function initHeader(){
        $this->assign("css_files",$this->css_files);
        $this->assign("js_all_files",$this->js_all_files);
        $this->assign("js_files",$this->js_files);
        $sellerType = $this->getSellerType();
        $menuHtml = $this->generateMenu();
        $this->assign('menuProduct',$menuHtml);
        $this->assign('sellerType',$sellerType);
        $this->assign('imgApi',C('IMG_API'));
    }

    public function generateMenu(){
        $menuModel = D('Menu');
        $result = $menuModel->getProductMenu();

        if($result['status']==1){

            $menuOrg = $result['data'];
            $menuNew = array();

            $menuParent = array();
            $menuIds     = array();
            foreach($menuOrg as $row){
                $menuParent[$row['father_type_id']][] = $row;
                $menuIds[$row['id']] = $row;
            }

            $menuFinal = $this->getTree($menuParent,$menuIds,3,null);


            return $menuFinal['children'];
//            生成html
//            return $this->buildHtml($menuFinal);

        }


    }
    public function getTree($menuParent,$menuIds,$maxDepth,$id_menu = null,$currentDepth = 0){

         $children = array();
        if(is_null($id_menu)){
            $id_menu = 0;
        }
        if(isset($menuParent[$id_menu]) && count($menuParent[$id_menu]) && ($maxDepth ==0 || $maxDepth>$currentDepth)){
            foreach($menuParent[$id_menu] as $subcat){
                $children[] = $this->getTree($menuParent,$menuIds,$maxDepth,$subcat['id'],$currentDepth+1);
            }
        }
        return array('id'=>$id_menu,'name'=>$menuIds[$id_menu]['good_type_name'],'fatherid'=>$menuIds[$id_menu]['father_type_id'],'children'=>$children);
    }

    public function buildHtml($tree){
        $html = '';
        if(count($tree)>0){
            $html = '<ul>';
            if(count($tree['children'])>0){
                foreach($tree['children'] as $treeItem){
                    $html .='<li>'.$treeItem['name'].'</li>';
                     $html .=$this->buildHtml($treeItem);

                }
            }
            $html .='</ul>';

        }
        return $html;

    }

    public function getSellerType()
    {
        $sellerTypeDate = D('Menu');
        $result = $sellerTypeDate->getSellerTypeInfo();
        if($result['code'] == 1) {
            return $result['data'];
        }
    }
}