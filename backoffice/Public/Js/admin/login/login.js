
//登陆验证开始
var login_account = $("#login_account"), login_pass = $("#login_pass"), login_sub = $("#login_submit");
var login_lock = false, login_keep = $("#login_keep"), login_test = $("#login_test");
var baseUrl = $('#baseUrl').val();
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
        return false;
    }
    else{
        login_account.css("border-color", "#d5d5d5");
        return va;
    }
};
var login_passTest = function(){
    var va = $.trim(login_pass.val());
    if(va.length < 1){
        login_pass.css("border-color", "#E05A5A");
        return false;
    }
    else{
        login_pass.css("border-color", "#d5d5d5");
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
            url: baseUrl+"/Login/loginCheck",
            data: bo,
            dataType: "Json",
            error: function(data){
                login_lock = false;
            },
            success: function(data){
                if(data.res){
                   window.location = baseUrl + "/Index/index";
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