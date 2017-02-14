<?php if (!defined('THINK_PATH')) exit();?><!--修改样式2 p元素自适应宽度 start-->
<div class="pageContent">
  <form method="post" action="<?php echo ($host_name); ?>/device/doAddBox" class="pageForm required-validate" enctype="multipart/form-data" onsubmit="return iframeCallback(this, dialogAjaxDone)">
    <input type="hidden" name="id" value="<?php echo ($vinfo["id"]); ?>">

    <div class="pageFormContent modal-body">
      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
          包间名称:
        </label>
        <div class="col-xs-12 col-sm-10">
         <select name="room_id" class="form-control bs-select">
        	<?php if(is_array($rooms)): $i = 0; $__LIST__ = $rooms;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>" <?php if($key == $vinfo['room_id']): ?>selected<?php endif; ?> ><?php echo ($vo); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
		</select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
          机顶盒名称:
        </label>
        <div class="col-xs-12 col-sm-10">
          <input type="text" class="form-control" name="name" minlength="1" maxlength="20" value="<?php echo ($vinfo["name"]); ?>" required>
        </div>
      </div>

      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
          mac地址:
        </label>
        <div class="col-xs-12 col-sm-10">
          <input type="text" class="form-control" name="mac" minlength="12" maxlength="12" value="<?php echo ($vinfo["mac"]); ?>" required>
        </div>
      </div>

      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
         切换时间:
        </label>
        <div class="col-xs-12 col-sm-10">
          <input type="text" class="form-control digits" name="switch_time" minlength="1" maxlength="20" value="<?php echo ($vinfo["switch_time"]); ?>">
        </div>
      </div>


        <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
         音量:
        </label>
        <div class="col-xs-12 col-sm-10">
          <input type="text" class="form-control" name="volum" minlength="1" maxlength="20" value="<?php echo ($vinfo["volum"]); ?>">
        </div>
      </div>
  
      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
          删除状态:
        </label>
        <div class="col-xs-12 col-sm-10">
         <select name="flag" class="form-control bs-select" title="请选择..." required>
              <option value="0" <?php if($vinfo["flag"] == 0): ?>selected<?php endif; ?>  >正常</option><br>
              <option value="1" <?php if($vinfo["flag"] == 1): ?>selected<?php endif; ?>  >删除</option>
          </select>
        </div>
      </div>

      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
          冻结状态:
        </label>
        <div class="col-xs-12 col-sm-10">
         <select name="state" class="form-control bs-select" title="请选择..." required>
           <option value="2" <?php if($vinfo["state"] == 2): ?>selected<?php endif; ?> >冻结</option><br>
           <option value="1" <?php if($vinfo["state"] == 1): ?>selected<?php endif; ?> >正常</option><br>
           <option value="3" <?php if($vinfo["state"] == 3): ?>selected<?php endif; ?> >报损</option>
          </select>
        </div>
      </div>
            
    </div>

    <div class="modal-footer">
      <button class="btn btn-default close-m" type="button">取消</button>
      <button class="btn btn-primary" type="submit">保存</button>     
    </div>

  </form>
</div>