<?php
/**
 * Created by 李文起
 * User: 01
 * Date: 2018/3/5
 * Time: 11:36
 */

namespace Admin\Model;


class AdminMsgModel extends ApiModel
{
    public function getList($para){
        $url = '/Manager/managerMsg/';
        foreach($para as $key=>$value){
            $url .= $key.'/'.$value.'/';
        }
        $result = $this->getResult($url.rtrim('/'));

        return $result;
    }

    public function readMsg($id)
    {
        $result = $this->getResult("/Manager/readMsg/msgId/".$id, 'get');
        return $result;
    }

    public function delMsg($id)
    {
        $result = $this->getResult("/Manager/delMsg/msgId/".$id, 'get');
        return $result;
    }

    /**
     * 得到登陆管理员的消息个数
     * @param $status
     * @return mixed
     */
    public function getAdminMsgCount($status){
        $result = $this->getResult("/Manager/getManagerMsgCount/status/".$status, 'get');
        return $result;
    }
}