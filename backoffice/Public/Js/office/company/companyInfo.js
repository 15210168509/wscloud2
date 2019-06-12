/**
 * Created by dev on 2018/6/27.
 */
$(function () {
    var baseUrl = $('#baseUrl').val();
    console.log(baseUrl);
    layui.use(['element','laydate'],function () {
        var element = layui.element;
        var laydate = layui.laydate;

    });
    var companyId = $('#currentCompanyId').val();
    console.log(companyId);
    //报警导出列表
    $("#export_warn_bt").click(function(){

        var data= {};
        data.companyId = $('#currentCompanyId').val();
        data.companyName = $('#currentCompanyName').val();
        data.code      = "null";
        data.vehicleNo = "null";
        data.deviceNo  = "null";
        data.pageNo    = 1;
        data.startTime = "null";
        data.endTime   = "null";

        //$('#warnFrame').attr('src',baseUrl+'/Stat/exportWarn/companyId/'+data.companyId+'/code/'+data.code+'/vehicleNo/'+data.vehicleNo+'/deviceNo/'+data.deviceNo+'/startTime/'+data.startTime+'/endTime/'+data.endTime);
        window.open(baseUrl+'/Stat/exportWarn/companyId/'+data.companyId+'/companyName/'+data.companyName+'/code/'+data.code+'/vehicleNo/'+data.vehicleNo+'/deviceNo/'+data.deviceNo+'/startTime/'+data.startTime+'/endTime/'+data.endTime,'_blank');

        return  false;
    });

    //上下线导出列表
    $("#export_online_bt").click(function(){

        var data= {};
        data.companyId = $('#currentCompanyId').val();
        data.deviceNo  =  "null";
        data.vehicleNo = "null";
        data.pageNo = 1;
        //$('#onlineFrame').attr('src',baseUrl+'/Stat/exportWarn/companyId/'+data.companyId+'/code/'+data.code+'/vehicleNo/'+data.vehicleNo+'/deviceNo/'+data.deviceNo);
        window.open(baseUrl+'/Stat/onlineWarn/companyId/'+data.companyId+'/vehicleNo/'+data.vehicleNo+'/deviceNo/'+data.deviceNo,'_blank');

        return  false;
    });
});