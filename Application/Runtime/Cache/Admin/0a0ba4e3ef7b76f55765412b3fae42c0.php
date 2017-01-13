<?php if (!defined('THINK_PATH')) exit();?><script>  
    if(!window.jQuery){
      var path = window.location.pathname;
      path = path.replace("/admin/","");
      console.log(path);
      window.location.href = "<?php echo ($host_name); ?>#" + path;
    }
</script>

<div class="pageHeader">
  <form onsubmit="return navTabSearch(this);" id="pagerForm" action="<?php echo ($host_name); ?>/uploadmgr/uploadmgrList" method="post">
    <input type="hidden" name="numPerPage" value="<?php echo ($numPerPage); ?>"/>
    <input type="hidden" name="pageNum" value="<?php echo ($pageNum); ?>"/>
    <input type="hidden" name="_order" value="<?php echo ($_order); ?>"/>
    <input type="hidden" name="_sort" value="<?php echo ($_sort); ?>"/>
    <div class="searchBar">
      <div class="clearfix">
        <div class="col-xs-12">
          <div class="btn btn-primary btn-sm browse">
            <span><i class="fa fa-upload"></i> 上传</span>
            <div id="file-input"></div>
          </div>
          <button class="btn btn-success btn-sm select-all" type="button"><i class="fa fa-square"></i> 全部</button>
          <button class="btn btn-danger btn-sm del-select" type="button" data-msg="你确认要删除你选择的图片吗？" data-form="#del-upload-form"><i class="fa fa-trash"></i> 批量删除</button>
        </div>
        <div id="advance-search" class="collapse">
          <div class="adv-search clearfix">
            <div class="col-sm-4 col-md-4 col-lg-5">
              <div class="form-group">
                <input type="text" name="s_name" class="form-control" placeholder="文件名称">
              </div>
            </div>
            <div class="col-sm-4 col-md-3">
              <div class="form-group">
                <select name="s_type" class="form-control bs-select" data-container="body" title="文件类型">
                  <option value="">所有类型</option>
                  <option value="image">图片类型</option>
                  <option value="video">视频类型</option>
                  <option value="audio">音频类型</option>
                  <option value="other">其他类型</option>
                </select>
              </div>
            </div>
            <div class="col-sm-4 col-md-3">
              <div class="form-group">
                <select name="s_time" class="form-control bs-select" data-container="body" title="上传日期">
                  <option value="">所有日期</option>
                  <?php if(is_array($time)): $i = 0; $__LIST__ = $time;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($time["shw_savepath"]); ?>"><?php echo ($time["shw_savepath"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                </select>
              </div>
            </div>
            <div class="col-xs-4 col-sm-4 col-md-2 col-lg-1 pull-right">
              <div class="form-group">
                <button class="btn btn-success btn-block">搜索</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>
