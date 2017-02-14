<?php if (!defined('THINK_PATH')) exit();?><!--修改样式2 p元素自适应宽度 start-->
<div class="pageContent">
  <form method="post" action="<?php echo ($host_name); ?>/resource/editResource" class="pageForm required-validate" enctype="multipart/form-data" onsubmit="return iframeCallback(this, dialogAjaxDone)">
   	<input type="hidden" name="id" value="<?php echo ($vinfo["id"]); ?>">
    <div class="pageFormContent modal-body">
		<div class="form-group row">
	         <label class="col-xs-12 col-sm-2 control-label">
	          资源URL:
	        </label>
	        <div class="col-xs-12 col-sm-10">
	          <input type="text" class="form-control" id="media_url" value="<?php echo ($vinfo["oss_addr"]); ?>" readonly>
	      	 </div>
	      </div>
		 <div class="form-group row">
	         <label class="col-xs-12 col-sm-2 control-label">
	          资源名称:
	        </label>
	        <div class="col-xs-12 col-sm-10">
	          <input type="text" class="form-control" name="name" minlength="2" maxlength="20" value="<?php echo ($vinfo["name"]); ?>" required>
	      	 </div>
	      </div>
	      <div class="form-group row">
              <label class="col-xs-12 col-sm-2 control-label">
                资源类型：
              </label>
              <div class="col-xs-12 col-sm-10">
                <?php $_result=C('RESOURCE_TYPE');if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><input name="type" type="radio" id="<?php echo ($key); ?>" value="<?php echo ($key); ?>" <?php if($key == $vinfo['type']): ?>checked<?php endif; ?>/>&nbsp;<?php echo ($vo); ?>
                  &nbsp;&nbsp;<?php endforeach; endif; else: echo "" ;endif; ?>
              </div>
          		</div>
 			<div class="form-group row" id="duration" style="display:none;">
              <label class="col-xs-12 col-sm-2 control-label">
                视频时长：
              </label>
              <div class="col-xs-12 col-sm-10">
                <input name="duration" type="text" value="" class="form-control" />
              </div>
            </div>           		
	      <div class="form-group row">
              <label class="col-xs-12 col-sm-2 control-label">
                页面描述：
              </label>
              <div class="col-xs-12 col-sm-10">
                <textarea name="description" type="textInput" class="form-control"><?php echo ($vinfo["description"]); ?></textarea>
                <span class="tips">注：请输入资源描述，允许为空。</span>
              </div>
         		 </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-default close-m" type="button">取消</button>
      <button class="btn btn-primary" type="submit">保存</button>     
    </div>
  </form>
</div>
<script>
  $(function() {
    $("input[type='radio']").click(function() {
      var id = $(this).attr("id");
      if (id == 1) {
        $("#duration").show();
      } else {
        $("#duration").hide();
      }
    });
  });
</script>