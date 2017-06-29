<?php if (!defined('THINK_PATH')) exit();?><!--修改样式2 p元素自适应宽度 start-->
<script>  
    if(!window.jQuery){
      var path = window.location.pathname;
      path = path.replace("/admin/","");
      console.log(path);
      window.location.href = "<?php echo ($host_name); ?>#" + path;
    }
</script>

<div class="pageContent">
  <form method="post" action="<?php echo ($host_name); ?>/tag/doAddTag" class="pageForm required-validate" enctype="multipart/form-data" onsubmit="return iframeCallback(this, dialogAjaxDone)">
    <input type="hidden" name="id" value="<?php echo ($vinfo["id"]); ?>">

    <div class="pageFormContent modal-body">

      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
          标签名称
        </label>
        <div class="col-xs-12 col-sm-10">
          <input type="text" class="form-control" name="tag_name" minlength="2" maxlength="15" value="<?php echo ($vinfo["name"]); ?>" required>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-default close-m" type="button">取消</button>
      <button class="btn btn-primary" type="submit">保存</button>
    </div>

  </form>
</div>