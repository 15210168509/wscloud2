/**
 * Created by dev on 2018/5/16.
 */
$(function () {
    var baseUrl = $('#baseUrl').val();
    layui.use(['table','autocomplete'],function () {
        var table = layui.table;
        var autocomplete = layui.autocomplete;
        //过滤条件
        var vehicle_no = $("#vehicle_no"),companyId = $('#companyId'),company = $('#company');
        var data = {vehicle_no: "null",companyId:companyId.val().length>0?companyId.val():"null",company:company.val().length>0?company.val():"null"}, va = "";
        console.log(data);
        //获取列表数据
        table.render({
            elem: '#tableList'
            ,url:baseUrl+'/Vehicle/search'
            ,cols: [[
                {field:'vehicle_no',  title: '车牌号'}
                ,{field:'company_name',  title: '所属公司'}
                ,{field:'model',  title: '车型'}
                ,{field:'device_no',  title: '设备号'}
                ,{field:'create_time',  title: '时间'}
            ]]
            ,page: true,
            where:data
        });

        //重置
        $("#reset_bt").click(function(){
            vehicle_no.val('');
            data.vehicle_no =  "null";
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
            va = $.trim(vehicle_no.val());
            data.vehicle_no = va.length > 0 ? va : "null";
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
            return  false;
        });

        autocomplete.render({
            elem: $('#company'),
            url: baseUrl + "/Vehicle/searchCompany",
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