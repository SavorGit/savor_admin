<?php if (!defined('THINK_PATH')) exit();?><div class="pageContent">
  <div id="dz-filecontainer">
    <ul style="margin-bottom:10px;" class="nav nav-tabs">
      <li class="active"><a href="#dropbase" data-toggle="tab"><span>选择本地内容</span></a></li>
      <li class=""><a href="#files" data-toggle="tab"><span>选择资源库</span></a></li>
      <li class="pull-right"><a data-dismiss="modal" href="#">&times;</a></li>
    </ul>
    <div class="tab-content">
    <div id="dropbase" class="tab-pane fade active in pageContent">
    	<input id="autofill" type="hidden" value="<?php echo ($autofill); ?>">
    	<input id="hidden_filed" type="hidden" value="<?php echo ($hidden_filed); ?>">
    	<input id="up_resourcetype" type="hidden" value="<?php echo ($rtype); ?>">
    	<input id="oss_host" type="hidden" value="<?php echo ($oss_host); ?>">
		  <form id="dropbase-form" method="post" name=theform action="<?php echo ($host_name); ?>/resource/uploadResource" class="pageForm required-validate" enctype="multipart/form-data" onsubmit="return iframeCallback(this, dialogAjaxDone)">
		   	<input id="oss_id" type="hidden" name="id" value="<?php echo ($row["id"]); ?>">
		   	<input type="hidden" name="oss_addr" id='oss_addr' value=''>
		   	<input type="hidden" name="oss_filesize" id='oss_filesize' value=''>
		    <div class="pageFormContent modal-body">
		    	<div class="form-group row">
			        <div class="col-xs-12 col-sm-12">
			        	<div id="ossfile">你的浏览器不支持flash,Silverlight或者HTML5！</div>
			        </div>
		       </div>
		        
		        <div class="form-group row">
    			    <label class="col-xs-12 col-sm-2 control-label">
    			    上传资源
		        	</label>
					<div class="col-xs-12 col-sm-10">
		               <a id="selectfiles" class="btn btn-success" href="javascript:void(0);" ><i class="fa fa-plus"></i> 选择文件</a>
				  		<a id="postfiles" class="btn btn-success" href="javascript:void(0);" ><i class="fa fa-upload"></i> 开始上传</a>
		        	</div>
		          </div>
				 <div class="form-group row">
			         <label class="col-xs-12 col-sm-2 control-label">
			          资源URL:
			        </label>
			        <div class="col-xs-12 col-sm-10">
			          <input type="text" class="form-control" id="media_url" value="" readonly>
			      	 </div>
			      </div>
			      <?php if($rtype == 1): ?><div class="form-group row">
			         <label class="col-xs-12 col-sm-2 control-label">
			          资源名称:
			        </label>
			        <div class="col-xs-12 col-sm-10">
			          <input type="text" class="form-control" id="resource_name" name="name" minlength="2" maxlength="20" value="<?php echo ($vinfo["name"]); ?>" required>
			      	 </div>
			      </div><?php endif; ?>
			      <div class="form-group row">
		              <label class="col-xs-12 col-sm-2 control-label">
		                资源类型：
		              </label>
		              <div class="col-xs-12 col-sm-10">
		                <?php $_result=C('RESOURCE_TYPE');if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><input class="resource_type" name="type" type="radio" id="<?php echo ($key); ?>" value="<?php echo ($key); ?>" 
		                  <?php if($key == $rtype): ?>checked<?php else: ?>disabled<?php endif; ?>/>&nbsp;<?php echo ($vo); ?>
		                  &nbsp;&nbsp;<?php endforeach; endif; else: echo "" ;endif; ?>
		              </div>
            		</div>
		 			<div class="form-group row" id="duration" style="display:none;">
		              <label class="col-xs-12 col-sm-2 control-label">
		                视频时长：
		              </label>
		              <div class="col-xs-12 col-sm-10">
		                <input name="duration" id="resource_duration" type="text" value="" class="form-control" />
		              </div>
		            </div>           		
			      <div class="form-group row">
		              <label class="col-xs-12 col-sm-2 control-label">
		                页面描述：
		              </label>
		              <div class="col-xs-12 col-sm-10">
		                <textarea name="description" id="description" type="textInput" class="form-control"></textarea>
		                <span class="tips">注：请输入资源描述，允许为空。</span>
		              </div>
           		 </div>
		    </div>
		    <div class="modal-footer">
		      <button id="cancel_upload" class="btn btn-default close-m" type="button">取消</button>
		      <button id="saveImg" class="btn btn-primary" type="button">保存</button>     
		    </div>
		  </form>
	  </div>
      <!--  -->
      <div id="files" class="files-container tab-pane fade">

        <textarea class="load-tmp " id="titlename" name="titlename"></textarea>
        <button id="searchmedia" class="btn btn-primary" type="button" style="margin-top:-25px;">搜索</button>

        <div class="dz-file-viewport" style="max-height: 188px; padding:0;width:100%;margin-top:20px;">
          <form id="file-list" style="margin:0;" class="dropzone clearfix" data-column="4">
            <?php if(is_array($datalist)): $i = 0; $__LIST__ = $datalist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vinfo): $mod = ($i % 2 );++$i;?><div class="dz-preview dz-file-preview" data-list-file>
                <div class="file-content" data-wh="" data-title="<?php echo ($vinfo["name"]); ?>" data-src="<?php echo ($vinfo["oss_addr"]); ?>">
                  <div class="dz-overlay hidden"></div>
                  <label class="dz-check">
                    <input type="checkbox" value="<?php echo ($vinfo["id"]); ?>" name="selected[]">
                    <span><i class="fa fa-check"></i></span>
                  </label>
                  <div class="dz-details" title="<?php echo ($vinfo["name"]); ?>">
                    <?php if(($vinfo['surfix'] == 'png') or ($vinfo['surfix'] == 'jpg') or ($vinfo['surfix'] == 'gif') or ($vinfo['surfix'] == 'jpeg')): ?><img class="dz-nthumb" style="width:100%" src="<?php echo ($vinfo["oss_addr"]); ?>"/>
                        <span style="width:100%;height:1.4em;line-height:1.4em;padding:0 10px;position:absolute;bottom:10px;overflow:hidden;text-overflow:ellipsis;background:rgba(255,255,255,0.5);"><?php echo ($vinfo["name"]); ?></span>
                    <?php else: ?>
                    <div class="dz-file">
                      <i class="file-<?php echo ($vinfo["surfix"]); ?>"></i>
                      <span><?php echo ($vinfo["name"]); ?></span>
                    </div><?php endif; ?>
                  </div>
                  <!-- <div class="dz-info clearfix">  
                    <div class="dz-size" data-dz-size data-size="<?php echo ($vlist["shw_size"]); ?>"></div>
                    <a warn="警告" title="你确定要删除这文件吗？" target="ajaxTodo" href="<?php echo ($host_name); ?>/uploadmgr/uploadmgrDel?id=<?php echo ($vlist["id"]); ?>" calback="navTabAjaxDone" class="btn btn-danger btn-icon pull-right del-file"><span><i class="fa fa-trash"></i></span></a>
                    <a title="文件信息" href="<?php echo ($host_name); ?>/uploadmgr/uploadmgrInfo?id=<?php echo ($vlist["id"]); ?>" target="dialog" class="btn btn-primary btn-icon pull-right" data-dz-remove><i class='fa fa-search'></i></a>
                  </div> -->
                </div>
              </div><?php endforeach; endif; else: echo "" ;endif; ?>
          </form>
          <div class="loadpoint" data-load=0></div>
        </div>
        <div class="modal-footer">
          <div class="multiple-select pull-left hidden">
            已选择<strong>0</strong>图片
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
var imgs="Image files";
var files="files";
var imgExt="<?php echo ($file_allexts["img_ext"]); ?>";
var fileExt="<?php echo ($file_allexts["file_ext"]); ?>";
</script>
<script src='/Public/admin/assets/js/oss/upload.js'></script>
<script>
/*==============================mark by s++================================*/

