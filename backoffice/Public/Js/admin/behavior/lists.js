layui.use(['form', 'laydate','laypage','layer','autocomplete'], function(){

    var form = layui.form
        ,layer = layui.layer
        ,laydate = layui.laydate
        ,laypage = layui.laypage;

    var baseUrl = $('#baseUrl').val();

    var autocomplete = layui.autocomplete;


    //日期范围
    laydate.render({
        elem: '#behaviorTime'
        ,range: true
    });

    var name = $('#driver_name'),/*phone = $('#driver_phone'),*/behaviorTime = $('#behaviorTime') ,companyId = $('#companyId'),company = $('#company');
    var data = { startTime: 'null', endTime: 'null',name:"null",phone:"null",page:1,limit:10,companyId:companyId.val().length>0?companyId.val():"null",company:company.val().length>0?company.val():"null"};

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
                var patrn = /^[1-9]{1}[0-9]{2,3}\-[0-9]{2}\-[0-9]{2}$/i;
                var startTime = $.trim(time[0]), endTime = $.trim(time[1]);
                var st = patrn.exec(startTime), en = patrn.exec(endTime);
                if (st != null && en != null) {
                    data.startTime = startTime;
                    data.endTime   = endTime;
                }
            }
        } else {
            data.startTime = 'null';
            data.endTime   = 'null';
        }

        // 获取数据
        $.ajax({
            type: "get",
            url: baseUrl+"/Behavior/ajaxLists",
            data: data,
            dataType: "json",
            success: function(data)
            {
                if(data.code == 0 && parseInt(data.count) > 0)
                {
                    calcPages(data.count);           //分页，
                    setListsData(data.data);           //数据显示
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
            ,limit:data.limit
            ,count: totalRecord //数据总数
            ,curr: data.page
            ,jump: function(obj, first){
                if ( data.page != obj.curr){
                    data.page = obj.curr;
                    setLists();
                }
            }
        });
    }

    /**
     * 设置数据
     */
    function setListsData(data) {

        var tem = '<div class="item">';
        tem += '<div class="photo">';
        tem += '<div class="img">';
        tem += '<img class="b-img" src="##photo##" />';
        tem += '<div class="over">';
        tem += '<div class="func">';
        tem += '<a class="image-zoom" href="##photo##"><i class="fa fa-search"></i></a>';
        tem += '</div>';
        tem += '</div>';
        tem += '</div>';
        tem += '<div class="head">';
        tem += '<div class="layui-row ">';
        tem += '<div class="layui-col-md12">';
        tem += '<span class="layui-col-md12"><span class="layui-col-md4 layui-col-xs12 layui-col-sm6" style="text-align: center"><b>司机姓名</b></span><span class="layui-col-md8 layui-col-xs12 layui-col-sm6">##name##</span></span>';
        tem += '<span class="layui-col-md12"><span class="layui-col-md4 layui-col-xs12 layui-col-sm6" style="text-align: center"><b>行为类型</b></span><span class="layui-col-md8 layui-col-xs12 layui-col-sm6">##type##</span></span>';
        tem += '<span class="layui-col-md12"><span class="layui-col-md4 layui-col-xs12 layui-col-sm6" style="text-align: center"><b>具体行为</b></span><span class="layui-col-md8 layui-col-xs12 layui-col-sm6">##code##</span></span>';
        tem += '<span class="layui-col-md12"><span class="layui-col-md4 layui-col-xs12 layui-col-sm6" style="text-align: center"><b>行为级别</b></span><span class="layui-col-md8 layui-col-xs12 layui-col-sm6">##level##</span></span>';
        tem += '<span class="layui-col-md12"><span class="layui-col-md4 layui-col-xs12 layui-col-sm6" style="text-align: center"><b>当时车速</b></span><span class="layui-col-md8 layui-col-xs12 layui-col-sm6">##kmh## km/h</span></span>';
        tem += '<span class="layui-col-md12"><span class="layui-col-md4 layui-col-xs12 layui-col-sm6" style="text-align: center"><b>当时位置</b></span><span class="layui-col-md8 layui-col-xs12 layui-col-sm6"><span style="width:60%; white-space:nowrap; text-overflow:ellipsis" title="##location##">##location##</span></span>';
        tem += '<span class="layui-col-md12"><span class="layui-col-md4 layui-col-xs12 layui-col-sm6" style="text-align: center"><b>定位时间</b></span><span class="layui-col-md8 layui-col-xs12 layui-col-sm6">##time##</span></span>';
        tem += '</div>';
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
                temStr = temStr.replace(/##photo##/g, o.path);
            }
            temStr = temStr.replace(/##name##/g, o.name);
            temStr = temStr.replace(/##type##/g, o.type_text);
            temStr = temStr.replace(/##code##/g, o.code_text);
            temStr = temStr.replace(/##level##/g, o.level_text);
            temStr = temStr.replace(/##kmh##/g, o.kmh);
            temStr = temStr.replace(/##location##/g, o.location);
            temStr = temStr.replace(/##time##/g, o.location_time);
            $('#behaviorItemBody').append(temStr);
        });

    }

    //重置
    $("#reset_bt").click(function(){
        name.val('');
        //phone.val('');
        status.val('');
        account.val('');

        data.name =  "null";
        //data.phone =  "null";
        data.startTime  =  "null";
        data.endTime    =  "null";
        data.companyId  =  "null";
        data.company    =  "null";
        data.companyId  =  "null";
        data.is_bt = 1;
        table.reload("tableList", {
            where: data
        });
        return  false;
    });

    //搜索
    $("#src_bt").click(function(){
        var va = $.trim(name.val());
        data.name = va.length > 0 ? va : "null";
        /*va = $.trim(phone.val());
        data.phone = va.length > 0 ? va : "null";*/

        va = $.trim(company.val());
        data.company = va.length > 0    ? va : "null";

        if (va.length >0 ) {
            va = $.trim(companyId.val());
            data.companyId = va.length > 0  ? va : "null";
        } else {
            companyId.val('');
            data.companyId = "null";
        }
        data.is_bt = 1;
        setLists();
        return  false;
    });

    autocomplete.render({
        elem: $('#company'),
        url: baseUrl + "/Driver/searchCompany",
        cache: false,
        template_val: '{{d.name}}',
        template_txt: '{{d.name}}',
        onselect: function (resp) {
            //得到设备id
            $('#companyId').val(resp.id);
        }
    });

    $('#company').bind('input propertychange', function() {
        $('#companyId').val('');
    });

});