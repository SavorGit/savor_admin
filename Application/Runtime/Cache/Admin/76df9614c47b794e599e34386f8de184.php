<?php if (!defined('THINK_PATH')) exit();?><!--修改样式2 p元素自适应宽度 start-->
<style type="text/css">
	.zhezhao{
			display: none;
			position: absolute;
			top: 0;
			bottom: 0;
			right: 0;
			left: 0;
			background-color: black;
			opacity: 0.7;
			text-align: center;
			z-index: 999;
		}
	.big{
			display: none;
		}
	.addbig{
		position: absolute;
		width: 500px;
		height: 500px;
		top: 100px;
		left: 26%;
		z-index: 1000;
	}
</style>
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
           logo图片：
         </label>
              <div class="col-xs-12 col-sm-10">
                <div class="fileinput fileinput-new" data-fileinput>
                  <div class="fileinput-preview thumbnail" data-trigger="fileinput">
                    <a data-target="#modal-file" href="javascript:void(0)">
                      <?php if(($vinfo['oss_addr'] == 'NULL') OR $vinfo['oss_addr'] == ''): ?><img id="media_idimg" src="/Public/admin/assets/img/noimage.png" border="0" />
                        <span id="media_idimgname"></span>
                      <?php else: ?>
                        <img id="media_idimg" src="<?php echo ($vinfo["oss_addr"]); ?>" border="0" /><?php endif; ?>
                      <span id="media_idimgname"></span>
                    </a>
                  </div>
                  <div>
                    <a class="btn btn-success btn-file" data-target="#modal-file" href="<?php echo ($host_name); ?>/resource/uploadResource?filed=media_id&rtype=2" data-browse-file>
                      选择图片                     
                    </a>
                    <input type="hidden" name="media_id" id="media_id" value="<?php echo ($vinfo["media_id"]); ?>" required>
                    <a href="javascript:;" class="btn btn-danger" data-remove-file="/Public/admin/assets/img/noimage.png">
                    删除 </a>
                  </div>
                </div>
              </div>
            </div>
            
      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
          酒店地址:
        </label>
        <div class="col-xs-12 col-sm-10">
          <input type="text" class="form-control" name="addr" minlength="2"  value="<?php echo ($vinfo["addr"]); ?>" required>
        </div>
      </div>

      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
          酒店区域:
        </label>
        <div class="col-xs-12 col-sm-10">
          <select name="area_id" class="form-control bs-select" title="请选择..." data-size="20" required>
              
             <?php if(is_array($area)): $i = 0; $__LIST__ = $area;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$row): $mod = ($i % 2 );++$i;?><option value="<?php echo ($row['id']); ?>" <?php if($row['id'] == $vinfo['area_id']): ?>selected<?php endif; ?> > <?php echo ($row['region_name']); ?> </option><?php endforeach; endif; else: echo "" ;endif; ?>

          </select>
        </div>
      </div>

      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
         酒店联系人：
        </label>
        <div class="col-xs-12 col-sm-10">
          <input type="text" class="form-control" name="contractor" minlength="1" required value="<?php echo ($vinfo["contractor"]); ?>" >
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
                对账单联系人:
            </label>
            <div class="col-xs-12 col-sm-10">
                <input name="bill_per" type="text" class="form-control" value="<?php echo ($vinfo["bill_per"]); ?>" />
        </div>
        </div>


        <div class="form-group row">
            <label class="col-xs-12 col-sm-2 control-label">
                对账单联系人手机:
            </label>
            <div class="col-xs-12 col-sm-10">
                <input name="bill_tel" type="text" class="form-control" value="<?php echo ($vinfo["bill_tel"]); ?>" />
            </div>
        </div>

      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
         合作维护人：
        </label>
        <div class="col-xs-12 col-sm-10">
          <input type="text" class="form-control" name="maintainer" required minlength="1" maxlength="20" value="<?php echo ($vinfo["maintainer"]); ?>" >
        </div>
      </div>

        <div class="form-group row">
            <label class="col-xs-12 col-sm-2 control-label">
                技术运维人：
            </label>
            <div class="col-xs-12 col-sm-10">
                <input type="text" class="form-control" name="techmaintainer" required minlength="1" maxlength="20" value="<?php echo ($vinfo["tech_maintainer"]); ?>" >
            </div>
        </div>
        <div class="form-group row">
            <label class="col-xs-12 col-sm-2 control-label">
                小平台MAC地址：
            </label>
            <div class="col-xs-12 col-sm-10">
                <input type="text" class="form-control" name="mac_addr" id="mac_addr"  value="<?php echo ($vinfo["mac_addr"]); ?>" >
            </div>
        </div>

        <div class="form-group row macccc" style="display:none;">
            <label style="color:red;"class="col-xs-12 col-sm-2 control-label">

            </label>
            <div style="color:red;"  class="col-xs-12 col-sm-10">
            
            </div>
        </div>


        <div class="form-group row">
            <label class="col-xs-12 col-sm-2 control-label">
                小平台存放位置：
            </label>
            <div class="col-xs-12 col-sm-10">
                <input type="text" class="form-control" name="server_location" id="server_location"  value="<?php echo ($vinfo["server_location"]); ?>" >
            </div>
        </div>

        <div class="form-group row">
            <label class="col-xs-12 col-sm-2 control-label">
                远程ID：
            </label>
            <div class="col-xs-12 col-sm-10">
                <input type="text" class="form-control" name="remote_id" id="remote_id"  value="<?php echo ($vinfo["remote_id"]); ?>" >
            </div>
        </div>


        <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
         酒楼级别：
        </label>
          <div class="col-xs-12 col-sm-10">
          <select name="level" class="form-control bs-select" title="请选择..." required data-style="btn-success btn-sm" data-container="body">
              <?php $_result=C('HOTEL_LEVEL');if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>" <?php if($key == $vinfo['level']): ?>selected<?php endif; ?>><?php echo ($vo); ?></option><br><?php endforeach; endif; else: echo "" ;endif; ?>
          </select>
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
                <div class="input-group date form_datetime" data-date="<?php echo ($vinfo["install_date"]); ?>" data-ymd="true">
                    <input name="install_date" type="text" size="16" class="form-control date" placeholder="开始日期" value="<?php echo ($vinfo["install_date"]); ?>" readonly >
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
         <select name="state" id="state" class="form-control bs-select" required>
             <option value="2" <?php if($vinfo["state"] == 2): ?>selected<?php endif; ?> >冻结</option><br>
              <option value="1" <?php if($vinfo["state"] == 1): ?>selected<?php endif; ?> >正常</option><br>
              <option value="3" <?php if($vinfo["state"] == 3): ?>selected<?php endif; ?> >报损</option>
          </select>
        </div>
      </div>

       <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
         状态变更说明:
        </label>
        <div class="col-xs-12 col-sm-10">
            <select name="state_change_reason" style="width: 20px" class="form-control bs-select class-filter" data-style="btn-success btn-sm">
                <?php $_result=C('STATE_REASON');if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>" <?php if($key == $vinfo['state_change_reason']): ?>selected<?php endif; ?>><?php echo ($vo); ?></option><br><?php endforeach; endif; else: echo "" ;endif; ?>
            </select>
        </div>
      </div>

      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
         备注:
        </label>
        <div class="col-xs-12 col-sm-10">
          <input type="text" value="<?php echo ($vinfo["remark"]); ?>" class="form-control" name="remark">
        </div>
      </div>
	  <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
         酒楼机顶盒类型：
        </label>
          <div class="col-xs-12 col-sm-10">
          <select name="hotel_box_type" class="form-control bs-select" title="请选择..." required data-style="btn-success btn-sm" data-container="body">
              <?php $_result=C('hotel_box_type');if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>" <?php if($key == $vinfo['hotel_box_type']): ?>selected<?php endif; ?>><?php echo ($vo); ?></option><br><?php endforeach; endif; else: echo "" ;endif; ?>
          </select>
              </div>
      </div>
	  
      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
         酒楼位置坐标:
        </label>
        <div class="col-xs-12 col-sm-10">

          <input value="<?php echo ($vinfo["gps"]); ?>" type="text" required placeholder ="请输入经纬度并以,隔开" class="form-control" name="gps">

        	<a href="http://api.map.baidu.com/lbsapi/getpoint/index.html" target="_blank">查找坐标:http://api.map.baidu.com/lbsapi/getpoint/index.html</a>

        </div>
      </div>
      <div class="form-group row">
        <label class="col-xs-12 col-sm-2 control-label">
          删除状态:
        </label>
        <div class="col-xs-12 col-sm-10">
         <select name="flag" class="form-control bs-select" title="请选择..." required>
              <option value="0" <?php if($vinfo["flag"] == 0): ?>selected<?php endif; ?>  >正常</option>
              <option value="1" <?php if($vinfo["flag"] == 1): ?>selected<?php endif; ?>  >删除</option>
          </select>
        </div>
      </div>

        <div class="form-group row">
            <label class="col-xs-12 col-sm-2 control-label">
                酒楼wifi:
            </label>
            <div class="col-xs-12 col-sm-10">
                <input type="text" value="<?php echo ($vinfo["hotel_wifi"]); ?>" class="form-control" name="hotel_wifi">
            </div>
        </div>

        <div class="form-group row">
            <label class="col-xs-12 col-sm-2 control-label">
                酒楼wifi密码:
            </label>
            <div class="col-xs-12 col-sm-10">
                <input type="text" value="<?php echo ($vinfo["hotel_wifi_pas"]); ?>" class="form-control" name="hotel_wifi_pas">
            </div>
        </div>

            
    </div>

    <div class="modal-footer">
      <button class="btn btn-default close-m" type="button">取消</button>
      <button class="btn btn-primary" id="saveinfos" type="submit">保存</button>
    </div>
    	<div class="zhezhao"></div>
			<img class="big" src=""/>
  </form>