var hiddenType=$("#hidden_filed").val();
var hiddenImg=hiddenType+'img';
var resourceType="";
$(".resource_type").click(function(){
  resourceType=$(this).val();
});
console.log(resourceType);
//选择本地图片点击保存 
$("#saveImg").click(function(){
    var url = $("#media_url").val();
  var cot = $("#ossfile .progress-bar").attr('aria-valuenow');

  if(url == ''){
    if(cot != '100' && cot>0){
      alert('资源正在上传，请稍后');
      return false;
    }else{
      alert('请上传资源');
      return false;
    }
  }


    $.ajax({
        url:$("#dropbase-form").attr("action"),
        type:"post",
        dataType:"json",
        data:{
        	"id":$("#oss_id").val(),
        	"oss_addr":$("#oss_addr").val(),
        	"oss_filesize":$("#oss_filesize").val(),
        	"name":$("#resource_name").val(),
        	"type":resourceType,
        	"duration":$("#resource_duration").val(),
        	"description":$("#description").val(),
        },
        success:function(result){
          //covervideo_id  covervideo_idimg media_id media_idimg
          console.log(result);
         // alert($("#resource_name").val());
         // $("#media_idimgname").val($("#resource_name").val());
        //  $("#media_idimgname").html($("#resource_name").val());
          //console.log($("#resource_type").val());

            if(result.code==10000){
                $("#"+hiddenType).val(result.data.media_id);
                $("#"+hiddenImg).attr("src",result.data.path).show();
                if(hiddenType=='ueditor'){
                    var ueIn = UE.getEditor("editor");
                    ueIn.execCommand('insertimage', {
                         src:result.data.path
                    });
                }else{
                  if(hiddenType =='media_id' && hiddenImg == 'media_idimg'){
                    $("#xuanpian #media_idimg").attr("src",$("#covervideo_idimg").attr("src"));
                    $("#xuanpian #xuanpianhr").attr("href",result.data.path);
                    $("#media_idimgname").val($("#resource_name").val());
                    $("#media_idimgname").html($("#resource_name").val());
                  }

                }
                $("#dz-filecontainer .nav-tabs .pull-right a").click();
            }else{
              // $("#dz-filecontainer .nav-tabs .pull-right a").click();
              alert('资源名称已经存在请换名称');
              return false;
            }
        },
        error:function(){
            $("#dz-filecontainer .nav-tabs .pull-right a").click(); 
        }
    })
})

