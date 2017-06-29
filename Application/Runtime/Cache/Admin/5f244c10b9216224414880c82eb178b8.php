<?php if (!defined('THINK_PATH')) exit();?><!--修改样式2 p元素自适应宽度 start-->
<style type="text/css">

</style>
<div class="pageContent">
  <form method="post" action="<?php echo ($host_name); ?>/sysnode/sysnodeAdd" class="pageForm required-validate" enctype="multipart/form-data" onsubmit="return iframeCallback(this, dialogAjaxDone)">
    <input type="hidden" name="acttype" value="<?php echo ($acttype); ?>">
    <input type="hidden" name="id" value="<?php echo ($vinfo["id"]); ?>">
    <input type="hidden" name="sysmenuid" value="<?php echo ($vinfo["sysmenuid"]); ?>">
    <input type="hidden" id="passid" name="passid" value="<?php echo ($vinfo["parentid"]); ?>">
    <div class="pageFormContent modal-body">
      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
          节点key：
        </label>
        <div class="col-xs-12 col-sm-10">
          <select id="nodekey" name="nodekey" class="form-control bs-select" title="请选择..." required>
            <?php $_result=C('MANGER_KEY');if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>" <?php if($key == $vinfo['nodekey']): ?>selected<?php endif; ?>><?php echo ($vo); ?></option><br><?php endforeach; endif; else: echo "" ;endif; ?>
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
          模块名：
        </label>
        <div class="col-xs-12 col-sm-10">
          <input type="text" class="form-control" name="modulename" minlength="1" maxlength="40" value="<?php echo ($vinfo["name"]); ?>" required>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
          菜单级别：
        </label>
        <div class="col-xs-12 col-sm-10">
          <select id="menulevel" name="menulevel" class="form-control bs-select" title="请选择..."  required>
            <?php $_result=C('MANGER_LEVEL');if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>" <?php if($key == $vinfo['menulevel']): ?>selected<?php endif; ?>><?php echo ($vo); ?></option><br><?php endforeach; endif; else: echo "" ;endif; ?>
          </select>
        </div>
      </div>



      <div class="form-group row" id="sechidden">
        <label class="col-xs-12 col-sm-2 control-label">
          对应二级栏目列表：
        </label>
        <div class="col-xs-12 col-sm-10">
          <select name="secid" id="secid" class="form-control" title="请选择..." >
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
          M模块：
        </label>
        <div class="col-xs-12 col-sm-10">
          <input type="text" class="form-control" name="mfield" id="mfield" value="<?php echo ($vinfo["mfield"]); ?>" <?php if($acttype == 1): ?>readonly<?php endif; ?>>
        </div>
      </div>

      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
          控制器：
        </label>
        <div class="col-xs-12 col-sm-10">
          <input type="text" class="form-control" name="cfield" id="cfield" value="<?php echo ($vinfo["cfield"]); ?>" <?php if($acttype == 1): ?>readonly<?php endif; ?>>
        </div>
      </div>


      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
          方法：
        </label>
        <div class="col-xs-12 col-sm-10">
          <input type="text" class="form-control" name="afield" id="afield" value="<?php echo ($vinfo["afield"]); ?>" <?php if($acttype == 1): ?>readonly<?php endif; ?>>
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
      <?php if($vinfo["menulevel"] == 0): ?><div class="form-group row" id="defaultpic">
              <label class="col-xs-12 col-sm-2 control-label">
               		 默认图标：
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
            
             <div class="form-group row" id="choosemepic">
              <label class="col-xs-12 col-sm-2 control-label">
                	选中图标：
              </label>
              <div class="col-xs-12 col-sm-10">
                <div class="fileinput fileinput-new" data-fileinput>
                  <div class="fileinput-preview thumbnail" data-trigger="fileinput">
                    <a data-target="#modal-file" href="javascript:void(0)">
                      <?php if(($vinfo['select_oss_addr'] == 'NULL') OR $vinfo['select_oss_addr'] == ''): ?><img id="select_media_idimg" src="/Public/admin/assets/img/noimage.png" border="0" />
                        <?php else: ?>
                        <img id="select_media_idimg" src="<?php echo ($vinfo["select_oss_addr"]); ?>" border="0" /><?php endif; ?>
                       <span id="select_media_idimgname"></span>
                    </a>
                  </div>
                  <div>
                    <a class="btn btn-success btn-file" data-target="#modal-file" href="<?php echo ($host_name); ?>/resource/uploadResourceNew?filed=select_media_id&rtype=2" data-browse-file>
                      选择图片
                    </a>
                    <input type="hidden" name="select_media_id" id="select_media_id" value="">
                    <a href="javascript:;" class="btn btn-danger" data-remove-file="/Public/admin/assets/img/noimage.png">
                      删除 </a>
                  </div>
                </div>
              </div>
            </div><?php endif; ?>
      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
          状态：
        </label>
        <div class="col-xs-12 col-sm-10">
          <input type="hidden" name="isenable" value="2">
          <input type="checkbox" value="1" class="make-switch status" name="isenable" data-size="small" data-on-text="启用" data-off-text="禁用" <?php if($vinfo["isenable"] != 2): ?>checked<?php endif; ?>>
        </div>
      </div>


      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
          输入框：
        </label>
        <div class="col-xs-12 col-sm-10 form-inline" style="margin-top:8px">
          <div class="checkbox" style="margin-left:10px;">
            <input id="radio2"  class="styled" type="radio" name="ertype" value="2" <?php if($vinfo["ertype"] == 2): ?>checked<?php endif; ?>>
            <label for="radio2" class="font-bolder">
              原生js
            </label>
          </div>

          <div class="checkbox" style="margin-left:20px;">
            <input id="radio1" class="styled" type="radio" name="ertype" value="1" <?php if($vinfo["ertype"] == 1): ?>checked<?php endif; ?>>
            <label for="radio1" class="font-bolder">
              系统自带
            </label>
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
<script type="text/javascript">
  var  gaid = $("#menulevel").val();
  var nodekeys = $("#nodekey").val();
  if(gaid == 2){
    var passid = $("#passid").val();
    $("#sechidden").css('display','');
    $("#mopermi").css('display','none');
    $("#mopejs").css('display','none');

    $.ajax({
      type: 'POST',
      //设置json格式,接收返回数组。
      dataType: 'json',
      url: '<?php echo ($host_name); ?>/sysnode/getnodeinfo',
      //ajax传递当前选项的value值,也就是当前的region_id。
      data:"nokey="+nodekeys,
      success: function(data) {
        console.log(data);
        //如果返回值不为空则执行。
        if (data != null) {
          var option_str = '';
          //循环书写下一个select中要添加的内容。并添加name标记。
          for (var i = 0; i < data.length; i++) {
            if(data[i].id == passid){
              option_str+='<option selected="selected" value="'+data[i].id+'">'+data[i].name+'</option>';
            }else{
              option_str+='<option value="'+data[i].id+'">'+data[i].name+'</option>';
            }

          }
          //向下一个select中添加书写好的内容。
          $('#secid').append(option_str);
        }
      }

    })
  }else{
    $("#sechidden").css('display','none');
    $("#mopermi").css('display','');
    $("#mopejs").css('display','');
    $("#defaultpic").css('display','');
    $("#choosemepic").css('display','');
  }

  $("#menulevel").change(function() {
    var  gid = $("#menulevel").val();
    var nodekey = $("#nodekey").val();
    if(gid == 2){
      $("#sechidden").css('display','');
      $("#defaultpic").css('display','none');
      $("#choosemepic").css('display','none');
      $("#mopermi").css('display','none');
      $("#mopejs").css('display','none');
      $('#secid').empty();
      $.ajax({
        type: 'POST',
        //设置json格式,接收返回数组。
        dataType: 'json',
        url: '<?php echo ($host_name); ?>/sysnode/getnodeinfo',
        //ajax传递当前选项的value值,也就是当前的region_id。
        data:"nokey="+nodekey,
        success: function(data) {
          console.log(data);

          //如果返回值不为空则执行。
          if (data != null) {
            var option_str = '';
            //循环书写下一个select中要添加的内容。并添加name标记。
            for (var i = 0; i < data.length; i++) {
              option_str+='<option value="'+data[i].id+'">'+data[i].name+'</option>';
            }
            //向下一个select中添加书写好的内容。
            $('#secid').append(option_str);
          }
        }

      })
    }else{
      $("#sechidden").css('display','none');
      $("#defaultpic").css('display','');
      $("#choosemepic").css('display','');
      $("#mopermi").css('display','');
      $("#mopejs").css('display','');
      $('#secid').empty();
      return false;
    }

  });


  function setlevel(obj) {
    console.log(obj);


  }
</script>