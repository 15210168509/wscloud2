<?php
/**
 * Created by 李文起
 * User: 01
 * Date: 2018/5/23
 * Time: 9:50
 */

namespace Office\Model;


class PollModel extends ApiModel
{
    public function uploadTiredValue($data){
        return $this->getResult('/Poll/uploadTiredValue','post',$data);
    }
}