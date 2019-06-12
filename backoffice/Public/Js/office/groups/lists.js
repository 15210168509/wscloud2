/**
 * Created by dev on 2018/5/10.
 */
layui.use(['form', 'laydate','layer','table'], function(){

    var table = layui.table
        ,form = layui.form
        ,layer = layui.layer
        ,laydate = layui.laydate;

    //过滤条件
    var name = $("#groups_name");
    var data = {name: "null"}, va = "";

    //获取列表数据
    table.render({
        elem: '#tableList'
        ,url:'ajaxLists'
        ,cols: [[
            {field:'name',  title: '分组名称'}
           /* ,{field:'type_name',  title: '分组类型'}*/
            ,{field:'action', title: '操作', width: 250}
        ]]
        ,page: true
        ,where:data
    });

    //重置
    $("#reset_bt").click(function(){
        name.val('');

        data.name =  "null";
        data.is_bt = 1;
        table.reload("tableList", {
            where: data
        });
        return  false;
    });

    //搜索
    $("#src_bt").click(function(){

        va = $.trim(name.val());
        data.name = va.length > 0 ? va : "null";

        data.is_bt = 1;
        table.reload("tableList", {
            where: data
        });
        return  false;
    });

    //操作
    table.on('tool(tableList)', function(obj){
        var data = obj.data;
        if(obj.event === 'detail'){

            $('#groupsId').val(data.id);
            $('#name').val(data.name);

            var type = data.id;

            layer.open({
                type: 1
                ,title: '修改分组信息'
                ,offset: type                        //具体配置参考：http://www.layui.com/doc/modules/layer.html#offset
                ,id: 'tableList'+type               //防止重复弹出
                ,content: $('#add_driver')
                ,shade: 0                           //不显示遮罩
            });

        } else if(obj.event === 'del'){
            layer.confirm('真的删除此分组么', function(index){
                deleteGroups(data.id);
                layer.close(index);
            });
        } else if(obj.event === 'edit'){
            if (data.type == 10){
                location.href = 'vehicleItemList/id/'+data.id;
            }

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
    });

    //监听提交
    form.on('submit(demo1)', function(data){
        var baseUrl = $('#baseUrl').val();

        var update_btn = $("#update_btn");
        update_btn.addClass("layui-btn-disabled");
        update_btn.attr('disabled',true);

        $.ajax({
            type: "post",
            url: baseUrl + "/Groups/ajaxUpdateGroups",
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


    //删除司机
    var deleteGroups = function (id) {
        var baseUrl = $('#baseUrl').val();
        var data = {id:id};

        $.ajax({
            type: "post",
            url: baseUrl + "/Groups/ajaxDeleteGroups",
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

});