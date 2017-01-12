<?php if (!defined('THINK_PATH')) exit();?><!--修改样式2 p元素自适应宽度 start-->
<div class="pageContent">
  <form method="post" action="<?php echo ($host_name); ?>/hotel/doAdd" class="pageForm required-validate" enctype="multipart/form-data" onsubmit="return iframeCallback(this, dialogAjaxDone)">
    <input type="hidden" name="id" value="<?php echo ($vinfo["id"]); ?>">

    <div class="pageFormContent modal-body">

      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
          酒店名称:
        </label>
        <div class="col-xs-12 col-sm-10">
          <input type="text" class="form-control" name="name" minlength="2" maxlength="20" value="<?php echo ($vinfo["name"]); ?>" required>
        </div>
      </div>

      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
          酒店地址:
        </label>
        <div class="col-xs-12 col-sm-10">
          <input type="text" class="form-control" name="addr" minlength="2" maxlength="20" value="<?php echo ($vinfo["addr"]); ?>" required>
        </div>
      </div>

      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
          酒店区域:
        </label>
        <div class="col-xs-12 col-sm-10">
          <select name="area_id" class="form-control bs-select" title="请选择..." required>
              
             <?php if(is_array($area)): $i = 0; $__LIST__ = $area;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$row): $mod = ($i % 2 );++$i;?><option value="<?php echo ($row['id']); ?>" <?php if($row['id'] == $vinfo['area_id']): ?>selected<?php endif; ?> > <?php echo ($row['name']); ?> </option><?php endforeach; endif; else: echo "" ;endif; ?>

          </select>
        </div>
      </div>

      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
         酒店维护人：
        </label>
        <div class="col-xs-12 col-sm-10">
          <input type="text" class="form-control" name="contactor" minlength="1" maxlength="20" value="<?php echo ($vinfo["contactor"]); ?>" required>
        </div>
      </div>

        <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
          手机:
        </label>
        <div class="col-xs-12 col-sm-10">
          <input name="mobile" type="text" class="form-control digits" value="<?php echo ($vinfo["mobile"]); ?>" />
        </div>
      </div>

      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
          固定电话:
        </label>
        <div class="col-xs-12 col-sm-10">
          <input name="tel" type="text" class="form-control" value="<?php echo ($vinfo["tel"]); ?>" />
        </div>
      </div>

      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
         酒楼级别：
        </label>
        <div class="col-xs-12 col-sm-10">
          <input type="text" class="form-control" name="level" value="<?php echo ($vinfo["level"]); ?>" >
        </div>
      </div>

      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
          是否重点：
        </label>
        <div class="col-xs-12 col-sm-10">
         <select name="iskey" class="form-control bs-select" title="请选择..." required>
              <option value="1" <?php if($vinfo["iskey"] == 1): ?>selected<?php endif; ?> >是</option><br>
              <option value="2" <?php if($vinfo["iskey"] == 2): ?>selected<?php endif; ?> >否</option>
          </select>
        </div>
      </div>


       <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
          安装日期:：
        </label>
        <div class="col-xs-12 col-sm-10">
           <div class="input-group date form_datetime" data-date="<?php echo ($vinfo["install_date"]); ?>">
                  <input name="install_date" type="text" size="16" class="form-control date" placeholder="开始日期" value="<?php echo ($vinfo["install_date"]); ?>" readonly>
                  <span class="input-group-btn">
                    <button class="btn default date-reset" type="button"><i class="fa fa-times"></i></button>
                    <button class="btn btn-success date-set" type="button"><i class="fa fa-calendar"></i></button>
                  </span>
            </div> 
        </div>
      </div>


      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
          酒楼状态:
        </label>
        <div class="col-xs-12 col-sm-10">
         <select name="state" class="form-control bs-select" title="请选择..." required>
              <option value="1" <?php if($vinfo["state"] == 1): ?>selected<?php endif; ?> >正常</option><br>
              <option value="2" <?php if($vinfo["state"] == 2): ?>selected<?php endif; ?> >冻结</option><br>
              <option value="3" <?php if($vinfo["state"] == 3): ?>selected<?php endif; ?> >报损</option>
          </select>
        </div>
      </div>

      
       <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
         状态变更说明:
        </label>
        <div class="col-xs-12 col-sm-10">
          <input type="text" class="form-control" name="state_change_reason">
        </div>
      </div>

      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
         备注:
        </label>
        <div class="col-xs-12 col-sm-10">
          <input type="text" class="form-control" name="remark">
        </div>
      </div>

      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
         GPS:
        </label>
        <div class="col-xs-12 col-sm-10">
          <input type="text" class="form-control" name="gps">
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

            
    </div>

    <div class="modal-footer">
      <button class="btn btn-default close-m" type="button">取消</button>
      <button class="btn btn-primary" type="submit">保存</button>     
    </div>

  </form>
</div>