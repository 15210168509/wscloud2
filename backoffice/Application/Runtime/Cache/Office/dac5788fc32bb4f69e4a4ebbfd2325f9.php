<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html style="background-color: #f2f2f2;color: #666;">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>微视云监控平台</title>
    <link rel="icon" href="/Public/Images/office/logo.png" type="image/x-icon"/>
    <link rel="stylesheet" href="/Public/layui/css/layui.css">
    <link rel="stylesheet" href="/Public/layui/css/layim.css">
    <link rel="stylesheet" href="/Public/CSS/global.css">
    <!--<link rel="stylesheet" href="/Public/CSS/base.css">-->

    <?php if(isset($css_files)): if(is_array($css_files)): foreach($css_files as $css_file_path=>$media): ?><link href='/Public/CSS/<?php echo ($css_file_path); ?>?version=<?php echo ($version); ?>' rel="stylesheet" type="text/css" /><?php endforeach; endif; endif; ?>

</head>
<body class="layui-layout-body">
<div class="layui-layout layui-layout-admin">
    <input type="hidden" id="baseUrl" value="<?php echo ($baseUrl); ?>">
    <input type="hidden" id="warningDialog" value="<?php echo ($warningDialog); ?>">
    <input type="hidden" value="<?php echo ($adminTopic); ?>" id="adminTopic" />
    <input type="hidden" value="<?php echo ($mqttServer); ?>" id="mqttServer" />
    <input type="hidden" value="<?php echo ($mqttServerPort); ?>" id="mqttServerPort" />
    <input type="hidden" value="<?php echo ($tiredWarningNumber); ?>" id="tiredWarningNumber" />
    <div class="layui-header">
    <div class="layui-logo" style="width: 190px;"><img style="width: 24%;margin: 5px;" src="/Public/Images/office/web-logo.png">微视云监控平台</div>

        <?php echo ($topMenu); ?>
        <!--头部menu end-->
        <ul class="layui-nav layui-layout-right">
            <li class="layui-nav-item">
                <a href="<?php echo ($baseUrl); ?>/Driver/behaviorLists">
                    <i class="layui-icon layui-icon-notice"><span class="layui-badge" id="waringNum">0</span></i>
                </a>

            </li>

            <li class="layui-nav-item">
                <a href="<?php echo ($baseUrl); ?>/Admin/adminMsg">
                    <i class="layui-icon layui-icon-reply-fill"><span  class="layui-badge" id="msgNum"><?php echo ($msgNum); ?></span></i>
                </a>

            </li>

            <li class="layui-nav-item">
                <a href="javascript:;">公司：<?php echo ($companyName); ?></a>
            </li>

            <li class="layui-nav-item">
                <a href="javascript:;">管理员：<?php echo ($username); ?></a>
                <dl class="layui-nav-child">
                    <dd><a href="<?php echo ($baseUrl); ?>/Admin/adminInfo">个人信息</a></dd>
                    <dd><a href="<?php echo ($baseUrl); ?>/Logout">退出</a></dd>
                </dl>
            </li>
        </ul>
    </div>

    <div class="layui-side layui-bg-black">
        <div class="layui-side-scroll">
            <?php echo ($menu); ?>
        </div>
    </div>

    <div class="layui-body">
        <div style="background-color: #f5f5f5;min-height: 40px;line-height: 40px;padding: 0 12px 0;">
            <span class="layui-breadcrumb" lay-separator=">">

                <?php if(is_array($breadcrumb)): foreach($breadcrumb as $name=>$url): if(!empty($url)): ?><a href="<?php echo ($url); ?>"><?php echo ($name); ?></a>

                                <?php else: ?>
                                <a href="#"><?php echo ($name); ?></a><?php endif; endforeach; endif; ?>
            </span>
        </div>

        <!-- 内容主体区域 -->
        <div class="layui-fluid">
            
    <div class="layui-card">
        <div class="layui-card-body">
            <form class="layui-form" action="javascript:;" method="" id="">

                <div class="layui-form-item">
                    <label class="layui-form-label">低速报警</label>
                    <div class="layui-input-block">
                        <input type="checkbox" <?php if($setting[10] == 0): ?>checked<?php endif; ?> name="10" lay-skin="switch" lay-filter="switchTest" lay-text="开|关">

                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">警报声音</label>
                    <div class="layui-input-block">
                        <input type="checkbox" <?php if($setting[20] == 1): ?>checked<?php endif; ?>  name="20" lay-skin="switch" lay-filter="switchTest" lay-text="开|关">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">抽烟报警</label>
                    <div class="layui-input-block">
                        <input type="checkbox" <?php if($setting[30] == 1): ?>checked<?php endif; ?>  name="30" lay-skin="switch" lay-filter="switchTest" lay-text="开|关">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">打电话报警</label>
                    <div class="layui-input-block">
                        <input type="checkbox" <?php if($setting[40] == 1): ?>checked<?php endif; ?>  name="40" lay-skin="switch" lay-filter="switchTest" lay-text="开|关">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">左顾右盼报警</label>
                    <div class="layui-input-block">
                        <input type="checkbox" <?php if($setting[50] == 1): ?>checked<?php endif; ?>  name="50" lay-skin="switch" lay-filter="switchTest" lay-text="开|关">
                    </div>
                </div>

                <!--<div class="layui-form-item">
                    <label class="layui-form-label">低头角度</label>
                    <div class="layui-input-inline">
                        <input type="text" class="layui-input" name="60" value="<?php echo ($setting[60]); ?>">
                    </div>
                </div>-->

                <div class="layui-form-item">
                    <label class="layui-form-label">左顾右盼延时</label>
                    <div class="layui-input-inline">
                        <input placeholder="取值0.5-1.5，以5递增" lay-verify="70" type="text" class="layui-input" name="70" value="<?php echo ($setting[70]); ?>">
                    </div>
                    <div class="layui-form-mid layui-word-aux">秒</div>
                </div>

                <!--<div class="layui-form-item">
                    <label class="layui-form-label">左顾角度</label>
                    <div class="layui-input-inline">
                        <input type="text" class="layui-input" name="80" value="<?php echo ($setting[80]); ?>">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">右盼角度</label>
                    <div class="layui-input-inline">
                        <input type="text" class="layui-input" name="90" value="<?php echo ($setting[90]); ?>">
                    </div>
                </div>-->

                <div class="layui-form-item">
                    <label class="layui-form-label">闭眼延时</label>
                    <div class="layui-input-inline">
                        <input placeholder="取值0.5-5，以0.5递增" lay-verify="100" type="text" class="layui-input" name="100" value="<?php echo ($setting[100]); ?>">
                    </div>
                    <div class="layui-form-mid layui-word-aux">秒</div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">低头报警间隔</label>
                    <div class="layui-input-inline">
                        <input placeholder="取值为正整数" lay-verify="110" type="text" class="layui-input" name="110" value="<?php echo ($setting[110]); ?>">
                    </div>
                    <div class="layui-form-mid layui-word-aux">秒</div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">闭眼报警间隔</label>
                    <div class="layui-input-inline">
                        <input placeholder="取值为正整数" lay-verify="110" type="text" class="layui-input" name="120" value="<?php echo ($setting[120]); ?>">
                    </div>
                    <div class="layui-form-mid layui-word-aux">秒</div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">打哈欠报警间隔</label>
                    <div class="layui-input-inline">
                        <input placeholder="取值为正整数" lay-verify="110" type="text" class="layui-input" name="130" value="<?php echo ($setting[130]); ?>">
                    </div>
                    <div class="layui-form-mid layui-word-aux">秒</div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">抽烟报警间隔</label>
                    <div class="layui-input-inline">
                        <input placeholder="取值为正整数" lay-verify="110" type="text" class="layui-input" name="140" value="<?php echo ($setting[140]); ?>">
                    </div>
                    <div class="layui-form-mid layui-word-aux">秒</div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">打电话报警间隔</label>
                    <div class="layui-input-inline">
                        <input placeholder="取值为正整数" lay-verify="110" type="text" class="layui-input" name="150" value="<?php echo ($setting[150]); ?>">
                    </div>
                    <div class="layui-form-mid layui-word-aux">秒</div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">左顾右盼报警间隔</label>
                    <div class="layui-input-inline">
                        <input placeholder="取值为正整数" lay-verify="110" type="text" class="layui-input" name="160" value="<?php echo ($setting[160]); ?>">
                    </div>
                    <div class="layui-form-mid layui-word-aux">秒</div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">抽烟延时</label>
                    <div class="layui-input-inline">
                        <input placeholder="取值为正整数" lay-verify="110" type="text" class="layui-input" name="170" value="<?php echo ($setting[170]); ?>">
                    </div>
                    <div class="layui-form-mid layui-word-aux">秒</div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">打电话延时</label>
                    <div class="layui-input-inline">
                        <input placeholder="取值为正整数" lay-verify="110" type="text" class="layui-input" name="180" value="<?php echo ($setting[180]); ?>">
                    </div>
                    <div class="layui-form-mid layui-word-aux">秒</div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">低头延时</label>
                    <div class="layui-input-inline">
                        <input placeholder="取值为正整数" lay-verify="110" type="text" class="layui-input" name="190" value="<?php echo ($setting[190]); ?>">
                    </div>
                    <div class="layui-form-mid layui-word-aux">秒</div>
                </div>

                <div class="layui-form-item">
                    <div class="layui-inline">
                        <button class="layui-btn" lay-submit="" lay-filter="submit" id="submit">提交</button>
                    </div>
                </div>

            </form>
        </div>
    </div>

        </div>

    </div>

    <div class="site-tree-mobile layui-hide"><i class="layui-icon"></i></div>

    <div class="layui-footer">
        <!-- 底部固定区域 -->

    </div>
