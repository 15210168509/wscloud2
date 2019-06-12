/**
 * Created by dev on 2018/5/28.
 */
$(function () {
    layui.use(['form', 'layedit','layer'], function(){

        var baseUrl = $('#baseUrl').val();

        var form = layui.form
            ,layer = layui.layer
            ,layedit = layui.layedit;

        //自定义验证规则
        form.verify({
            name:function (value) {
                if (value.length == 0) {
                    return '请输入公司名称';
                }
            }
        });

        //监听提交
        form.on('submit(demo1)', function(data){
            $.ajax({
                type: "post",
                url: baseUrl + "/Admin/saveCompanyInfo",
                data: data.field,
                dataType: "json",
                success: function(data)
                {
                    if(data.code == 1)
                    {
                        //form.render();
                        $('.hidebtn').css('display','none');
                    }
                    layer.msg(data.msg);

                }
            });
            return false;
        });

    });
});