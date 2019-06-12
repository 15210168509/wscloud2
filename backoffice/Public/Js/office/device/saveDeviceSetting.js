/**
 * Created by dev on 2018/11/23.
 */
$(function () {
    var baseUrl = $('#baseUrl').val();
    layui.use(['form','layer'], function(){
        /////////////////////////////////////
        var topic = $('#deviceTopic').val();
        console.log($('#deviceTopic').val());
        var host = $('#mqttServer').val();
        var host_port = $('#mqttServerPort').val();
        var client = new Messaging.Client(host, Number(host_port), "admin-"+(Math.floor(Math.random() * 100000)));
        client.onConnect = onConnect;

        client.onMessageArrived = onMessageArrived;
        client.onConnectionLost = onConnectionLost;

        client.connect({
            onSuccess:onConnect,
            onFailure:onFailure
        });

        function onConnect(frame) {
            client.subscribe(topic);
        };

        function onFailure(failure) {
            console.log(failure.errorMessage);
        }
        function onMessageArrived(message){

            var msgObj = JSON.parse(message.payloadString);
            //展示设备信息
            console.log(msgObj);
            if (msgObj.code == 1) {
                $("input[name='60']").val(msgObj.data[60].value);
                $("input[name='80']").val(msgObj.data[80].value);
                $("input[name='90']").val(msgObj.data[90].value);

            } else {

            }

        }

        function onConnectionLost(responseObject) {
            if (responseObject.errorCode !== 0) {
                console.log(client.clientId + ": " + responseObject.errorCode + "\n");
            }
        }
        $.ajax({
            type: "post",
            url: baseUrl + "/Device/getDeviceSettingInfo",
            data: {deviceType:$('#deviceType').val(),serialNo:$('#serialNo').val(),topic:topic},
            dataType: "json",
            success: function(data)
            {

            }
        });
        /////////////////////////////////////
        var form = layui.form;
        var layer = layui.layer;
        form.verify({
            60: function(value, item){ //value：表单的值、item：表单的DOM对象
                if(!/^(-[1-9][0-9]*)$/.test(value)){
                    return '请输入-30— -10的整数';
                }
                if(Math.abs(value)<10 || Math.abs(value)>30){
                    return '请输入-30— -10的整数';
                }
            },
            80: function(value, item){ //value：表单的值、item：表单的DOM对象
                if(!/^(-[1-9][0-9]*)$/.test(value)){
                    return '请输入-35— -15的整数';
                }
                if(Math.abs(value)<15 || Math.abs(value)>35){
                    return '请输入-35— -15的整数';
                }
            },
            90: function(value, item){ //value：表单的值、item：表单的DOM对象
                if(!/^[1-9]\d*$/.test(value)){
                    return '请输入15—35的整数';
                }
                if(value<15 || value>35){
                    return '请输入15—35的整数';
                }
            },

        });
        form.on('submit(submit)', function(data){

            var postData = {
                serialNo:$('#serialNo').val(),
                data:{
                    type:20,
                    60:$("input[name='60']").val(),
                    80:$("input[name='80']").val(),
                    90:$("input[name='90']").val(),
                }
            };

            $.ajax({
                type: "post",
                url: baseUrl + "/Device/ajaxSaveDeviceSetting",
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