/**
 * Created by dev on 2018/5/15.
 */
$(function () {
    var baseUrl = $('#baseUrl').val();
    layui.use(['table','laydate'], function(){
        var table = layui.table;
        var laydate = layui.laydate;
        laydate.render({
            elem: '#startTime' //指定元素
        });
        laydate.render({
            elem: '#endTime' //指定元素
        });
        //过滤条件
        var startTime = $("#startTime"), endTime = $("#endTime"), status = $("#status");
        var data = {startTime: "null", endTime: "null", status: "null"}, va = "";

        //获取列表数据
        table.render({
            elem: '#tableList'
            ,url:'msgSearch'
            ,cols: [[
                {field:'title',  title: '标题'}
                ,{field:'content',  title: '内容'}
                ,{field:'status_str',  title: '状态'}
                ,{field:'create_time',  title: '时间'}
                ,{field:'action', title: '操作', minWidth: 150}
            ]]
            ,page: true,
            where:data
        });

        //重置
        $("#reset_bt").click(function(){
            startTime.val('');
            endTime.val('');
            status.val('');

            data.startTime =  "null";
            data.endTime =  "null";
            data.status = "null";
            data.is_bt = 1;
            table.reload("tableList", {
                where: data
            });
            return  false;
        });

        //搜索
        $("#src_bt").click(function(){
            jiazai_index = layer.load(2, { shade: [.3, '#FFF']});
            va = $.trim(startTime.val());
            data.startTime = va.length > 0 ? va : "null";
            va = $.trim(endTime.val());
            data.endTime = va.length > 0 ? va : "null";
            va = $.trim(status.val());
            data.status = va.length > 0 ? va : "null";
            data.is_bt = 1;
            table.reload("tableList", {
                where: data
            });
            layer.close(jiazai_index);
            return  false;
        });
        //删除
        table.on('tool(tableList)', function(obj){
            var data = obj.data;
            if(obj.event === 'del'){
                layer.confirm('确定删除该消息吗？', function(index){
                    $.ajax({
                        type: "get",
                        url: baseUrl + "/Admin/delMsg",
                        data: {id:data.id},
                        dataType: "json",
                        success: function(data)
                        {
                            if (data.code == 1) {
                                layer.msg('删除成功');

                                obj.del();
                                layer.close(index);

                                if (obj.data.status == 10) {
                                    changeMsgCount(1);
                                }

                            } else {
                                layer.msg(data.msg);
                            }

                        }
                    });

                });
            } else if(obj.event === 'read'){
                $.ajax({
                    type: "get",
                    url: baseUrl + "/Admin/readMsg",
                    data: {id:data.id},
                    dataType: "json",
                    success: function(data)
                    {
                        if (data.code == 1) {
                            table.reload("tableList", {});
                            changeMsgCount(1);
                        }

                    }
                });
            }

        });

    });
});