<div class="pageContent" id="pagecontent">
  <div id="w_list_print">

   <div class="dropbase">
      <form method="post" action="<?php echo ($host_name); ?>/uploadmgr/uploadmgrAdd" id="dropzone" enctype="multipart/form-data"></form>
      <h2>拖拽文件到这里或点击上传</h2>
      <h4>(文件上传不能超过2MB)</h4>
    </div>
    <!-- <form method="post" action="<?php echo ($host_name); ?>/uploadmgr/uploadmgrAdd" enctype="multipart/form-data" style="height: 100px">
      测试中... 
      <input type="file" name="fileup[]" multiple="multiple" />
      <button type="submit">上传</button>
    </form> -->
    <form action="<?php echo ($host_name); ?>/uploadmgr/uploadmgrDel" method="post" class="required-validate" id="del-upload-form" onsubmit="return validateCallback(this, navTabAjaxDone)">
      <div id="file-container" class="dropzone clearfix" data-column="10">
        <?php if(is_array($uploadmgrlist)): $i = 0; $__LIST__ = $uploadmgrlist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vlist): $mod = ($i % 2 );++$i;?><div class="dz-preview dz-file-preview" data-list-file>
            <div class="file-content">
              <div class="dz-overlay hidden"></div>
              <label class="dz-check">
                <input type="checkbox" value="<?php echo ($vlist["id"]); ?>" name="delid[]">
                <span><i class="fa fa-check"></i></span>
              </label>
              <div class="dz-details">
                <a title="文件信息" href="<?php echo ($host_name); ?>/uploadmgr/uploadmgrInfo?id=<?php echo ($vlist["id"]); ?>" target="dialog">
                  <?php if(($vlist['shw_fileext'] == 'png') or ($vlist['shw_fileext'] == 'jpg') or ($vlist['shw_fileext'] == 'gif') or ($vlist['shw_fileext'] == 'jpeg')): if(($vlist["shw_width"] > 160) && ($vlist['shw_height'] > 160)): ?><img src="<?php echo ($imgup_show); echo ($vlist["shw_savepath"]); ?>/160x160_<?php echo ($vlist["shw_savename"]); ?>"/>
                    <?php else: ?>
                      <img class="dz-nthumb" src="<?php echo ($imgup_show); echo ($vlist["shw_savepath"]); ?>/<?php echo ($vlist["shw_savename"]); ?>"/><?php endif; ?>
                  <?php else: ?>
                  <div class="dz-file">
                    <i class="file-<?php echo ($vlist["shw_fileext"]); ?>"></i>
                    <span title="<?php echo ($vlist["shw_title"]); ?>"><?php echo ($vlist["shw_title"]); ?></span>
                  </div><?php endif; ?>
                </a>
              </div>
              <div class="dz-info clearfix">  
                <div class="dz-size" data-dz-size data-size="<?php echo ($vlist["shw_size"]); ?>"></div>
                <a warn="警告" title="你确定要删除这文件吗？" target="ajaxTodo" href="<?php echo ($host_name); ?>/uploadmgr/uploadmgrDel?id=<?php echo ($vlist["id"]); ?>" calback="navTabAjaxDone" class="btn btn-danger btn-icon pull-right"><span><i class="fa fa-trash"></i></span></a>
              </div>
            </div>
          </div><?php endforeach; endif; else: echo "" ;endif; ?>
      </div>
    </form>
    
  </div>
  <?php echo ($page); ?>
</div>
<div id="preview-template" style="display: none;">
  
  <div class="dz-preview dz-file-preview">
    <div class="file-content">
      <div class="dz-details">    
        <img class="dz-nthumb" data-dz-thumbnail />
        <div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>
      </div>
      <div class="dz-info clearfix">  
        <div class="dz-size" data-dz-size></div>
        <div class="btn btn-danger btn-icon dz-remove pull-right" data-dz-remove><i class='fa fa-trash'></i></div>
      </div>
      <div class="dz-success-mark"><i class="fa fa-check-circle"></i></div>
      <div class="dz-error-message"><span data-dz-errormessage></span></div>
    </div>
  </div>
