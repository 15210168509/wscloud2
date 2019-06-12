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

            $('#msgNum').html(parseInt($('#msgNum').html())+1);

        }

        function onConnectionLost(responseObject) {
            if (responseObject.errorCode !== 0) {
                console.log(client.clientId + ": " + responseObject.errorCode + "\n");
            }
        }


        /**********************layui 弹出窗口*************************/
        function warningPop(data) {

            var  tem = '<a href="/Driver/behaviorLists"><table class="layui-table">';
            tem += '<tbody class="no-border-x no-border-y">';
            tem += '<tr><td style="width:30%;min-width: 70px;"><span>司机姓名</span></td><td>'+data.driver_name+'</td></tr>';
            tem += '<tr><td style="width:30%"><span>行为类型</span></td><td>'+data.type_text+'</td></tr>';
            tem += '<tr><td style="width:30%;"><span>具体行为</span></td><td>'+data.code_text+'</td></tr>';
            tem += '<tr><td style="width:30%;"><span>行为级别</span></td><td>'+data.level_text+'</td></tr>';
            tem += '<tr><td style="width:30%;"><span>当时车速</span></td><td>'+data.kmh+'km/h</td></tr>';
            tem += '<tr><td style="width:30%;"><span>当时位置</span></td><td>'+data.location+'</td></tr>';
            tem += '<tr><td style="width:30%;"><span>定位时间</span></td><td>'+data.time+'</td></tr>';
            tem += '</tbody>';
            tem += '</table></a>';

            layer.open({
                type: 1
                ,title: '预警信息'
                ,offset: 'rb'                    //具体配置参考：http://www.layui.com/doc/modules/layer.html#offset
                ,content: tem
                ,shade: 0                           //不显示遮罩
            });
        }
    });

});

function changeMsgCount(num){
    $('#msgNum').html(parseInt($('#msgNum').html())-num);
}