</div>
<!--行为弹出-->
<div class="warning-pop" style="width: 300px;display: none">
    <div class="layim-chat-box">
        <div class="layim-chat layim-chat-friend layui-show">
            <div class="layim-chat-main">
                <div style="padding-top: 10px">
                    <span class="layui-badge" style="background-color: #EA0000">危险</span>
                    <span class="layui-badge" style="background-color: #FF7575">严重</span>
                    <span class="layui-badge" style="background-color: #FF79BC">较重</span>
                    <span class="layui-badge" style="background-color: #FFA6FF">轻微</span>
                    <span class="layui-badge" style="background-color: #96FED1">正常</span>
                </div>
                <ul id="chat-main"></ul>
            </div>
        </div>
    </div>
</div>

<div class="site-mobile-shade"></div>
<script src="/Public/Js/jquery.min.js"></script>
<script src="/Public/layui/layui.js"></script>
<script src="/Public/Js/office/mqttws31.js?version=<?php echo ($version); ?>"></script>
<script src="/Public/Js/office/getNews.js?version=<?php echo ($version); ?>"></script>
<script>
    //JavaScript代码区域
    layui.use('element', function(){
        var element = layui.element;
        var treeMobile = $('.site-tree-mobile'),
                shadeMobile = $('.site-mobile-shade');

        treeMobile.on('click', function(){
            $('body').addClass('site-mobile');
        });

        shadeMobile.on('click', function(){
            $('body').removeClass('site-mobile');
        });
    });

</script>
<!-- add page js-->
<?php if(isset($js_all_files)): if(is_array($js_all_files)): foreach($js_all_files as $key=>$js_file_path): ?><script src="<?php echo ($js_file_path); ?>" type="text/javascript"></script><?php endforeach; endif; endif; ?>
<!-- end of add js-->

<!-- add page js-->
<?php if(isset($js_files)): if(is_array($js_files)): foreach($js_files as $key=>$js_file_path): ?><script src="/Public/Js/<?php echo ($js_file_path); ?>?version=<?php echo ($version); ?>" type="text/javascript"></script><?php endforeach; endif; endif; ?>



<!-- end of add js-->
</body>
</html>