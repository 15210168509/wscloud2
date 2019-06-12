/*
bootbox.confirm({
    buttons: {  
        confirm:{  
            label: '确定',  
            className: "btn-sm btn-primary"
        },  
        cancel:{  
            label: '取消',  
            className: 'btn-sm btn-default'  
        }  
    }, message: "ffffffffffffffffffffff", callback: function(result){
        if(result){
        }
    }
});
bootbox.dialog({
    message: "ffffffffffffffffffffffff",
    buttons: {
        click: {
            label: "确定",
            className: "btn-sm btn-primary",
            callback: function(){}
        }
    }
});
*/
//地图
/*var g = new BMap.Geocoder(), l = new BMap.LocalCity(), province = $("#province"),
city = $("#city"), district = $("#district");
l.get(function(r){ 
    g.getLocation(r.center, function(res){
        province.val(res.addressComponents.province);
        city.val(res.addressComponents.city);
        district.val(res.addressComponents.district);
    });	
});*/
//登陆验证开始
var login_form = $("#login_form"), login_account = $("#login_account"), login_pass = $("#login_pass"), login_sub = $("#login_submit");
var regex = /^[1][3|5|7|8][\d]{9}$/, login_pass_test = $("#login_pass_test"), login_account_test = $("#login_account_test"), regexs = /^[a-zA-Z0-9]{5,50}$/;
var login_lock = false, login_keep = $("#login_keep"), login_test = $("#login_test");
var checkObj = function(obj){
    for(var key in obj)
        if(obj[key] === false) 
            return false;
    return true;
};
var login_accountTest = function(){
    var va = $.trim(login_account.val());
    if(va.length < 1){
        login_account.css("border-color", "#E05A5A");
        //login_account_test.text("登陆账号或密码不能为空");
        return false;
    }
    else{
        login_account.css("border-color", "#d5d5d5");
        //login_account_test.text(" ");
        return va;
    }
};
var login_passTest = function(){
    var va = $.trim(login_pass.val());
    if(va.length < 1){
        login_pass.css("border-color", "#E05A5A");
        //login_pass_test.text("登陆账号或密码不能为空");
        return false;
    }
    else{
        login_pass.css("border-color", "#d5d5d5");
        //login_pass_test.text(" ");
        return va;
    }
};
var login_check = function(){
    if(login_lock) return;
    login_lock = true;
    var bo = {
        login_account: login_accountTest(),
        login_pass: login_passTest()
    }
    login_test.html("");
    if(checkObj(bo)){
        bo.login_keep = login_keep.prop("checked");
        $('#login_submit').attr('disabled', true);
        $('#login_font').text('正在登录...');

        $.ajax({
            type: "post",
            url: "/Office/ManagementLogin/loginCheck",
            data: bo,
            dataType: "Json",
            error: function(data){
                login_lock = false;
            },
            success: function(data){
                if(data.res){
                   window.location = "/Office/Index/index";
                }

                else{
                    // $('#login_account').val('');
                    $('#login_pass').val('');
                    $('#login_submit').attr('disabled', false);
                    $('#login_font').text('登录');
                    switch(data.code){
                        case "account":
                            login_account.css("border-color", "#aa0000");
                            login_test.html(data.msg);
                            break;
                        case "pass":
                            login_pass.css("border-color", "#aa0000");
                            login_test.html(data.msg);
                            break;
                        default:
                            login_test.html(data.msg);
                            break;
                    }
                }
                login_lock = false;
            }
        });
    }
    else{
        login_test.html("用户名或密码不能为空");
        login_lock = false;
    } 
    return false;
};
login_account.blur(login_accountTest);
login_pass.blur(login_passTest);
login_sub.click(login_check);
$(document).keyup(function(event){
    if(event.keyCode ==13){
        login_check();
    }
});
//找回密码验证开始
var CODE = false;
var retrieve_form = $("#retrieve_form"), retrieve_mobile = $("#retrieve_mobile"), retrieve_sub = $("#retrieve_submit");
var mobile_res = $("#retrieve_mobile_res"), retrieve_code_bt = $("#retrieve_code_bt"), retrieve_code = $("#retrieve_code");
var retrieve_pass = $("#retrieve_pass"), retrieve_pass_repeat = $("#retrieve_pass_repeat"); //, retrieve_test = $("#retrieve_test");
var retrieve_mobile_test = $("#retrieve_mobile_test"), retrieve_code_test = $("#retrieve_code_test"), retrieve_pass_test = $("#retrieve_pass_test"),
    retrieve_pass_repeat_test = $("#retrieve_pass_repeat_test");
