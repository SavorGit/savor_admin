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
  <form onsubmit="return navTabSearch(this);" id="pagerForm" action="<?php echo ($host_name); ?>/checkaccount/showHotel" method="post" >
    <input type="hidden" name="statementid" value="<?php echo ($statementid); ?>"/>
    <input type="hidden" name="pageNum" value="<?php echo ($pageNum); ?>"/>
    <input type="hidden" name="numPerPage" value="<?php echo ($numPerPage); ?>"/>
    <input type="hidden" name="_order" value="<?php echo ($_order); ?>"/>
    <input type="hidden" name="_sort" value="<?php echo ($_sort); ?>"/>
    <div class="searchBar">
      <div class="clearfix">
	      <div class="form-inline">
		      <div class="form-group">
		      	<h1>对账单<?php echo ($statementid); ?>酒楼列表</h1>

		       </div>
	      </div>
	      </div>


	   </div>
	   </form>
</div>
<div class="pageContent" id="pagecontent">
  <div id="w_list_print">
    <div class="no-more-tables">
      <form method="post" action="#" id="del-form" class="pageForm required-validate" enctype="multipart/form-data" onsubmit="return iframeCallback(this, dialogAjaxDone)">
        <table class="table table-bordered table-striped" targetType="navTab" asc="asc" desc="desc">
          <thead>
            <tr id="post">
              <!-- <th class="table-checkbox">
                <input type="checkbox" data-check="all" data-parent=".table" />
              </th> -->
              <th>酒楼id</th>
              <th>酒楼名称</th>
              <th>金额</th>
              <th>通知状态</th>
              <th>对账状态</th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody data-check="list" data-parent=".table">
            <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vlist): $mod = ($i % 2 );++$i;?><tr target="sid_user">
              <!-- <td class="table-checkbox">
                <input type="checkbox" class="checkboxes" value="30" name="postlist[]">
              </td> -->
              <td data-title="酒楼id"><?php echo ($vlist["hotelid"]); ?></td>
              <td data-title="酒楼名称"><?php echo ($vlist["name"]); ?></td>
              <td data-title="金额"><?php echo ($vlist["money"]); ?></td>
              <td data-title="通知状态"><?php echo ($vlist["no_mes"]); ?></td>
              <td data-title="对账状态"><?php echo ($vlist["ch_mes"]); ?></td>
              <td class="table-tool" data-title="操作">
                <div class="tools-edit">


                  <?php if($vlist["cont"] == 2): else: ?>
                  <a style="width:90px;"  class="btn btn-default btn-icon" warn="警告" data-tip="" title="你确定要点击付款完成吗" target="ajaxTodo" href="<?php echo ($host_name); ?>/checkaccount/confirmPayDone?detailid=<?php echo ($vlist["detailid"]); ?>&statementid=<?php echo ($statementid); ?>" calback="navTabAjaxDone" data-original-title="付款">
				  <span>
				   <?php echo ($vlist["cont"]); ?>
				  </span>
				  </a><?php endif; ?>
                </div>
              </td>
            </tr><?php endforeach; endif; else: echo "" ;endif; ?>
          </tbody>
        </table>
      </form>
      <?php echo ($instruction); ?>
    </div>
  </div>
  <?php echo ($page); ?>

</div>