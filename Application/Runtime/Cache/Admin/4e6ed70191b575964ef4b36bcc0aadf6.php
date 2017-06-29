<?php if (!defined('THINK_PATH')) exit();?><script>  
    if(!window.jQuery){
      var path = window.location.pathname;
      path = path.replace("/admin/","");
      console.log(path);
      window.location.href = "<?php echo ($host_name); ?>#" + path;
    }
</script>

<style type="text/css">
  body{
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    -khtml-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
  }
  .doms img{
    display: inline-block;
    margin-top: 35px;
  }
  .divlist{
    padding-top: 7px;
    clear: both;
    width: 100%;
    height: 23px;
  }
  .sleft{
    float: left;
  }
  .sright{
    float: right;
  }
  .active{
    background-color: #d7d7d7;
  }
  .xuhao{
    width: 82px;
    float: left;
  }
  .xuhao li{
    height: 20px;
    line-height: 20px;
  }
  .shuju li{
    height: 20px;
    line-height: 20px;
  }

  .name_shuju{
    display:inline-block;
    width:73%;
  }
</style>
<script src="../../../../Public/admin/assets/js/jquery-ui.js" type="text/javascript" charset="utf-8"></script>
<!--显示列表样式333331 start-->
<div class="pageContent" id="pagecontent">
  <div id="w_list_print">
    <div class="no-more-tables">
      <form method="post" action="<?php echo ($host_name); ?>/release/dosort" id="del-form" class="pageForm required-validate" enctype="multipart/form-data" onsubmit="return iframeCallback(this, dialogAjaxDone)">
        <button type="submit" id="savesort" class="btn btn-primary" onclick="return savp();">保存排序</button>



        <div id="left" style="margin-left:4%;width:92%;background-color: #f2f2f2;margin-top: 25px;float: left;">

          <div style="width: 100%;background-color:#d7d7d7;margin-top: 15px;height: 40px;font-size: 18px;">
            <span style="margin-left: 15px;display:inline-block;margin-top: 15px;">序号</span>
            <span style="margin-left: 50px;display:inline-block;margin-top: 15px;">节目名称</span>
            <span style="margin-left: 62%;display:inline-block;margin-top: 15px;">上传时间</span>
          </div>
          <div id="lefta" style="width: 94%;max-height:300px;min-height:474px;margin-left: 15px;overflow: auto;cursor: pointer;">
            <input type="hidden" id="sortid" />
            <ul class="xuhao">
              <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vlist): $mod = ($i % 2 );++$i;?><li><?php echo ($vlist["sort_num"]); ?></li><?php endforeach; endif; else: echo "" ;endif; ?>
            </ul>
            <ul class="shuju" id="shuju">
              <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vlist): $mod = ($i % 2 );++$i;?><li aid="<?php echo ($vlist["id"]); ?>"><span class="name_shuju"><?php echo ($vlist["name"]); ?></span><span class="timees"><?php echo ($vlist["update_time"]); ?></span></li><?php endforeach; endif; else: echo "" ;endif; ?>
            </ul>

          </div>
        </div>
        <input type="hidden" name="soar" id="soar"/>
        <input type="hidden" name="bbb" id="eeee" value="555"/>
      </form>

    </div>
  </div>
</div>

<div id="mymodal2" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">导入结果</h4>
      </div>
      <div class="modal-body">
        <ul id="notinclude">

        </ul>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<SCRIPT LANGUAGE="JavaScript">

  function savp(){
    var arr = [];
    $("#shuju>li").each(function(){
      var $aid = $(this).attr('aid');
      arr.push($aid);
    });
    $("#soar").val(arr);
  }
  /*$("#savesort").click(
   function(){
   var arr = [];

   $("#shuju>li").each(function(){
   var $aid = $(this).attr('aid');
   arr.push($aid);
   });
   $.ajax({
   type:"POST",
   dataType: "text",
   url:"<?php echo ($host_name); ?>/article/dosort",
   data:"soar="+arr,
   success:function(data){
   alert(data);
   if(data == 'success'){
   //$("#mymodal2").modal('show');
   //
   // $(".modal .fade .in .no-transform").css('display','none');
   //refresh();
   location.href = "#content/getlist";
   }else{
   // $("#mymodal2").modal('show');
   }


   }
   });
   }
   );*/
  function refresh(){
    // window.location.reload();//刷新当前页面.

    //或者下方刷新方法
    //parent.location.reload();
    // opener.location.reload()刷新父窗口对象（用于单开窗口
    //top.location.reload()刷新最顶端对象（用于多开窗口）
  }

  $(function() {
    updateIndex = function(e, ui) {
      $('.xuhao>li', ui.item.parent()).each(function (i) {
        $(this).html(i + 1);
      });
    };

    $("#lefta>.shuju").sortable({
      // helper: fixHelperModified,
      stop: updateIndex
    }).disableSelection();

  });
</script>