</div>
<script type="text/javascript">
  function reloadFile(){
    console.log('load');
  }
  
  $(function(){ 
    var success = {};
    var error = {}
    $(".select-all").click(function(){
      if($(this).find(".fa-square").length){
        $("#file-container input").prop('checked',true);
        $(this).find(".fa-square").removeClass("fa-square").addClass("fa-check-square");
      }else{
        $("#file-container input").prop('checked',false);
        $(this).find(".fa-check-square").removeClass("fa-check-square").addClass("fa-square");
      }
    })
    var myDropzone = new Dropzone('#dropzone', {
        paramName: "fileup[]",
        url: "<?php echo ($host_name); ?>/uploadmgr/uploadmgrAdd",
        method: "post",
        maxFilesize: 2,
        previewsContainer: "#file-container",
        hiddenInputContainer: "#file-input",
        previewTemplate: $("#preview-template").html(),
        acceptedFiles: ".pdf,.zip,.rar,.txt,.doc,.docx,.ppt,.xls,.xlsx,.csv,.jpg,.jpeg,.gif,.png,.bmp,.swf,.flv,.fla,.avi,.wmv,.wma,.rm,.mov,.mpg,.rmvb,.3gp,.mp4,.mp3",
        // accept: function(file, done) {
        //   //console.log(file);
        //   if(file.size > 2097152){
        //     done("文件已超过2MB!不能上传。");
        //     //console.log(done);
        //     setTimeout(function(){
        //       $(file.previewElement).remove();
        //     },550)
        //   }else{
        //     done();
        //   }
        // },
        init: function() {
          this.on("addedfile", function(file) {
            
            $("[data-list-file]").find(".dz-overlay").removeClass("hidden");
             var t = file.previewElement;
             var n = file.name;
             var split = n.split('.');
             var sl = split.length;
             var ext = split[sl-1].toLowerCase();
             var name = n.replace('.'+ext, '');
             console.log(ext);
             var icon = Dropzone.createElement("<div class='dz-file'><i class='file-"+ext+"'></i><span>"+name+"</span></div>");      
             if(ext != 'jpg' && ext != 'gif' && ext != 'jpeg' && ext != 'png'){           
               var r = t.querySelector(".dz-details").appendChild(icon);
               //console.log(r);
             }
          });
            // this.on("addedfile", function(file) {
            //     // Create the remove button
            //     console.log(file.name);
            //     var removeButton = Dropzone.createElement("<button class='btn btn-danger btn-icon remove'><i class='fa fa-trash'></i></button>");                       
            //     // Capture the Dropzone instance as closure.
            //     var _this = this;
            //     // Listen to the click event
            //     removeButton.addEventListener("click", function(e) {
            //       // Make sure the button click doesn't submit the form:
            //       e.preventDefault();
            //       e.stopPropagation();
            //       // Remove the file preview.
            //       _this.removeFile(file);
            //       // If you want to the delete the file on the server as well,
            //       // you can do the AJAX request here.
            //     });
            //     // Add the button to the file preview element.
            //     console.log(console.log(n););
            //     file.previewElement.appendChild(removeButton);
            // });
        },
        success: function(a,v){
          
          var m;
          if(v){
            var m = eval("("+v+")");
            if(m.status == 0){
              $(a.previewElement).find(".dz-error-message").addClass("show");
              $(a.previewElement).find("[data-dz-errormessage]").text(m.info);
            }else if(m.status == 1){
              success[m.name] = 1;
              $(a.previewElement).addClass("dz-success");
              $(a.previewElement).find('.file-content').append("<div class='dz-overlay'></div>");
            }else if(m.status == 2){
              //$(a.previewElement).addClass("dz-error");
              $(a.previewElement).find(".dz-error-message").addClass("show");
              $(a.previewElement).find("[data-dz-errormessage]").text("上传失败！");
            }

          }       
          
        },
        queuecomplete: function(){
          if(to){
            clearTimeout(to);
          }
          var to = setTimeout(function(){
            if(countObj(success) > 0){
              navTab.reloadFlag("uploadmgr/uploadmgrList");
            }else{
              $("#file-container .dz-error").remove();
              $("#file-container .dz-overlay").addClass("hidden");
            }
            
          },2500)
                 
        }   
    });
    if($(".del-select")){
      $(".del-select").click(function(){
        var msg = $(this).data("msg");
        var form = $(this).data("form")
        if($("#file-container input:checked").length > 0){
          alertMsg.confirm(msg,{okCall: function(){$(form).submit()}})
        }else{
          alertMsg.error("操作失败！你还没选择要删除的图片。")
        }
      })
    }

    function col(){
      bw = $(window).width();
      var w = $("#file-container").width();
      var iw = parseInt(w / 160);
      if (iw > 10) {
        iw = 10;
      }
      if (iw < 2) {
        iw = 2;
      }
      console.log(iw);
      $("#file-container").attr("data-column", iw);
    }
    col();
    $(window).on('resize', function(e) {
      col();
    });
    $("[data-size]").each(function(){
      var $bytes = parseInt($(this).data("size"));
        if ($bytes >= 1073741824) {
            $bytes = '<strong>' + parseFloat($bytes / 1073741824).toFixed(1) + '</strong> GB';
        }else if ($bytes >= 100000){
            $bytes = '<strong>' + parseFloat($bytes / 1048576).toFixed(1) + '</strong> MB';
        }else if ($bytes >= 1024){
            $bytes = '<strong>' + parseFloat($bytes / 1024).toFixed(1) + '</strong> KB';
        }else if ($bytes > 1){
            $bytes = '<strong>' + $bytes + '</strong> B';
        }else if ($bytes == 1){
            $bytes = '<strong>' + $bytes + '</strong> B';
        }else{
            $bytes = '<strong>0</strong> B';
        }
      $(this).html($bytes);
    })
  })

</script>