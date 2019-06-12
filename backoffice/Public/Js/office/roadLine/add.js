/**
 * Created by dev on 2018/7/31.
 */
$(function () {
    layui.use(['form','layer'],function () {
        var layer = layui.layer;
        var baseUrl = $('#baseUrl').val();
        // 百度地图API功能
        var map = new BMap.Map("allmap");    // 创建Map实例
        map.centerAndZoom(new BMap.Point(116.404, 39.915), 12);  // 初始化地图,设置中心点坐标和地图级别
        //添加地图类型控件
        map.enableScrollWheelZoom(true);     //开启鼠标滚轮缩放

        var p ='';
        var transit = new BMap.DrivingRoute(map, {
            renderOptions: {
                map: map,
                enableDragging : true //起终点可进行拖拽
            },
            onPolylinesSet:function(routes) {
                //console.log(routes);
                p =''; //用来存储折线的点
                for (var j = 0;j<routes.length;j++) {
                    searchRoute = routes[j].getPolyline();//导航路线
                    for (var i= 0;i<searchRoute['ia'].length;i++) {
                        var str = searchRoute['ia'][i]['lng']+','+searchRoute['ia'][i]['lat']+';';
                        p = p+str;
                    }
                }

            }
        });

        $('#add').click(function () {
            var start = $('#start').val();
            var end = $('#end').val();
            transit.search(start,end);
        });

        $('#create').click(function () {

            if (p) {
                layer.open({
                    type: 1,
                    content: '<form class="layui-form" action="">' +
                    '<div class="layui-form-item">'+
                    '<label class="layui-form-label">路线名称</label>'+
                    '<div class="layui-input-block">'+
                    '<input id="lineName" type="text" required  lay-verify="required" placeholder="请输入路线名称" autocomplete="off" class="layui-input">'+
                    '</div>'+
                    '</div></form>'
                    ,btn: ['确定', '取消']
                    ,yes: function(index, layero){
                        //按钮【按钮一】的回调
                        var lineName = $('#lineName').val();
                        console.log(lineName);
                        layer.close(index);
                        $.ajax({
                            url:baseUrl+'/RoadLine/createRoadLine',
                            data:{name:lineName,point:JSON.stringify(p)},
                            dataType: "json",
                            type:'post',
                            success:function (data) {
                                if (data.code==1) {
                                    p ='';
                                    layer.msg('添加成功');
                                } else {
                                    layer.msg(data.msg);
                                }
                            }
                        })
                    }
                    ,btn2: function(index, layero){
                        //按钮【按钮二】的回调

                        //return false 开启该代码可禁止点击该按钮关闭
                    }

                });

            } else {
                layer.msg('请添加路线');
            }


        });
    });

});