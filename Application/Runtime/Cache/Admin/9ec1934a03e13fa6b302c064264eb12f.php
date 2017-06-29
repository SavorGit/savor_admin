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
  <form onsubmit="return navTabSearch(this);" id="pagerForm" action="<?php echo ($host_name); ?>/sysnode/manager" method="post">
    <input type="hidden" name="pageNum" value="<?php echo ($pageNum); ?>"/>
    <input type="hidden" name="numPerPage" value="<?php echo ($numPerPage); ?>"/>
    <input type="hidden" name="_order" value="<?php echo ($_order); ?>"/>
    <input type="hidden" name="_sort" value="<?php echo ($_sort); ?>"/>
    <div class="searchBar">
      <div class="clearfix">
        <div class="col-xs-12 col-sm-4 col-md-3 col-lg-2">
          <div class="tools-group">
            <a class="btn btn-success btn-sm add" href="<?php echo ($host_name); ?>/sysnode/sysnodeadd?acttype=0" title="新增模块" target="dialog" mask="true"><i class="fa fa-plus"></i> 新增节点</a>
          </div>
        </div>
        
        <div class="col-xs-12 col-sm-8 col-md-6 col-lg-4 pull-right">
          <div class="input-group input-group-sm">
            <input type="text" class="form-control" name="searchTitle" value="<?php echo ($searchTitle); ?>" placeholder="模块名称...">
            <span class="input-group-btn">
              <select name="searchCode" class="form-control bs-select class-filter" data-style="btn-success btn-sm" data-container="body">
                <option value='' >所有节点</option>
                <?php $_result=C('MANGER_KEY');if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>" <?php if($key == $searchCode): ?>selected<?php endif; ?>><?php echo ($vo); ?></option><br><?php endforeach; endif; else: echo "" ;endif; ?>
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
              <!-- <th class="table-checkbox">
                <input type="checkbox" data-check="all" data-parent=".table" />
              </th> -->
              <th>模块ID</th>
              <th>模块名称</th>
              <th>节点KEY</th>
              <th>菜单级别</th>
              <th>M</th>
              <th>C</th>
              <th>A</th>
              <th>显示顺序</th>
              <th>状态</th>
              <th class="table-tool">操作</th>
            </tr>
          </thead>
          <tbody data-check="list" data-parent=".table">
            <?php if(is_array($sysmenulist)): $i = 0; $__LIST__ = $sysmenulist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vlist): $mod = ($i % 2 );++$i;?><tr target="sid_user">
              <!-- <td class="table-checkbox">
                <input type="checkbox" class="checkboxes" value="30" name="postlist[]">
              </td> -->
              <td data-title="模块ID"><?php echo ($vlist["id"]); ?></td>
              <td data-title="模块名称"><a href="#" class="click-able-title"><?php echo ($vlist["name"]); ?></a></td>
              <td data-title="节点KEY">
                <?php $_result=C('MANGER_KEY');if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($key == $vlist['nodekey']): echo ($vo); endif; endforeach; endif; else: echo "" ;endif; ?>
              </td>
              <td data-title="菜单级别">
                <?php $_result=C('MANGER_LEVEL');if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($key == $vlist['menulevel']): echo ($vo); endif; endforeach; endif; else: echo "" ;endif; ?>
              </td>
              <td data-title="M"><?php echo ($vlist["m"]); ?></td>
              <td data-title="C"><?php echo ($vlist["c"]); ?></td>
              <td data-title="A"><?php echo ($vlist["a"]); ?></td>
              <td data-title="显示顺序"><?php echo ($vlist["displayorder"]); ?></td>
              <td data-title="是否可用" <?php if($vlist['isenable'] != 1): ?>style="font-weight:bold; color:red;";<?php endif; ?>>
                <?php $_result=C('MANGER_STATUS');if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($key == $vlist['isenable']): echo ($vo); endif; endforeach; endif; else: echo "" ;endif; ?>
              </td>
              <td class="table-tool" data-title="操作">
                <div class="tools-edit">
                  <a data-tip="修改" target="dialog" mask="true" href="<?php echo ($host_name); ?>/sysnode/sysnodeAdd?id=<?php echo ($vlist["id"]); ?>&acttype=1&sysmenuid=<?php echo ($vlist["sysmenuid"]); ?>" class="btn btn-success btn-icon">
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
<script>
  $(function(){
    $(".class-filter").change(function (){
      $(this).closest("form").submit();
    })
  })
</script>