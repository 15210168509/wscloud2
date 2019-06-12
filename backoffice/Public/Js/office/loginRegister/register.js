$(function () {

    var baseUrl = $('#baseUrl').val() + '/';

    // 自定义规则
    jQuery.validator.addMethod("checkString", function(value, element){ // 员工姓名
        var tel = /^[\u4e00-\u9fa5]+$/;
        return this.optional(element) || (tel.test(value));
    },'');

    jQuery.validator.addMethod("checkPhone", function(value, element){ // 手机号码
        var tel = /^[1][3|4|5|7|8][\d]{9}$/;
        return this.optional(element) || (tel.test(value));
    },'');

    // 表单验证
    $('#create_form').validate({
        onfocusout: false,
        focusInvalid: false,
        focusCleanup: true,
        // 验证规则
        rules:{
            registerCompanyName:{
                required: true
            },
            registerCompanyContactPhone:{
                required: true,
                checkPhone:true
            },
            registerAuthCode:{
                required: true,
                maxlength: 4,
                remote: {
                    url: baseUrl+'ManagementLogin/ajaxValidateMobileVerificationCode',
                    type: "get",
                    dataType: "json",
                    data: {
                        phone: function() {
                            return $("#registerCompanyContactPhone").val();
                        },
                        verificationCode : function() {
                            return $('#registerAuthCode').val();
                        }
                    }
                }
            }
        },
        // 验证信息
        messages:{
            registerCompanyName:{
                required: "公司名称不能为空"
            },
            registerCompanyContactPhone:{
                required: "手机号码不能为空",
                checkPhone: "手机号码格式不正确"
            },
            registerAuthCode:{
                required: "验证码不能为空",
                maxlength: "验证码应为4位",
                remote: "验证码错误"
            }
        },
        errorPlacement: function(error, element) {
            var msgTest = $('#registerTest');
            var msg = error.text();
            if (msg != '') {
                msgTest.text('');
                msgTest.text(msg);
            }
            element.css('border-color', 'rgb(224, 90, 90)');
        },
        success: function(label, element) {
            $(element).css('border-color', '#d5d5d5');
            if ($('.error').length == 0) {
                $('#registerTest').text('');
            }
        },
        submitHandler: function(){

            var email = $('#registerCompanyContactEmail').val();
            if (!(/^(\w-*\.*)+@(\w-?)+(\.\w{2,})+$/.test(email))){
                $('#registerTest').text('邮箱格式不正确');
                return false;
            }

            $('#registerTest').text('');
            create({
                registerCompanyName : $('#registerCompanyName').val(),
                registerCompanyContactPhone : $('#registerCompanyContactPhone').val(),
                email : $('#registerCompanyContactEmail').val()
            });
            return false;
        }
    });

    $('#btnRegisterAuthCode').click(function (e) {
        var phone = $('#registerCompanyContactPhone').val();

        if (phone != '') {
            if(!(/^[1][3|4|5|7|8][\d]{9}$/g.test(phone))){
                $('#registerTest').text('手机号码非法');
            } else {
                $('#registerTest').text('');
                setAuthCode(this, phone);
            }
        } else {
            $('#registerTest').text('手机号码不能为空');
        }
    });

    function setAuthCode (that, phone) {
        $(that).attr('disabled', true);

        sendAuthCode(phone);

        var btnTextTem = $(that).html();
        $(that).html('<span id="authCodeTime">120</span>s');
        setAuthTime($('#authCodeTime'), 120);

        function setAuthTime(obj, count) {
            window.setTimeout(function(){
                count--;
                if(count > 0) {
                    obj.text(count);
                    setAuthTime(obj, count);
                } else {
                    $(that).html(btnTextTem);
                    $(that).attr('disabled', false);
                }
            }, 1000);
        }
    }
    
    function sendAuthCode(phone) {
        $.ajax({
            url: baseUrl+'ManagementLogin/ajaxSendMobileVerificationCode',
            method:'get',
            dataType:'json',
            data: {phone:phone},
            success:function(data){
                if(data.code==1){
                }else{
                    $('#registerTest').text('验证码发送失败');
                }
            },
            error:function(){

            }
        });
    }

    var create = function (data)
    {
        $('#create_bt').attr('disabled', true);
        $.ajax({
            type: "post",
            url: baseUrl + "ManagementLogin/registerCompany",
            data: data,
            dataType: "json",
            success: function(data)
            {
                if(data.code == 1)
                {
                    $('#registerSuccessBox').show();
                    $('#create_bt').attr('disabled', false);
                }
                else
                {
                    $('#registerTest').text(data.msg);
                    $('#create_bt').attr('disabled', false);
                }
                $('#create_bt').attr('disabled', false);
            }
        });
    };

});