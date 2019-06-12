function Car(options,baiduMap,mapObject,infoBar){
    /**
     * 信息面板打开标识
     * @private
     * */
    var _showInfo       = false;
    /**
     * 车辆默认配置
     * */
    var _defaultOptions = $.extend({},options);
    /**
     * 车辆图标
     * @private
     * */
    var _marker         = null;
    this.map            = baiduMap;
    /**
     * 车辆Id
     * @public
     * */
    this.id             = _defaultOptions.id;
    /**
     * 车牌号码
     * */
    this.vehicle_number = _defaultOptions.vehicle_number;
    /**
     * 从地图上移除车辆图标
     * @public
     * */
    this.remove = function(){
        if (_marker !== null) {
            this.map.removeOverlay(_marker);
        }
    };
    /**
     * 隐藏车辆图标
     * @public
     * */
    this.hide = function(){
        _marker.hide();
    };
    /**
     * 显示车辆图标
     * @public
     * */
    this.show = function(){
        _marker.show();
    };
    /***/
    this.getPosition = function () {
        return new BMap.Point(_defaultOptions.gps.lng, _defaultOptions.gps.lat);
    };
    /**
     * 在地图上添加车辆图标
     * */
    this.renderCar = function(){
        var that = this;
        //获取中心点
        var _point  = new BMap.Point(_defaultOptions.gps.lng, _defaultOptions.gps.lat);
        var _myIcon = new BMap.Icon(this.getGPSIcon(_defaultOptions.gps.course,
                                                    _defaultOptions.gps.device_status,
                                                    _defaultOptions.gps.device_info),
                                   new BMap.Size(38,38));
        // 创建图标
        _marker = new BMap.Marker(_point,{icon:_myIcon});
        //创建label
        var opts = {
            position : _point,    // 指定文本标注所在的地理位置
            offset   : new BMap.Size(20, -20)//设置文本偏移量
        }
        var label = new BMap.Label(this.vehicle_number, opts);  // 创建文本标注对象
        label.setStyle({
            color : '#000',
            fontSize : "12px",
            height : "20px",
            lineHeight : "20px",
            fontFamily:"微软雅黑",
            display:'none',
            border:'none',
            backgroundColor:'#fff'
        });
        _marker.setLabel(label);
        //添加点击事件
        _marker.addEventListener("click",function(e){
            //在地图底部显示车辆信息详情
            if (mapObject.showPanel == 0 || mapObject.showPanel != that.id) {
                that.openInfo();
            } else {
                that.closeInfo();
            }
        });
        ////鼠标滑过事件
        //_marker.addEventListener("mouseover", function(e){
        //    _marker.getLabel().setStyle({    //给label设置样式，任意的CSS都是可以的
        //        display:"block"
        //    });
        //
        //
        //});
        ////鼠标移出事件
        //_marker.addEventListener("mouseout", function(){
        //    _marker.getLabel().setStyle({    //给label设置样式，任意的CSS都是可以的
        //        display:"none"
        //    });
        //});
        //添加到地图
        this.map.addOverlay(_marker);
    };
    this.getGPSIcon = function(course,device_status,device_info){
        var icon = '', icon_direction = 'north';

        if( 45 <course && course<=135) {
            icon_direction = 'east';
        } else if (135<course && course<= 225) {
            icon_direction = 'south';
        } else if (225<course && course<=315) {
            icon_direction = 'west';
        }

        if (device_info == 3){
            icon =  'white_'+icon_direction+'_24.gif';
        } else {
            icon = device_status =='运动' ? 'green_'+icon_direction+'_24.gif' : 'blue_'+icon_direction+'_24.gif';
        }
        return '/Public/Images/office/gps_icon/'+icon;
    };
    /**
     * 打开车辆信息面板
     * @public
     * */
    this.openInfo = function(){
        // mapObject.showPanel = this.id;
        // console.log(_defaultOptions);
        // var _infoBarOption = {
        //                      ajaxUrl:       _defaultOptions.ajaxUrl,//请求信息url
        //                      carId:         _defaultOptions.id,
        //                      number:        _defaultOptions.vehicle_number,//车辆内部编号
        //                      carStatus:     _defaultOptions.status
        //                     };
        // if (_defaultOptions.transportId){
        //     _infoBarOption.transportId = _defaultOptions.transportId;
        // } else {
        //     _infoBarOption.transportId = '';
        // }
        // infoBar.render(_infoBarOption);
    };
    /**
     * 关闭车辆信息面板
     * @public
     * */
    this.closeInfo = function(){
        // mapObject.showPanel = 0;
        // infoBar.hide();

    };
}