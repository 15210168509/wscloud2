/**
 * Created by dev on 2018/11/22.
 */
$(function () {
    var baseUrl = $('#baseUrl').val();
    layui.use(['form','layer'], function(){
        var form = layui.form;
        var layer = layui.layer;
        form.verify({
            70: function(value, item){ //value：表单的值、item：表单的DOM对象
                if(!/^[1-9]\d*\.[5]$|0\.[5]$|^[1-9]\d*$/.test(value)){
                    return '取值0.5-5，以0.5递增';
                }
                if(value<0.5 || value>5){
                    return '取值0.5-5，以0.5递增';
                }
            },
            100: function(value, item){ //value：表单的值、item：表单的DOM对象
                if(!/^[1-9]\d*\.[5]$|0\.[5]$|^[1-9]\d*$/.test(value)){
                    return '取值0.5-5，以0.5递增';
                }
                if(value<0.5 || value>5){
                    return '取值0.5-5，以0.5递增';
                }
            },
            110: function(value, item){ //value：表单的值、item：表单的DOM对象
                if(!/^([1-9]\d*|[0]{1,1})$/.test(value)){
                    return '请输入大于零的整数';
                }
            },

        });
        form.on('submit(submit)', function(data){

            var postData = {
                10:$("input[name='10']").is(":checked") == true? 0 : 1,
                20:$("input[name='20']").is(":checked") == true? 1 : 0,
                30:$("input[name='30']").is(":checked") == true? 1 : 0,
                40:$("input[name='40']").is(":checked") == true? 1 : 0,
                50:$("input[name='50']").is(":checked") == true? 1 : 0,
                //60:$("input[name='60']").val(),
                70:$("input[name='70']").val(),
                //80:$("input[name='80']").val(),
                //90:$("input[name='90']").val(),
                100:$("input[name='100']").val(),
                110:$("input[name='110']").val(),
                120:$("input[name='120']").val(),
                130:$("input[name='130']").val(),
                140:$("input[name='140']").val(),
                150:$("input[name='150']").val(),
                160:$("input[name='160']").val(),
                170:$("input[name='170']").val(),
                180:$("input[name='180']").val(),
                190:$("input[name='190']").val(),
                200:$("input[name='200']").is(":checked") == true? 1 : 0,
            };

            $.ajax({
                type: "post",
                url: baseUrl + "/Device/ajaxAddDeviceSetting",
                data: postData,
                dataType: "json",
                success: function(data)
                {
                    layer.msg(data.msg);
                }
            });
            return false;

        });
    })
});