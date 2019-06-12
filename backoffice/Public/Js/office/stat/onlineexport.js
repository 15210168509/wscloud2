
layui.use(['form', 'laydate','autocomplete','laypage','layer'], function(){

    var form = layui.form
        ,layer = layui.layer

        ,autocomplete = layui.autocomplete
        ,laypage = layui.laypage;

    var baseUrl = $('#baseUrl').val();

    var  vehicleNo = $('#vehicleNo'), deviceNo = $('#deviceNo');
    var data = {pageNo:1,pageSize:10,code:"null",vehicleNo:vehicleNo.val().length>0 ? vehicleNo.val() : "null", deviceNo:deviceNo.val().length>0 ? deviceNo.val() : "null"};
    autocomplete.render({
        elem: $('#vehicleNo'),
        url: baseUrl+"/RoadLine/searchVehicle",
        cache: false,
        template_val: '{{d.vehicle_no}}',
        template_txt: '{{d.vehicle_no}}',
        onselect: function (resp) {
            //得到设备id
            //$('#deviceNo').val(resp.device_no);
        }
    });



    //导出列表
    $("#src_bt").click(function(){
        var va = $.trim(vehicleNo.val());
        data.vehicleNo = va.length > 0 ? va : "null";
        var va = $.trim(deviceNo.val());
        data.deviceNo = va.length > 0 ? va : "null";
        data.pageNo = 1;
        //$('#exportFrame').attr('src',baseUrl+'/Stat/exportWarn/code/'+data.code+'/vehicleNo/'+data.vehicleNo+'/deviceNo/'+data.deviceNo+'/startTime/'+data.startTime+'/endTime/'+data.endTime);
        window.open(baseUrl+'/Stat/onlineWarn/companyId/null/vehicleNo/'+data.vehicleNo+'/deviceNo/'+data.deviceNo,'_blank');

        return  false;
    });
});
