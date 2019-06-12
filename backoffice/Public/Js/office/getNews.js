/**
 * Created by dev on 2017/3/10.
 */
$(function () {

    layui.use(['layer'],function () {

        var layer = layui.layer;

        var topic = $('#adminTopic').val();
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
            if($('#msgContainer').hasClass('msgContainer')){
                $('#msgContainer').removeClass('msgContainer');
            }
            $('#msgContainer').addClass('msgContainer');

            var msgObj = JSON.parse(message.payloadString);

            if (msgObj.msgType == 20){
                $('#waringNum').html(parseInt($('#waringNum').html())+1);

                //是否报警
                if ($('#warningDialog').val() == 1 ){
                    warningPop(msgObj.data);
                }

            } else {
                $('#msgNum').html(parseInt($('#msgNum').html())+1);
            }

        }

        function onConnectionLost(responseObject) {
            if (responseObject.errorCode !== 0) {
                console.log(client.clientId + ": " + responseObject.errorCode + "\n");
            }
        }


        /**********************layui 弹出窗口*************************/

        function warningPop(data) {

            layer.open({
                type: 1
                ,title: '预警信息'
                ,id:'warming'
                ,offset: 'rb'                    //具体配置参考：http://www.layui.com/doc/modules/layer.html#offset
                ,content: $('.warning-pop')
                ,shade: 0                           //不显示遮罩
            });

            var warmingStr = '<li>'+
            '<div class="layim-chat-text" style="background-color: '+data.level_color+'">'+
            '<a href="/Driver/behaviorLists">'+
            '司机姓名：'+data.driver_name+'<br/>'+
            '驾驶车辆：'+data.vehicle_no+'<br/>'+/*
            '行为类型：'+data.type_text+'<br/>'+*/
            '驾驶行为：'+data.code_text+'<br/>'+/*
            '行为级别：'+data.level_text+'<br/>'+*/
            '当时车速：'+data.kmh+'km/h<br/>'+
            '当时位置：'+data.location+'<br/>'+
            '定位时间：'+data.time+'<br/>'+
            '</a>'+
            '</div>'+
            '</li>';

            if ($('#chat-main li').length > 9 ) {
                $('#chat-main li:last').remove();
            }
            $('#chat-main').prepend(warmingStr);

        }

    });

});

function changeMsgCount(num){
    $('#msgNum').html(parseInt($('#msgNum').html())-num);
}