//查找数据
$("#searchmedia").click(function(){
  $.ajax({
    url:"<?php echo ($host_name); ?>/resource/searchResource",
    type:"post",
    dataType:"html",
    data:{
      "filed":$("#hidden_filed").val(),
      "rtype":$("#up_resourcetype").val(),
      "autofill":$("#autofill").val(),
      "name":$("#titlename").val(),
    },
    success:function(result){
      $("#file-list").html('');
      $("#file-list").html(result);
    },
    error:function(){

      $("#dz-filecontainer .nav-tabs .pull-right a").click();
    }
  })
})

$("#cancel_upload").click(function(){
	$("#dz-filecontainer .nav-tabs .pull-right a").click();
})
//领取图片数据
function getImageInfo($this){
  $('#files').find('.file-content').removeClass("active");
  var $file = $this.closest('.file-content');
  $file.addClass("active");
  var $ck = $('#files input:checked')
  var count = $ck.length;
  var autofill = $("#autofill").val();
  if(count==1){
    //alert($ck.val());
    //alert($file.find(".dz-nthumb").attr("src"));
    $("#"+hiddenType).val($ck.val());
    $("#dz-filecontainer .nav-tabs .pull-right a").click();
    if($file.find(".dz-nthumb").size()>0){
      $("#"+hiddenImg).attr("src",$file.find(".dz-nthumb").attr("src")).show();
      $("#"+hiddenImg+"name").hide();
      if(hiddenType=='ueditor'){
          var ueIn = UE.getEditor("editor");
          ueIn.execCommand('insertimage', {
               src:$file.find(".dz-nthumb").attr("src")
          });
        }
    }else{
      $("#"+hiddenImg).hide();
      if(autofill==1){
    	  var autofillname = $file.find(".dz-file").text();
    	  $("#"+hiddenImg+"name").val($.trim(autofillname));

          if (hiddenImg == 'media_idimg' && hiddenType == 'media_id') {
            var mphr = $file.attr("data-src");
            $file.addClass("active");
            $("#xuanpian #"+hiddenImg).show();
            $("#xuanpian #"+hiddenImg).attr("src",$("#covervideo_idimg").attr("src"));
            $("#xuanpian #xuanpianhr").attr("href", mphr);
          }
      }else{
    	  $("#"+hiddenImg+"name").text($file.find(".dz-file").text()).show(); 
      }
    }    
  }else{
    alert("只能选择一张图片");
  }  
}
//选择资料库文件
var cntrlIsPressed = false;
$(document).keydown(function(event){
  if(event.which=="17" && mult != ""){
    cntrlIsPressed = true;
  }else{
    cntrlIsPressed = false;
  }
});

