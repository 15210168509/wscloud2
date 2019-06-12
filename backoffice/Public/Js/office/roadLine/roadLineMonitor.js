/**
 * Created by dev on 2018/8/1.
 */
$(function () {
    var baseUrl = $('#baseUrl').val();
    // 百度地图API功能
    var map = new BMap.Map("allmap");    // 创建Map实例
    map.centerAndZoom(new BMap.Point(116.404, 39.915), 12);  // 初始化地图,设置中心点坐标和地图级别
    //添加地图类型控件
    map.enableScrollWheelZoom(true);     //开启鼠标滚轮缩放
    layui.use(['form','autocomplete','layer'],function () {
        var autocomplete = layui.autocomplete;
        var layer = layui.layer;
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

        var marker;//车辆表记
        var polyline;//路线
        var monitor;//定时器
        $('#sure').click(function () {
            //绘制路线
            var road = $('#road').val();
            if (!road) {
                layer.msg('请选择路线');
                return false;
            }
            if (!$('#device_no').val()) {
                layer.msg('请选择车辆');
                return false;
            }

            $.ajax({
                url:baseUrl+'/RoadLine/getRoadLineInfo',
                data:{roadId:road},
                dataType:'json',
                type:'post',
                success:function (data) {
                    //console.log(data.data);
                    if (polyline) {
                        map.removeOverlay(polyline);
                    }
                    polyline = new BMap.Polyline(data.data);

                    map.addOverlay(polyline);
                    if (monitor) {
                        clearInterval(monitor);
                    }
                    //获取指定车辆坐标
                    monitor = setInterval(getVehicleLocation,5000);
                }
            });

        });


        function getVehicleLocation() {
            var deviceNo = $('#device_no').val();
            $.ajax({
                 url:baseUrl+'/RoadLine/getVehicleLocation',
                 data:{deviceNo:deviceNo},
                 dataType:'json',
                 type:'get',
                 success:function (data) {
                     if (data.code == 1) {
                         if (marker) {
                             map.removeOverlay(marker);
                         }
                         var point = new BMap.Point(data.data.lng,data.data.lat);
                         marker = new BMap.Marker(point);
                         map.addOverlay(marker);
                         if (BMapLib.GeoUtils.isPointOnRoad(point, polyline)) {
                             console.log('正常行驶中');
                         } else {
                             layer.msg('偏离路线');
                         }
                     } else {
                        layer.msg('暂无车辆位置信息');
                     }
                 }
            });
        }



    })
});