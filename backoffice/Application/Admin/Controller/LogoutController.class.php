<?php
/**
 * Created by ye
 * Date: 2016-6-8
 */

namespace Admin\Controller;

use Lib\ListAdminController;

class LogoutController extends ListAdminController
{
    
    public $authentication = false;

    public function index(){
        $model = D('Login');
        $res = $model->logOut(C('TOKEN_TYPE'));

        //删除session
        unset($_SESSION[C('OfficeSessionKey')]);
        redirect(C('baseUrl').'/Login');
    }

}