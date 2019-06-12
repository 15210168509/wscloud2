<?php
namespace Admin\Controller;

use Lib\ListAdminController;

class IndexController extends ListAdminController  {
    /**
     * 首页控制面板
     */
    public function Index()
    {
        $this->breadcrumb = array("控制面板"=>'/');

        $this->addCSS(array('admin/index/index.css'=>'all'));
        $this->display();

    }

}