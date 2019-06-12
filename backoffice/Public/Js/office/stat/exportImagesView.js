/**
 * Created by 01 on 2019/1/11.
 */

layui.use(['form', 'laydate','autocomplete','laypage','layer'], function(){

    var form = layui.form
        ,layer = layui.layer
        ,laydate = layui.laydate
        ,autocomplete = layui.autocomplete
        ,laypage = layui.laypage;

    var baseUrl = $('#baseUrl').val();


    //日期范围
    laydate.render({
        elem: '#behaviorTime'
        ,type: 'datetime'
        ,range: true

    });

    var behaviorTime = $('#behaviorTime');
    var code = $('#code'),  deviceNo = $('#deviceNo'),imgType = $('#imgType');
    var data = { startTime: 'null', endTime: 'null',code:"null", deviceNos:deviceNo.val().length>0 ? deviceNo.val() : "null",imgType:'img'};

    /**
     * 检索列表
     */
    function setLists() {
        $('#behaviorItemBody').html('<div style="margin: 10px;">查询数据中...</div>');


        timeVal = behaviorTime.val();


        if (timeVal != '') {
            var time = timeVal.split(" - ");
            if (time.length == 2) {
                var startTime = $.trim(time[0]), endTime = $.trim(time[1]);
                data.startTime = startTime;
                data.endTime   = endTime;

            }
        }

        // 获取数据
        $.ajax({
            type: "post",
            url: baseUrl+"/Driver/exportBehaviorImages",
            data: data,
            dataType: "json",
            success: function(data)
            {
                if(data.code == 1)
                {
                    checkPullAll(data.data.zip);
                } else {
                    $('#behaviorItemBody').html('<div style="margin: 10px;">查询失败</div>');
                }
            }
        });
    }

    autocomplete.render({
        elem: $('#deviceNo'),
        url: baseUrl+"/Vehicle/searchDevice",
        cache: false,
        template_val: '{{d.serial_no}}',
        template_txt: '{{d.serial_no}}',
        onselect: function (resp) {
            //得到设备id
            //$('#deviceNo').val(resp.device_no);
        }
    });

    //搜索
    $("#src_bt").click(function(){
        va = $.trim(code.val());
        data.code = va.length > 0 ? va : "null";
        va = $.trim(deviceNo.val());
        data.deviceNos = va.length > 0 ? va : "null";
        va = $.trim(imgType.val());
        data.imgType = va.length > 0 ? va : "null";
        va = $.trim(behaviorTime.val());
        if (va.length == 0 ){
            data.startTime = 'null';
            data.endTime = 'null';
        }

        setLists();
        return  false;
    });

    /**
     * 检测是否拉取完毕
     */
    var checkPullAll = function (zip) {
        var inter = setInterval(function () {
            // 获取数据
            $.ajax({
                type: "get",
                url: baseUrl+"/Driver/checkPullAll",
                data: data,
                dataType: "json",
                success: function(data)
                {
                    $('#behaviorItemBody').html('<div style="margin: 10px;">'+data.data.msg+'</div>');
                    if(data.code == 1)
                    {
                        if (data.data.flg == 60 || data.data.flg == 20 || data.data.flg == 40) {
                            clearInterval(inter);
                            if (data.data.flg == 60) {
                                open(baseUrl+'/Driver/downloadFile/fileName/'+zip,'_blank');
                            }
                        }
                    }
                }
            });
        },5000);
    }

});
