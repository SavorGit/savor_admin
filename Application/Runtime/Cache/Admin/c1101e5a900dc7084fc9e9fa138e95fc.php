<?php if (!defined('THINK_PATH')) exit();?><!--修改样式2 p元素自适应宽度 start-->
<div class="pageContent">
  <form method="post" action="<?php echo ($host_name); ?>/user/userRank" class="pageForm required-validate" onsubmit="return validateCallback(this, dialogAjaxDone)">
    <input type="hidden" name="acttype" value="<?php echo ($acttype); ?>">
    <input type="hidden" name="uid" value="<?php echo ($vinfo["id"]); ?>">
    <div class="pageFormContent modal-body">
      <div class="form-group row group-static">
        <label class="col-xs-12 col-sm-2 control-label">
          用户昵称：
        </label>
        <div class="col-xs-12 col-sm-10">
          <p class="form-control-static"><?php echo ($vinfo["remark"]); ?></p>
        </div>
      </div>
      <div class="form-group row group-static">
        <label class="col-xs-12 col-sm-2 control-label">
          登录名称：
        </label>
        <div class="col-xs-12 col-sm-10">
          <p class="form-control-static"><?php echo ($vinfo["username"]); ?></p>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
          用户分组：
        </label>
        <div class="col-xs-12 col-sm-10">
          <select name="group" id="group" class="form-control bs-select" title="请选择分组...">
          <!--<option value=0>未分组</option>-->
          <?php if(is_array($groupslist)): $i = 0; $__LIST__ = $groupslist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>" groupid="<?php echo ($vo["id"]); ?>" <?php if($vinfo['gid'] == $vo['id']): ?>selected<?php endif; ?>> <?php echo ($vo["name"]); ?> </option><?php endforeach; endif; else: echo "" ;endif; ?>
          </select>
          <p class="form-heading">允许查看模块：</p>
          <div class="row sm-row" id="allow_module">
            <?php if(is_array($userrank)): $n = 0; $__LIST__ = $userrank;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($n % 2 );++$n;?><div class="col-xs-4 col-sm-3 col-md-2">
              <input type="checkbox" name="rank[]" value="<?php echo ($vo["rank"]); ?>" disabled <?php if(is_array($vinfo["code"])): $i = 0; $__LIST__ = $vinfo["code"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vos): $mod = ($i % 2 );++$i; if($vo["rank"] == $vos): ?>checked<?php endif; endforeach; endif; else: echo "" ;endif; ?>>&nbsp;<?php echo ($vo["title"]); ?>
            </div><?php endforeach; endif; else: echo "" ;endif; ?>
          </div>
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
$(function(){
  $("#group").change(function(){
    var gid = $(this).find(":selected").attr("groupid");
    $.post("<?php echo ($host_name); ?>/user/currentRank", { gid:gid },function(data){
      $("#allow_module").empty();
      $("#allow_module").html(data);
      });
  })
})
</script>