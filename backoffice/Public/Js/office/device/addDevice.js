/**
 * Created by dev on 2018/5/15.
 */
$(function () {
    var baseUrl = $('#baseUrl').val();
    layui.use(['form','layer','autocomplete'], function(){

        var baseUrl = $('#baseUrl').val();

        var form = layui.form
            ,layer = layui.layer;

        //监听提交
        form.on('submit(demo1)', function(data){
            var add_btn = $('#add_btn');
            add_btn.addClass("layui-btn-disabled");
            $.ajax({
                type: "post",
                url: baseUrl + "/Device/ajaxAddDevice",
                data: data.field,
                dataType: "json",
                success: function(data)
                {
                    add_btn.removeClass("layui-btn-disabled");
                    if(data.code == 1)
                    {
                        $("input[name='name']").val("");
                        $("input[name='serial_no']").val("");
                        $("input[name='model']").val("");
                        $("input[name='sim_no']").val("");
                    }
                    layer.msg(data.msg);
                }
            });
            return false;
        });

    });
});