var revertCode = function(type){
    if(CODE) return;
    CODE = true;
    var va = type == "retrieve_code_bt" ? retrieve_mobileTest() : register_mobileTest();
    if(va === false){
        CODE = false;
        return;
    } 
    $.ajax({
        type: "post",
        url: "/login/getrevertcode/type/" + (type == "retrieve_code_bt" ? "setpass" : "register"),
        data: {mobile: va},
        dataType: "Json",
        success: function(data){
            if(data.res){
                retrieve_code_bt.text("（60秒）");
                register_code_bt.text("（60秒）");
                unlockRevertCode(61);
            }
            else{
                alert("发送验证码失败：" + data.msg);
                CODE = false;
            }
        }
    });
};
var unlockRevertCode = function(sode){
    retrieve_code_bt.text("（" + --sode + "秒）");
    register_code_bt.text("（" + --sode + "秒）");
    if(sode > 0) setTimeout(function(){
        unlockRevertCode(sode);
    }, 1000);
    else{
        retrieve_code_bt.html("<i class=\"icon-lightbulb\"></i>获取验证码");
        register_code_bt.html("<i class=\"icon-lightbulb\"></i>获取验证码");
        CODE = false;
    }
};
var retrieve_mobileTest = function(){
    var va = $.trim(retrieve_mobile.val());
    if(va.length < 1){
        retrieve_mobile.css("border-color", "#aa0000");
        retrieve_mobile_test.text("手机号不能为空");
        return false;
    }
    if(!regex.test(va)){
        retrieve_mobile.css("border-color", "#aa0000");
        retrieve_mobile_test.text("手机号格式不正确");
        return false;
    }
    retrieve_mobile.css("border-color", "#317ecc");
    retrieve_mobile_test.text("");
    return va;
};
var retrieve_codeTest = function(){
    var va = $.trim(retrieve_code.val());
    if(va.length < 1){
        retrieve_code.css("border-color", "#aa0000");
        retrieve_code_test.text("验证码不能为空");
        return false;
    }
    /*
    if(va.length != 6){
        retrieve_code.css("border-color", "#aa0000");
        retrieve_code_test.text("验证码为6位");
        return false;
    }
    */
    if(!/^[a-zA-Z0-9]+$/.test(va)){
        retrieve_code.css("border-color", "#aa0000");
        retrieve_code_test.text("验证码只能填写为字母或数字");
        return false;
    }
    retrieve_code.css("border-color", "#317ecc");
    retrieve_code_test.text("");
    return va;
};
var retrieve_passTest = function(){
    var va = $.trim(retrieve_pass.val());
    var _va = $.trim(retrieve_pass_repeat.val());
    if(va.length < 1){
        retrieve_pass.css("border-color", "#aa0000");
        retrieve_pass_test.text("新密码不能为空");
        return false;
    }
    if(va.length < 6){
        retrieve_pass.css("border-color", "#aa0000");
        retrieve_pass_test.text("密码不能小于6位");
        return false;
    }
    if(va.length > 12){
        retrieve_pass.css("border-color", "#aa0000");
        retrieve_pass_test.text("密码不能大于位");
        return false;
    }
    if(!/^[a-zA-Z0-9\-_]+$/.test(va)){
        retrieve_pass.css("border-color", "#aa0000");
        retrieve_pass_test.text("密码只能由数字、字母或下划线组成");
        return false;
    }
    if(_va.length > 0 && va != _va){
        retrieve_pass.css("border-color", "#317ecc");
        retrieve_pass_test.text("");
        retrieve_pass_repeat.css("border-color", "#aa0000");
        retrieve_pass_repeat_test.text("密码不一致");
        return false;
    }
    retrieve_pass.css("border-color", "#317ecc");
    retrieve_pass_test.text("");
    retrieve_pass_repeat.css("border-color", "#317ecc");
    retrieve_pass_repeat_test.text("");
    return _va.length > 0 ? retrieve_pass_repeatTest() : va;
};
var retrieve_pass_repeatTest = function(){
    var va = $.trim(retrieve_pass.val());
    var _va = $.trim(retrieve_pass_repeat.val());
    if(_va.length < 1){
        retrieve_pass_repeat.css("border-color", "#aa0000");
        retrieve_pass_repeat_test.text("确认密码不能为空");
        return false;
    }
    if(_va.length < 6){
        retrieve_pass_repeat.css("border-color", "#aa0000");
        retrieve_pass_repeat_test.text("确认密码不能小于6位");
        return false;
    }
    if(_va.length > 12){
        retrieve_pass_repeat.css("border-color", "#aa0000");
        retrieve_pass_repeat_test.text("确认密码不能大于位");
        return false;
    }
    if(!/^[a-zA-Z0-9\-_]+$/.test(_va)){
        retrieve_pass_repeat.css("border-color", "#aa0000");
        retrieve_pass_repeat_test.text("确认密码只能由数字、字母或下划线组成");
        return false;
    }
    if(va != _va){
        retrieve_pass_repeat.css("border-color", "#aa0000");
        retrieve_pass_repeat_test.text("密码不一致");
        return false;
    }
    retrieve_pass.css("border-color", "#317ecc");
    retrieve_pass_test.text("");
    retrieve_pass_repeat.css("border-color", "#317ecc");
    retrieve_pass_repeat_test.text("");
    return va;
};
var retrieve_check = function(){
    var bo = {
        mobile: retrieve_mobileTest(),
        revert: retrieve_codeTest(),
        pass: retrieve_passTest(),
        pass_repeat: retrieve_pass_repeatTest()
    };
    if(checkObj(bo)){
        $.ajax({
            type: "post",
            url: "/login/resetpassWord",
            data: bo,
            dataType: "Json",
            success: function(data){
                if(data.res){
                    window.location='/login';
                }
                else{
                    switch(data.code){
                        case "mobile": 
                            retrieve_mobile.css("border-color", "#aa0000");
                            retrieve_mobile_test.text("");
                            break;
                        case "revert": 
                            retrieve_code.css("border-color", "#aa0000");
                            retrieve_code_test.text("");
                            break;
                        case "pass": 
                            retrieve_pass.css("border-color", "#aa0000");
                            retrieve_pass_repeat_test.text("");
                            break;
                        default:

                            retrieve_pass_repeat_test.text(data.msg);
                            break;
                    }
                }
            }
        });
    }
};
retrieve_code_bt.click(function(){ revertCode("retrieve_code_bt"); });
//retrieve_form.submit(retrieve_check);
retrieve_mobile.blur(retrieve_mobileTest);
retrieve_code.blur(retrieve_codeTest);
retrieve_pass.blur(retrieve_passTest);
retrieve_pass_repeat.blur(retrieve_pass_repeatTest);
retrieve_sub.click(retrieve_check);
//注册验证开始
var register_form = $("#register_form"), register_mobile = $("#register_mobile"), register_account = $("#register_account"), register_accounts = $("#register_accounts");
var register_pass = $("#register_pass"), register_pass_repeat = $("#register_pass_repeat"), register_agree = $("#register_agree");
var register_sub = $("#register_submit"), register_code_bt = $("#register_code_bt"), register_agree_test = $("#register_agree_test");
var register_mobile_test = $("#register_mobile_test"), register_code_test = $("#register_code_test"), register_pass_test = $("#register_pass_test"),
    register_pass_repeat_test = $("#register_pass_repeat_test"), register_accounts_test = $("#register_accounts_test");
