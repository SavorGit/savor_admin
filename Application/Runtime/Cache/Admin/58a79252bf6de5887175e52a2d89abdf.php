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
  <form onsubmit="return navTabSearch(this);" id="pagerForm" action="<?php echo ($host_name); ?>/sysusergroup/sysusergroupList" method="post">
    <input type="hidden" name="pageNum" value="<?php echo ($pageNum); ?>"/>
    <input type="hidden" name="numPerPage" value="<?php echo ($numPerPage); ?>"/>
    <input type="hidden" name="_order" value="<?php echo ($_order); ?>"/>
    <input type="hidden" name="_sort" value="<?php echo ($_sort); ?>"/>
    <div class="searchBar">
      <div class="clearfix">
        <div class="col-xs-12 col-sm-4 col-md-3">
          <div class="tools-group">
            <a class="btn btn-success btn-sm add" href="<?php echo ($host_name); ?>/sysusergroup/sysusergroupAdd?acttype=0" title="新增分组" target="dialog" mask="true"><i class="fa fa-plus"></i> 新增</a>
          </div>
        </div>
        <div class="col-xs-12 col-sm-5 col-md-4 pull-right">
          <div class="input-group input-group-sm">
            <input type="text" class="form-control" name="searchTitle" value="<?php echo ($searchTitle); ?>" placeholder="分组名">
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
              <th>分组名</th>
              <th>创建人</th>
              <th>创建时间</th>
              <th class="table-tool">操作</th>
            </tr>
          </thead>
          <tbody data-check="list" data-parent=".table">
            <?php if(is_array($sysusergrouplist)): $i = 0; $__LIST__ = $sysusergrouplist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vlist): $mod = ($i % 2 );++$i;?><tr target="sid_user">
              <!-- <td class="table-checkbox">
                <input type="checkbox" class="checkboxes" value="30" name="postlist[]">
              </td> -->
              <td data-title="分组名"><a href="#" class="click-able-title"><?php echo ($vlist["name"]); ?></a></td>
              <td data-title="创建人"><?php echo ($vlist["username"]); ?></td>
              <td data-title="创建时间"><?php echo (substr($vlist["createtime"],0,10)); ?></td>
              <td class="table-tool" data-title="操作">
                <div class="tools-edit">
                  <a data-tip="修改用户分组" target="dialog" mask="true" href="<?php echo ($host_name); ?>/sysusergroup/sysusergroupAdd?id=<?php echo ($vlist["id"]); ?>&acttype=1" class="btn btn-success btn-icon">
                    <i class="fa fa-pencil"></i>
                  </a>
                  <?php if($vlist['id'] != 1): ?><a warn="警告" data-tip="删除" title="你确定要删除吗？" target="ajaxTodo" href="<?php echo ($host_name); ?>/sysusergroup/sysusergroupDel?id=<?php echo ($vlist["id"]); ?>" calback="navTabAjaxDone" class="btn btn-danger btn-icon"><span><i class="fa fa-trash"></i></span></a>
                  <?php else: ?>
                    <a onclick="alertMsg.error('超级管理员不能被删除，请谨慎操作！')" href="#" class="btn btn-danger btn-icon" style="opacity:0.6"><span><i class="fa fa-trash"></i></span></a><?php endif; ?>
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