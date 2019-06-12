/**
 * Created by 01 on 2018/5/11.
 */

layui.use(['form','layer'], function(){

    var baseUrl = $('#baseUrl').val();

    var form = layui.form
        ,layer = layui.layer;

    //自定义验证规则
    form.verify({
        name:function (value) {
            if (value.length == 0) {
                return '请输入司机姓名';
            } else {
                var reg = /^([\u4e00-\u9fa5]){2,7}$/;
                if(!reg.test(value)){
                    return '姓名只能为中文';
                }
            }
        }
        ,account: function (value) {
            if (value .length == 0) {
                return '请填写登录账户';
            } else {
                var reg = /^[a-zA-Z0-9]{3,16}$/;
                if (!reg.test(value)){
                    return '用户名只能为数字或字母';
                }
            }
        }
        ,password: [/(.+){6,12}$/, '密码必须6到12位']
        ,verify_password: function (value) {
            if (value.length == 0){
                return '请输入确认密码';
            }
            if($('#password').val() != value) {
                return '确认密码不匹配';
            }
        }
        ,phone_code: [/^[0-9]{4}$/,'请填写正确的验证码']
    });

    //监听提交
    form.on('submit(demo1)', function(data){

        $.ajax({
            type: "post",
            url: baseUrl + "/Driver/wsUserRegister",
            data: data.field,
            dataType: "json",
            success: function(data)
            {
                if(data.code == 1)
                {
                    $('#send_code').removeClass('layui-btn-disabled').attr('disabled', false);
                    $('#send_code').html('获取验证码');
                }
                layer.msg(data.msg);
            }
        });
        return false;
    });


    $('#send_code').click(function () {

        var phone = $('#phone').val();
        if (phone.length == 0){
            layer.msg('手机号不能为空');
            return;
        }

        var reg = /^[1][3,4,5,7,8][0-9]{9}$/;
        if (!reg.test(phone)){
            layer.msg('请输入正确的手机号');
            return;
        }

        var data = {phone:phone};

        $('#send_code').addClass('layui-btn-disabled');
        $('#send_code').attr('disabled',true);

        $.ajax({
            type: "post",
            url: baseUrl + "/Driver/ajaxSendCode",
            data: data,
            dataType: "json",
            success: function(data)
            {
                if(data.code == 1)
                {
                    setAuthTime( $('#send_code'),60);
                } else {
                    $('#send_code').removeClass('layui-btn-disabled');
                    $('#send_code').attr('disabled',false);
                }
                layer.msg(data.msg);
            }
        });

    });

    //倒计时
    function setAuthTime(obj, count) {
        window.setTimeout(function(){
            count--;
            if(count > 0) {
                obj.text(count+"秒");
                setAuthTime(obj, count);
            } else {
                obj.removeClass('layui-btn-disabled').attr('disabled', false);
                obj.html('重新获取');
            }
        }, 1000);
    }

});