var registerCode = function(){
    if(register_code_bt.attr("locked")) return;
    register_code_bt.attr("locked", "locked");
    var va = register_mobileTest();
    if(va === false){
        register_code_bt.removeAttr("locked");
        return;
    } 
    $.ajax({
        type: "post",
        url: "/login/getrevertcode",
        data: {mobile: va},
        dataType: "Json",
        success: function(data){
            if(data.res){
                register_code_bt.text("（60秒）");
                unlockRegisterCode(61);
            }
            else{
                alert("发送验证码失败：" + data.msg);
                register_code_bt.removeAttr("locked");
            }
        }
    });
};
var unlockRegisterCode = function(sode){
    register_code_bt.text("（" + --sode + "秒）");
    if(sode > 0) setTimeout(function(){
        unlockRegisterCode(sode);
    }, 1000);
    else{
        register_code_bt.html("<i class=\"icon-lightbulb\"></i>获取验证码");
        register_code_bt.removeAttr("locked");
    }
};
var register_accountsTest = function(){
    var va = $.trim(register_accounts.val());
    if(va.length < 1){
        register_accounts.css("border-color", "#aa0000");
        register_accounts_test.text("账号不能为空");
        return false;
    }
    if(!regexs.test(va)){
        register_accounts.css("border-color", "#aa0000");
        register_accounts_test.text("账号由字母+数字组成，不能少于5位");
        return false;
    }
    register_accounts.css("border-color", "#317ecc");
    register_accounts_test.text("");
    return va;
};
var register_mobileTest = function(){
    var va = $.trim(register_mobile.val());
    if(va.length < 1){
        register_mobile.css("border-color", "#aa0000");
        register_mobile_test.text("手机号码不能为空");
        return false;
    }
    if(!regex.test(va)){
        register_mobile.css("border-color", "#aa0000");
        register_mobile_test.text("手机号码格式不正确");
        return false;
    }
    register_mobile.css("border-color", "#317ecc");
    register_mobile_test.text("");
    return va;
};
var register_accountTest = function(){
    var va = $.trim(register_account.val());
    if(va.length < 1){
        register_account.css("border-color", "#aa0000");
        register_code_test.text("验证码不能为空");
        return false;
    }
    /*
    if(va.length != 6){
        register_account.css("border-color", "#aa0000");
        register_code_test.text("验证码为6位");
        return false;
    }
    */
    if(!/^[a-zA-Z0-9]+$/.test(va)){
        register_account.css("border-color", "#aa0000");
        register_code_test.text("验证码只能填写为字母或数字");
        return false;
    }
    register_account.css("border-color", "#317ecc");
    register_code_test.text("");
    return va;
};
var register_passTest = function(){
    var va = $.trim(register_pass.val());
    var _va = $.trim(register_pass_repeat.val());
    if(va.length < 1){
        register_pass.css("border-color", "#aa0000");
        register_pass_test.text("密码不能为空");
        return false;
    }
    if(va.length < 6){
        register_pass.css("border-color", "#aa0000");
        register_pass_test.text("密码不能小于6位");
        return false;
    }
    if(va.length > 12){
        register_pass.css("border-color", "#aa0000");
        register_pass_test.text("密码不能大于12位");
        return false;
    }
    if(!/^[a-zA-Z0-9\-_]+$/.test(va)){
        register_pass.css("border-color", "#aa0000");
        register_pass_test.text("密码只能由数字、字母或下划线组成");
        return false;
    }
    if(_va.length > 0 && va != _va){
        register_pass_repeat.css("border-color", "#aa0000");
        register_pass_repeat_test.text("密码不一致");
        return false;
    }
    register_pass.css("border-color", "#317ecc");
    register_pass_test.text("");
    register_pass_repeat.css("border-color", "#317ecc");
    register_pass_repeat_test.text("");
    return _va.length > 0 ? register_pass_repeatTest() : va;
};
var register_pass_repeatTest = function(){
    var va = $.trim(register_pass.val());
    var _va = $.trim(register_pass_repeat.val());
    if(_va.length < 1){
        register_pass_repeat.css("border-color", "#aa0000");
        register_pass_repeat_test.text("重复密码不能为空");
        return false;
    }
    if(_va.length < 6){
        register_pass_repeat.css("border-color", "#aa0000");
        register_pass_repeat_test.text("密码不能小于6位");
        return false;
    }
    if(_va.length > 12){
        register_pass_repeat.css("border-color", "#aa0000");
        register_pass_repeat_test.text("密码不能大于12位");
        return false;
    }
    if(!/^[a-zA-Z0-9\-_]+$/.test(_va)){
        register_pass_repeat.css("border-color", "#aa0000");
        register_pass_repeat_test.text("密码只能由数字、字母或下划线组成");
        return false;
    }
    if(va != _va){
        register_pass_repeat.css("border-color", "#aa0000");
        register_pass_repeat_test.text("密码不一致");
        return false;
    }
    register_pass.css("border-color", "#317ecc");
    register_pass_test.text("");
    register_pass_repeat.css("border-color", "#317ecc");
    register_pass_repeat_test.text("");
    return va;
};
var register_agreeTest = function(){
    var va = register_agree.prop("checked");
    if(!va){
        register_agree.css("border-color", "#aa0000");
        register_agree_test.html("请勾选");
        return false;
    }else{
        register_agree.css("border-color", "#317ecc");
        register_agree_test.html("");
        return va;
    }
};
var register_check = function(){
    if(register_sub.attr("lock") == "lock") return false;
    register_sub.attr("lock", "lock");
    var bo = {
        accounts: register_accountsTest(),
        mobile: register_mobileTest(),
        code: register_accountTest(),
        pass: register_passTest(),
        pass_repeate: register_pass_repeatTest(),
        auto: register_agreeTest(),
        province: $.trim(province.val()),
        city: $.trim(city.val()),
        district: $.trim(district.val())
    }
    if(checkObj(bo)){
        $.ajax({
            type: "post",
            url: "login/registerCheck",
            data: bo,
            dataType: "json",
            success: function(data){
                if(data.res){
                    alert("注册成功！");
                    window.location = "/login";
                }
                register_agree_test.html(data.msg);
                register_sub.removeAttr("lock");
            }
        });
    }
    else register_sub.removeAttr("lock");
    return false;
};
register_accounts.blur(register_accountsTest);
register_mobile.blur(register_mobileTest);
register_account.blur(register_accountTest);
register_pass.blur(register_passTest);
register_pass_repeat.blur(register_pass_repeatTest);
register_form.submit(register_check);
register_code_bt.click(function(){ revertCode("register_code_bt"); });
register_sub.click(function(){ register_form.submit(); });