/**
 * Created by dev on 2018/5/15.
 */
$(function () {
    var baseUrl = $('#baseUrl').val();
    layui.use(['form','layer','autocomplete'], function(){

        var baseUrl = $('#baseUrl').val();

        var form = layui.form
            ,layer = layui.layer;
        var autocomplete = layui.autocomplete;
        //监听提交
        form.on('submit(demo1)', function(data){
            var postData = data.field;
            postData.device_id = $('#device_id').val();
            var add_btn = $('#add_btn');
            add_btn.addClass("layui-btn-disabled");
            $.ajax({
                type: "post",
                url: baseUrl + "/Vehicle/ajaxAddVehicle",
                data: postData,
                dataType: "json",
                success: function(data)
                {
                    add_btn.removeClass("layui-btn-disabled");
                    if(data.code == 1)
                    {
                        $("input[name='vehicle_no']").val("");
                        $("input[name='model']").val("");
                        $("input[name='serial_no']").val("");
                        $('#device_id').val('');
                    }
                    layer.msg(data.msg);
                }
            });
            return false;
        });

        autocomplete.render({
            elem: $('#serial_no'),
            url: "searchDevice",
            cache: false,
            template_val: '{{d.serial_no}}',
            template_txt: '{{d.serial_no}}',
            onselect: function (resp) {
                //得到设备id
                $('#device_id').val(resp.id);
            }
        });

        $('#serial_no').bind('input propertychange', function() {
            $('#device_id').val('');
        });

    });
});