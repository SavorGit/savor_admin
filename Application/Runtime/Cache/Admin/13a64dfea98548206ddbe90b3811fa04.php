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
  <form onsubmit="return navTabSearch(this);" id="pagerForm" action="<?php echo ($host_name); ?>/hotel/room" method="post" >
    <input type="hidden" name="pageNum" value="<?php echo ($pageNum); ?>"/>
    <input type="hidden" name="numPerPage" value="<?php echo ($numPerPage); ?>"/>
    <input type="hidden" name="_order" value="<?php echo ($_order); ?>"/>
    <input type="hidden" name="_sort" value="<?php echo ($_sort); ?>"/>
    <div class="searchBar">
      <div class="clearfix">
        <div class="col-xs-12 col-sm-4 col-md-3">
          <div class="tools-group">
            <a class="btn btn-success btn-sm add" href="<?php echo ($host_name); ?>/hotel/addRoom" title="新增包间" target="dialog" mask="true"><i class="fa fa-plus"></i> 新增</a>
          </div>
        </div>
        <div class="col-xs-12 col-sm-5 col-md-4 pull-right">
          <div class="input-group input-group-sm">
            <input type="text" class="form-control" name="name" value="<?php echo ($name); ?>" placeholder="包间名称">
            <span class="input-group-btn">
              <button class="btn btn-primary" type="submit" id="choosedata"><i class="fa fa-search"></i></button>
            </span>
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
              <th>酒店ID</th>
              <th>包间ID</th>
              <th>酒店名称</th>
              <th>包间名称</th>
              <th>包间类型</th>
              <th>备注</th>
              <th>创建时间</th>
              <th>最后更新时间</th>
              <th>删除状态</th>
              <th>冻结状态</th>         
              <th class="table-tool">操作</th>
            </tr>
          </thead>
          <tbody data-check="list" data-parent=".table">
            <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vlist): $mod = ($i % 2 );++$i;?><tr target="sid_user">
              <td data-title="酒店ID"><?php echo ($vlist["hotel_id"]); ?></td>
              <td data-title="包间ID"><?php echo ($vlist["id"]); ?></td>
              <td data-title="酒店名称"><?php echo ($vlist["hotel_name"]); ?></td>
              <td data-title="包间名称"><?php echo ($vlist["name"]); ?></td>
              <td data-title="包间类型"><?php echo ($vlist["type"]); ?></td>
              <td data-title="备注"><?php echo ($vlist["remark"]); ?></td>
              <td data-title="创建时间"><?php echo ($vlist["create_time"]); ?></td>
              <td data-title="最后更新时间"><?php echo ($vlist["update_time"]); ?></td>

              <td data-title="删除状态">
                <?php if($vlist["flag"] == 1): ?>删除  <?php else: ?> 正常<?php endif; ?> 
              </td>

              <td data-title="冻结状态">
                <?php if($vlist["state"] == 1): ?>正常
                 <?php elseif($vlist["state"] == 2): ?> 冻结 
                 <?php else: ?>报损<?php endif; ?> 
              </td>
              
              <td class="table-tool" data-title="操作">
                <div class="tools-edit">
                  <a data-tip="修改" target="dialog" mask="true" href="<?php echo ($host_name); ?>/hotel/addRoom?id=<?php echo ($vlist["id"]); ?>&acttype=1" class="btn btn-success btn-icon">
                    <i class="fa fa-pencil"></i>
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