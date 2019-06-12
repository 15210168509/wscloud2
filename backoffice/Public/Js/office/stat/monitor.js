/**
 * Created by dev on 2018/5/22.
 */
$(function () {
    layui.use(['form'],function () {
        var form = layui.form;
        var baseUrl = $('#baseUrl').val();
        form.on('select(time)', function(data){
            //报警次数
            getTired_chart();
            //报警分类
            getTiredNo();
            //行为占比
            getTiredType();
            //报警集中时间段
            getTired_chart_time();

        });
        //报警次数
        var tired_chart = echarts.init(document.getElementById('tired_no'));
        var tired_option =
        {
            legend: {},
            title: {
                text: '报警次数'
            },
            tooltip:{
                trigger:'axis'
            },
            toolbox: {
                feature: {
                    dataZoom: {
                        yAxisIndex: 'none'
                    },
                    restore: {},
                    saveAsImage: {}
                }
            },
            dataZoom: [{
                start: 0,
                end: 100,
                handleIcon: 'M10.7,11.9v-1.3H9.3v1.3c-4.9,0.3-8.8,4.4-8.8,9.4c0,5,3.9,9.1,8.8,9.4v1.3h1.3v-1.3c4.9-0.3,8.8-4.4,8.8-9.4C19.5,16.3,15.6,12.2,10.7,11.9z M13.3,24.4H6.7V23h6.6V24.4z M13.3,19.6H6.7v-1.4h6.6V19.6z',
                handleSize: '80%',
                handleStyle: {
                    color: '#fff',
                    shadowBlur: 3,
                    shadowColor: 'rgba(0, 0, 0, 0.6)',
                    shadowOffsetX: 2,
                    shadowOffsetY: 2
                }
            }],
            xAxis: {
                type: 'category',
                data: [],
                splitLine:{
                    show:false
                }
            },
            yAxis: {
                type: 'value',
            },
            series: [{
                name:'报警次数',
                data: [],
                type: 'line',
                smooth: true,
                lineStyle:{
                    color:'#67e0e3'
                },
                itemStyle:{
                    color:'#67e0e3'
                }
            }]
        };
        tired_chart.setOption(tired_option);
        tired_chart.showLoading();
        function getTired_chart() {
            $.ajax({
                type: "post",
                url: baseUrl + "/Driver/driverTiredNumber/driverId/"+$('#driverId').val(),
                dataType: "json",
                data:{timeType:$('#time').val()},
                success: function(data)
                {
                    tired_chart.hideLoading();
                    if(data.code == 1)
                    {
                        tired_chart.setOption({
                            xAxis:{
                                data:data.data.xAxis
                            },
                            series:[{
                                data:data.data.yAxis
                            }]
                        });
                    }

                }
            });
        }
        getTired_chart();

        //报警分类
        var tired_no = echarts.init(document.getElementById('tired_no_realtime'));
        var tired_no_option = {
            title: {
                text: '报警分类',
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
                    type : 'value'
                }
            ],
            series : [
                {
                    name:'报警次数',
                    type:'bar',
                    data:[],
                    //设置柱子的宽度
                    barWidth : 30,
                    //配置样式
                    itemStyle: {
                        //通常情况下：
                        normal:{
                            //每个柱子的颜色即为colorList数组里的每一项，如果柱子数目多于colorList的长度，则柱子颜色循环使用该数组
                            color: function (params){
                                var colorList = ['#c23531','#2f4554', '#61a0a8', '#d48265', '#91c7ae','#749f83',  '#ca8622', '#bda29a','#6e7074', '#546570', '#c4ccd3'];
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
        tired_no.showLoading();
        function getTiredNo() {
            $.ajax({
                type: "post",
                url: baseUrl + "/Driver/driverTiredNoByType",
                dataType: "json",
                data:{driverId:$('#driverId').val(),timeType:$('#time').val()},
                success: function(data)
                {
                    tired_no.hideLoading();
                    if(data.code == 1 && data.data.y.length>0)
                    {
                        tired_no.setOption({
                            xAxis:[{
                                data:data.data.x
                            }],
                            series:[{
                                data:data.data.y,
                            }]
                        });
                    } else {
                        tired_no.setOption({
                            title:{
                                subtext:'暂无数据'
                            },
                            xAxis:[{
                                data:data.data.x
                            }],
                            series:[{
                                data:data.data.y,
                            }]
                        })
                    }

                }
            });
        }
        getTiredNo();
        /*var tiredNoInterval = setInterval(function () {
         getTiredNo();
         }, 10000);*/
        //行为占比
        var tired_type = echarts.init(document.getElementById('tired_type'));
        var tired_type_option = {

            title : {
                text: '行为类型占比统计',
                x:'left'
            },
            tooltip : {
                trigger: 'item',
                formatter: "{a} <br/>{b} : {c} ({d}%)"
            },
            legend: {
                type: 'scroll',
                orient: 'vertical',
                right: 10,
                top: 20,
                bottom: 20,
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
                type: "get",
                url: baseUrl + "/Driver/driverTiredType",
                data:{driverId:$('#driverId').val(),timeType:$('#time').val()},
                dataType: "json",
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
                            title:{
                                subtext:'暂无数据'
                            },
                            series:[{
                                data:data.data
                            }]
                        })
                    }

                }
            });
        };
        getTiredType();
        /*var tiredTypeInterval = setInterval(function () {
         getTiredType();
         }, 10000);*/

        //报警集中时间段
        var tired_chart_time = echarts.init(document.getElementById('tired_no_time'));
        var tired_time_option =
        {
            legend: {
                width:'60%',
            },
            title: {
                text: '报警集中时间段',
            },
            tooltip:{
                trigger:'axis'
            },
            toolbox: {
                feature: {
                    dataZoom: {
                        yAxisIndex: 'none'
                    },
                    restore: {},
                    saveAsImage: {}
                }
            },
            dataZoom: [{
                start: 0,
                end: 100,
                handleIcon: 'M10.7,11.9v-1.3H9.3v1.3c-4.9,0.3-8.8,4.4-8.8,9.4c0,5,3.9,9.1,8.8,9.4v1.3h1.3v-1.3c4.9-0.3,8.8-4.4,8.8-9.4C19.5,16.3,15.6,12.2,10.7,11.9z M13.3,24.4H6.7V23h6.6V24.4z M13.3,19.6H6.7v-1.4h6.6V19.6z',
                handleSize: '80%',
                handleStyle: {
                    color: '#fff',
                    shadowBlur: 3,
                    shadowColor: 'rgba(0, 0, 0, 0.6)',
                    shadowOffsetX: 2,
                    shadowOffsetY: 2
                }
            }],
            xAxis: {
                type: 'category',
                data: [],
                splitLine:{
                    show:false
                }
            },
            yAxis: {
                type: 'value'
            },
            series: []
        };
        tired_chart_time.setOption(tired_time_option);
        tired_chart_time.showLoading();
        function getTired_chart_time() {
            $.ajax({
                type: "get",
                url: baseUrl + "/Driver/driverTiredByTimeGroup",
                dataType: "json",
                data:{driverId:$('#driverId').val(),timeType:$('#time').val()},
                success: function(data)
                {
                    tired_chart_time.hideLoading();
                    if(data.code == 1 && data.data.length>0)
                    {
                        tired_time_option.series = data.data;
                        tired_time_option.xAxis = {
                            type: 'category',
                            data: [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23]
                        };
                        tired_chart_time.setOption(tired_time_option);
                    } else {
                        tired_time_option.series = data.data;
                        tired_time_option.title.subtext = '暂无数据';
                        tired_chart_time.setOption(tired_time_option,true);
                    }

                }
            });
        }
        getTired_chart_time();

        //疲劳值
        var tired_value = echarts.init(document.getElementById('tired_value'));
        var tired_value_option = {
            title: {
                text: '疲劳值'
            },
            tooltip: {
                trigger: 'axis'
            },
            legend: {

            },
            toolbox: {
                feature: {
                    dataZoom: {
                        yAxisIndex: 'none'
                    },
                    restore: {},
                    saveAsImage: {}
                }
            },

            xAxis: {
                type: 'category',
                boundaryGap: false,
                data: [],
                splitLine:{
                    show:false
                }
            },
            yAxis: {
                max:100,
                min:0,
                type: 'value'
            },
            series: []
        };

        tired_value.setOption(tired_value_option);
        tired_value.showLoading();
        function getTiredValue () {
            $.ajax({
                type: "post",
                url: baseUrl + "/Driver/driverTiredValue",
                dataType: "json",
                data:{driverId:$('#driverId').val(),timeType:$('#time').val()},
                success: function(data)
                {
                    tired_value.hideLoading();
                    if(data.code == 1)
                    {
                        tired_value.setOption({
                            xAxis:{
                                data :data.data.x
                            },
                            series:[{
                                name:'疲劳值',
                                type:'line',
                                smooth:'smooth',
                                symbol:'none',
                                data:data.data.y,
                            }]
                        });
                    } else {
                        tired_value.setOption({
                            title:{
                                subtext:'暂无数据'
                            }
                        })
                    }

                }
            });
        };
        getTiredValue();
        /*var tiredValueInterval = setInterval(function () {
         getTiredValue();
         }, 10000);*/

        /****************************推送消息*********************************/

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

                getTiredNo();
                getTiredType();
            }

        }

        function onConnectionLost(responseObject) {
            if (responseObject.errorCode !== 0) {
                console.log(client.clientId + ": " + responseObject.errorCode + "\n");
            }
        }
    });

});