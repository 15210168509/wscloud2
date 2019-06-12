/**
 * Created by dev on 2018/5/14.
 */
$(function () {
    layui.use(['form','layer'], function(){

        var baseUrl = $('#baseUrl').val();

        var form = layui.form
            ,layer = layui.layer;

        //自定义验证规则
        form.verify({
            name:function (value) {
                if (value.length == 0) {
                    return '请输入姓名';
                } else {
                    var reg = /^([\u4e00-\u9fa5]){2,7}$/;
                    if(!reg.test(value)){
                        return '姓名只能为中文';
                    }
                }
            },
            account: function (value) {
                if (value .length == 0) {
                    return '请填写登录账户';
                } else {
                    var reg = /^[a-zA-Z0-9]{3,16}$/;
                    if (!reg.test(value)){
                        return '用户名只能为数字或字母';
                    }
                }
            }
            ,oldPassword: function (value) {
                if (($('#newPassword').val().length > 0 || $('#verifyPassword').val().length >0) && value.length == 0){
                    return '请填写原密码';
                }

                if (value.length >0) {
                    var reg = /(.+){6,12}$/;
                    if (!reg.test(value)){
                        return '原密码必须6到12位';
                    }
                }

            }
            ,newPassword:  function (value) {

                if (($('#oldPassword').val().length > 0 || $('#verifyPassword').val().length >0) && value.length == 0){
                    return '请填写原密码';
                }

                if (value.length >0) {
                    var reg = /(.+){6,12}$/;
                    if (!reg.test(value)){
                        return '新密码必须6到12位';
                    }
                }
            }
            ,verifyPassword: function (value) {
                if($('#newPassword').val() != value) {
                    return '确认密码不匹配';
                }
            }
        });

        //监听提交
        form.on('submit(demo1)', function(data){

            $.ajax({
                type: "post",
                url: baseUrl + "/Admin/ajaxUpdateAdminInfo",
                data: data.field,
                dataType: "json",
                success: function(data)
                {
                    if(data.code == 1)
                    {
                        form.render();
                    }
                    layer.msg(data.msg);
                }
            });
            return false;
        });

        $('#updatePassBtn').click(function () {
            $('#updatePass').hide();
            $('#updatePassDiv').show();
        });

    });
});