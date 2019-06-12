<?php
/**
 * Created by 李文起
 * User: 01
 * Date: 2018/5/3
 * Time: 14:53
 */

namespace Lib\RefreshToken;


use Lib\Auth;

class RefreshToken
{
    protected $oldToken;
    protected $refreshToken;

    public function __construct($oldToken, $refreshToken)
    {
        $this->oldToken         = $oldToken;
        $this->refreshToken     = $refreshToken;

        $this->refreshResult = false;
    }

    public function refreshTokenValue($result){
        $auth = new Auth();
        return $auth->refreshToken($this->oldToken,$result);
    }
}