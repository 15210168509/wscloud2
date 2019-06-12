/**
 * Created by 01 on 2018/5/11.
 */

layui.use(['form', 'laydate','layer'], function(){

    var baseUrl = $('#baseUrl').val();

    var form = layui.form
        ,layer = layui.layer
        ,laydate = layui.laydate;

    //日期
    laydate.render({
        elem: '#certification_expire_time'
    });

    //自定义验证规则
    form.verify({
        name:function (value) {
            if (value.length == 0) {
                return '请输入分组姓名';
            } else {
                var reg = /^[A-Za-z0-9\u4e00-\u9fa5]+$/;
                if(!reg.test(value)){
                    return '分组只能为中文或字母或数字';
                }
            }
        }
    });

    //监听提交
    form.on('submit(demo1)', function(data){

        var add_btn = $('#add_btn');
        add_btn.addClass("layui-btn-disabled");
        add_btn.attr('disabled',true);

        $.ajax({
            type: "post",
            url: baseUrl + "/Groups/ajaxAddGroups",
            data: data.field,
            dataType: "json",
            success: function(data)
            {
                if(data.code == 1)
                {
                    $("#add_groups input").val("")
                }
                add_btn.removeClass("layui-btn-disabled");
                add_btn.attr('disabled',false);
                layer.msg(data.msg);
            }
        });
        return false;
    });

});