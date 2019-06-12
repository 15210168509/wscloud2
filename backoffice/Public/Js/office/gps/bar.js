function Bar(){
    /**
     * 显示底部信息栏
     */
    this.render = function(setting){
        $('#floorInfo').css('display', 'block').html('正在加载');
        //填充的数据
        this.data(setting);
    };
    /**
     * 隐藏底部信息栏
     * @public
     * */
    this.hide = function(){
        $('#floorInfo').css('display','none');
    };
    /**
     * 要填充的数据
     * @param options
     */
    this.data = function(options){

    };

}