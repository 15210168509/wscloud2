/**
 * Created by dev on 2018/5/22.
 */
var baseUrl = $('#baseUrl').val();
$(function () {

    var height = (document.documentElement.clientHeight-170-(document.documentElement.clientHeight)*0.02);
    $('#title').height(height);
    function show(){
        if(document.getElementById("nowDiv")!=null){
            var date = new Date(); //日期对象
            var now = "";
            now = date.getFullYear()+"年";
            now = now + ((date.getMonth()+1) < 10 ? '0'+(date.getMonth()+1): (date.getMonth()+1)) +"月";
            now = now + (date.getDate() < 10 ? '0'+date.getDate():date.getDate())+"日 ";
            now = now + (date.getHours() < 10 ? '0'+ date.getHours() : date.getHours()) +":";
            now = now + (date.getMinutes() < 10 ? '0' + date.getMinutes() : date.getMinutes()) +":";
            now = now + (date.getSeconds() < 10 ? '0' + date.getSeconds() : date.getSeconds());
            document.getElementById("nowDiv").innerHTML = now;
        }
    }
    var x = setInterval(function () {
        show();
    },1000);
   /* var test = setInterval(function () {
        $('#msg').prepend('<div class="layui-col-md4">法大师</div><div class="layui-col-md4">2018-15-26</div><div class="layui-col-md4">法大师傅士大夫</div>');
    },2000);*/
    var baseUrl = $('#baseUrl').val();
    var tired_no = echarts.init(document.getElementById('tired_no'),'westeros');
    var tired_no_option = {
        title: {

        },
        tooltip : {
            trigger: 'axis',
            axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                type : 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
            }
        },
        xAxis : [
            {
                type : 'category',
                data : [],
                axisTick: {
                    alignWithLabel: true
                },
                splitLine:{
                    show:false
                }
            }
        ],
        yAxis : [
            {
                type : 'value',
                minInterval: 1
            }
        ],
        series : [
            {
                name:'报警次数',
                type:'bar',
                data:[],
                itemStyle: {
                    //通常情况下：
                    normal:{
                        //每个柱子的颜色即为colorList数组里的每一项，如果柱子数目多于colorList的长度，则柱子颜色循环使用该数组
                        color: function (params){
                            var colorList = [
                                "#9b8bba",
                                "#e098c7",
                                "#8fd3e8",
                                "#71669e",
                                "#cc70af",
                                "#7cb4cc"
                            ];
                            return colorList[params.dataIndex];
                        }
                    },
                    //鼠标悬停时：
                    emphasis: {
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: 'rgba(0, 0, 0, 0.5)'
                    }
                },
            }
        ]
    };
    tired_no.setOption(tired_no_option);
    getTiredNo();
    function getTiredNo() {
        $.ajax({
            type: "post",
            url: baseUrl + "/Stat/statTiredNo",
            dataType: "json",
            success: function(data)
            {
                if(data.code == 1 && data.data.y.length>0)
                {
                    tired_no.setOption({
                        title: {
                            subtext:'',
                        },
                        xAxis:[{
                            data:data.data.x
                        }],
                        series:[{
                            data:data.data.y
                        }]
                    });

                } else {
                    tired_no.setOption({
                        title: {
                            subtext:'暂无数据',

                        }
                    });
                }

            }
        });
    }
    /*var tiredNoInterval = setInterval(function () {
        getTiredNo();
     }, 10000);*/
    //行为分类
    var tired_type = echarts.init(document.getElementById('tired_type'),'westeros');
    var tired_type_option = {

        title : {

        },
        tooltip : {
            trigger: 'item',
            formatter: "{a} <br/>{b} : {c} ({d}%)"
        },
        legend: {
            /*type: 'scroll',
            orient: 'vertical',
            right: 10,
            top: 20,
            bottom: 20,*/
            show:false
        },
        series : [
            {
                name: '疲劳类型',
                type: 'pie',
                radius : ['45%', '60%'],
                data: [],
                itemStyle: {
                    emphasis: {
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: 'rgba(0, 0, 0, 0.5)'
                    }
                }
            }
        ]
    };
    tired_type.setOption(tired_type_option);
    tired_type.showLoading();

    function getTiredType () {
        $.ajax({
            type: "post",
            url: baseUrl + "/Stat/showTiredType",
            dataType: "json",
            data:{timeType:'today'},
            success: function(data)
            {
                tired_type.hideLoading();
                if(data.code == 1)
                {
                    tired_type.setOption({
                        series:[{
                            data:data.data
                        }]
                    });
                } else {
                    tired_type.setOption({
                        series:[{
                            data:[{name:'暂无数据',value:0}]
                        }]
                    });
                }

            }
        });
    };
    getTiredType();
    /*var tiredTypeInterval = setInterval(function () {
        getTiredType();
    }, 10000);*/
    //行为类型end

    //疲劳值
    //var tired_value = echarts.init(document.getElementById('tired_value'),'westeros');
    //var tired_value_option = {
    //    title: {
    //
    //    },
    //    tooltip: {
    //        trigger: 'axis',
    //        axisPointer: {
    //            type: 'cross',
    //            label: {
    //                backgroundColor: '#6a7985'
    //            }
    //        }
    //    },
    //
    //    xAxis: {
    //        type: 'category',
    //        boundaryGap: false,
    //        data: []
    //    },
    //    yAxis: {
    //        max:100,
    //        min:0,
    //        type: 'value',
    //
    //    },
    //    series: []
    //};
    //tired_value.setOption(tired_value_option,true);

    var tiredParam = [];
    var tiredValueInterval = null;
    var setLegend = false;
    function getTiredValue () {
        if (tiredValueInterval != null) {
            clearInterval(tiredValueInterval);
            tiredValueInterval = null;
            if (tiredParam.length == 0) {
                return ;
            }
        }


        tiredValueInterval = setInterval(function () {
            ajaxGetTiredValue();
        }, 4000);
    }

    function ajaxGetTiredValue() {
        $.ajax({
            type: "post",
            url: baseUrl + "/Stat/showTiredValue",
            data:{'device':tiredParam},
            dataType: "json",
            success: function(data)
            {
                if(data.code == 1)
                {
                    if (setLegend) {
                        tired_value_option.legend = {
                            type:'scroll',
                        }
                    }
                    tired_value_option.series = data.data;
                    tired_value.setOption(tired_value_option,setLegend);
                    setLegend = false;
                }
            }
        });
    }

    //地图
    var map = echarts.init(document.getElementById('map'));
    var map_option = {
        bmap: {
            center: [108.953098279,34.2777998978],
            zoom: 5,
            roam: true,
            mapStyle: {
                styleJson: [
                    {
                        'featureType': 'land',     //调整土地颜色
                        'elementType': 'geometry',
                        'stylers': {
                            'color': '#020213'
                        }
                    },
                    {
                        'featureType': 'building',   //调整建筑物颜色
                        'elementType': 'geometry',
                        'stylers': {
                            'color': '#04406F'
                        }
                    },
                    {
                        'featureType': 'building',   //调整建筑物标签是否可视
                        'elementType': 'labels',
                        'stylers': {
                            'visibility': 'off'
                        }
                    },
                    {
                        'featureType': 'highway',     //调整高速道路颜色
                        'elementType': 'geometry',
                        'stylers': {
                            'color': '#015B99'
                        }
                    },
                    {
                        'featureType': 'highway',    //调整高速名字是否可视
                        'elementType': 'labels',
                        'stylers': {
                            'visibility': 'off'
                        }
                    },
                    {
                        'featureType': 'arterial',   //调整一些干道颜色
                        'elementType': 'geometry',
                        'stylers': {
                            'color':'#003051'
                        }
                    },
                    {
                        'featureType': 'arterial',
                        'elementType': 'labels',
                        'stylers': {
                            'visibility': 'off'
                        }
                    },
                    {
                        'featureType': 'green',
                        'elementType': 'geometry',
                        'stylers': {
                            'visibility': 'off'
                        }
                    },
                    {
                        'featureType': 'water',
                        'elementType': 'geometry',
                        'stylers': {
                            'color': '#044161'
                        }
                    },
                    {
                        'featureType': 'subway',    //调整地铁颜色
                        'elementType': 'geometry.stroke',
                        'stylers': {
                            'color': '#003051'
                        }
                    },
                    {
                        'featureType': 'subway',
                        'elementType': 'labels',
                        'stylers': {
                            'visibility': 'off'
                        }
                    },
                    {
                        'featureType': 'railway',
                        'elementType': 'geometry',
                        'stylers': {
                            'visibility': 'off'
                        }
                    },
                    {
                        'featureType': 'railway',
                        'elementType': 'labels',
                        'stylers': {
                            'visibility': 'off'
                        }
                    },
                    {
                        'featureType': 'all',     //调整所有的标签的边缘颜色
                        'elementType': 'labels.text.stroke',
                        'stylers': {
                            'color': '#313131'
                        }
                    },
                    {
                        'featureType': 'all',     //调整所有标签的填充颜色
                        'elementType': 'labels.text.fill',
                        'stylers': {
                            'color': '#FFFFFF'
                        }
                    },
                    {
                        'featureType': 'manmade',
                        'elementType': 'geometry',
                        'stylers': {
                            'visibility': 'off'
                        }
                    },
                    {
                        'featureType': 'manmade',
                        'elementType': 'labels',
                        'stylers': {
                            'visibility': 'off'
                        }
                    },
                    {
                        'featureType': 'local',
                        'elementType': 'geometry',
                        'stylers': {
                            'visibility': 'off'
                        }
                    },
                    {
                        'featureType': 'local',
                        'elementType': 'labels',
                        'stylers': {
                            'visibility': 'off'
                        }
                    },
                    {
                        'featureType': 'subway',
                        'elementType': 'geometry',
                        'stylers': {
                            'lightness': -65
                        }
                    },
                    {
                        'featureType': 'railway',
                        'elementType': 'all',
                        'stylers': {
                            'lightness': -40
                        }
                    },
                    {
                        'featureType': 'boundary',
                        'elementType': 'geometry',
                        'stylers': {
                            'color': '#8b8787',
                            'weight': '1',
                            'lightness': -29
                        }
                    }]
            }
        },
        series: [],
        tooltip: {
            padding: 10,
            backgroundColor: '#222',
            borderColor: '#777',
            borderWidth: 1,
            triggerOn: 'click',
            formatter: function (obj) {
                var value = obj.value;
                return  '驾驶车辆：' + value[2]+ '<br>'
                    +   '当时车速：' + value[3]['speed'] + 'km/h<br>'
                    + '当时位置：' + value[3]['position'] + '<br>'
                    + '定位时间：' + value[3]['gps_time'] + '<br>';

            },
            position: function(point, params, dom) {
                var width = $(dom).width();     //获取tooltip原来的width
                var m = $("#mapStoreClass");    //获取我们自定义模拟的tooltip dom
                $(dom).css("position","initial");    //将原来的tooltip设置为initial *重要，为了不让原来的tooltip dom乱跑
                $(m).html(dom);                      //将更改好的dom放入我们模拟的tooltip dom
                $(m).css("left",point[0]+20);        //设置模拟dom显示位置，此为鼠标位置
                $(m).css("top",point[1]+20);         //设置模拟dom显示位置，此为鼠标位置
                $(m).css("width",width+15);          //设置模拟dom宽度
            }
        }
    };
    map.setOption(map_option);
    var vehicleList = null;
    function getVehicle() {

        if (vehicleList != null ){
            clearInterval(vehicleList);
            vehicleList = null;
            if (tiredParam.length == 0) {
                return ;
            }
        }

        vehicleList  = setInterval(function () {
            ajaxGetVehicle();
        },4000);


    }

    function ajaxGetVehicle() {
        $.ajax({
            type: "post",
            url: baseUrl + "/Stat/showVehicle",
            data: {device:tiredParam},
            dataType: "json",
            success: function(data)
            {
                if(data.code == 1)
                {
                    if(data.data.driving) {
                        if (data.data.driving.north) {
                            data.data.driving.north.forEach(function (val,index) {
                                var result = gcoord.transform(
                                    [ val[0], val[1] ],     // 经纬度坐标
                                    gcoord.GCJ02,                 // 当前坐标系
                                    gcoord.BD09                   // 目标坐标系
                                );
                                val[0] = result[0];
                                val[1] = result[1];
                            });
                        }
                        if (data.data.driving.south) {
                            data.data.driving.south.forEach(function (val,index) {
                                var result = gcoord.transform(
                                    [ val[0], val[1] ],     // 经纬度坐标
                                    gcoord.GCJ02,                 // 当前坐标系
                                    gcoord.BD09                   // 目标坐标系
                                );
                                val[0] = result[0];
                                val[1] = result[1];
                            });
                        }
                        if (data.data.driving.east) {
                            data.data.driving.east.forEach(function (val,index) {
                                var result = gcoord.transform(
                                    [ val[0], val[1] ],     // 经纬度坐标
                                    gcoord.GCJ02,                 // 当前坐标系
                                    gcoord.BD09                   // 目标坐标系
                                );
                                val[0] = result[0];
                                val[1] = result[1];
                            });
                        }
                        if (data.data.driving.west) {
                            data.data.driving.west.forEach(function (val,index) {
                                var result = gcoord.transform(
                                    [ val[0], val[1] ],     // 经纬度坐标
                                    gcoord.GCJ02,                 // 当前坐标系
                                    gcoord.BD09                   // 目标坐标系
                                );
                                val[0] = result[0];
                                val[1] = result[1];
                            });
                        }
                    }
                    if (data.data.stop) {
                        if (data.data.stop.north) {
                            data.data.stop.north.forEach(function (val,index) {
                                var result = gcoord.transform(
                                    [ val[0], val[1] ],     // 经纬度坐标
                                    gcoord.GCJ02,                 // 当前坐标系
                                    gcoord.BD09                   // 目标坐标系
                                );
                                val[0] = result[0];
                                val[1] = result[1];
                            });
                        }
                        if (data.data.stop.south) {
                            data.data.stop.south.forEach(function (val,index) {
                                var result = gcoord.transform(
                                    [ val[0], val[1] ],     // 经纬度坐标
                                    gcoord.GCJ02,                 // 当前坐标系
                                    gcoord.BD09                   // 目标坐标系
                                );
                                val[0] = result[0];
                                val[1] = result[1];
                            });
                        }
                        if (data.data.stop.east) {
                            data.data.stop.east.forEach(function (val,index) {
                                var result = gcoord.transform(
                                    [ val[0], val[1] ],     // 经纬度坐标
                                    gcoord.GCJ02,                 // 当前坐标系
                                    gcoord.BD09                   // 目标坐标系
                                );
                                val[0] = result[0];
                                val[1] = result[1];
                            });
                        }
                        if (data.data.stop.west) {
                            data.data.stop.west.forEach(function (val,index) {
                                var result = gcoord.transform(
                                    [ val[0], val[1] ],     // 经纬度坐标
                                    gcoord.GCJ02,                 // 当前坐标系
                                    gcoord.BD09                   // 目标坐标系
                                );
                                val[0] = result[0];
                                val[1] = result[1];
                            });
                        }
                    }

                    map.setOption({
                        series:[
                            {
                                //driving_north
                                type: 'scatter',
                                coordinateSystem: 'bmap',
                                data: data.data.driving.north,
                                symbol:'image:///Public/Images/office/gps_icon/green_north_24.png',
                                symbolSize:[28,28],
                                label: {
                                    emphasis: {
                                        show: true,
                                        formatter: function (param) {
                                            return param.data[2];
                                        },
                                        position: 'top'
                                    }
                                }
                            },
                            {
                                //driving_south
                                type: 'scatter',
                                coordinateSystem: 'bmap',
                                data: data.data.driving.south,
                                symbol:'image:///Public/Images/office/gps_icon/green_south_24.png',
                                symbolSize:[28,28],
                                label: {
                                    emphasis: {
                                        show: true,
                                        formatter: function (param) {
                                            return param.data[2];
                                        },
                                        position: 'top'
                                    }
                                }
                            },
                            {
                                //driving_east
                                type: 'scatter',
                                coordinateSystem: 'bmap',
                                data: data.data.driving.east,
                                symbol:'image:///Public/Images/office/gps_icon/green_east_24.png',
                                symbolSize:[28,28],
                                label: {
                                    emphasis: {
                                        show: true,
                                        formatter: function (param) {
                                            return param.data[2];
                                        },
                                        position: 'top'
                                    }
                                }
                            },
                            {
                                //driving_west
                                type: 'scatter',
                                coordinateSystem: 'bmap',
                                data: data.data.driving.west,
                                symbol:'image:///Public/Images/office/gps_icon/green_west_24.png',
                                symbolSize:[28,28],
                                label: {
                                    emphasis: {
                                        show: true,
                                        formatter: function (param) {
                                            return param.data[2];
                                        },
                                        position: 'top'
                                    }
                                }
                            },

                            {
                                //stop北
                                type: 'scatter',
                                coordinateSystem: 'bmap',
                                data: data.data.stop.north,
                                symbol:'image:///Public/Images/office/gps_icon/blue_north_24.png',
                                symbolSize:[28,28],
                                label: {
                                    emphasis: {
                                        show: true,
                                        formatter: function (param) {
                                            return param.data[2];
                                        },
                                        position: 'top'
                                    }
                                }
                            },
                            {
                                //stop东
                                type: 'scatter',
                                coordinateSystem: 'bmap',
                                data: data.data.stop.east,
                                symbol:'image:///Public/Images/office/gps_icon/blue_east_24.png',
                                symbolSize:[28,28],
                                label: {
                                    emphasis: {
                                        show: true,
                                        formatter: function (param) {
                                            return param.data[2];
                                        },
                                        position: 'top'
                                    }
                                }
                            },
                            {
                                //stop南
                                type: 'scatter',
                                coordinateSystem: 'bmap',
                                data: data.data.stop.south,
                                symbol:'image:///Public/Images/office/gps_icon/blue_south_24.png',
                                symbolSize:[28,28],
                                label: {
                                    emphasis: {
                                        show: true,
                                        formatter: function (param) {
                                            return param.data[2];
                                        },
                                        position: 'top'
                                    }
                                }
                            },
                            {
                                //stop西
                                type: 'scatter',
                                coordinateSystem: 'bmap',
                                data: data.data.stop.west,
                                symbol:'image:///Public/Images/office/gps_icon/blue_west_24.png',
                                symbolSize:[28,28],
                                label: {
                                    emphasis: {
                                        show: true,
                                        formatter: function (param) {
                                            return param.data[2];
                                        },
                                        position: 'top'
                                    }
                                }
                            },
                            {
                                //offLine北
                                type: 'scatter',
                                coordinateSystem: 'bmap',
                                data: data.data.offLine.north,
                                symbol:'image:///Public/Images/office/gps_icon/white_north_24.png',
                                symbolSize:[28,28],
                                label: {
                                    emphasis: {
                                        show: true,
                                        formatter: function (param) {
                                            return param.data[2];
                                        },
                                        position: 'top'
                                    }
                                }
                            },
                            {
                                //offLine南
                                type: 'scatter',
                                coordinateSystem: 'bmap',
                                data: data.data.offLine.south,
                                symbol:'image:///Public/Images/office/gps_icon/white_west_24.png',
                                symbolSize:[28,28],
                                label: {
                                    emphasis: {
                                        show: true,
                                        formatter: function (param) {
                                            return param.data[2];
                                        },
                                        position: 'top'
                                    }
                                }
                            },
                            {
                                //offLine东
                                type: 'scatter',
                                coordinateSystem: 'bmap',
                                data: data.data.offLine.east,
                                symbol:'image:///Public/Images/office/gps_icon/white_east_24.png',
                                symbolSize:[28,28],
                                label: {
                                    emphasis: {
                                        show: true,
                                        formatter: function (param) {
                                            return param.data[2];
                                        },
                                        position: 'top'
                                    }
                                }
                            },
                            {
                                //offLine西
                                type: 'scatter',
                                coordinateSystem: 'bmap',
                                data: data.data.offLine.west,
                                symbol:'image:///Public/Images/office/gps_icon/white_south_24.png',
                                symbolSize:[28,28],
                                label: {
                                    emphasis: {
                                        show: true,
                                        formatter: function (param) {
                                            return param.data[2];
                                        },
                                        position: 'top'
                                    }
                                }
                            },


                        ]
                    })

                } else {
                }


            }
        });
    }

    /*map.on('click', function (params) {

    });*/

    //////////////////

    layui.use(['form','layer','tree'], function(){

        var baseUrl = $('#baseUrl').val();

        var form = layui.form
            ,layer = layui.layer
            ,tree = layui.tree;

        $.ajax({
            type: "post",
            url: baseUrl + "/Stat/typeGroupsLists",
            data: {type:10},
            dataType: "json",
            success: function(data)
            {
                if(data.code == 1)
                {
                    layui.tree({
                        elem: '#demo'               //传入元素选择器
                        ,nodes: data.data
                        ,click: function(node){
                            /*if ($('#input_'+node.group_id+'_'+node.id).is(':checked')){
                                $('#input_'+node.group_id+'_'+node.id).prop('checked',false);
                            } else {
                                $('#input_'+node.group_id+'_'+node.id).prop('checked',true);
                            }*/
                            var obj = $('.input_device');
                            var arr = [];

                            obj.each(function(){
                                if ($(this).is(':checked')) {
                                    arr.push($(this).attr('data'));
                                }
                            });
                            setLegend = true;
                            tiredParam = arr;
                            ajaxGetTiredValue();
                            getTiredValue();
                            ajaxGetVehicle();
                            getVehicle();
                        }
                    });
                } else {
                    layer.msg(data.msg);
                }

            }
        });

    });

    $('#allChecked').click(function () {
        if ($(this).is(':checked')) {
            $('.input_device').prop('checked','checked')
        } else {
            $('.input_device').attr("checked",false);
        }
        var obj = $('.input_device');
        var arr = [];

        obj.each(function(){
            if ($(this).is(':checked')) {
                arr.push($(this).attr('data'));
            }
        });
        setLegend = true;
        tiredParam = arr;
        ajaxGetTiredValue();
        getTiredValue();
        ajaxGetVehicle();
        getVehicle();
    });

    $('#closeVehicleMsg').click(function () {
        $('.vehicleMsg').css('display','none');
        $('#msgBody').html('');
    });

    ///////////////
    var topic = $('#adminTopic').val();
    var host = $('#mqttServer').val();
    var host_port = $('#mqttServerPort').val();
    var client = new Messaging.Client(host, Number(host_port), "admin-"+(Math.floor(Math.random() * 100000)));
    client.onConnect = onConnect;

    client.onMessageArrived = onMessageArrived;
    client.onConnectionLost = onConnectionLost;

    client.connect({
        onSuccess:onConnect,
        onFailure:onFailure
    });
    function onConnect(frame) {
        client.subscribe(topic);
    };

    function onFailure(failure) {
        console.log(failure.errorMessage);
    }

    function onMessageArrived(message){
        if($('#msgContainer').hasClass('msgContainer')){
            $('#msgContainer').removeClass('msgContainer');
        }
        $('#msgContainer').addClass('msgContainer');
        var msgObj = JSON.parse(message.payloadString);

        if (msgObj.msgType == 20){
            show_num1(sum1);
            getTiredNo();
            getTiredType();
            var msg = msgObj.data;

            $('#msg').prepend('<div class="layui-col-md12 tiredMsg"><div class="layui-col-md3">'+msg.vehicle_no+'</div><div class="layui-col-md3">'+msg.driver_name+'</div><div class="layui-col-md3">'+msg.time_text+'</div><div class="layui-col-md3">'+msg.code_text+'</div></div>');
            if (msg.tired_value > $('#tiredWarningNumber').val()) {
                $('#msgBody').html(
                    '<p><span class="msgTitle">车牌号:</span>'+msg.vehicle_no+'</p>'+
                    '<p><span class="msgTitle">疲劳值:</span>'+msg.tired_value+'</p>'+
                    '<p><span class="msgTitle">司机:</span>'+msg.driver_name+'</p>'+
                    '<p><span class="msgTitle">预警类型:</span>'+msg.code_text+'</p>'+
                    '<p><span class="msgTitle">速度:</span>'+msg.speed+' km/h</p>'+
                    '<p><span class="msgTitle">位置:</span>'+msg.location+'</p>'
                );
                $('.vehicleMsg').css('display','block');
                setTimeout(function () {
                    $('.vehicleMsg').css('display','none');
                    $('#msgBody').html('');
                }, 10000)
            }

        }

    }



    function onConnectionLost(responseObject) {
        if (responseObject.errorCode !== 0) {
            console.log(client.clientId + ": " + responseObject.errorCode + "\n");
        }
    }

    var sum1 = $('#sumTiredNo').val();
    var sum = parseInt(sum1);

    var isScroll = '1,1,1,1,1,1,1';
    show_num1(sum1);
        /*setInterval(function(){
            show_num1(sum1)
        },1000);*/


    function show_num1(n) {


        var it = $(".t_num1 i");
        var len = String(n).length;

        if(it.length < n.length) {
            $(".t_num1").prepend("<i></i>");
            isScroll = '1,'+isScroll;
        }

        for(var i = 0; i < len; i++) {

            var num = String(n).charAt(i);

            //根据数字图片的高度设置相应的值
            var y = -parseInt(num) * 58;
            var obj = $(".t_num1 i").eq(i);


            var isScrollArr = isScroll.split(',');
            if(isScrollArr[i] == '1') {
                if(num != 0){
                    obj.animate({
                        backgroundPosition: '(0 ' + String(y) + 'px)'
                    }, 'slow', 'swing', function() {});
                } else {

                    obj.css('background-position','0px 0px');
                    //obj.stop(true);
                }



            } else {

                obj.animate({
                    backgroundPosition: '(0 -580px)'
                }, 'slow', 'swing', function() {

                    obj.css('background-position','0px 0px');

                });


            }


        }

        for(var i = 0; i < len; i++) {
            var num = String(n).charAt(i);
            if(i == 0) {
                isScroll = '';
            }

            if(i<len - 1) {
                if(num == 9 && (String(n).charAt(i+1) == 9 || String(n).charAt(i+1) == '')) {
                    isScroll += 0 + ',';
                } else{
                    isScroll += 1 + ',';
                }
            } else {

                if(num == 9 && (String(n).charAt(i+1) == 9 || String(n).charAt(i+1) == '')) {
                    isScroll += 0 + '';
                } else{
                    isScroll += 1 + '';
                }

            }


        }


        sum= sum+1;
        if (n.length>String(sum).length){
            sum1 = sum1.substring(0,n.length-String(sum).length)+""+sum;
        } else {
            sum1 = sum + "";
        }

    }

});

