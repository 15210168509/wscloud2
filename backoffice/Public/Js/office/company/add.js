/**
 * Created by dev on 2018/6/27.
 */
$(function () {
    var baseUrl = $('#baseUrl').val();
    layui.use(['form','layer'],function () {
        var form = layui.form,
            layer = layui.layer;
        form.on('submit(demo1)', function(data){

            var email = $('#email').val();
            if (!(/^(\w-*\.*)+@(\w-?)+(\.\w{2,})+$/.test(email))){
                layer.msg('邮箱格式不正确');
                return false;
            }

            var add_btn = $('#add_btn');
            add_btn.addClass("layui-btn-disabled");
            $.ajax({
                type: "post",
                url: baseUrl + "/Company/ajaxAddCompany",
                data: data.field,
                dataType: "json",
                success: function(data)
                {
                    add_btn.removeClass("layui-btn-disabled");
                    if(data.code == 1)
                    {
                        $('.layui-input').val('');
                        //form.render();
                    }
                    layer.msg(data.msg);
                }
            });
            return false;
        });
    });
});