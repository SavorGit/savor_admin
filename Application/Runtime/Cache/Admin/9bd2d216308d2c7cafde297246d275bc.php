<?php if (!defined('THINK_PATH')) exit();?><!--修改样式2 p元素自适应宽度 start-->
<div class="pageContent">
  <form method="post" action="<?php echo ($host_name); ?>/sysusergroup/sysusergroupAdd" class="pageForm required-validate" enctype="multipart/form-data" onsubmit="return iframeCallback(this, dialogAjaxDone)">
    <input type="hidden" name="acttype" value="<?php echo ($acttype); ?>">
    <input type="hidden" name="id" value="<?php echo ($vinfo["id"]); ?>">
    <div class="pageFormContent modal-body">
      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
          权限分组名：
        </label>
        <div class="col-xs-12 col-sm-10">
          <input name="name" type="text" value="<?php echo ($vinfo["name"]); ?>" class="form-control" required/>
          <p class="form-heading">允许查看模块：</p>
          <div class="row sm-row" data-check="list" data-parent=".pageContent">
            <?php if(is_array($groupList)): $n = 0; $__LIST__ = $groupList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($n % 2 );++$n;?><div class="col-xs-4 col-sm-3 col-md-2">
		            <label>
		              	<input type="checkbox" name="rank[]" value="<?php echo ($vo["rank"]); ?>" 
		              	<?php if(is_array($vinfo["code"])): $i = 0; $__LIST__ = $vinfo["code"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vos): $mod = ($i % 2 );++$i; if($vo["rank"] == $vos): ?>checked  
		              	<?php if(($vo['rank'] == 'sysusergroup.sysusergroupList') or ($vo['rank'] == 'user.userList')): ?>readonly<?php endif; endif; endforeach; endif; else: echo "" ;endif; ?>>&nbsp;<?php echo ($vo["title"]); ?>
		            </label>
	            </div><?php endforeach; endif; else: echo "" ;endif; ?>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-footer">
	    <div class="pull-left">
	    	<label>
		     	<input type="checkbox" data-check="all" data-parent=".pageContent"> 全部
		    </label>
		  </div>
      <button class="btn btn-default close-m" type="button">取消</button>
      <button class="btn btn-success" type="button" data-check="invert" data-parent=".pageContent">反选</button>
      <button class="btn btn-primary" type="submit">保存</button>     
    </div>
  </form>
</div>