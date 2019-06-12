<?php
/**
 * Created by ye
 * Date: 2016-6-8
 */

namespace Office\Controller;


use Lib\BaseManagementController;
use Lib\CommonConst;

class LogoutController extends BaseManagementController
{
    
    public $authentication = false;

    public function index(){
        $model = D('ManagementLogin');
        $model->logOut(C('TOKEN_TYPE'));

        //删除session
        unset($_SESSION[C('OfficeSessionKey')]);
        redirect(C('baseUrl').'/ManagementLogin');
    }

}