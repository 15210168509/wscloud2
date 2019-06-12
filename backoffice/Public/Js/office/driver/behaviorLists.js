
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
    var player;
    var name = $('#driver_name'),behaviorTime = $('#behaviorTime');
    var code = $('#code'), vehicleNo = $('#vehicleNo'), deviceNo = $('#deviceNo');
    var data = { startTime: 'null', endTime: 'null',name:"null",phone:"null",pageNo:1,pageSize:10,code:"null",vehicleNo:vehicleNo.val().length>0 ? vehicleNo.val() : "null", deviceNo:deviceNo.val().length>0 ? deviceNo.val() : "null"};
    var behaviorData = null;
    //持此请求
    setLists();
    /**
     * 检索列表
     */
    function setLists() {
        $('#behaviorItemBody').html('<div style="margin: 10px;">查询数据中...</div>');


        timeVal = behaviorTime.val();


        if (timeVal != '') {
            var time = timeVal.split(" - ");
            if (time.length == 2) {
                //var patrn = /^[1-9]{1}[0-9]{2,3}\-[0-9]{2}\-[0-9]{2}\s\[0-9]{2}:\s\[0-9]{2}:\s\[0-9]{2}$/i;
                var startTime = $.trim(time[0]), endTime = $.trim(time[1]);
               // var st = patrn.exec(startTime), en = patrn.exec(endTime);
                //if (st != null && en != null) {
                    data.startTime = startTime;
                    data.endTime   = endTime;
               // }
            }
        }

        // 获取数据
        $.ajax({
            type: "post",
            url: baseUrl+"/Driver/ajaxGetBehaviorLists",
            data: data,
            dataType: "json",
            success: function(data)
            {
                if(data.code == 1 && parseInt(data.data.totalRecord) > 0)
                {
                    behaviorData = data.data.dataList;
                    calcPages(data.data.totalRecord);           //分页，
                    setListsData(data.data.dataList);           //数据显示
                }
                else
                {
                    calcPages(0);           //分页，
                    $('#behaviorItemBody').html('<div style="margin: 10px;">未查询到数据</div>');
                }
            }
        });
    }

    /**
     * 计算分页
     */

    function calcPages(totalRecord) {
        //总页数低于页码总数
        laypage.render({
            elem: 'pageContainer'
            ,limit:data.pageSize
            ,count: totalRecord //数据总数
            ,curr: data.pageNo
            ,layout: ['count', 'prev', 'page', 'next', 'skip']
            ,jump: function(obj, first){
                if ( data.pageNo != obj.curr){
                    data.pageNo = obj.curr;
                    setLists();
                }
            }
        });
    }

    /**
     * 设置数据
     */
    function setListsData(data) {

        var tem = '<div class="item" data-index="##index##">';
        tem += '<div class="photo">';
        tem += '<div class="img">';
        tem += '<div>##videoImg##</div>';
        tem += '<img class="b-img" src="##photo##" />';
        tem += '<div class="over">';
        tem += '<div class="func">';
        tem += '<a class="image-zoom" href="##photo##"><i class="fa fa-search"></i></a>';
        tem += '</div>';
        tem += '</div>';
        tem += '</div>';
        tem += '<div class="head" data-index="##index##">';
        tem += '<div class="layui-col-md12">';
        tem += '<span class="layui-col-md12"><span class="layui-col-md3 layui-col-xs12 layui-col-sm6"><b>司机姓名</b></span><span class="layui-col-md9 layui-col-xs12 layui-col-sm6 behavior-value">##name##</span></span>';
        tem += '<span class="layui-col-md12"><span class="layui-col-md3 layui-col-xs12 layui-col-sm6"><b>驾驶车辆</b></span><span class="layui-col-md9 layui-col-xs12 layui-col-sm6 behavior-value">##vehicle_no##</span></span>';/*
        tem += '<span class="layui-col-md12"><span class="layui-col-md3 layui-col-xs12 layui-col-sm6"><b>行为类型</b></span><span class="layui-col-md9 layui-col-xs12 layui-col-sm6 behavior-value">##type##</span></span>';*/
        tem += '<span class="layui-col-md12"><span class="layui-col-md3 layui-col-xs12 layui-col-sm6"><b>驾驶行为</b></span><span class="layui-col-md9 layui-col-xs12 layui-col-sm6 behavior-value">##code##</span></span>';/*
        tem += '<span class="layui-col-md12"><span class="layui-col-md3 layui-col-xs12 layui-col-sm6"><b>行为级别</b></span><span class="layui-col-md9 layui-col-xs12 layui-col-sm6 behavior-value">##level##</span></span>';*/
        tem += '<span class="layui-col-md12"><span class="layui-col-md3 layui-col-xs12 layui-col-sm6"><b>当时车速</b></span><span class="layui-col-md9 layui-col-xs12 layui-col-sm6 behavior-value">##kmh## km/h</span></span>';
        tem += '<span class="layui-col-md12"><span class="layui-col-md3 layui-col-xs12 layui-col-sm6"><b>当时位置</b></span><span class="layui-col-md9 layui-col-xs12 layui-col-sm6 behavior-value"><span style="width:60%; white-space:nowrap; text-overflow:ellipsis" title="##location##">##location##</span></span>';
        tem += '<span class="layui-col-md12"><span class="layui-col-md3 layui-col-xs12 layui-col-sm6"><b>定位时间</b></span><span class="layui-col-md9 layui-col-xs12 layui-col-sm6 behavior-value">##time##</span></span>';
        tem += '<span class="layui-col-md12" style="display: none"><span class="layui-col-md3 layui-col-xs12 layui-col-sm6"><b>定位时间</b></span><span class="layui-col-md9 layui-col-xs12 layui-col-sm6 behavior-value">##extra_info##</span></span>';
        tem += '</div>';
        tem += '</div>';
        tem += '</div>';
        tem += '</div>';

        $('#behaviorItemBody').html('');

        $.each(data, function (i) {
            var temStr = tem;
            var o = data[i];
            if (o.path == '') {
                temStr = temStr.replace(/##photo##/g, '/Public/Images/office/behavior_default.png');
            } else {
                temStr = temStr.replace(/##photo##/g, o.path+'?x-oss-process=image/resize,m_fill,h_220,w_300');
            }
            if (o.video_path != '') {
                temStr = temStr.replace(/##videoImg##/g, '<img class="video" src="/Public/Images/office/video.png" style="width: 32px;height: 32px" data="'+o.video_path+'">');
            }
            else {
                temStr = temStr.replace(/##videoImg##/g,'')
            }
            temStr = temStr.replace(/##name##/g, o.name);
            temStr = temStr.replace(/##vehicle_no##/g, o.vehicle_no);
            /*temStr = temStr.replace(/##type##/g, o.type_text);*/
            temStr = temStr.replace(/##code##/g, o.code_text);/*
            temStr = temStr.replace(/##level##/g, o.level_text);*/
            temStr = temStr.replace(/##kmh##/g, o.kmh);
            temStr = temStr.replace(/##location##/g, o.location);
            temStr = temStr.replace(/##index##/g, i);
            temStr = temStr.replace(/##time##/g, o.location_time);
            temStr = temStr.replace(/##extra_info##/g, o.extra_info);
            $('#behaviorItemBody').append(temStr);
        });

        /**
         * 弹出地图
         */
        var behaviorBox;
        $('.head').click(function () {

            var data = behaviorData[$(this).data('index')];

            var tem =
                '<div class="behavior-info">' +
                    '<div class="layui-col-sm12">' +
                        '<div class="layui-col-sm12 driver-behavior">' +
                            '<img class="layui-col-sm6" src="'+data.path+'?x-oss-process=image/resize,m_fill,h_360,w_480"/>' +
                            '<div class="layui-col-sm6 layui-bg-gray behavior-warming">' +
                                '<span class="layui-col-sm12"><span class="layui-col-md2 layui-icon layui-icon-username" ></span><span  class="layui-col-md10">'+data.name+'</span></span>'+
                                '<span class="layui-col-sm12"><span class="layui-col-md2 layui-icon layui-icon-cart" ></span><span  class="layui-col-md10">'+data.vehicle_no+'</span></span>'+/*
                                '<span class="layui-col-sm12"><span class="layui-col-md2 layui-icon layui-icon-chart" ></span><span  class="layui-col-md10">'+data.type_text+'</span></span>'+*/
                                '<span class="layui-col-sm12"><span class="layui-col-md2 layui-icon layui-icon-app" ></span><span  class="layui-col-md10">'+data.code_text+'</span></span>'+/*
                                '<span class="layui-col-sm12"><span class="layui-col-md2 layui-icon layui-icon-senior" ></span><span  class="layui-col-md10">'+data.level_text+'</span></span>'+*/
                                '<span class="layui-col-sm12"><span class="layui-col-md2 layui-icon layui-icon-console"></span><span  class="layui-col-md10">'+data.kmh+'km/h</span></span>'+
                                '<span class="layui-col-sm12"><span class="layui-col-md2 layui-icon layui-icon-location"></span><span  class="layui-col-md10">'+data.location+'</span></span>'+
                                '<span class="layui-col-sm12"><span class="layui-col-md2 layui-icon layui-icon-log"></span><span  class="layui-col-md10">'+data.location_time+'</span></span>'+
                                '<span class="layui-col-sm12" style="display: none"><span class="layui-col-md2 layui-icon layui-icon-log"></span><span  class="layui-col-md10">'+data.extra_info+'</span></span>'+
                            '</div>'+
                        '</div>' +
                        '<div class="layui-col-sm12">' +
                            '<div id="allmap" class="bd-map"></div>' +
                        '</div>' +
                    '</div>' +
                '</div>';

            behaviorBox = layer.open({
                type: 1
                ,title: false
                ,area: ['960px', '620px']
                ,closeBtn: false
                ,id:'behavior'
                ,offset: 'auto'
                ,skin:'behavior-box'
                ,content: tem
                ,shade: 0
            });
            //var height = $('.driver-behavior img').height();
            $('.behavior-box').css('background','transparent');
            //$('.behavior-box').css('height',620 - 360+height);
            //$('.behavior-warming').css('height',height);
            $('.mask').show();
            $('.mask img').click(function () {
                layer.close(behaviorBox);
                $('.mask').hide();
            });


            var map = new BMap.Map("allmap");    // 创建Map实例
            var point = new BMap.Point(data.location_lng_bd, data.location_lat_bd);
            map.centerAndZoom(point, 12);
            var marker = new BMap.Marker(point);  // 创建标注
            map.addOverlay(marker);
            map.enableScrollWheelZoom(true);     //开启鼠标滚轮缩放

        });
    }


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
        var va = $.trim(name.val());
        data.name = va.length > 0 ? va : "null";
        va = $.trim(code.val());
        data.code = va.length > 0 ? va : "null";
        va = $.trim(vehicleNo.val());
        data.vehicleNo = va.length > 0 ? va : "null";
        va = $.trim(deviceNo.val());
        data.deviceNo = va.length > 0 ? va : "null";
        data.pageNo = 1;
        va = $.trim(behaviorTime.val());
        if (va.length == 0 ){
            data.startTime = 'null';
            data.endTime = 'null';
        }

        /*va = $.trim(phone.val());
         data.phone = va.length > 0 ? va : "null";*/
        setLists();
        return  false;
    });

    $(document).on("click", ".video", function(event){
        var videoPath = $(this).attr('data');
        event.stopPropagation();
        player = new Aliplayer({
                "id": "player-con",
                //"source": "//player.alicdn.com/video/aliyunmedia.mp4",
                "source": videoPath,
                "width": "100%",
                "height": "497px",
                "isLive": false,
                "rePlay": false,
                "showBuffer": true,
                "snapshot": false,
                "showBarTime": 5000,
                "useFlashPrism": true,
            }, function (player) {

            }
        );
        layer.open({
            type: 1,
            shade:0,
            area:['600px', '543px'],
            content: $('#videoBox'),
            cancel: function(index, layero){
                player.dispose();
                layer.close(index);
                return false;
            }

        });
        event.stopPropagation();
        return false;
    });

});
