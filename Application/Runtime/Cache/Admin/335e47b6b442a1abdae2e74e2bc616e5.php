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
  <form onsubmit="return navTabSearch(this);" id="pagerForm" action="<?php echo ($host_name); ?>/testdownloadrp/appdownload" method="post" >
    <input type="hidden" name="pageNum" value="<?php echo ($pageNum); ?>"/>
    <input type="hidden" name="numPerPage" value="<?php echo ($numPerPage); ?>"/>
    <input type="hidden" name="_order" value="<?php echo ($_order); ?>"/>
    <input type="hidden" name="_sort" value="<?php echo ($_sort); ?>"/>
    <div class="searchBar">
      <div class="clearfix">
        <div class="form-inline" style="margin-top:3px;">
        <div class="form-group">
          <div class="input-group input-group-sm">
            <label style="margin-left: 3px;" class="col-xs-1 col-sm-1 control-label">
              来源筛选：
            </label>
                
          </div>
        </div>
		<div class="form-group">
            <div class="input-group-sm input-group">
              <label class="control-label" style="">
                酒楼名称：
              </label>
                <span class="input-group-btn input-group-sm">
                <input type="text" class="form-control" name="hotel_name" value="<?php echo ($hotel_name); ?>" placeholder="请输入酒楼名称" >
            </span>
            </div>
          </div>
        <div class="form-group">
            <div class="input-group-sm input-group">
              <label class="control-label" style="">
                维护人：
              </label>
                <span class="input-group-btn input-group-sm">
                <input type="text" class="form-control" name="guardian" value="<?php echo ($guardian); ?>" placeholder="请输入维护人姓名" style="width:120px;">
            </span>
            </div>
          </div>

          <div class="form-group"  >
            <div class="input-group input-group-sm date form_datetime" data-date="" data-ymd="true">
              <input name="start_date" type="text" size="14" class="form-control date" placeholder="开始日期" value="<?php echo ($start_date); ?>" readonly>
                        <span class="input-group-btn" style="padding:0px;margin:0px;" >
                    <button class="btn default date-reset  btn-sm" type="button"><i class="fa fa-times"></i></button>
                    <button  class="btn btn-success date-set  btn-sm" type="submit"><i class="fa fa-calendar"></i></button>
                  </span>
            </div>


            <div class="input-group input-group-sm date form_datetime" data-date="" data-ymd="true">
              <input name="end_date" type="text" size="14" class="form-control date" placeholder="结束日期" value="<?php echo ($end_date); ?>" readonly>
                     <span class="input-group-btn" style="padding:0px;margin:0px;" >
                    <button class="btn default date-reset  btn-sm" type="button"><i class="fa fa-times"></i></button>
                    <button class="btn btn-success date-set  btn-sm" type="button"><i class="fa fa-calendar"></i></button>
                  </span>
            </div>
          </div>



          <div class="input-group">
            <span class="input-group-btn">
              <button class="btn btn-primary" type="submit" id="choosedata"><i class="fa fa-search"></i></button>
            </span>
          </div>

          <div class="input-group input-group-sm pull-right">
            <a class="btn btn-success btn-sm add" href="<?php echo ($host_name); ?>/excel/excelAppDownload?hote_name=<?php echo ($hote_name); ?>&guardian=<?php echo ($guardian); ?>&start_date=<?php echo ($start_date); ?>&end_date=<?php echo ($end_date); ?>" title="导出APP下载统计总表" target="_blank" mask="true"><i class="fa fa-plus"></i> 导出下载点播次数统计</a>
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
      <form method="post" action="#" id="del-form" class="pageForm required-validate" rel="second"
enctype="multipart/form-data" onsubmit="return iframeCallback(this, dialogAjaxDoneER)">
        <table class="table table-bordered table-striped" targetType="navTab" asc="asc" desc="desc">
          <thead>
          <tr id="post">
            <th>时段</th>
            <th>酒楼名称</th>
            <th>维护人</th>
            <th>首次投屏数量</th>
            <th>二维码扫描下载</th>
            <th>首次打开</th>
            <th>去重后总计</th>
          </tr>
          </thead>
          <tbody data-check="list" data-parent=".table">
          <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vlist): $mod = ($i % 2 );++$i;?><tr target="sid_user">
              <td data-title="日期"><?php echo ($vlist["start_date_time"]); ?>---<?php echo ($vlist["end_date_time"]); ?></td>
              <td data-title="酒楼名称"><?php echo ($vlist["hotel_name"]); ?></td>
              <td data-title="维护人"><?php echo ($vlist["guardian"]); ?></td>
              <td data-title="首次投屏数量"><?php echo ($vlist["box_num"]); ?></td>
              <td data-title="二维码扫描下载"><?php echo ($vlist["rq_num"]); ?> </td>
              <td data-title="首次打开"><?php echo ($vlist["mob_num"]); ?> </td>
              <td data-title="去重后总计"><?php echo ($vlist["all_num"]); ?> </td>
            </tr><?php endforeach; endif; else: echo "" ;endif; ?>
          </tbody>
        </table>
      </form>

    </div>
  </div>
  <?php echo ($page); ?>
</div>