/**
 * Created by dev on 2018/6/25.
 */
$(function () {
    var baseUrl = $('#baseUrl').val();
    layui.use(['table','layer','form','laydate'],function () {
        var table = layui.table;
        var layer = layui.layer;
        var form  = layui.form;
        var laydate = layui.laydate;
        var name = $("#name");
        var data = {name: "null",parentCompanyId:$('#parentCompanyId').val()}, va = "";
        laydate.render({
            elem: '#start_time'
        });
        laydate.render({
            elem: '#end_time',
        });
        //获取列表数据
        table.render({
            elem: '#tableList'
            ,url:baseUrl+'/Company/search'
            ,cols: [[
                 {field:'name',  title: '公司名称'}
                ,{field:'phone',  title: '手机号'}
                ,{field:'create_time',  title: '注册时间'}
                ,{field:'verify_status_str',  title: '审核状态'}
                ,{field:'action', title: '操作', minWidth: 150}
            ]]
            ,page: true,
            where:data
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
            jiazai_index = layer.load(2, { shade: [.3, '#FFF']});
            va = $.trim(name.val());
            data.name = va.length > 0 ? va : "null";
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
                layer.confirm('确定删除该公司吗？', function(index){
                    $.ajax({
                        type: "get",
                        url: baseUrl + "/Company/delCompany",
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
            //审核
            if (obj.event === 'verify') {
                layer.open({
                    content: $('#verify')
                    ,area: '420px'
                    ,type: 1
                    ,btn: ['提交', '取消']
                    ,shade:0
                    ,yes: function(index, layero){
                        var load = layer.load();
                        var verifyStatus = $("input[name='verify']:checked").val();
                        var comment = $('#comment_text').val();
                        if (verifyStatus != 30 && comment.length<=0) {
                            layer.msg('请填写未通过理由');
                            return;
                        }
                        $.ajax({
                            type: "get",
                            url: baseUrl + "/Company/verifyCompany",
                            data: {id:data.id,verifyStatus:verifyStatus,comment:comment},
                            dataType: "json",
                            success: function(data)
                            {
                                if (data.code == 1) {
                                    layer.msg('操作成功');
                                } else {
                                    layer.msg(data.msg);
                                }
                                layer.close(load);
                                layer.close(index);
                            }
                        });
                    }
                    ,btn2: function(index, layero){
                        //按钮【按钮二】的回调

                        //return false 开启该代码可禁止点击该按钮关闭
                    }
                    ,cancel: function(){
                        //右上角关闭回调

                        //return false 开启该代码可禁止点击该按钮关闭
                    }
                });
            }
            //套餐
            if (obj.event === 'package') {
                //获取套餐信息
                $('#money').val('');
                $('#devices').val('');
                $('#start_time').val('');
                $('#end_time').val('');
                $.ajax({
                    type:'get',
                    data:{id:data.id},
                    dataType:'json',
                    url:baseUrl+'/Company/getPackageInfo',
                    success:function (data) {
                        if (data.code == 1) {
                            laydate.render({
                                elem: '#start_time',
                                value:data.data.start_time_str,
                                isInitValue: true
                            });
                            laydate.render({
                                elem: '#end_time',
                                value:data.data.end_time_str,
                                isInitValue: true
                            });
                        } else {
                            laydate.render({
                                elem: '#start_time',
                                isInitValue: true
                            });
                            laydate.render({
                                elem: '#end_time',
                                isInitValue: true
                            });
                        }
                    }
                });
                layer.open({
                    content: $('#package')
                    ,type: 1
                    ,btn: ['确定', '取消']
                    ,shade:0
                    ,yes: function(index, layero){
                        var load = layer.load();
                        var start_time = $('#start_time').val();
                        var end_time = $('#end_time').val();
                        var money = $('#money').val();
                        var devices = $('#devices').val();
                        if (!start_time || !end_time) {
                            layer.msg('请选择时间');
                        }
                        if (!devices && devices != 0) {
                            layer.msg('请填写设备数');
                        }
                        $.ajax({
                            type: "get",
                            url: baseUrl + "/Company/setCompanyPackage",
                            data: {id:data.id,start_time:start_time,end_time:end_time,money:money,devices:devices},
                            dataType: "json",
                            success: function(data)
                            {
                                layer.close(load);
                                layer.msg(data.msg);
                                layer.close(index);
                            }
                        });
                    }
                    ,btn2: function(index, layero){
                        //按钮【按钮二】的回调

                        //return false 开启该代码可禁止点击该按钮关闭
                    }
                    ,cancel: function(){
                        //右上角关闭回调

                        //return false 开启该代码可禁止点击该按钮关闭
                    }
                });
            }
            //详情
            if (obj.event === 'info') {
                window.location = baseUrl+'/Company/companyInfo/id/'+data.id;
            }

        });

        form.on('radio(erweima)', function (data) {
            //alert(data.elem);
            //console.log(data.elem);
            //alert(data.value);//判断单选框的选中值
            if (data.value == 30) {
                $('#comment').addClass('layui-hide')
            } else {
                $('#comment').removeClass('layui-hide')
            }
        });

    })
});