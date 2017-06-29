<?php if (!defined('THINK_PATH')) exit();?><script>  
    if(!window.jQuery){
      var path = window.location.pathname;
      path = path.replace("/admin/","");
      console.log(path);
      window.location.href = "<?php echo ($host_name); ?>#" + path;
    }
</script>

<!--显示列表样式333331 start-->
<div class="pageHeader">
  <form onsubmit="return navTabSearch(this);" id="pagerForm" action="<?php echo ($host_name); ?>/article/homemanager" method="post" >
    <input type="hidden" name="pageNum" value="<?php echo ($pageNum); ?>"/>
    <input type="hidden" name="numPerPage" value="<?php echo ($numPerPage); ?>"/>
    <input type="hidden" name="_order" value="<?php echo ($_order); ?>"/>
    <input type="hidden" name="_sort" value="<?php echo ($_sort); ?>"/>
    <div class="searchBar">
      <div class="clearfix">
        <div class="col-xs-12 col-sm-4 col-md-3">
          <div class="tools-group">
            <a class="btn btn-success btn-sm add" href="<?php echo ($host_name); ?>/article/addsort" title="排序" target="dialog" mask="true"><i class="fa fa-plus"></i> 排序</a>
          </div>
        </div>
        <div class="col-xs-12 col-sm-5 col-md-4 pull-right">
          <div class="input-group input-group-sm">
            <input type="text" class="form-control" name="name" value="<?php echo ($name); ?>" placeholder="首页查找">
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
            <th>序号</th>
            <th>文章标题</th>
            <th>分类</th>
            <th>内容类型</th>
            <th>排序</th>
            <th>容量(<?php echo ($tsize); ?>MB)</th>
            <th>最后时间</th>
            <th>状态</th>
            <th class="table-tool">操作</th>
          </tr>
          </thead>
          <tbody data-check="list" data-parent=".table">
          <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vlist): $mod = ($i % 2 );++$i;?><tr target="sid_user">
              <td data-title="序号"><?php echo ($vlist["id"]); ?></td>
              <td data-title="标题"><?php echo ($vlist["title"]); ?></td>
              <td data-title="分类"><?php echo ($vlist["cat_name"]); ?></td>
              <td data-title="内容类型">
                <?php if($vlist["type"] == 1): ?>图文
                  <?php elseif($vlist["type"] == 2): ?>图集
                  <?php elseif($vlist["type"] == 3): ?>
                  <?php if($vlist["media_id"] == 0): ?>视频（非点播）
                    <?php else: ?> 视频（点播）<?php endif; ?>
                  <?php else: ?> 纯文本<?php endif; ?>
              </td>
              <td data-title="排序"><?php echo ($vlist["sort_num"]); ?></td>
              <td data-title="大小"> <?php if($vlist["type"] == 3): if($vlist["media_id"] > 0): echo ($vlist["size"]); ?>MB<?php else: endif; ?> <?php else: endif; ?></td>
              <td data-title="最后时间"><?php echo ($vlist["update_time"]); ?></td>
              <td data-title="状态">
                <?php if($vlist['state'] == 0): ?><a data-tip="已经下线" target="ajaxTodo" href="<?php echo ($host_name); ?>/article/changestatus?id=<?php echo ($vlist["id"]); ?>&flag=1" calback="navTabAjaxDone" class="btn btn-default btn-icon"><span>
                <i class="fa fa-toggle-off"></i></span></a>
                  <?php else: ?>
                  <a data-tip="已经上线" target="ajaxTodo" href="<?php echo ($host_name); ?>/article/changestatus?id=<?php echo ($vlist["id"]); ?>&flag=0" calback="navTabAjaxDone" class="btn btn-default btn-icon"><span>
                <i class="fa fa-toggle-on"></i></span></a><?php endif; ?>

              </td>

              <td class="table-tool" data-title="操作">
                <div class="tools-edit">
                  
                   <a warn="警告" data-tip="移除" title="你确定要从首页移除吗？" target="ajaxTodo" rel="article/homemanager"
href="<?php echo ($host_name); ?>/article/delhome?id=<?php echo ($vlist["id"]); ?>&acttype=2"  class="btn btn-success btn-icon">
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
  <?php echo ($page); ?>
</div>

<SCRIPT LANGUAGE="JavaScript">

  $(function() {

    $("select").each(function(){
      $(this).change(function(){
        var sta = $(this).val();
        var cid = $(this).attr('data-state');
        $.ajax({
          type:"POST",
          dataType: "json",
          url:"<?php echo ($host_name); ?>/release/changestate",
          data:"state="+sta+"&cid="+cid,
          success:function(data){
            if(data == 1) {
              alert('修改成功');
            } else {
              alert('修改失败');
            }

          }
        });
      })
    });



  });
</script>