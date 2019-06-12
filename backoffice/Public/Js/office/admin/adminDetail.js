/**
 * Created by dev on 2018/5/14.
 */
$(function () {
    layui.use(['form', 'layedit','layer'], function(){

        var baseUrl = $('#baseUrl').val();

        $.ajax({
            type: "post",
            url: baseUrl+"/Admin/adminFind/adminId/"+($('#adminId').val()),
            dataType: "json",
            success: function (data) {
                if(data.code == 1)
                {
                    $('#adminId').val(data.data.id);
                    $('#name').val(data.data.name);
                    $('#phone').val(data.data.phone);
                    $('#account').val(data.data.account);
                    $('#email').val(data.data.email);
                    $('#status_'+data.data.status).prop("selected", true);
                    var obj = $("#rightHtml input[type='checkbox']");
                    for (var i=0; i<obj.length; i++) {
                        if (($.inArray($(obj[i]).val(), data.data.right)) != -1) {
                            var right = $(obj[i]).attr('right'); // 当前权限
                            var father = $($(obj[i]).parent('div')); // 当前层级对象
                            father.css('display', 'block');
                            $(obj[i]).prop('checked', true);
                            var fatherDepth = $($(obj[i]).parent('div')).attr('depth'); // 当前层级
                            var children = $('#r_'+(parseInt(fatherDepth)+1)+'_'+right);
                            if (children.length > 0) {
                                children.css('display', 'block');
                            }
                        }
                    }
                }
                else
                {

                }
            }
        });

        $('body').on('click', '[name="right"]', function () {
            var right = $(this).attr('right'); // 当前权限
            var father = $($(this).parent('div')).attr('depth'); // 当前层级

            var children = $('#r_'+(parseInt(father)+1)+'_'+right);
            if (children.length > 0) {
                var ob = $(this);
                if ($(this).is(":checked")) {
                    children.slideDown();
                } else {
                    var flg = false; // 子级权限无选中
                    var childrenInput = children.children('input');
                    childrenInput.each(function (i) {
                        if ($(childrenInput[i]).is(":checked")) {
                            flg = true; // 子级权限有选中
                            ob.prop('checked', true);
                            return false;
                        }
                    });

                    if (!flg) {
                        children.slideUp();
                    }
                }
            }
        });

        var form = layui.form
            ,layer = layui.layer
            ,layedit = layui.layedit;

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
            ,pass: [/(.+){6,12}$/, '密码必须6到12位']
            ,verify_pass: [/(.+){6,12}$/, '确认密码不匹配']
        });

        //监听提交
        form.on('submit(demo1)', function(data){
            var obj = $("#rightHtml input[type='checkbox']");
            var i = 0;
            var arr = [];
            for (i=0; i<obj.length; i++) {
                if ($(obj[i]).prop('checked')) {
                    arr.push($(obj[i]).val());
                }
            }
            data.field['right'] = arr;
            data.field['id'] = $('#adminId').val();
            $.ajax({
                type: "post",
                url: baseUrl + "/Admin/ajaxEditAdmin",
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

        //权限
        $('#addRight').click(function () {

            layer.open({
                title:['权限'],
                type:1,
                skin:'layui-layer-lan',
                content: $('#rightHtml')
                ,btn: ['保存', '全选', '重置']
                ,yes: function(index, layero){
                    //按钮【按钮一】的回调
                    layer.close(index);
                }
                ,btn2: function(index, layero){
                    //按钮【按钮二】的回调
                    var radio = $("#rightHtml input[type='checkbox']");
                    radio.each(function(i){
                        $(radio[i]).prop('checked', true);
                    });
                    // 打开所有权限
                    var radio = $("#r_1_0");
                    var radio_box =radio.find('div');
                    radio_box.each(function (i) {
                        $(radio_box[i]).css('display', 'block');
                    });
                    return false
                }
                ,btn3: function(index, layero){
                    //按钮【按钮三】的回调
                    var obj = $("#rightHtml input[type='checkbox']");
                    var i = 0;
                    for (i=0; i<obj.length; i++) {
                        if ($(obj[i]).prop('checked')) {
                            $(obj[i]).prop('checked', false);
                        }
                    }
                    // 关闭所有权限
                    var radio = $("#r_1_0");
                    var radio_box =radio.find('div');
                    radio_box.each(function (i) {
                        $(radio_box[i]).css('display', 'none');
                    });
                    return false
                }
                ,cancel: function(){
                    //右上角关闭回调

                    //return false 开启该代码可禁止点击该按钮关闭
                }
            });
            $('.layui-layer-shade').css('display','none');
        });
    //重置密码
        $('#resetPassword').click(function () {
            layer.confirm('确定重置该管理员的密码吗？', {
                btn: ['确定', '取消']
            }, function(index, layero){
                $.ajax({
                    type: "get",
                    url: baseUrl + "/Admin/resetAdminPWD",
                    data: {adminId:$('#adminId').val()},
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
            }, function(index){

            });
        })

    });
});