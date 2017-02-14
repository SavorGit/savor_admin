<?php if (!defined('THINK_PATH')) exit();?><div class="pageContent">
  <div id="dz-filecontainer">
    <div class="tab-content">
	   	<input id="oss_host" type="hidden" value="<?php echo ($oss_host); ?>">
		  <form id="dropbase-form" method="post" name=theform action="<?php echo ($host_name); ?>/version/addVersion" class="pageForm required-validate" enctype="multipart/form-data" onsubmit="return iframeCallback(this, dialogAjaxDone)">
		   	<input type="hidden" name="vid" value="<?php echo ($vinfo["id"]); ?>">
		   	<input type="hidden" name="oss_addr" id='oss_addr' value=''>
		   	<input type="hidden" name="oss_filesize" id='oss_filesize' value=''>
		    <div class="pageFormContent modal-body">
		         <div class="form-group row">
		              <label class="col-xs-12 col-sm-2 control-label">
		                设备类型：
		              </label>
		              <div class="col-xs-12 col-sm-10">
		                <?php $_result=C('DEVICE_TYPE');if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($key < 4): ?><input name="devicetype" type="radio" id="<?php echo ($key); ?>" value="<?php echo ($key); ?>" <?php if($key == 1): ?>checked<?php endif; ?>/>&nbsp;
		                  <?php if($key == 3): ?>客户端<?php else: echo ($vo); endif; ?>
		                  &nbsp;&nbsp;<?php endif; endforeach; endif; else: echo "" ;endif; ?>
		              </div>
		         </div>
		         <div class="form-group row" id="clienttype" style="display:none">
		              <label class="col-xs-12 col-sm-2 control-label">
		                客户端类型：
		              </label>
		              <div class="col-xs-12 col-sm-10">
		                <?php $_result=C('DEVICE_TYPE');if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($key > 2): ?><input name="clienttype" type="radio" id="<?php echo ($key); ?>" value="<?php echo ($key); ?>" <?php if($key == 3): ?>checked<?php endif; ?>/>&nbsp;<?php echo ($vo); ?>
		                  &nbsp;&nbsp;<?php endif; endforeach; endif; else: echo "" ;endif; ?>
		              </div>
		         </div>
		        <div class="form-group row">
			        <div class="col-xs-12 col-sm-12">
			        	<div id="ossfile">你的浏览器不支持flash,Silverlight或者HTML5！</div>
			        </div>
		       </div>
		        
		        <div class="form-group row" id="file_up">
    			    <label class="col-xs-12 col-sm-2 control-label">
    			    文件上传
		        	</label>
					<div class="col-xs-12 col-sm-10">
		               <a id="selectfiles" class="btn btn-success" href="javascript:void(0);" ><i class="fa fa-plus"></i> 选择文件</a>
				  		<a id="postfiles" class="btn btn-success" href="javascript:void(0);" ><i class="fa fa-upload"></i> 开始上传</a>
		        	</div>
		          </div>
		        <div class="form-group row" id="download_url">
			         <label class="col-xs-12 col-sm-2 control-label">
			          下载地址:
			        </label>
			        <div class="col-xs-12 col-sm-10">
			          <input type="text" class="form-control" id="media_url" value="" readonly>
			      	 </div>
			    </div>
		 		<div class="form-group row">
		              <label class="col-xs-12 col-sm-2 control-label">
		                版本名称：
		              </label>
		              <div class="col-xs-12 col-sm-10">
		                <input name="version_name" type="text" value="" class="form-control" required/>
		              </div>
		          </div>           		
		 		<div class="form-group row">
		              <label class="col-xs-12 col-sm-2 control-label">
		                版本号：
		              </label>
		              <div class="col-xs-12 col-sm-10">
		                <input name="version_code" type="text" value="" class="form-control" required/>
		              </div>
		          </div>           		
			      <div class="form-group row">
		              <label class="col-xs-12 col-sm-2 control-label">
		                版本描述：
		              </label>
		              <div class="col-xs-12 col-sm-10">
		                <textarea name="remark" type="textInput" class="form-control"></textarea>
		              </div>
           		 </div>			    
			    
		    </div>
		    <div class="modal-footer">
		      <button class="btn btn-default close-m" type="button">取消</button>
		      <button class="btn btn-primary" type="submit">提交</button>     
		    </div>
		  </form>
      </div>
  </div>
</div>
<script src='/Public/admin/assets/js/oss/upload.js'></script>
<script>
  $(function() {
    $("input[name='devicetype']").click(function() {
      var id = $(this).attr("id");
      if (id == 3) {
        $("#clienttype").show();
      } else {
        $("#clienttype").hide();
      }
      $("#file_up").show();
      $("#download_url").show();
    });
    $("input[name='clienttype']").click(function() {
      var id = $(this).attr("id");
      if (id == 4) {
        $("#file_up").hide();
        $("#download_url").hide();
      } else {
        $("#file_up").show();
        $("#download_url").show();
      }
    });
  });
</script>