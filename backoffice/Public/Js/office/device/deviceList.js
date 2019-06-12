/**
 * Created by dev on 2018/5/15.
 */
$(function () {
    var baseUrl = $('#baseUrl').val();
    var timeIndex;
    layui.use(['table','form','layer'], function(){
        var topic = $('#deviceTopic').val();
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
            var layer = layui.layer;
            var msgObj = JSON.parse(message.payloadString);
            //展示设备信息
            console.log(msgObj);
            layer.close(loadingIndex);
            clearTimeout(timeIndex);
            if (msgObj.code == 1) {
                var num = 1;
                var html = '<table class="layui-table">'+
                    '<thead>'+
                    '<tr>'+
                    '<th>配置</th>'+
                    '<th>值</th>'+
                    '<th>配置</th>'+
                    '<th>值</th>'+
                    '</tr>'+
                    '</thead>'+
                    '<tbody>';
                $.each(msgObj.data,function(index,value){
                    if (num %2 !=0) {
                        html+='<tr><td>'+value.name+'</td><td>'+value.valueStr+'</td>';
                    } else {
                        html+='<td>'+value.name+'</td><td>'+value.valueStr+'</td></tr>';
                    }
                    num++;
                });
                if (num %2 ==0) {
                    html+='<td></td><td></td></tr>';
                }
                html+='</tbody></table>';
                layer.open({
                    type: 1,
                    content: html,
                    area: ['auto', 'auto']
                });
            } else {
                layer.msg(msgObj.msg);
            }

        }

        function onConnectionLost(responseObject) {
            if (responseObject.errorCode !== 0) {
                console.log(client.clientId + ": " + responseObject.errorCode + "\n");
            }
        }

        var table = layui.table;
        var layer = layui.layer;
        var form = layui.form;
        var loadingIndex;
        //过滤条件
        var name = $("#name"), serialNo = $("#serialNo");
        var data = {name: "null", serialNo: "null",companyId:$('#companyId').val()}, va = "";


        //获取列表数据
        table.render({
            elem: '#tableList'
            ,url:'search'
            ,cols: [[
                {field:'name',  title: '设备名',width:150}
                ,{field:'serial_no',  title: '设备号'}
                ,{field:'type_str',  title: '类型',width:150}
                ,{field:'sim_no',title:'SIM卡号',width:150}
                ,{field:'status_str',  title: '设备状态',width:150}
                ,{field:'device_line_status_str',  title: '在线状态',width:150}
                ,{field:'create_time',  title: '添加时间'}
                ,{field:'action', title: '操作', minWidth: 350}
            ]]
            ,page: true,
            where:data
        });

        //重置
        $("#reset_bt").click(function(){
            name.val('');
            serialNo.val('');
            data.name =  "null";
            data.serialNo =  "null";
            data.is_bt = 1;
            table.reload("tableList", {
                where: data
            });
            return  false;
        });

        //搜索
        $("#src_bt").click(function(){
            jiazai_index = layer.load(2, { shade: [.3, '#FFF']});
            va = $.trim(name.val());
            data.name = va.length > 0 ? va : "null";
            va = $.trim(serialNo.val());
            data.serialNo = va.length > 0 ? va : "null";
            data.is_bt = 1;
            table.reload("tableList", {
                where: data
            });
            layer.close(jiazai_index);
            return  false;
        });

        var editDeviceIndex = 0;
        //列表条目操作
        table.on('tool(tableList)', function(obj){
            var data = obj.data;
            console.log(obj);
            if(obj.event === 'monitor'){
                window.location.href = baseUrl+'/Driver/behaviorLists/deviceNo/'+data.serial_no
            }

            if(obj.event === 'del'){
                layer.confirm('确定删除该设备吗？', function(index){
                    $.ajax({
                        type: "get",
                        url: baseUrl + "/Device/delDevice",
                        data: {id:data.id},
                        dataType: "json",
                        success: function(data)
                        {
                            if (data.code == 1) {
                                layer.msg('删除成功');
                            } else {
                                layer.msg(data.msg);
                            }

                        }
                    });
                    obj.del();
                    layer.close(index);
                });
            }
            //修改设备信息
            if (obj.event === 'edit') {
                console.log(data);

                $('#edit_device_id').val(data.id);
                $('#device_name_input').val(data.name);
                //$('#device_sim_no').val(data.sim_no);
                $('#device_no_input').val(data.serial_no);


                //提交按钮可用
                $('#editDevice_btn').attr('disabled',false);
                $('#editDevice_btn').removeClass('layui-btn-disabled');

                editDeviceIndex = layer.open({
                    area:['600','400']
                    ,type: 1
                    ,title: '修改设备信息'
                    ,offset: 'auto'                        //具体配置参考：http://www.layui.com/doc/modules/layer.html#offset
                    ,id: 'deviceInfo'                    //防止重复弹出
                    ,content: $('#editDevice')
                    ,shade:0                         //不显示遮罩
                });

            }
            if(obj.event === 'setting'){
                //查看设备配置
                $.ajax({
                    type: "post",
                    url: baseUrl + "/Device/getDeviceSettingInfo",
                    data: {deviceType:data.type,serialNo:data.serial_no,topic:topic},
                    dataType: "json",
                    success: function(data)
                    {
                        if (data.code == 1) {
                            loadingIndex = layer.load(1, {shade: false});
                            timeIndex = setTimeout(function () {
                                layer.close(loadingIndex);
                                layer.msg('暂无配置信息');
                            },10000);
                        } else {
                            layer.msg(data.msg);
                        }
                    }
                });
            }

            if(obj.event === 'detail'){
                window.location = baseUrl + "/Device/deviceInfo/serialNo/"+data.serial_no;
            }
            //重启设备
            if (obj.event == 'restart') {
                $.ajax({
                    type: "post",
                    url: baseUrl + "/Device/restartDevice",
                    data: {deviceType:data.type,serialNo:data.serial_no},
                    dataType: "json",
                    success: function(data)
                    {
                        layer.msg(data.msg);
                    }
                });
            }
            //开
            if (obj.event == 'open') {
                $.ajax({
                    type: "post",
                    url: baseUrl + "/Device/pushMsg",
                    data: {deviceType:data.type,serialNo:data.serial_no,type:60},
                    dataType: "json",
                    success: function(data)
                    {
                        layer.msg(data.msg);
                    }
                });
            }
            //关
            if (obj.event == 'off') {
                $.ajax({
                    type: "post",
                    url: baseUrl + "/Device/pushMsg",
                    data: {deviceType:data.type,serialNo:data.serial_no,type:70},
                    dataType: "json",
                    success: function(data)
                    {
                        layer.msg(data.msg);
                    }
                });
            }

        });

        //提交编辑车辆信息
        form.on('submit(editDevice)', function(data){

            $('#editDevice_btn').attr('disabled',true);
            $('#editDevice_btn').addClass('layui-btn-disabled');

            var postData = data.field;
            postData.id         = $('#edit_device_id').val();
            $.ajax({
                type: "post",
                url: baseUrl + "/Device/editDevice",
                data: postData,
                dataType: "json",
                success: function(data)
                {
                    if (data.code == 1) {
                        layer.close(editDeviceIndex);
                        table.reload('tableList',{});
                    }
                    layer.msg(data.msg);

                    $('#editDevice_btn').attr('disabled',false);
                    $('#editDevice_btn').removeClass('layui-btn-disabled');
                }
            });

        });

    });
});