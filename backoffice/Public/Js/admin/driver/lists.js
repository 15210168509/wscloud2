/**
 * Created by dev on 2018/5/10.
 */
layui.use(['table','autocomplete'], function(){
    var baseUrl = $('#baseUrl').val();
    var table = layui.table;
    var autocomplete = layui.autocomplete;

    //过滤条件
    var name = $("#driver_name"), phone = $("#driver_phone"), status = $("#driver_status"),account = $('#driver_account')
        ,companyId = $('#companyId'),company = $('#company');
    var data = {name: "null", account: "null", status: "null",phone:"null",companyId:companyId.val().length>0?companyId.val():"null",company:company.val().length>0?company.val():"null"}, va = "";

    //获取列表数据
    table.render({
        elem: '#tableList'
        ,url:baseUrl+'/Driver/ajaxLists'
        ,cols: [[
            {field:'name',  title: '司机姓名'}
            ,{field:'company_name',  title: '所属公司'}
            ,{field:'sex_name',  title: '性别',width: 100}
            ,{field:'phone',  title: '手机号'}
            ,{field:'account',  title: '司机账号'}
            ,{field:'status_name',  title: '状态'}
            ,{field:'create_time',  title: '时间'}
        ]]
        ,page: true
        ,where:data
    });

    //重置
    $("#reset_bt").click(function(){
        name.val('');
        phone.val('');
        status.val('');
        account.val('');

        data.name =  "null";
        data.phone =  "null";
        data.status = "null";
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
        va = $.trim(name.val());
        data.name = va.length > 0 ? va : "null";
        va = $.trim(phone.val());
        data.phone = va.length > 0 ? va : "null";
        va = $.trim(status.val());
        data.status = va.length > 0 ? va : "null";
        va = $.trim(account.val());
        data.account = va.length > 0 ? va : "null";
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
        url: baseUrl + "/Driver/searchCompany",
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