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
	      <div class="form-inline">
		      <div class="form-group">
		      	<h1><?php echo ($hotelinfo["name"]); ?>酒楼宣传片管理</h1>
		       </div>
	      </div>
	      </div>
	        <div class="form-inline">
		        <div class="form-group">
		          <div class="input-group input-group-sm">
		            <input type="text" class="form-control" name="keywords" value="<?php echo ($keywords); ?>" placeholder="输入名称查找">
		          </div>
		          <div class="form-group">
                    <label>上传时间：</label>
                    <div class="input-group input-group-sm date form_datetime" data-date="<?php echo ($timeinfo["now_time"]); ?>" data-ymd="true">
                         <input name="begin_time" type="text" size="14" class="form-control date" placeholder="开始日期" value="<?php echo ($timeinfo["begin_time"]); ?>" readonly>
                         <span class="input-group-btn">
                           <button class="btn default btn-sm date-reset" type="button" style="display:none"><i class="fa fa-times"></i></button>
                           <button class="btn btn-success btn-sm date-set" type="button"><i class="fa fa-calendar"></i></button>
                         </span>
                     </div>
                 </div>
                   <div class="input-group input-group-sm date form_datetime" data-date="<?php echo ($timeinfo["now_time"]); ?>" data-ymd="true">
                     <input name="end_time" type="text" size="14" class="form-control date" placeholder="结束日期" value="<?php echo ($timeinfo["end_time"]); ?>" readonly>
                     <span class="input-group-btn">
                       <button class="btn default btn-sm date-reset" type="button" style="display:none"><i class="fa fa-times"></i></button>
                       <button class="btn btn-success btn-sm date-set" type="button"><i class="fa fa-calendar"></i></button>
                     </span>
                     <span class="input-group-btn">
              			<button class="btn btn-primary" type="submit" id="choosedata"><i class="fa fa-search">搜索</i></button>
            		</span>
                 </div>
		        </div>
	        </div>
        	      <div class="form-inline">
	            <div class="tools-group">
	            	<a class="btn btn-success btn-sm add" href="<?php echo ($host_name); ?>/hotel/addpub?hotel_id=<?php echo ($hotelinfo["id"]); ?>&acttype=0" title="新增宣传片" target="dialog" mask="true"><i class="fa fa-plus"></i>新增宣传片</a>
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
              <th>封面图片</th>
              <th>创建人</th>
              <th>简介</th>
              <th>所属酒店</th>
              <th>创建时间</th>
              <th>状态</th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody data-check="list" data-parent=".table">
            <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vlist): $mod = ($i % 2 );++$i;?><tr target="sid_user">
              <!-- <td class="table-checkbox">
                <input type="checkbox" class="checkboxes" value="30" name="postlist[]">
              </td> -->
              <td data-title="宣传片名称"><?php echo ($vlist["name"]); ?></td>
              <td data-title="封面图片"><a data-tip="预览" target="__blank" mask="true" href="<?php echo ($host_name); ?>/hotel/getpic?img=<?php echo ($vlist["img_url"]); ?>" class="btn btn-success btn-icon">
                <i class="fa fa-pencil"></i>
              </a></td>
              <td data-title="创建人"><?php echo ($vlist["creator_name"]); ?></td>
              <td data-title="简介"><?php echo ($vlist["description"]); ?></td>
              <td data-title="所属酒店"><?php echo ($hotelinfo['name']); ?></td>
              <td data-title="创建时间"><?php echo ($vlist["create_time"]); ?></td>
              <td data-title="宣传片状态">
                <?php if($vlist['state'] == 1): ?><a data-tip="已审核" target="ajaxTodo" href="<?php echo ($host_name); ?>/hotel/operateStatus?adsid=<?php echo ($vlist["id"]); ?>&flag=0" calback="navTabAjaxDone" class="btn btn-default btn-icon"><span>
                <i class="fa fa-toggle-on"></i></span></a>
                  <?php else: ?>
                  <a data-tip="未审核" target="ajaxTodo" href="<?php echo ($host_name); ?>/hotel/operateStatus?adsid=<?php echo ($vlist["id"]); ?>&flag=1" calback="navTabAjaxDone" class="btn btn-default btn-icon"><span>
                <i class="fa fa-toggle-off"></i></span></a><?php endif; ?>

              </td>
              <td class="table-tool" data-title="操作">
                <div class="tools-edit">
                  <a data-tip="修改" target="dialog" mask="true" href="<?php echo ($host_name); ?>/hotel/addpub?hotel_id=<?php echo ($hotelinfo["id"]); ?>&ads_id=<?php echo ($vlist["id"]); ?>&acttype=1" class="btn btn-success btn-icon">
                    <i class="fa fa-pencil"></i>
                  </a>
                  <a style="display:none;" class="btn btn-danger btn-icon" warn="警告" data-tip="" title="你确定要删除吗？" target="ajaxTodo" href="<?php echo ($host_name); ?>/hotel/delpub?ads_id=<?php echo ($vlist["id"]); ?>&hotel_id=<?php echo ($hotelinfo["id"]); ?>" calback="navTabAjaxDone" data-original-title="删除">
				  <span>
				   <i class="fa fa-trash"></i>
				  </span>
				  </a>
                </div>
              </td>
            </tr><?php endforeach; endif; else: echo "" ;endif; ?>
          </tbody>
        </table>
      </form>

    </div>
  </div>
  <?php echo ($page); ?>
</div>