</div>
<script type="text/javascript">
    $(function(){
	$('#media_idimg').click(function(){
		var $a = $(this).attr('src');
			$('.big').prop('src',$a).addClass('addbig')
			$('.zhezhao').show(500);
			$('.big').show(500);
	})
  $('.zhezhao').click(function(){

		$('.zhezhao').hide(500);
		$('.big').hide(500);
   })
        $('#mac_addr').blur(function(){
                var vp = $(this).val();
                var ln = $(this).val().length;
                var reg = /^[0-9A-F]+$/;
                var r = vp.match(reg);
                if(ln==0){
                    $('.macccc').css('display','none');
                }else if(ln!=12){
                    $('.macccc').css('display','');
                    $('.macccc div').html('请输入12位字符');

                }else{
                    if(r==null) {
                        $('.macccc').css('display', '');
                        $('.macccc div').html('不允许输入非法字符，请输入正确字符');

                    }else{
                        $('.macccc').css('display','none');
                    }
                }


        });

        $('#saveinfos').click(function(){
            var vp = $('#mac_addr').val();
            var ln = $('#mac_addr').val().length;
            var reg = /^[0-9A-F]+$/;
            var r = vp.match(reg);
            if(ln==0){
                $('.macccc').css('display','none');
                return true;
            }else if(ln!=12){
                $('.macccc').css('display','');
                $('.macccc div').html('请输入12位字符');
                return false;
            }else{
                if(r==null) {
                    $('.macccc').css('display', '');
                    $('.macccc div').html('不允许输入非法字符，请输入正确字符');
                    return false;
                }else{
                    $('.macccc').css('display','none');
                    return true;
                }
            }
        });


    });


    /*function dialogAjaxDone(json){
        //上一次的rel链接

        //  $.pdialog.open(url, dlgId, title);
        //   navTab.closeCurrentTab();
        // navTab.closeTab(json.navTabId);
        DWZ.ajaxDone(json);
        // alert(json);
        jap = JSON.stringify(json);
        console.log(jap);
        var $pagerForm = $("#pagerForm", navTab.getCurrentPanel());
        var args = $pagerForm.size()>0 ? $pagerForm.serializeArray() : {}
        console.log(navTab.getCurrentPanel());
        console.log($pagerForm);
        alert(jap);
        // navTab.closeTab('clientconfig/addclientconfig');
        // navTab.reload('clientconfig/configdata');
        //navTab.reloadFlag('Sysconfig/configData');
        //注意返回的JSON的数据结构
        //navTab.closeTab('abce');
        if (json.status == DWZ.statusCode.ok){
            if (json.navTabId){
                $.pdialog.closeCurrent();
                // navTab.reload('clientconfig/configdatar');
            }


        }

    }*/
</script>