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
  <form onsubmit="return navTabSearch(this);" id="pagerForm" action="<?php echo ($host_name); ?>/menu/getlist" method="post" >
    <input type="hidden" name="pageNum" value="<?php echo ($pageNum); ?>"/>
    <input type="hidden" name="numPerPage" value="<?php echo ($numPerPage); ?>"/>
    <input type="hidden" name="_order" value="<?php echo ($_order); ?>"/>
    <input type="hidden" name="_sort" value="<?php echo ($_sort); ?>"/>
    <div class="searchBar">
      <div class="clearfix">
        <div class="form-inline">
          <div class="goods-date">
            <div class="form-inline">
              <div class="form-group">
                <label>上传时间：</label>
                <div class="input-group input-group-sm date form_datetime" data-date="<?php echo ($vinfo["log_time"]); ?>" data-ymd="true">
                  <input name="starttime" type="text" size="14" class="form-control date" placeholder="开始日期" value="<?php echo ($vinfo["log_time"]); ?>" readonly>
                         <span class="input-group-btn">
                           <button class="btn default btn-sm date-reset" type="button" style="display:none"><i class="fa fa-times"></i></button>
                           <button class="btn btn-success btn-sm date-set" type="button"><i class="fa fa-calendar"></i></button>
                         </span>
                </div>
              </div>
              <div class="input-group input-group-sm date form_datetime" data-date="<?php echo ($vinfo["log_time"]); ?>" data-ymd="true">
                <input name="end_time" type="text" size="14" class="form-control date" placeholder="结束日期" value="<?php echo ($vinfo["log_time"]); ?>" readonly>
                     <span class="input-group-btn">
                       <button class="btn default btn-sm date-reset" type="button" style="display:none"><i class="fa fa-times"></i></button>
                       <button class="btn btn-success btn-sm date-set" type="button"><i class="fa fa-calendar"></i></button>
                     </span>
              </div>

              <div class="col-xs-4 col-sm-4 col-md-4 pull-right">
                <div class="input-group input-group-sm">
                  <input type="text" class="form-control" name="titlename" value="<?php echo ($name); ?>" placeholder="输入名称查找">
            <span class="input-group-btn">
              <button class="btn btn-primary" type="submit" id="choosedata"><i class="fa fa-search"></i></button>
            </span>
                </div>
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
    <div class="no-more-tables">
      <form method="post" action="#" id="del-form" class="pageForm required-validate" enctype="multipart/form-data" onsubmit="return iframeCallback(this, dialogAjaxDone)">
        <table class="table table-bordered table-striped" targetType="navTab" asc="asc" desc="desc">
          <thead>
          <tr id="post">
            <th width="22"><input type="checkbox" group="ids" class="checkboxCtrl"></th>
            <th>序号</th>
            <th>名称</th>
            <th>创建日期</th>
            <th>更新日期</th>
            <th>是否选择洒楼</th>
            <th>节目详情</th>
            <th>状态</th>
            <th>操作日志</th>
            <th class="table-tool">操作</th>
          </tr>
          </thead>
          <tbody data-check="list" data-parent=".table">
          <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vlist): $mod = ($i % 2 );++$i;?><tr target="sid_user">
              <td><input name="ids" value="<?php echo ($vlist["id"]); ?>" type="checkbox"></td>
              <td data-title="序号"><?php echo ($vlist["id"]); ?></td>
              <td data-title="名称"><?php echo ($vlist["menu_name"]); ?></td>
              <td data-title="创建日期"><?php echo ($vlist["create_time"]); ?></td>
              <td data-title="更新日期"><?php echo ($vlist["update_time"]); ?></td>
              <td data-title="是否选择洒楼">

                <?php if($vlist["count"] == 0): ?><a data-tip="请选择酒楼" class="btn btn-success " target="dialog" mask="true" href="<?php echo ($host_name); ?>/menu/selecthotel?menuid=<?php echo ($vlist["id"]); ?>&menuname=<?php echo ($vlist["menu_name"]); ?>">请选择酒楼</a>
                  <?php else: ?> <a data-tip="酒楼数为" class="btn btn-success " target="dialog" mask="true" href="<?php echo ($host_name); ?>/menu/gethotelinfo?menuid=<?php echo ($vlist["id"]); ?>&menuname=<?php echo ($vlist["menu_name"]); ?>"><?php echo ($vlist["count"]); ?></a><?php endif; ?>
              </td>
              <td data-title="节目详情">
                <a data-tip="查看" class="btn btn-success " target="dialog" mask="true" href="<?php echo ($host_name); ?>/menu/getdetail?id=<?php echo ($vlist["id"]); ?>&name=<?php echo ($vlist["menu_name"]); ?>">查看</a>
              </td>
              <td data-title="状态">

                <?php if($vlist['state'] == 0): ?>下线
                  <?php elseif($vlist['state'] == 1): ?>
                  上线<?php endif; ?>
              </td>

              <td class="table-tool" data-title="操作日志">
                <div class="tools-edit">
                  <a data-tip="日志查看" target="dialog" mask="true"
                     href="<?php echo ($host_name); ?>/menu/getlog?id=<?php echo ($vlist["id"]); ?>&name=<?php echo ($vlist["menu_name"]); ?>"
                     class="btn btn-success btn-icon">
                    <i class="fa fa-pencil"></i>
                  </a>
                </div>
              </td>


              <td class="table-tool" data-title="操作">
                <div class="tools-edit">
                  <a data-tip="修改" target="dialog" mask="true"

                     href="<?php echo ($host_name); ?>/menu/addmenu?id=<?php echo ($vlist["id"]); ?>&type=2&name=<?php echo ($vlist["menu_name"]); ?>"
                     class="btn btn-success btn-icon">
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