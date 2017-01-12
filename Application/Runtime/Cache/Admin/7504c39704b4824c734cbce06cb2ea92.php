<?php if (!defined('THINK_PATH')) exit();?><!--修改样式2 p元素自适应宽度 start-->
<div class="pageContent">
  <form method="post" action="<?php echo ($host_name); ?>/hotel/doAddRoom" class="pageForm required-validate" enctype="multipart/form-data" onsubmit="return iframeCallback(this, dialogAjaxDone)">
    <input type="hidden" name="id" value="<?php echo ($vinfo["id"]); ?>">

    <div class="pageFormContent modal-body">

      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
          酒店ID:
        </label>
        <div class="col-xs-12 col-sm-10">
           <input type="text" class="form-control" name="hotel_id" minlength="2" maxlength="20" value="<?php echo ($vinfo["hotel_id"]); ?>" required>
        </div>
      </div>


      <?php if(!empty($vinfo['hotel_name'])): ?><div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
          酒店名称:
        </label>
        <div class="col-xs-12 col-sm-10">
           <input type="text" class="form-control" minlength="2" maxlength="20"  readonly="readonly" value="<?php echo ($vinfo["hotel_name"]); ?>" >
        </div>
      </div><?php endif; ?> 


      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
          包间名称:
        </label>
        <div class="col-xs-12 col-sm-10">
          <input type="text" class="form-control" name="name" minlength="2" maxlength="20" value="<?php echo ($vinfo["name"]); ?>" required>
        </div>
      </div>

      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
          包间类型:
        </label>
        <div class="col-xs-12 col-sm-10">
          <select name="type" class="form-control bs-select" title="请选择..." required>
              <option value="1" <?php if($vinfo["type"] == 1): ?>selected<?php endif; ?>  >包间</option><br>
              <option value="2" <?php if($vinfo["type"] == 2): ?>selected<?php endif; ?>  >大厅</option><br>
              <option value="3" <?php if($vinfo["type"] == 3): ?>selected<?php endif; ?>  >等候区</option>
          </select>
        </div>
      </div>

      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
         备注
        </label>
        <div class="col-xs-12 col-sm-10">
          <input type="text" class="form-control" name="remark" minlength="1" maxlength="20" value="<?php echo ($vinfo["remark"]); ?>">
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
              <option value="0" <?php if($vinfo["state"] == 0): ?>selected<?php endif; ?>  >正常</option><br>
              <option value="1" <?php if($vinfo["state"] == 1): ?>selected<?php endif; ?>  >删除</option>
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