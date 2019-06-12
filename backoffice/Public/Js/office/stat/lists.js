/**
 * Created by dev on 2018/5/10.
 */
layui.use(['form','upload', 'laydate','layer','table'], function(){

    var table = layui.table
        ,form = layui.form
        ,layer = layui.layer
        ,laydate = layui.laydate
        ,upload = layui.upload;

    var baseUrl = $('#baseUrl').val();
    //过滤条件
    var name = $("#driver_name"), phone = $("#driver_phone"), status = $("#driver_status"),account = $('#driver_account');
    var data = {name: "null", account: "null", status: "null",phone:"null"}, va = "";

    //获取列表数据
    table.render({
        elem: '#tableList'
        ,url:'ajaxLists'
        ,cols: [[
            {field:'name',  title: '司机姓名',width: 150}
            ,{field:'sex_name',  title: '性别',width: 100}
            ,{field:'phone',  title: '手机号'}
            ,{field:'account',  title: '司机账号'}
            ,{field:'certification_code',  title: '驾驶证号'}
            ,{field:'certification_expire_time',  title: '驾驶证到期日'}
            ,{field:'driving_age',  title: '驾龄',width: 100}
            /*,{field:'status_name',  title: '状态'}*/
            ,{field:'create_time',  title: '时间'}
            ,{field:'action', title: '操作', width: 300}
        ]]
        ,page: true
        ,where:data
    });

    //重置
    $("#reset_bt").click(function(){
        name.val('');
        phone.val('');
        status.val('');
        account.val('');

        data.name =  "null";
        data.phone =  "null";
        data.status = "null";
        data.is_bt = 1;
        table.reload("tableList", {
            where: data
        });
        return  false;
    });

    //搜索
    $("#src_bt").click(function(){
        va = $.trim(name.val());
        data.name = va.length > 0 ? va : "null";
        va = $.trim(phone.val());
        data.phone = va.length > 0 ? va : "null";
        va = $.trim(status.val());
        data.status = va.length > 0 ? va : "null";
        va = $.trim(account.val());
        data.account = va.length > 0 ? va : "null";
        data.is_bt = 1;
        table.reload("tableList", {
            where: data
        });
        return  false;
    });


    //操作
    table.on('tool(tableList)', function(obj){
        var data = obj.data;
        if(obj.event === 'detail'){

            $('#driverId').val(data.id);
            $('#status').val(data.status);
            form.render('select');
            $('#name').val(data.name);
            $('#account').val(data.account);
            $('input[name=sex]').eq(data.sex-1).next().find("i").click();
            $('#phone').val(data.phone);
            $('#driving_age').val(data.driving_age);
            $('#certification_code').val(data.certification_code);
            $('#certification_expire_time').val(data.certification_expire_time);

            var type = data.id;

            layer.open({
                type: 1
                ,title: '修改司机信息'
                ,offset: 'auto'                        //具体配置参考：http://www.layui.com/doc/modules/layer.html#offset
                ,id: 'driverInfo'               //防止重复弹出
                ,content: $('#add_driver')
                ,shade: 0                           //不显示遮罩
            });

        } else if(obj.event === 'del'){
            layer.confirm('真的删除此司机么', function(index){
                deleteDriver(data.id);
                layer.close(index);
            });
        } else if(obj.event === 'edit'){
            location.href = 'openWs/id/'+data.id;
        } else if (obj.event === 'monitor'){
            location.href = 'monitor/id/'+data.id;
        } else if (obj.event === 'face'){

            $('#driver_id').val(data.id);
            $('#faceImg').attr('src', data.face_path!=''?data.face_path+'?x-oss-process=image/resize,m_lfit,w_230,h_280':'/Public/Images/office/user_default.png'); //图片链接（base64）
            layer.open({
                type: 1
                ,title: '人脸照片信息'
                ,offset: 'auto'                        //具体配置参考：http://www.layui.com/doc/modules/layer.html#offset
                ,id: 'driverInfo'               //防止重复弹出
                ,content: $('#add_driver_face')
                ,shade: 0                           //不显示遮罩
            });
        }

    });

    //日期
    /*laydate.render({
        elem: '#certification_expire_time'
    });*/

    //自定义验证规则
    form.verify({
        name:function (value) {
            if (value.length == 0) {
                return '请输入司机姓名';
            } else {
                var reg = /^([\u4e00-\u9fa5]){2,7}$/;
                if(!reg.test(value)){
                    return '姓名只能为中文';
                }
            }
        }
        ,account: function (value) {
            if (value .length == 0) {
                return '请填写登录账户';
            } else {
                var reg = /^[a-zA-Z0-9]{3,16}$/;
                if (!reg.test(value)){
                    return '用户名只能为数字或字母';
                }
            }
        }
        /*,driving_age : function (value) {
            if (value.length == 0) {
                return '请输入驾驶年龄';
            } else {
                var reg = /^[0-9]{1,2}$/;
                if(!reg.test(value)){
                    return '请输入1到2位数字';
                }
            }
        }
        ,certification_code: function (value) {
            if (value.length == 0) {
                return '请输入驾驶证号';
            } else {
                var reg =  /(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/;
                if(!reg.test(value)){
                    return '请输入正确的驾驶证号';
                }
            }
        },*/
    });

    //监听提交
    form.on('submit(demo1)', function(data){

        var update_btn = $("#update_btn");
        update_btn.addClass("layui-btn-disabled");
        update_btn.attr('disabled',true);

        $.ajax({
            type: "post",
            url: baseUrl + "/Driver/ajaxUpdateDriver",
            data: data.field,
            dataType: "json",
            success: function(data)
            {
                if (data.code == 1){
                    layer.closeAll();
                    table.reload('tableList',{});
                }
                layer.msg(data.msg);
                update_btn.removeClass("layui-btn-disabled");
                update_btn.attr('disabled',false);
            }
        });
        return false;
    });


    //删除司机
    var deleteDriver = function (id) {

        var data = {id:id};

        $.ajax({
            type: "post",
            url: baseUrl + "/Driver/ajaxDeleteDriver",
            data: data,
            dataType: "json",
            success: function(data)
            {
                if (data.code == 1){
                    table.reload('tableList',{});
                }
                layer.msg(data.msg);
            }
        });
        return false;
    };

    //上传人脸照片
    var uploadInst = upload.render({
        elem: '#uploadFace'
        ,url: baseUrl + '/Driver/uploadFace'
        ,data:{driverId:function(){
            return $('#driver_id').val();
        }}
        ,size:2048
        ,acceptMime: 'image/jpeg,image/jpg, image/png'
        ,before: function(obj){
            //预读本地文件示例，不支持ie8
            obj.preview(function(index, file, result){
                $('#faceImg').attr('src', result); //图片链接（base64）
            });
            $('#uploadFace').attr('disabled',true);
            $('#uploadFace').text('上传中...');
        }
        ,done: function(res){

            $('#uploadFace').attr('disabled',false);
            $('#uploadFace').text('选择图片');

            //如果上传失败
            if(res.code == 0){

                reloadUpload();
                layer.msg('上传失败');
            } else {
                layer.closeAll();
                $('#faceImg').attr('src', '');
                $('#demoText').hide();
                layer.msg(res.msg);
                table.reload("tableList", {
                    where: data
                });
            }

            //上传成功
        }
        ,error: function(){
            $('#uploadFace').attr('disabled',false);
            $('#uploadFace').text('选择图片');
            reloadUpload();
        }
    });

    function reloadUpload() {
        //演示失败状态，并实现重传
        var demoText = $('#demoText');
        demoText.html('<span style="color: #FF5722;">上传失败</span> <a class="layui-btn layui-btn-xs demo-reload">重试</a>');
        demoText.find('.demo-reload').on('click', function(){
            uploadInst.upload();
        });
    }

});