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
  <form onsubmit="return navTabSearch(this);" id="pagerForm" action="<?php echo ($host_name); ?>/content/check" method="post" >
    <input type="hidden" name="pageNum" value="<?php echo ($pageNum); ?>"/>
    <input type="hidden" name="numPerPage" value="<?php echo ($numPerPage); ?>"/>
    <input type="hidden" name="_order" value="<?php echo ($_order); ?>"/>
    <input type="hidden" name="_sort" value="<?php echo ($_sort); ?>"/>
    <div class="searchBar">
      <div class="clearfix">
      </div>
      <div class="form-inline" style="margin-top:3px;">

        <div class="form-group">

          <div class="input-group input-group-sm date form_date" data-ymd="true" data-date="<?php echo ($timeinfo["now_time"]); ?>">
              <input style="margin-left: 6px;" name="begin_time" type="text" size="16" class="form-control date" placeholder="开始日期" value="<?php echo ($timeinfo["begin_time"]); ?>" readonly>
                  <span class="input-group-btn">
                    <button class="btn default date-reset  btn-sm" type="button"><i class="fa fa-times"></i></button>
                    <button class="btn btn-success date-set  btn-sm" type="button"><i class="fa fa-calendar"></i></button>
                  </span>
            </div>

            <div class="input-group input-group-sm date form_date" data-ymd="true" data-date="<?php echo ($timeinfo["now_time"]); ?>" style="width:135px;">

              <input style="width:73px;padding:0px;margin:0px;margin-left: 15px;"  name="end_time" type="text" size="16" class="form-control date" placeholder="结束日期" value="<?php echo ($timeinfo["end_time"]); ?>" readonly>
                  <span class="input-group-btn" style="padding:0px;margin:0px;" >
                    <button class="btn default date-reset  btn-sm" type="button"><i class="fa fa-times"></i></button>
                    <button class="btn btn-success date-set  btn-sm" type="button"><i class="fa fa-calendar"></i></button>
                  </span>
            </div>
		
			

		  
          <div class="form-group">
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
  </form>
</div>
<div class="pageContent" id="pagecontent" style="margin-top:30px;">
  <div id="w_list_print">
    <div class="no-more-tables">
      <form method="post" action="#" id="del-form" class="pageForm required-validate" enctype="multipart/form-data" onsubmit="return iframeCallback(this, dialogAjaxDone)">
        <table class="table table-bordered table-striped" targetType="navTab" asc="asc" desc="desc">
          <thead>
          <tr id="post">
            <th width="22" style="display:none"><input type="checkbox" group="ids" class="checkboxCtrl"></th>
            <th>序号</th>
            <th>名称</th>
            <th>分类</th>
            <th style="display: none;">>封面图片</th>
            <th>内容类型</th>
            <th>创建人</th>
            <th>创建时间</th>
            <th>状态</th>
            <th class="table-tool">操作</th>
          </tr>
          </thead>
          <tbody data-check="list" data-parent=".table">
          <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vlist): $mod = ($i % 2 );++$i;?><tr target="sid_user">
              <td style="display:none"><input name="ids" value="<?php echo ($vlist["id"]); ?>" type="checkbox"></td>
              <td data-title="序号"><?php echo ($vlist["id"]); ?></td>
              <td data-title="名称"><?php echo ($vlist["title"]); ?></td>
              <td data-title="名称"><?php echo ($vlist["cat_name"]); ?></td>
              <td data-title="封面图片" style="display: none;"><a data-tip="预览图片" target="dialog" mask="true"
                                       href="<?php echo ($host_name); ?>/article/showpic?pic=<?php echo ($vlist["img_url"]); ?>"   class="btn btn-success btn-icon">
                <i class="icon-list"></i>
              </a></td>
              <td data-title="内容类型">
                <?php if($vlist["type"] == 1): ?>图文
                  <?php elseif($vlist["type"] == 2): ?>图集
                  <?php elseif($vlist["type"] == 3): ?>
                    <?php if($vlist["media_id"] == 0): ?>视频（非点播）
                      <?php else: ?> 视频（点播）<?php endif; ?>
                  <?php else: ?> 纯文本<?php endif; ?>
              </td>
              <td data-title="创建人"><?php echo ($vlist["operators"]); ?></td>
              <td data-title="创建时间"><?php echo ($vlist["create_time"]); ?></td>
              <td data-title="状态" style="text-align:center;">
                <?php if($vlist['state'] == 2): ?><a style="width:60px;" data-tip="已审核" target="ajaxTodo" href="<?php echo ($host_name); ?>/content/operateStatus?adsid=<?php echo ($vlist["id"]); ?>&flag=2" calback="navTabAjaxDone" class="btn btn-default btn-icon"><span>
                <i class="fa fa-toggle-on"></i></span></a>
                  <?php else: ?>
                  <a style="width:60px;" data-tip="未审核" target="ajaxTodo" rel="content/getlist" href="<?php echo ($host_name); ?>/content/operateStatus?adsid=<?php echo ($vlist["id"]); ?>&flag=1" calback="navTabAjaxDone" class="btn btn-default btn-icon"><span>
                <i class="fa fa-toggle-off"></i></span></a><?php endif; ?>

              </td>

              <td class="table-tool" data-title="操作">
                <div class="tools-edit">
                  <a data-tip="预览"

                     href="/content/<?php echo ($vlist["id"]); ?>.html"


                     target="_blank"  class="btn btn-success btn-icon">
                    <i class="fa fa-eye"></i>
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
  <script type="text/javascript">
        $(function(){
          $(".form-control.date").datetimepicker({
            minView: "month", //选择日期后，不会再跳转去选择时分秒
            language:  'zh-CN',
            format: 'yyyy-mm-dd',
            todayBtn:  1,
            autoclose: 1,
          });
        })
</script>