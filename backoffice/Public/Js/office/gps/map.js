/**
 * Map 基础类
 *
 * */
function Map(options){

    /**
     * 统一标准错误信息定义
     */
    var ERROR = {
        OK: {code:0, text:"成功"},
        MAP_ABSENT:     {code:1,text:"百度地图加载失败"},
        LOAD_DATA_FAILED:     {code:2, text:"车辆数据加载失败"},
        INVALID_TYPE: {code:12, text:"参数类型错误： {0} for {1}."},
        EMPTY_CARS: {code:13, text:"车辆列表为空"},
        MISSING_ARGUMENT: {code:14,text:"参数{0} 缺失"},
        UNSUPPORTED_OPERATION: {code:14, text:"AMQJS0014E Unsupported operation."},
        INVALID_STORED_DATA: {code:15, text:"AMQJS0015E Invalid data in local storage key={0} value={1}."},
        INVALID_MQTT_MESSAGE_TYPE: {code:16, text:"AMQJS0016E Invalid MQTT message type {0}."},
        MALFORMED_UNICODE: {code:17, text:"AMQJS0017E Malformed Unicode string:{0} {1}."}
    };

    /**
     * 地图默认配置项
     * */
    var defaultOptions = {
        zoom:5,
        enableScroll:false,
        autoRefresh:false,
        interval:10
    };

    /**
     *车辆容器
     * */
    var cars     = [];//车辆容器


    /**
     * 信息面板
     * */
    var infoBar  = new Bar();

    /**
     * 车辆过滤条件
     * @private
     * */
    var myDis    = null;
    var filter   = '';

    /**
     * 百度地图
     * @private
     * */
    var map      = null;


    /**
     * 路书
     * */
    var lushu    = null;

    /**
     * 配置项检查
     * */
    var validate = function(obj, keys) {
        for(key in obj) {
            if (obj.hasOwnProperty(key)) {
                if (keys.hasOwnProperty(key)) {
                    if (typeof obj[key] !== keys[key])
                        throw new Error(format(ERROR.INVALID_TYPE, [typeof obj[key], key]));
                } else {
                    var errorStr = "未知属性, " + key + "";
                    throw new Error(errorStr);
                }
            }
        }
    };

    /**
     * 格式化信息展示
     * @private
     * @param {error} 预定义的错误代码，参见：ERROR.KEY .
     * @param {substitutions} [array] substituted into the text.
     * @return string 错误信息.
     */
    var format = function(error, substitutions) {
        var text = error.text;
        if (substitutions) {
            for (var i=0; i<substitutions.length; i++) {
                field = "{"+i+"}";
                start = text.indexOf(field);
                if(start > 0) {
                    var part1 = text.substring(0,start);
                    var part2 = text.substring(start+field.length);
                    text = part1+substitutions[i]+part2;
                }
            }
        }
        return text;
    };

    /**
     * 在地图上添加车辆图标
     * @public
     * */
    this.renderCars = function(){
        if(cars.length>0){
            $.each(cars, function(key, car){
                car.renderCar();
                if (filter!='' && car.vehicle_number.indexOf(filter)==-1) {
                    car.hide();
                }
            });
        }else{
            throw new Error(format(ERROR.EMPTY_CARS));
        }
    };

    /**
     * 从地图上移除车辆图标
     * @public
     * */
    this.removeCars = function () {
        if (cars.length>0) {
            $.each(cars, function(key, car){
                car.remove();
            });
            cars = [];
        }
    };

    /**
     * 初始化地图
     * @public
     * */
    this.initMap   = function(){
        var that = this;
        defaultOptions   = $.extend(defaultOptions,options);
        validate(defaultOptions,  {
                container:"string",
                zoom:"number",
                center:"object",
                enableScroll:"boolean",
                baseUrl:"string",
                autoRefresh:"boolean",
                interval:"number"});
        if (typeof window.BMap === "undefined") {
            throw new Error(format(ERROR.MAP_ABSENT));
        }
        if (typeof defaultOptions.container === "undefined") {
            throw new Error(format(ERROR.MISSING_ARGUMENT, ['center']));
        }
        if (typeof defaultOptions.container === "undefined") {
            throw new Error(format(ERROR.MISSING_ARGUMENT, ['container']));
        }
        if (typeof defaultOptions.baseUrl === "undefined") {
            throw new Error(format(ERROR.MISSING_ARGUMENT, ['baseUrl']));
        }
        //适配屏幕大小
        var container = $('#'+defaultOptions.container);
        container.height($(window).height()*0.7);
        map = new BMap.Map(defaultOptions.container);
        map.centerAndZoom(new BMap.Point(defaultOptions.center.lng,defaultOptions.center.lat), defaultOptions.zoom);

        //开启地图工具

        if(defaultOptions.enableScroll){
            map.enableScrollWheelZoom();   //启用滚轮放大缩小，默认禁用
            map.enableContinuousZoom();
        }
        // 创建文本标注对象
        var label = new BMap.Label();
        label.setStyle({
            color : '#000',
            fontSize : "12px",
            height : "20px",
            lineHeight : "20px",
            fontFamily:"微软雅黑",
            display:'none',
            border:'none'
        });
        label.setOffset(new BMap.Size(20, -10));
        map.addOverlay(label);

    };

    /**
     * 开启关闭测试工具
     */
    this.distanceTool = function () {

        if (myDis == null){
            myDis = new BMapLib.DistanceTool(map);
        }
        myDis.open();
    };

    /**
     * 追踪当前车辆
     * @public
     * */
    var trackSingleCarGps = [];
    this.trackSingleCar   = function () {
        var that = this;
        $.unifyAjax({
            type: "get",
            url: defaultOptions.baseUrl+"/UserBehavior/ajaxGetCurrentGps",
            dataType: "json",
            success: function (data) {
                if (data.code == 1) {
                    //清空原有车辆图标
                    that.removeCars();

                    //原有坐标为高德坐标GCJ02,转换为百度坐标BD09
                    var result = gcoord.transform(
                        [ data.data.lng, data.data.lat ],     // 经纬度坐标
                        gcoord.GCJ02,                 // 当前坐标系
                        gcoord.BD09                   // 目标坐标系
                    );
                    data.data.lng = result[0];
                    data.data.lat = result[1];

                    //重新加载车辆
                    var carOptions = {};
                    carOptions.gps = data.data;
                    carOptions.gps.ajaxUrl  = defaultOptions.baseUrl;

                    var car = new Car(carOptions, map, that, infoBar);

                    //设置初始地图中心点
                    if (trackSingleCarGps.length == 0) {
                        var _point  = new BMap.Point(carOptions.gps.lng, carOptions.gps.lat);
                        map.centerAndZoom(_point, 15);
                    }
                    cars.push(car);

                    //渲染车辆
                    that.renderCars();
                    while (trackSingleCarGps.length > 1) {
                        trackSingleCarGps.shift(); // trackSingleCarGps 只留上一次坐标，防止重复画线
                    }
                    trackSingleCarGps.push({lng: carOptions.gps.lng, lat: carOptions.gps.lat});

                    // 生成坐标点
                    var trackPoint = [];
                    for (var i = 0; i < trackSingleCarGps.length; i++) {
                        trackPoint.push(new BMap.Point(trackSingleCarGps[i].lng, trackSingleCarGps[i].lat));
                    }

                    // 画线
                    var polyline = new BMap.Polyline(trackPoint, {
                        strokeColor: "#1869AD",
                        strokeWeight: 3,
                        strokeOpacity: 1
                    });
                    map.addOverlay(polyline);
                } else {
                    $.showMsg('未获取到当前用户位置', 'warning');
                }
            },
            error: function (error) {
                that.block = false;//解锁
                $.showMsg('加载失败，请稍后重试', 'danger');
            }
        });
    };

    /**
     * 回放历史轨迹
     * @param startTimeStr 查询开始时间 YYYY-mm-dd HH:ii:ss
     * @param endTimeStr 查询结束时间 YYYY-mm-dd HH:ii:ss
     * @param initFlg 初始调用标识 true 为初始调用， false 为回调调用
     * @param successCallback 成功查询GPS数据执行函数
     * @param errorCallback 未成功查询GPS数据执行函数
     */
    var historyEndTimeStr  = null; // 路书GPS结束时间 YYYY-mm-dd HH:ii:ss
    var historyEndCallback = null; // 路书结束回调函数
    this.history = function (startTimeStr, endTimeStr, initFlg,deviceNo,successCallback, errorCallback) {
        var that = this;
        $.ajax({
            type: "post",
            url: defaultOptions.baseUrl+"/RoadLine/getVehicleHistoryPoint",
            data: {startTimeStr:startTimeStr, endTimeStr:endTimeStr, initFlg: initFlg,deviceNo:deviceNo},
            dataType: "json",
            success: function (data) {
                if (data.code == 1 && data.data.length > 0) {

                    // 生成坐标点
                    var arrPois = [];
                    for (var i = 0, j = data.data.length; i < j; i++) {
                        ////原有坐标为高德坐标GCJ02,转换为百度坐标BD09
                        //var result = gcoord.transform(
                        //    [ data.data[i].lng, data.data[i].lat ],     // 经纬度坐标
                        //    gcoord.GCJ02,                 // 当前坐标系
                        //    gcoord.BD09                   // 目标坐标系
                        //);
                        //data.data[i].lng = result[0];
                        //data.data[i].lat = result[1];
                        arrPois.push(new BMap.Point(data.data[i].lng, data.data[i].lat));

                        // 初始调用，设置初始地图中心点
                        if (i == 0 && initFlg) {
                            var _point  = new BMap.Point(data.data[i].lng, data.data[i].lat);
                            map.centerAndZoom(_point, 17);
                        }
                    }

                    // 画线
                    map.addOverlay(new BMap.Polyline(arrPois, {
                        strokeColor: "#1869AD",
                        strokeWeight: 3,
                        strokeOpacity: 1
                    }));

                    // 设置路书
                    lushu = new BMapLib.LuShu(map,arrPois,{
                        defaultContent:"",
                        autoView:true,//是否开启自动视野调整，如果开启那么路书在运动过程中会根据视野自动调整
                        icon  : new BMap.Icon('/Public/Images/office/gps_icon/green_right_24.png', new BMap.Size(52,26),{anchor : new BMap.Size(27, 13)}),
                        speed: 100,
                        enableRotation:true,//是否设置marker随着道路的走向进行旋转
                        landmarkPois: []
                    });

                    // 设置结尾GPS日期
                    historyEndTimeStr = data.data[data.data.length-1].sys_time_str;

                    // 设置路书结束回调函数
                    historyEndCallback = function () {
                        if (historyEndTimeStr < endTimeStr) {
                            that.history(historyEndTimeStr, endTimeStr, false,deviceNo);
                        }
                    };

                    // 如果非初始化，回调加载路书，直接开始路书
                    if (!initFlg) that.startLushu();

                    // 执行成功函数
                    if (successCallback) successCallback();

                    // 输出加载进度
                    //if ($('#closeEndTime').length > 0) $('#closeEndTime').text(historyEndTimeStr);
                }
                else {
                    // 清空路书结束回调设置
                    historyEndTimeStr  = null;
                    historyEndCallback = null;
                    // 执行失败函数
                    if (errorCallback) errorCallback();
                }
            }

        });

    };

    /**
     * 开始路书
     */
    this.startLushu = function(startCallback){
        lushu && lushu.start(function () {
            if (startCallback) startCallback();
            if (historyEndCallback) historyEndCallback();
        });
    };

    /**
     * 停止路书
     * @param stopCallback
     */
    this.stopLushu = function(stopCallback){
        // 清空路书结束回调设置
        historyEndTimeStr  = null;
        historyEndCallback = null;
        // 清除覆盖物
        map.clearOverlays();
        lushu && lushu.stop(stopCallback);
    };

    /**
     * 暂停路书
     */
    this.pauseLushu = function(){
        lushu && lushu.pause();
    };
}