/**
 * Created by dev on 2018/5/15.
 */
$(function () {
    var baseUrl = $('#baseUrl').val();
    layui.use(['table','autocomplete'], function(){
        var table = layui.table;
        var autocomplete = layui.autocomplete;
        //过滤条件
        var name = $("#name"), serialNo = $("#serialNo") ,companyId = $('#companyId'),company = $('#company');
        var data = {name: "null", serialNo: "null",companyId:companyId.val().length>0?companyId.val():"null",company:company.val().length>0?company.val():"null"}, va = "";


        //获取列表数据
        table.render({
            elem: '#tableList'
            ,url:baseUrl+'/Device/search'
            ,cols: [[
                {field:'name',  title: '设备名'}
                ,{field:'company_name',  title: '所属公司'}
                ,{field:'serial_no',  title: '设备号'}
                ,{field:'type_str',  title: '类型'}
                ,{field:'status_str',  title: '设备状态'}
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
            data.company    =  "null";
            data.companyId  =  "null";
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
            va = $.trim(company.val());
            data.company = va.length > 0    ? va : "null";

            if (va.length >0 ) {
                va = $.trim(companyId.val());
                data.companyId = va.length > 0  ? va : "null";
            } else {
                companyId.val('');
                data.companyId = "null";
            }

            data.is_bt = 1;
            table.reload("tableList", {
                where: data
            });
            layer.close(jiazai_index);
            return  false;
        });

        autocomplete.render({
            elem: $('#company'),
            url: baseUrl + "/Device/searchCompany",
            cache: false,
            template_val: '{{d.name}}',
            template_txt: '{{d.name}}',
            onselect: function (resp) {
                //得到设备id
                $('#companyId').val(resp.id);
            }
        });

        $('#company').bind('input propertychange', function() {
            $('#companyId').val('');
        });

    });
});