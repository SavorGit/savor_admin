<?php if (!defined('THINK_PATH')) exit();?><!--修改样式2 p元素自适应宽度 start-->
<style type="text/css">
    .zhezhao{



        display: none;
        position: absolute;
        top: 0;
        bottom: 0;
        right: 0;
        left: 0;
        background-color: black;
        opacity: 0.7;
        text-align: center;
        z-index: 99;
    }
    .big{
        display: none;
    }
    .addbig{
        position: absolute;
        width: 400px;
        height: 300px;
        top: 100px;
        left: 26%;
        z-index: 100;
    }
</style>
<div class="pageContent">
    <form method="post" action="<?php echo ($host_name); ?>/clientconfig/doAddclient" class="pageForm required-validate" enctype="multipart/form-data" onsubmit="return iframeCallback(this, dialogAjaxDone)">
        <input type="hidden" name="clid" value="<?php echo ($vainfo["id"]); ?>">
        <div class="pageFormContent modal-body">

            <div class="form-group row">
                <label class="col-xs-12 col-sm-2 control-label">
                    客户端类型：
                </label>
                <div class="col-xs-12 col-sm-10">
                    <?php $_result=C('DEVICE_TYPE');if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($vainfo["id"] > 0): if($key > 2): ?><input name="clienttype" type="radio" id="<?php echo ($key); ?>" value="<?php echo ($vainfo["ctype"]); ?>" <?php if($vainfo["ctype"] == $key): ?>checked<?php else: ?>disabled<?php endif; ?> />&nbsp;<?php echo ($vo); ?>
                                &nbsp;&nbsp;<?php endif; ?>

                            <?php else: ?>
                            <?php if($key > 2): ?><input name="clienttype" type="radio" id="<?php echo ($key); ?>" value="<?php echo ($key); ?>" <?php if($key == 3): ?>checked<?php endif; ?>/>&nbsp;<?php echo ($vo); ?>
                                &nbsp;&nbsp;<?php endif; endif; endforeach; endif; else: echo "" ;endif; ?>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-xs-12 col-sm-2 control-label">
                    启动图片：
                </label>
                <div class="col-xs-12 col-sm-10">
                    <div class="fileinput fileinput-new" data-fileinput>
                        <div class="fileinput-preview thumbnail" data-trigger="fileinput">
                            <a  href="javascript:void(0)" >
                                <?php if(($vainfo['oss_addr'] == 'NULL') OR $vainfo['oss_addr'] == ''): ?><img src="/Public/admin/assets/img/noimage.png" border="0" id="covervideo_idimg" />
                                    <?php else: ?>
                                    <img src="<?php echo ($vainfo["oss_addr"]); ?>" id="covervideo_idimg" border="0" /><?php endif; ?>
                                <span id="covervideo_idimgname"></span>
                            </a>
                        </div>
                        <div>
                            <a class="btn btn-success btn-file" data-target="#modal-file" href="<?php echo ($host_name); ?>/resource/uploadResource?filed=covervideo_id&rtype=2" data-browse-file>
                                选择图片
                            </a>
                            <input type="hidden" name="covervideo_id" id="covervideo_id" value="">
                            <a href="javascript:;" class="btn btn-danger" data-remove-file="/Public/admin/assets/img/noimage.png">
                                删除 </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-xs-12 col-sm-2 control-label">
                    启动视频：
                </label>
                <div class="col-xs-12 col-sm-10">
                    <div class="fileinput fileinput-new" data-fileinput>
                        <div class="fileinput-preview thumbnail" data-trigger="fileinput" id="xuanpian">
                            <a id="xuanpianhr" target="_blank"
                            <?php if(($vainfo['videooss_addr'] == 'NULL') OR $vainfo['videooss_addr'] == ''): ?>href="javascript:void(0)"
                                <?php else: ?>href="<?php echo ($vainfo["videooss_addr"]); ?>"<?php endif; ?>>

                            <?php if(($vainfo['videooss_addr'] == 'NULL') OR $vainfo['videooss_addr'] == ''): ?><img id="media_idimg"  src="/Public/admin/assets/img/noimage.png" border="0"/>
                                <?php else: ?>
                                <img id="media_idimg"  src="/Public/admin/assets/img/medianoimg.png" border="0"/><?php endif; ?>
                            </a>

                        </div>

                        <div>
                            <a class="btn btn-success btn-file" data-target="#modal-file" href="<?php echo ($host_name); ?>/resource/uploadResource?filed=media_id&rtype=1&autofill=1" data-browse-file>
                                选择视频
                            </a>
                            <input type="hidden" name="media_id" id="media_id" value="">
                            <a href="javascript:;" class="btn btn-danger" id="videoshan" data-remove-file="/Public/admin/assets/img/noimage.png">
                                删除 </a>
                        </div>
                    </div>


                </div>
            </div>

            <div class="form-group row" id="duration">

                <label class="col-xs-12 col-sm-2 control-label">                   停留时长：
                </label>
                <div class="col-xs-12 col-sm-10">
                    <div class="form-inline">
                        <input style="width:140px;" min="1" name="duration" type="number" value="<?php echo ($vainfo["duration"]); ?>" class="form-control" required />
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-xs-12 col-sm-2 control-label">
                    视频名称:
                </label>
                <div class="col-xs-12 col-sm-10">
                    <input id="media_idimgname" type="text" class="form-control"style="width: 214px;" name="adsname" minlength="2" maxlength="20" value="<?php echo ($vainfo["name"]); ?>" required>
                </div>
            </div>

        </div>

        <div class="modal-footer">
            <button type="submit" class="btn btn-primary" style="margin-left: 50%;">&nbsp;提交&nbsp;</button>
        </div>
        <div class="zhezhao"></div>
        <img class="big" src=""/>
    </form>
</div>

<script type="text/javascript">
    $(function(){
        $('#covervideo_idimg').click(function(){
            var $a = $(this).attr('src');
            $('.big').prop('src',$a).addClass('addbig');
            $('.zhezhao').show(500);
            $('.big').show(500);
        })
        $('.zhezhao').click(function(){
            $('.zhezhao').hide(500);
            $('.big').hide(500);
        })

    });
    $("#videoshan").click(function(){
        $("#xuanpian #xuanpianhr").attr("href",'javascript:void(0)');
    })




    /*function dialogAjaxDone(json){
     navTab.closeCurrentTab();
     navTab.closeTab(json.navTabId);
     DWZ.ajaxDone(json);
     // alert(json);
     jap = JSON.stringify(json);
     console.log(jap);
     // navTab.closeTab('clientconfig/addclientconfig');
     // navTab.reload('clientconfig/configdata');
     //navTab.reloadFlag('clientconfig/configdata');
     //注意返回的JSON的数据结构
     navTab.closeTab('abce');
     if (json.status == DWZ.statusCode.ok){
     if (json.navTabId){
     navTab.reload('clientconfig/configdata');
     }


     }

     }*/

</script>