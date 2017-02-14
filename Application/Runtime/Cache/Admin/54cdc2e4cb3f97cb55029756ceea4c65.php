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
  <form onsubmit="return navTabSearch(this);" id="pagerForm" action="<?php echo ($host_name); ?>/version/versionList" method="post" >
    <input type="hidden" name="pageNum" value="<?php echo ($pageNum); ?>"/>
    <input type="hidden" name="numPerPage" value="<?php echo ($numPerPage); ?>"/>
    <input type="hidden" name="_order" value="<?php echo ($_order); ?>"/>
    <input type="hidden" name="_sort" value="<?php echo ($_sort); ?>"/>
    <div class="searchBar">    
      <div class="clearfix">
        <div class="col-xs-12 col-sm-4 col-md-3 col-lg-2">
          <div class="tools-group s2">
            <a class="btn btn-success btn-sm add" href="<?php echo ($host_name); ?>/version/addVersion" title="新增版本" target="dialog" mask="true"><i class="fa fa-plus"></i><span>新增</span></a>
          </div>
        </div>
        <div class="col-xs-12 col-sm-8 col-md-6 col-lg-4 pull-right">
          <div class="input-group input-group-sm">
            <input type="text" class="form-control" name="keywords" value="<?php echo ($keywords); ?>" placeholder="版本名称">
            <span class="input-group-btn">
              <select name="device_type" class="form-control bs-select class-filter" data-style="btn-success btn-sm" data-container="body">
                <option value="">所有版本</option>
	                <?php $_result=C('DEVICE_TYPE');if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>" currentcid="<?php echo ($key); ?>"  <?php if($key == $device_type): ?>selected<?php endif; ?>><?php echo ($vo); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
              </select>
            </span>
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
              <th>ID</th>
              <th>版本名称</th>
              <th>版本号</th>
              <th>应用类型</th>
              <th>应用url</th>
              <th>md5值</th>
              <th>版本描述</th>
              <th>创建时间</th>
              <th class="table-tool">操作</th>
            </tr>
          </thead>
          <tbody data-check="list" data-parent=".table">
            <?php if(is_array($datalist)): $i = 0; $__LIST__ = $datalist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vlist): $mod = ($i % 2 );++$i;?><tr target="sid_user">
              <td data-title="ID"><?php echo ($vlist["id"]); ?></td>
              <td data-title="版本名称"><?php echo ($vlist["version_name"]); ?></td>
              <td data-title="版本号"><?php echo ($vlist["version_code"]); ?></td>
              <td data-title="应用类型"><?php echo ($vlist["device_typestr"]); ?></td>
              <td data-title="应用url"><?php echo ($vlist["oss_addr"]); ?></td>
              <td data-title="md5值"><?php echo ($vlist["md5"]); ?></td>
              <td data-title="版本描述"><?php echo ($vlist["remark"]); ?></td>
              <td data-title="创建时间"><?php echo ($vlist["create_time"]); ?></td>
              <td class="table-tool" data-title="操作">
                <div class="tools-edit">
                  <!--  
                  <a data-tip="修改" target="dialog" mask="true" href="<?php echo ($host_name); ?>/version/editVersion?vid=<?php echo ($vlist["id"]); ?>" class="btn btn-success btn-icon">
                    <i class="fa fa-pencil"></i>
                  </a>
                  -->
                  <a data-tip="删除" title="你确定要删除吗？" target="ajaxTodo" href="<?php echo ($host_name); ?>/version/delVersion?vid=<?php echo ($vlist["id"]); ?>" calback="navTabAjaxDone" class="btn btn-danger btn-icon">
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
<script>
  $(function(){
    $(".class-filter").change(function (){
      $(this).closest("form").submit();
    });
  })
</script>