/**
 * Created by dev on 2018/8/3.
 */
$(function () {
    layui.use(['layer','autocomplete','laydate'],function () {
        var baseUrl = $('#baseUrl').val();
        var layer = layui.layer;
        var autocomplete = layui.autocomplete;
        var laydate = layui.laydate;
        // 日期时间范围
        var gpsTimeLaydate = laydate.render({
            elem: '#gpsTime'
            ,type: 'datetime'
            ,range: true
            ,theme: '#2494F2'
            ,min: $('#minTime').val()
            ,max: $('#maxTime').val()
            ,ready: function(){
                gpsTimeLaydate.hint('可选日期范围 <br> '+$('#minTime').val()+' 到 '+$('#maxTime').val());
            }
            ,done: function(value, date, endDate){
                //console.log(value);
                history(value);
            }
        });

        autocomplete.render({
            elem: $('#vehicle_no'),
            url: "searchVehicle",
            cache: false,
            template_val: '{{d.vehicle_no}}',
            template_txt: '{{d.vehicle_no}}',
            onselect: function (resp) {
                //得到设备id
                $('#device_no').val(resp.device_no);
            }
        });
        // 百度地图设置
        var map = new Map({
            container:'allmap',
            center:{lng:116.404,lat:39.915},
            zoom:15,
            enableScroll:true,
            baseUrl:baseUrl,
            autoRefresh:true,
            interval:5
        });

        // 初始化地图
        map.initMap();



        // 轨迹回放
        function history(timeVal) {
            var data = {
                startTime: 'null',
                endTime: 'null'
            };
            var deviceNo = $('#device_no').val();
            if (!deviceNo) {
                layer.msg('请选择车辆');
                return false;
            }

            if (typeof timeVal == 'undefined') {
                timeVal = $('#gpsTime').val();
            }

            if (timeVal != '') {
                var time = timeVal.split(" - ");
                if (time.length == 2) {
                    var patrn = /^[1-9]{1}[0-9]{2,3}\-[0-9]{2}\-[0-9]{2}\s[0-9]{2}\:[0-9]{2}\:[0-9]{2}$/i;
                    var startTime = $.trim(time[0]), endTime = $.trim(time[1]);
                    var st = patrn.exec(startTime), en = patrn.exec(endTime);
                    if (st != null && en != null) {
                        data.startTime = startTime;
                        data.endTime   = endTime;
                    }
                }
            }

            // 先停止
            map.stopLushu();
            // 生成路书

            map.history(data.startTime, data.endTime,true,deviceNo,function () {
                layer.msg('点击开始，进行回放');
            },function () {
                layer.msg('未获取到车辆轨迹信息');
            });
        }

        $('#sure').click(function () {
            history();
        });

        $('#begin').click(function () {
            map.startLushu();
        });
        $('#pause').click(function () {
            map.pauseLushu();
        });
        $('#stop').click(function () {
            map.stopLushu( function () {
                history();
            } );
        });
    });


});