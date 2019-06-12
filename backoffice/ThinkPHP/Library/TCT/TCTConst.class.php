<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/1/14
 * Time: 11:04
 */
namespace TCT;

class TCTConst{

    //订单状态
    const ORDER_NEW         =  0;//新订单
    const ORDER_TO_PAY      =  1;//订单待支付
    const ORDER_PAYED       =  2;//订单已支付
    const ORDER_TRANSFER    =  3;//订单已取，配送中
    const ORDER_FINISHED    =  4;//订单已送达，客户已签收
    const ORDER_REFUND      =  5;//已退款

    //抢单状态
    const VIE_INIT          = 0;//订单初始态
    const VIE_PRIVATE       = 1;//订单可以推送给私人
    const VIE_PUBLIC        = 2;//订单可以推送给所有人
    const VIE_RUSHED        = 3;//订单已被抢
    const VIE_FAILED        = 4;//流单


    //逻辑删除位
    const FLG_DELETED        = 1;//已删除
    const FLG_VALIDATE       = 0;//未删除

    //取货密码状态
    const PASSSWORD_VALIDATE = 1;//取货密码有效
    const PASSWORD_INVALIDATE= 0;//取货密码失效

    //支付来源
    const PAY_SOURCE_WX       = 1;//微信支付
    const PAY_SOURCE_ALI      = 2;//支付宝支付
    const PAY_SOURCE_TCT      = 3;//当面支付
    const PAY_SOURCE_WALLET   = 4;//同城兔钱包支付

    //支付类型
    const PAY_TYPE_WX        = 1;//微信支付
    const PAY_TYPE_ALI       = 2;//支付宝支付
    const PAY_TYPE_TCT       = 3;//当面支付
    const PAY_TYPE_WALLET    = 4;//钱包支付


    //自动登录加密key
    const LOGIN_DES_KEY = 'jOls17DQWLHv9Hu';
}