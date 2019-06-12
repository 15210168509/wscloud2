/**
 * Created by dev on 2018/5/16.
 */
$(function () {
    var baseUrl = $('#baseUrl').val();
    var timeIndex;
    var loadingIndex;
    var timeIndexInstallInfo;
    layui.use(['table','form','layer','autocomplete'],function () {
        ////////////////////////////////////
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
            var msgObj = JSON.parse(message.payloadString);
            //展示设备信息
            console.log(msgObj);
            layer.close(loadingIndex);
            clearTimeout(timeIndex);
            if (msgObj.code == 1) {
                if (msgObj.data.msgType == 10) {
                    //设备安装图片
                    clearTimeout(timeIndexInstallInfo);
                    console.log(msgObj.data);
                    var img = '<img style="width: 500px;height: 500px" src="'+msgObj.data.url+'" alt="">';
                    layer.open({
                        type: 1,
                        content: img,
                        offset: 'auto'
                    });
                } else {
                    clearTimeout(timeIndex);
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
                }

            } else {
                layer.msg(msgObj.msg);
            }

        }

        function onConnectionLost(responseObject) {
            if (responseObject.errorCode !== 0) {
                console.log(client.clientId + ": " + responseObject.errorCode + "\n");
            }
        }

        ////////////////////////////////
        var table = layui.table;
        var form = layui.form;
        var layer = layui.layer;
        var autocomplete = layui.autocomplete;
        //过滤条件
        var vehicle_no = $("#vehicle_no"),device_no = $('#device_no');
        var data = {vehicle_no: "null",companyId:$('#companyId').val(),device_no:'null'}, va = "";

        //获取列表数据
        table.render({
            elem: '#tableList'
            ,url:'search'
            ,cols: [[
                {type:'checkbox'},
                {field:'vehicle_no',  title: '车牌号'}
                ,{field:'model',  title: '车型',width:200}
                ,{field:'device_no',  title: '设备号'}
                ,{field:'device_line_status_str',  title: '在线状态',width:150}
                ,{field:'create_time',  title: '时间',width:200}
                ,{field:'action', title: '操作', minWidth: 350}
            ]]
            ,page: true,
            where:data
        });

        //重置
        $("#reset_bt").click(function(){
            vehicle_no.val('');
            data.vehicle_no =  "null";
            device_no.val('');
            data.device_no =  "null";
            data.is_bt = 1;
            table.reload("tableList", {
                where: data
            });
            return  false;
        });

        //搜索
        $("#src_bt").click(function(){
            va = $.trim(vehicle_no.val());
            data.vehicle_no = va.length > 0 ? va : "null";

            va = $.trim(device_no.val());
            data.device_no = va.length > 0 ? va : "null";

            data.is_bt = 1;
            table.reload("tableList", {
                where: data
            });
            return  false;
        });

        //选择设备号
        autocomplete.render({
            elem: $('#device_no'),
            url: "searchDevice",
            cache: false,
            template_val: '{{d.serial_no}}',
            template_txt: '{{d.serial_no}}',
            onselect: function (resp) {
                //得到设备id
                $('#device_id_search').val(resp.id);
            }
        });

        var editVehicleIndex = 0;

        table.on('tool(tableList)', function(obj){
            var data = obj.data;
            console.log(data);
            if(obj.event === 'monitor'){
                window.location.href = baseUrl + '/Driver/behaviorLists/vehicleNo/'+data.vehicle_no;
            }else if(obj.event === 'del'){
                layer.confirm('确定删除该车辆吗？', function(index){
                    $.ajax({
                        type: "get",
                        url: baseUrl + "/Vehicle/delVehicle",
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
            } else if (obj.event === 'edit') {

                $('#vehicle_id').val(data.id);
                $('#vehicle_no_input').val(data.vehicle_no);
                $('#device_type').val(data.type);
                $('#model').val(data.model);
                $('#device_id').val(data.device_id);
                $('#serial_no').val(data.device_no);

                //提交按钮可用
                $('#editVehicle_btn').attr('disabled',false);
                $('#editVehicle_btn').removeClass('layui-btn-disabled');

                editVehicleIndex = layer.open({
                    area:['600','400']
                    ,type: 1
                    ,title: '修改车辆信息'
                    ,offset: 'auto'                        //具体配置参考：http://www.layui.com/doc/modules/layer.html#offset
                    ,id: 'vehicleInfo'                    //防止重复弹出
                    ,content: $('#editVehicle')
                    ,shade: 0                           //不显示遮罩
                });

            }
            if(obj.event === 'setting'){
                //查看设备配置
                $.ajax({
                    type: "post",
                    url: baseUrl + "/Device/getDeviceSettingInfo",
                    data: {deviceType:data.type,serialNo:data.device_no,topic:topic},
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
            if(obj.event === 'checkDeviceInstallInfo'){
                //查看车辆设备的安装图片
                $.ajax({
                    type: "post",
                    url: baseUrl + "/Vehicle/checkVehicleDeviceInstallInfo",
                    data: {deviceType:data.type,serialNo:data.device_no,topic:topic},
                    dataType: "json",
                    success: function(data)
                    {
                        if (data.code == 1) {
                            loadingIndex = layer.load(1, {shade: false});
                            timeIndexInstallInfo = setTimeout(function () {
                                layer.close(loadingIndex);
                                layer.msg('暂无设备安装信息');
                            },10000);
                        } else {
                            layer.msg(data.msg);
                        }
                    }
                });
            }

            //重启设备
            if (obj.event == 'restart') {
                $.ajax({
                    type: "post",
                    url: baseUrl + "/Device/restartDevice",
                    data: {deviceType:data.type,serialNo:data.device_no},
                    dataType: "json",
                    success: function(data)
                    {
                        layer.msg(data.msg);
                    }
                });
            }

            //升级
            if (obj.event == 'update') {
                $.ajax({
                    type: "post",
                    url: baseUrl + "/Vehicle/updateDevice",
                    data: {deviceType:data.type,serialNo:data.device_no},
                    dataType: "json",
                    success: function(data)
                    {
                        layer.msg(data.msg);
                    }
                });
            }

            if(obj.event === 'detail'){
                window.location = baseUrl + "/Device/deviceInfo/serialNo/"+data.device_no;
            }
        });

        //选择设备号
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

        //编辑车辆
        form.on('submit(editVehicle)', function(data){

            $('#editVehicle_btn').attr('disabled',true);
            $('#editVehicle_btn').addClass('layui-btn-disabled');

            var postData = data.field;

            postData.id         = $('#vehicle_id').val();
            postData.device_id = $('#device_id').val();

            $.ajax({
                type: "post",
                url: baseUrl + "/Vehicle/editVehicle",
                data: postData,
                dataType: "json",
                success: function(data)
                {
                    if (data.code == 1) {
                        layer.close(editVehicleIndex);
                        table.reload('tableList',{});
                    }
                    layer.msg(data.msg);

                    $('#editVehicle_btn').attr('disabled',false);
                    $('#editVehicle_btn').removeClass('layui-btn-disabled');
                }
            });

        });


        //添加分组
        $('#addGroups').click(function () {
            var checkStatus = table.checkStatus('tableList')
                ,data = checkStatus.data;

            //layer.alert(JSON.stringify(data));
            if (data.length) {
                layer.open({
                    area: ['auto', '300px'],
                    type:1,
                    content: $('#alertform')
                    ,btn: ['添加', '取消']
                    ,yes: function(index, layero){
                        //按钮【按钮一】的回调
                        if ($('#groupId').val()) {
                            $.ajax({
                                type: "post",
                                url: baseUrl + "/Vehicle/addGroups",
                                data: {vehicle:data,groupId:$('#groupId').val()},
                                dataType: "json",
                                success: function(data)
                                {
                                    if (data.code == 1) {
                                        layer.msg('添加成功');
                                        layer.close(index);
                                    } else {
                                        layer.msg(data.msg);
                                    }

                                }
                            });

                        } else {
                            layer.msg('请选择分组');
                        }
                    }
                    ,btn2: function(index, layero){
                        //按钮【按钮二】的回调
                        $('#groupId').val('');
                        //return false
                    }
                    ,cancel: function(){
                        //右上角关闭回调
                        $('#groupId').val('');
                        //return false 开启该代码可禁止点击该按钮关闭
                    },
                    success:function () {
                        $('.layui-layer-shade').css('display','none');
                    }
                });
            } else {
                layer.msg('请先选择车辆');
            }

        });


    });
});