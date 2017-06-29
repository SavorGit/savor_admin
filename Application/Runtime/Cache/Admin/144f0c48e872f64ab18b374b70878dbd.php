<?php if (!defined('THINK_PATH')) exit();?><script>  
    if(!window.jQuery){
      var path = window.location.pathname;
      path = path.replace("/admin/","");
      console.log(path);
      window.location.href = "<?php echo ($host_name); ?>#" + path;
    }
</script>

<!--显示列表样式1 start-->
<div class="pageHeader">
  <form onsubmit="return navTabSearch(this);" id="pagerForm" action="<?php echo ($host_name); ?>/hotel/pubmanager" method="post" >
    <input type="hidden" name="hotel_id" value="<?php echo ($hotelinfo["id"]); ?>"/>
    <input type="hidden" name="pageNum" value="<?php echo ($pageNum); ?>"/>
    <input type="hidden" name="numPerPage" value="<?php echo ($numPerPage); ?>"/>
    <input type="hidden" name="_order" value="<?php echo ($_order); ?>"/>
    <input type="hidden" name="_sort" value="<?php echo ($_sort); ?>"/>
    <div class="searchBar">
      <div class="clearfix">

	      </div>

      <div class="form-inline">
	            <div class="tools-group">
	            	<a class="btn btn-success btn-sm add" href="<?php echo ($host_name); ?>/clientconfig/addclientconfig?acttype=0" title="新增客户端启动" rel="abce" target="dialog" mask="true"><i class="fa fa-plus"></i>新增客户端启动</a>
	          	</div>
          </div>
	   </div>
	   </form>
</div>
<div class="pageContent" id="pagecontent" style="top:85px">
  <div id="w_list_print">
    <div class="no-more-tables">
      <form method="post" action="#" id="del-form" class="pageForm required-validate" enctype="multipart/form-data" onsubmit="return iframeCallback(this, dialogAjaxDone)">
        <table class="table table-bordered table-striped" targetType="navTab" asc="asc" desc="desc">
          <thead>
            <tr id="post">
              <!-- <th class="table-checkbox">
                <input type="checkbox" data-check="all" data-parent=".table" />
              </th> -->
              <th>名称</th>
              <th>启动图片</th>
              <th>启动视频</th>
              <th>默认启动状态</th>
              <!-- <th>上线下线</th> -->
               <th>操作</th>
             </tr>
           </thead>
           <tbody data-check="list" data-parent=".table">
             <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vlist): $mod = ($i % 2 );++$i;?><tr target="sid_user">
               <td data-title="名称">
                 <?php if($vlist['ctype'] == 3): ?>ANDROID
                   <?php else: ?>
                   IOS<?php endif; ?>
               </td>
               <td data-title="启动图片"><a data-tip="预览" target="__blank" mask="true" href="<?php echo ($vlist["img_url"]); ?>" class="btn btn-success btn-icon">
                 <i class="fa fa-eye"></i>
               </a></td>
               <td data-title="启动视频"><a data-tip="预览" target="__blank" mask="true" href="<?php echo ($vlist["media_url"]); ?>" class="btn btn-success btn-icon">
                 <i class="fa fa-eye"></i>
               </a></td>

               <td data-title="默认启动状态">
                     <input name="starten<?php echo ($vlist["id"]); ?>" type="radio" class="acp"  value="1" id="<?php echo ($vlist["id"]); ?>" <?php if($vlist['status'] == 1): ?>checked<?php endif; ?>/>
                 启动图片
                 <input id="<?php echo ($vlist["id"]); ?>" name="starten<?php echo ($vlist["id"]); ?>" type="radio" class="acp" value="2" <?php if($vlist['status'] == 2): ?>checked<?php endif; ?> />启动视频

               </td>
               <!--
                             <td data-title="上线状态">
                               <?php if($vlist['online'] == 1): ?><a data-tip="已上线" target="ajaxTodo" href="<?php echo ($host_name); ?>/clientconfig/operateStatus?adsid=<?php echo ($vlist["id"]); ?>&flag=0" calback="navTabAjaxDone" class="btn btn-default btn-icon"><span>
                               <i class="fa fa-toggle-on"></i></span></a>
                                 <?php else: ?>
                                 <a data-tip="未上线" target="ajaxTodo" href="<?php echo ($host_name); ?>/clientconfig/operateStatus?adsid=<?php echo ($vlist["id"]); ?>&flag=1" calback="navTabAjaxDone" class="btn btn-default btn-icon"><span>
                               <i class="fa fa-toggle-off"></i></span></a><?php endif; ?>

                             </td>-->
               <td class="table-tool" data-title="操作">
                 <div class="tools-edit">
                   <a data-tip="修改" target="dialog" mask="true" href="<?php echo ($host_name); ?>/clientconfig/addclientconfig?clid=<?php echo ($vlist["id"]); ?>" class="btn btn-success btn-icon">
                     <i class="fa fa-pencil"></i>
                   </a>
                 </div>

                 <div class="tools-edit">
                   <a warn="警告" data-tip="删除" title="你确定要删除吗？" target="ajaxTodo" href="<?php echo ($host_name); ?>/clientconfig/delconfig?id=<?php echo ($vlist["id"]); ?>"  class="btn btn-success btn-icon">
                     <i class="fa fa-trash"></i>
                   </a>
                 </div>
               </td>
             </tr><?php endforeach; endif; else: echo "" ;endif; ?>
           </tbody>
         </table>
       </form>

     </div>
   </div>
 </div>

 <script>

   $(function() {

     $(".acp").click(function(){
       var st_id = $(this).val();
       var cid = $(this).attr('id');
       $.ajax({
         type: "POST",
         dataType: "json",
         url: "<?php echo ($host_name); ?>/clientconfig/changestatus",
         data: "cid=" + st_id + "&id=" + cid,
         success:
                 function (data) {
                   if(data.status == 1){
                     alert('修改状态成功');
                   } else{
                     alert('修改状态成功');
                   }
                 }
       });
     })



 });
 </script>