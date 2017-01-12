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
  <form onsubmit="return navTabSearch(this);" id="pagerForm" action="<?php echo ($host_name); ?>/hotel/manager" method="post" >
    <input type="hidden" name="pageNum" value="<?php echo ($pageNum); ?>"/>
    <input type="hidden" name="numPerPage" value="<?php echo ($numPerPage); ?>"/>
    <input type="hidden" name="_order" value="<?php echo ($_order); ?>"/>
    <input type="hidden" name="_sort" value="<?php echo ($_sort); ?>"/>
    <div class="searchBar">
      <div class="clearfix">
        <div class="col-xs-12 col-sm-4 col-md-3">
          <div class="tools-group">
            <a class="btn btn-success btn-sm add" href="<?php echo ($host_name); ?>/hotel/add?acttype=0" title="新增用户" target="dialog" mask="true"><i class="fa fa-plus"></i> 新增</a>
          </div>
        </div>
        <div class="col-xs-12 col-sm-5 col-md-4 pull-right">
          <div class="input-group input-group-sm">
            <input type="text" class="form-control" name="name" value="<?php echo ($name); ?>" placeholder="酒店名称">
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
              <!-- <th class="table-checkbox">
                <input type="checkbox" data-check="all" data-parent=".table" />
              </th> -->
              <th>酒店ID</th>
              <th>酒店名称</th>
              <th>酒店地址</th>
              <th>酒店区域</th>
              <th>酒店维护人</th>
              <th>手机</th>
              <th>固定电话</th>
              <th>酒楼级别</th>
              <th>是否重点</th>
              <th>安装日期</th>
              <th>酒楼状态</th>
              <th>状态变更说明</th>
              <th>备注</th>
              <th>GPS</th>
              <th>创建日期</th>
              <th>最后更新日期</th>
              <th>删除状态</th>
              <th class="table-tool">操作</th>
            </tr>
          </thead>
          <tbody data-check="list" data-parent=".table">
            <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vlist): $mod = ($i % 2 );++$i;?><tr target="sid_user">
              <!-- <td class="table-checkbox">
                <input type="checkbox" class="checkboxes" value="30" name="postlist[]">
              </td> -->
              <td data-title="酒店ID"><?php echo ($vlist["id"]); ?></td>
              <td data-title="酒店名称"><?php echo ($vlist["name"]); ?></td>
              <td data-title="酒店地址"><?php echo ($vlist["addr"]); ?></td>
              <td data-title="酒店区域"><?php echo ($vlist["area_name"]); ?></td>
              <td data-title="酒店维护人"><?php echo ($vlist["contactor"]); ?></td>
              <td data-title="手机"><?php echo ($vlist["mobile"]); ?></td>
              <td data-title="固定电话"><?php echo ($vlist["tel"]); ?></td>
              <td data-title="酒楼级别"><?php echo ($vlist["level"]); ?></td>
              <td data-title="是否重点">
                <?php if($vlist["iskey"] == 1): ?>是  <?php else: ?> 否<?php endif; ?> 
              </td>
              <td data-title="安装日期"><?php echo ($vlist["install_date"]); ?></td>
              <td data-title="酒楼状态">
                 <?php if($vlist["state"] == 1): ?>正常
                 <?php elseif($vlist["state"] == 2): ?> 冻结 
                 <?php else: ?>报损<?php endif; ?> 
              </td>
              <td data-title="状态变更说明"><?php echo ($vlist["state_change_reason"]); ?></td>
              <td data-title="备注"><?php echo ($vlist["remark"]); ?></td>
              <td data-title="备注"><?php echo ($vlist["gps"]); ?></td>
              <td data-title="创建日期"><?php echo ($vlist["create_time"]); ?></td>
              <td data-title="最后更新日期"><?php echo ($vlist["update_time"]); ?></td>
              <td data-title="删除状态">
                  <?php if($vlist['flag'] == 0): ?>正常
                  <?php else: ?>
                      删除<?php endif; ?>
              </td>

              <td class="table-tool" data-title="操作">
                <div class="tools-edit">
                  <a data-tip="修改" target="dialog" mask="true" href="<?php echo ($host_name); ?>/hotel/add?id=<?php echo ($vlist["id"]); ?>&acttype=1" class="btn btn-success btn-icon">
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