/**
 * Created by 01 on 2018/6/12.
 */


layui.use(['form'], function() {

    var baseUrl = $('#baseUrl').val();

    var form = layui.form;

    //判断全选是否选中
    var selectAllStatus = function () {
        var checkNum = $(".layui-input-block .layui-form-checked").length;
        var boxNum   = $(".layui-input-block .layui-form-checkbox").length;
        if (checkNum == boxNum) {
            $('#selectAll').next("div").addClass('layui-form-checked');
            $('#selectAll').prop('checked',true);
        }
    };

    selectAllStatus();

    //点击全选
    form.on('checkbox(selectAll)', function(data){
        $('input[name="warningType[]"]:checkbox').each(function (index, item) {
            item.checked = data.elem.checked;
        });
        form.render('checkbox');
    });


    //点击单选一项
    form.on('checkbox(select)',function (data) {
        if (!data.elem.checked){
            $('#selectAll').next("div").removeClass('layui-form-checked');
            $('#selectAll').prop('checked',false);
        } else {
            selectAllStatus();
        }
    });


    //预警类型提交
    form.on('submit(demo1)', function(data){

        var add_btn = $('#add_btn');
        add_btn.addClass("layui-btn-disabled");
        add_btn.attr('disabled',true);

        $.ajax({
            type: "post",
            url: baseUrl + "/System/setWarning",
            data: data.field,
            dataType: "json",
            success: function(data)
            {
                if(data.code == 1)
                {
                    $("#add_driver input").val("")
                }
                add_btn.removeClass("layui-btn-disabled");
                add_btn.attr('disabled',false);
                layer.msg(data.msg);
            }
        });
        return false;
    });

    //监听提交
    form.on('submit(submit)', function(data){
        var add_btn = $('#submit');
        add_btn.addClass("layui-btn-disabled");
        add_btn.attr('disabled',true);
        var open = data.field.open == 'on'? 1 : 0;
        $.ajax({
            type: "post",
            url: baseUrl + "/System/setWarningDialog",
            data: data.field,
            dataType: "json",
            success: function(data)
            {
                $('#warningDialog').val(open);

                add_btn.removeClass("layui-btn-disabled");
                add_btn.attr('disabled',false);
                layer.msg(data.msg);
            }
        });
        return false;

    });

});