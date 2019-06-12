/**
 * Created by dev on 2018/5/15.
 */

layui.use(['form','layer','autocomplete'], function(){

    var baseUrl = $('#baseUrl').val();

    var form = layui.form
        ,layer = layui.layer
        ,autocomplete = layui.autocomplete;

    //监听提交
    form.on('submit(demo1)', function(data){

        $.ajax({
            type: "post",
            url: baseUrl + "/Groups/ajaxAddVehicle",
            data: data.field,
            dataType: "json",
            success: function(data)
            {
                if(data.code == 1)
                {
                    $("#vehicle_no").val("");
                }
                layer.msg(data.msg);
            }
        });
        return false;
    });

    autocomplete.render({
        elem: $('#vehicle_no'),
        url: baseUrl +"/Groups/searchVehicle",
        cache: false,
        template_val: '{{d.vehicle_no}}',
        template_txt: '{{d.vehicle_no}}',
        onselect: function (resp) {
            //得到设备id
            $('#vehicle_id').val(resp.id);
        }
    });

});