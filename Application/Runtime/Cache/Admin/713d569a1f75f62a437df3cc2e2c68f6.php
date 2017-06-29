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
    z-index: 999;
  }
  .big{
    display: none;
  }
  .addbig{
    position: absolute;
    width: 500px;
    height: 500px;
    top: 100px;
    left: 26%;
    z-index: 1000;
  }
</style>
<div class="pageContent">
  <form method="post" action="<?php echo ($host_name); ?>/article/doAddmaps" class="pageForm required-validate" enctype="multipart/form-data" onsubmit="return iframeCallback(this, dialogAjaxDone)">
    <input type="hidden" name="id" value="<?php echo ($vinfo["id"]); ?>">
    <input type="hidden" name="ctype" value="2">
    <div class="pageFormContent modal-body">
      <div class="tabsContent">
        <div class="tab-content">
          <div id="tab1" class="tab-pane active fade in">

            <div class="form-group row">
              <label class="col-xs-12 col-sm-2 control-label">
                分类:
              </label>
              <div class="col-xs-12 col-sm-10">
                <select name="cate" class="form-control bs-select" title="请选择..." required>

                  <?php if(is_array($vcainfo)): $i = 0; $__LIST__ = $vcainfo;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$row): $mod = ($i % 2 );++$i;?><option value="<?php echo ($row['id']); ?>" <?php if($row['id'] == $vinfo['category_id']): ?>selected<?php endif; ?> > <?php echo ($row['name']); ?> </option><?php endforeach; endif; else: echo "" ;endif; ?>

                </select>
              </div>
            </div>




            <div class="form-group row">
              <label class="col-xs-12 col-sm-2 control-label">
                标题：
              </label>
              <div class="col-xs-12 col-sm-10">
                <input name="title" type="text" value="<?php echo ($vinfo["title"]); ?>"  minlength="2" maxlength="30" class="form-control" required/>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-xs-12 col-sm-2 control-label">
                封面图片：
              </label>
              <div class="col-xs-12 col-sm-10">
                <div class="fileinput fileinput-new" data-fileinput>
                  <div class="fileinput-preview thumbnail" data-trigger="fileinput">
                    <a data-target="#modal-file" href="javascript:void(0)">
                      <?php if(($vinfo['oss_addr'] == 'NULL') OR $vinfo['oss_addr'] == ''): ?><img id="media_idimg" src="/Public/admin/assets/img/noimage.png" border="0" />
                        <?php else: ?>
                        <img id="media_idimg" src="<?php echo ($vinfo["oss_addr"]); ?>" border="0" /><?php endif; ?>
                       <span id="media_idimgname"></span>
                    </a>
                  </div>
                  <div>
                    <a class="btn btn-success btn-file" data-target="#modal-file" href="<?php echo ($host_name); ?>/resource/uploadResource?filed=media_id&rtype=2" data-browse-file>
                      选择图片
                    </a>
                    <input type="hidden" name="media_id" id="media_id" value="">
                    <a href="javascript:;" class="btn btn-danger" data-remove-file="/Public/admin/assets/img/noimage.png">
                      删除 </a>
                  </div>
                </div>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-xs-12 col-sm-2 control-label">
                内容：
              </label>
              <div class="col-xs-12 col-sm-10">
                <script id="editor" type="text/plain" class="ueditor-init" name="content" style="height: 250px; width: 100%"><?php echo (html_entity_decode($vinfo["content"],ENT_COMPAT)); ?></script>
              </div>
            </div>
            <!--<div class="form-group row">
              <label class="col-xs-12 col-sm-2 control-label">
                发布时间：
              </label>
              <div class="col-xs-12 col-sm-10">
                <div class="input-group date form_datetime" data-date="<?php echo ($vinfo["log_time"]); ?>">
                  <input name="logtime" type="text" size="16" class="form-control date" placeholder="开始日期" value="<?php echo ($vinfo["log_time"]); ?>" readonly>
                  <span class="input-group-btn">
                    <button class="btn default date-reset" type="button"><i class="fa fa-times"></i></button>
                    <button class="btn btn-success date-set" type="button"><i class="fa fa-calendar"></i></button>
                  </span>
                </div> 
              </div>
            </div>-->

            <div class="form-group row">
              <label class="col-xs-12 col-sm-2 control-label">
                来源：
              </label>
              <div class="col-xs-12 col-sm-10">
                <input name="source" type="text" value="<?php echo ($vinfo["source"]); ?>"  minlength="2" maxlength="30" class="form-control" />
              </div>
            </div>





            <div class="form-group row">
              <label class="col-xs-12 col-sm-2 control-label">
                预约发布：
              </label>
              <div class="col-xs-12 col-sm-10">
                <div class="input-group date form_datetime" data-date="<?php echo ($vinfo["bespeak_time"]); ?>">
                  <input name="logtime" type="text" size="16" class="form-control date" placeholder="开始日期" value="<?php echo ($vinfo["bespeak_time"]); ?>" readonly>
                  <span class="input-group-btn">
                    <button class="btn default date-reset" type="button"><i class="fa fa-times"></i></button>
                    <button class="btn btn-success date-set" type="button"><i class="fa fa-calendar"></i></button>
                  </span>
                </div>
              </div>
            </div>



          </div>

        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-default close-m" type="button">取消</button>
      <button class="btn btn-primary" type="submit">保存</button>
    </div>
    <div class="zhezhao"></div>
    <img class="big" src=""/>
  </form>
</div>

<script type="text/javascript">
  $(function() {


    $('#media_idimg').click(function(){
      var $a = $(this).attr('src');
      $('.big').prop('src',$a).addClass('addbig')
      $('.zhezhao').show(500);
      $('.big').show(500);
    });
    $('.zhezhao').click(function(){

      $('.zhezhao').hide(500);
      $('.big').hide(500);
    });





    var ue = UE.getEditor('editor',{

      //关闭字数统计
      wordCount:false,
      //关闭elementPath
      elementPathEnabled:false,
    });


    $("#yulan").click(function(){
      alert($("#ueditor_3".val()));
      $.ajax({
        type:"POST",
        dataType: "json",
        url:"<?php echo ($host_name); ?>/menu/get_se_left",
        data:"m_type="+$("#m_type option:selected").val()+"&starttime="+$("#starttime").val()+"&endtime="+$("#endtime").val()+"&searchtitle="+$("#searchtitle").val(),
        success:function(data){
          alert(data);

        }
      });
    });
  });
</script>