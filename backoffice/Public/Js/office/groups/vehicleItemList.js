/**
 * Created by dev on 2018/5/10.
 */
layui.use(['form','layer','table','autocomplete'], function(){

    var baseUrl = $('#baseUrl').val();

    var table = layui.table
        ,form = layui.form
        ,layer = layui.layer
        ,autocomplete = layui.autocomplete;

    //过滤条件
    var vehicle_no = $("#where_vehicle_no"), vehicle_model = $("#vehicle_model"), vehicle_status = $("#vehicle_status");
    var data = {id: $('#groupsId').val(),vehicleNo: "null", vehicleModel: "null", vehicleStatus: "null",is_bt:1}, va = "";

    //获取列表数据
    table.render({
        elem: '#tableList'
        ,url:baseUrl+ '/Groups/ajaxVehicleItemList'
        ,cols: [[
            {field:'vehicle_no',  title: '车牌号码'}
            ,{field:'model',  title: '车辆型号'}
            ,{field:'status_name',  title: '车辆状态'}
            ,{field:'create_time',  title: '添加时间'}
            ,{field:'action', title: '操作', width: 200}
        ]]
        ,page: true
        ,where:data
    });

    //重置
    $("#reset_bt").click(function(){
        vehicle_no.val('');
        vehicle_model.val('');
        vehicle_status.val('');

        data.vehicleNo =  "null";
        data.vehicleModel =  "null";
        data.vehicleStatus = "null";
        data.is_bt = 1;
        table.reload("tableList", {
            where: data
        });
        return  false;
    });

    //搜索
    $("#src_bt").click(function(){

        va = $.trim(vehicle_no.val());
        data.vehicleNo = va.length > 0 ? va : "null";
        va = $.trim(vehicle_model.val());
        data.vehicleModel = va.length > 0 ? va : "null";
        va = $.trim(vehicle_status.val());
        data.vehicleStatus = va.length > 0 ? va : "null";
        data.is_bt = 1;

        table.reload("tableList", {
            where: data
        });
        return  false;
    });
    var groupGroup;
    //操作
    table.on('tool(tableList)', function(obj){

        var data = obj.data;

        if(obj.event === 'del'){
            layer.confirm('真的移除此车辆么', function(index){
                deleteVehicle(data.id);
                layer.close(index);
            });
        }
        if(obj.event === 'move'){
            //获取公司下分组
            $("#vehicleGroup").val(data.id);
            $.ajax({
                type: "get",
                url: baseUrl + "/Groups/moveVehicleGroup",
                data: {id:data.id},
                dataType: "json",
                success: function(res)
                {
                    console.log(res);

                    if (res.code == 1) {
                        var str = '';
                        $.each(res.data,function (index,value) {
                            str+='<div class="layui-input-block" style="margin-left: 10px"><input type="radio" name="group" value="'+value.id+'" title="'+value.name+'"></div>';
                        });
                        $('#groupList').html(str);
                        groupGroup = layer.open({
                            type: 1
                            ,title: '移动分组'
                            ,offset: 'auto'
                            ,content:$('#groupListForm')
                            ,closeBtn:1
                            ,shade: 0                           //不显示遮罩
                        });
                        form.render();
                    } else {
                        layer.msg(res.msg);
                    }
                }
            });
        }
    });
    $(document).on("click", "#moveGroup", function(){
        console.log($('input[name="group"]:checked ').val());
        var groupId = $('input[name="group"]:checked ').val();
        if (groupId) {
            $.ajax({
                type: "post",
                url: baseUrl + "/Groups/ajaxMoveVehicleGroup",
                data: {id:$('#vehicleGroup').val(),groupId:groupId},
                dataType: "json",
                success: function(data)
                {
                    layer.msg(data.msg);
                    if (data.code == 1) {
                        layer.close(groupGroup);
                        table.reload('tableList',{});
                    }

                }
            });
        } else {
            layer.msg('请选择分组');
        }

    });

    //自定义验证规则
    form.verify({
        name:function (value) {
            if (value.length == 0) {
                return '请输入司机姓名';
            } else {
                var reg = /^([\u4e00-\u9fa5]){2,7}$/;
                if(!reg.test(value)){
                    return '姓名只能为中文';
                }
            }
        }
        ,account: function (value) {
            if (value .length == 0) {
                return '请填写登录账户';
            } else {
                var reg = /^[a-zA-Z0-9]{3,16}$/;
                if (!reg.test(value)){
                    return '用户名只能为数字或字母';
                }
            }
        }
        ,driving_age : function (value) {
            if (value.length == 0) {
                return '请输入驾驶年龄';
            } else {
                var reg = /^[0-9]{1,2}$/;
                if(!reg.test(value)){
                    return '请输入1到2位数字';
                }
            }
        }
        ,certification_code: function (value) {
            if (value.length == 0) {
                return '请输入驾驶证号';
            } else {
                var reg =  /(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/;
                if(!reg.test(value)){
                    return '请输入正确的驾驶证号';
                }
            }
        },
    });

    //监听提交
    form.on('submit(demo1)', function(data){
        var baseUrl = $('#baseUrl').val();

        var update_btn = $("#update_btn");
        update_btn.addClass("layui-btn-disabled");
        update_btn.attr('disabled',true);

        $.ajax({
            type: "post",
            url: baseUrl + "/Driver/ajaxUpdateDriver",
            data: data.field,
            dataType: "json",
            success: function(data)
            {
                if (data.code == 1){
                    layer.closeAll();
                    table.reload('tableList',{});
                }
                update_btn.removeClass("layui-btn-disabled");
                update_btn.attr('disabled',false);
                layer.msg(data.msg);
            }
        });
        return false;
    });


    //移除车辆
    var deleteVehicle = function (id) {
        var data = {id:id};

        $.ajax({
            type: "post",
            url: baseUrl + "/Groups/ajaxDeleteVehicle",
            data: data,
            dataType: "json",
            success: function(data)
            {
                if (data.code == 1){
                    table.reload('tableList',{});
                }
                layer.msg(data.msg);
            }
        });
        return false;
    }

    //搜索车辆
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

    //监听提交
    form.on('submit(demo1)', function(data){

        var add_btn = $('#add_btn');
        add_btn.addClass("layui-btn-disabled");
        add_btn.attr('disabled',true);

        $.ajax({
            type: "post",
            url: baseUrl + "/Groups/ajaxAddVehicle",
            data: data.field,
            dataType: "json",
            success: function(data)
            {
                if(data.code == 1)
                {
                    table.reload('tableList',{});
                    $("#vehicle_no").val("");
                    layer.closeAll();
                }
                layer.msg(data.msg);
                add_btn.removeClass("layui-btn-disabled");
                add_btn.attr('disabled',false);
            }
        });
        return false;
    });

    $('#addVehicle').click(function () {
        var type = 1;
        layer.open({
            type: 1
            ,title: '添加车辆'                     //具体配置参考：http://www.layui.com/doc/modules/layer.html#offset
            ,id: 'tableList'+type               //防止重复弹出
            ,content: $('#alert_form')
            ,shade: 0                           //不显示遮罩
        });

    });

});