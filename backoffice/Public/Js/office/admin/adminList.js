/**
 * Created by dev on 2018/5/10.
 */
$(function () {
    var baseUrl = $('#baseUrl').val();
    layui.use('table', function(){
        var table = layui.table;
        //过滤条件
        var name = $("#name"), phone = $("#phone"), status = $("#status");
        var data = {name: "null", account: "null", status: "null",companyId:$('#companyId').val()}, va = "";

        //获取列表数据
            table.render({
                elem: '#tableList'
                ,url:'search'
                ,cols: [[
                    {field:'name',  title: '用户名'}
                    ,{field:'phone',  title: '手机号'}
                    ,{field:'status_name',  title: '状态'}
                    ,{field:'create_time',  title: '时间'}
                    ,{field:'action', title: '操作', minWidth: 150}
                ]]
                ,page: true,
                where:data
            });

        //重置
        $("#reset_bt").click(function(){
            name.val('');
            phone.val('');
            status.val('');

            data.name =  "null";
            data.phone =  "null";
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
            va = $.trim(name.val());
            data.name = va.length > 0 ? va : "null";
            va = $.trim(phone.val());
            data.phone = va.length > 0 ? va : "null";
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
                layer.confirm('确定删除该管理员吗？', function(index){
                    $.ajax({
                        type: "get",
                        url: baseUrl + "/Admin/delAdmin",
                        data: {adminId:data.id},
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
        });

    });
});