$(document).keyup(function(){
  cntrlIsPressed = false;
});
$(document).on("click","#files input",function(){
  if(!cntrlIsPressed){
    $('#files').find('input').prop("checked",false);
    $(this).prop("checked",true);     
  }
  getImageInfo($(this));
});
var pageNum=2;
var rtype=$("#up_resourcetype").val();
$("#files .dz-file-viewport").scroll(function(){
  //console.log("scroll")
  var t = $("#file-list").height();
  var c = $(this).height();
  var s = $(this).scrollTop();
  // var n = $("#files<?php echo ($multiple); ?> .loadpoint").data("next");
  // var l = $("#files<?php echo ($multiple); ?> .loadpoint").data("load");
  //console.log(s+"-"+(t-c))
  if((t-c) <= s){
    $.ajax({
      url:"<?php echo ($host_name); ?>/resource/resourceList?isbrowse=1&pageNum="+pageNum+"&rtype="+rtype,
      type:"get",
      dataType:"json",
      success:function(result){
        if(result.code==10000&&result.data.length>0){          
          var str="";
          for(var i in result.data){
            //console.log(result.data[i].surfix+"|||"+result.data[i].name);
            if(typeof(result.data[i])=='object'){
                str+='<div class="dz-preview dz-file-preview" data-list-file>'+
                '<div class="file-content" data-wh="" data-title="'+result.data[i].name+'" data-src="'+result.data[i].oss_addr+'">'+
                  '<div class="dz-overlay hidden"></div>'+
                  '<label class="dz-check">'+
                    '<input type="checkbox" value="'+result.data[i].id+'" name="selected[]">'+
                    '<span><i class="fa fa-check"></i></span>'+
                  '</label>'+
                  '<div class="dz-details" title="'+result.data[i].name+'">';
                  if(result.data[i].surfix=="png"||result.data[i].surfix=="jpg"||result.data[i].surfix=="gif"||result.data[i].surfix=="jpeg"){
                    str+='<img class="dz-nthumb" style="width:100%" src="'+result.data[i].oss_addr+'"/>';
                    str+='<span style="width:100%;height:1.4em;line-height:1.4em;padding:0 10px;position:absolute;bottom:10px;overflow:hidden;text-overflow:ellipsis;background:rgba(255,255,255,0.5);">'+result.data[i].name+'</span>'
                  }else{
                    str+='<div class="dz-file">'+
                      '<i class="file-'+result.data[i].surfix+'"></i>'+
                      '<span>'+result.data[i].name+'</span>'+
                    '</div>';
                  }
                  str+='</div>'+              
                  // '<div class="dz-info clearfix">'+
                  //   '<div class="dz-size" data-dz-size data-size="'+result.data[i].shw_size+'"></div>'+
                  //   '<a warn="警告" title="你确定要删除这文件吗？" target="ajaxTodo" href="<?php echo ($host_name); ?>/uploadmgr/uploadmgrDel?id='+result.data[i].id+'" calback="navTabAjaxDone" class="btn btn-danger btn-icon pull-right del-file"><span><i class="fa fa-trash"></i></span></a>'+
                  // '</div>'+
                '</div>'+
              '</div>';
            }else{
              continue;
            }           
          }          
          $("#file-list").append(str);
          pageNum++;
        }       
      },
      error:function(){
        console.log("error");
      }
    })       
  }
});
$("input[type='radio']").click(function() {
  var id = $(this).val();
  console.log(id);
  if (id == 1) {
    $("#duration").show();
  } else {
    $("#duration").hide();
  }
});
</script>