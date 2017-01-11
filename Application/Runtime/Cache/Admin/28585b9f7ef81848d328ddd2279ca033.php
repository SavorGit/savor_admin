<?php if (!defined('THINK_PATH')) exit();?><!--修改样式2 p元素自适应宽度 start-->
<div class="pageContent">
  <form method="post" action="<?php echo ($host_name); ?>/sysmenu/sysmenuAdd" class="pageForm required-validate" enctype="multipart/form-data" onsubmit="return iframeCallback(this, dialogAjaxDone)">
    <input type="hidden" name="acttype" value="<?php echo ($acttype); ?>">
    <input type="hidden" name="id" value="<?php echo ($vinfo["id"]); ?>">
    <div class="pageFormContent modal-body">
      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
          节点key：
        </label>
        <div class="col-xs-12 col-sm-10">
          <select name="nodekey" class="form-control bs-select" title="请选择..." required>
            <?php $_result=C('MANGER_KEY');if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>" <?php if($key == $vinfo['nodekey']): ?>selected<?php endif; ?>><?php echo ($vo); ?></option><br><?php endforeach; endif; else: echo "" ;endif; ?>
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
          模块名：
        </label>
        <div class="col-xs-12 col-sm-10">
          <input type="text" class="form-control" name="modulename" minlength="4" maxlength="20" value="<?php echo ($vinfo["modulename"]); ?>" required>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
          菜单级别：
        </label>
        <div class="col-xs-12 col-sm-10">
          <select name="menulevel" class="form-control bs-select" title="请选择..." required>
            <?php $_result=C('MANGER_LEVEL');if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>" <?php if($key == $vinfo['menulevel']): ?>selected<?php endif; ?>><?php echo ($vo); ?></option><br><?php endforeach; endif; else: echo "" ;endif; ?>
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
          显示顺序：
        </label>
        <div class="col-xs-12 col-sm-10">
          <input name="displayorder" type="text" class="form-control digits" value="<?php echo ($vinfo["displayorder"]); ?>" required/>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
          模块权限：
        </label>
        <div class="col-xs-12 col-sm-10">
          <input type="text" class="form-control" name="code" id="code" value="<?php echo ($vinfo["code"]); ?>" <?php if($acttype == 1): ?>readonly<?php endif; ?>>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
          模块JS：
        </label>
        <div class="col-xs-12 col-sm-10">
          <input type="text" class="form-control" name="jstext" id="jstext" value="<?php echo ($vinfo["jstext"]); ?>" <?php if($acttype == 1): ?>readonly<?php endif; ?>>
        </div>
      </div>
      <!-- <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
          状态：
        </label>
        <div class="col-xs-12 col-sm-10">
          <input type="hidden" name="isenable" value="0">
          <input type="checkbox" value="1" class="make-switch status" name="isenable" data-size="small" data-on-text="启用" data-off-text="禁用" <?php if($vinfo["isenable"] != 0 or 2): ?>checked<?php endif; ?>>
        </div>
      </div> -->
      
      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
          状态：
        </label>
        <div class="col-xs-12 col-sm-10">
          <input type="hidden" name="isenable" value="2">
          <input type="checkbox" value="1" class="make-switch status" name="isenable" data-size="small" data-on-text="启用" data-off-text="禁用" <?php if($vinfo["isenable"] != 2): ?>checked<?php endif; ?>>
        </div>
      </div>
            
    </div>

    <div class="modal-footer">
      <button class="btn btn-default close-m" type="button">取消</button>
      <button class="btn btn-primary" type="submit">保存</button>     
    </div>
  </form>
</div>