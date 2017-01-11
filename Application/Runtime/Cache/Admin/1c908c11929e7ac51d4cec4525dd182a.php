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
  <form onsubmit="return navTabSearch(this);" id="pagerForm" action="<?php echo ($host_name); ?>/syslog/syslogList" method="post">
    <input type="hidden" name="pageNum" value="<?php echo ($pageNum); ?>"/>
    <input type="hidden" name="numPerPage" value="<?php echo ($numPerPage); ?>"/>
    <input type="hidden" name="_order" value="<?php echo ($_order); ?>"/>
    <input type="hidden" name="_sort" value="<?php echo ($_sort); ?>"/>
    <div class="searchBar">
      <div class="clearfix">
      	<div class="col-xs-12 col-sm-4 col-md-3 col-lg-2">
          <div class="tools-group">
            <a onclick="alertMsg.error('当前模块不允许新增')" href="#" class="btn btn-success btn-sm add" style="opacity:0.6" title="新增" ><i class="fa fa-plus"></i> 新增</a>
          </div>
        </div>
		<div class="col-xs-12 col-sm-8 col-md-6 col-lg-4 pull-right">
          <div class="input-group input-group-sm">
            <input type="text" class="form-control" name="searchTitle" value="<?php echo ($searchTitle); ?>" placeholder="列表名称/操作动作/时间">
            <span class="input-group-btn">
              <select name="shwcid" class="form-control bs-select class-filter" data-style="btn-success btn-sm" data-container="body">
                <option value="">所有栏目</option>
                <?php if(is_array($classList)): $i = 0; $__LIST__ = $classList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php if($key != 0): echo ($vo["id"]); endif; ?>" data-content='<span class="lvl"><?php echo ($vo["html"]); ?></span><?php echo ($vo["modulename"]); ?>'<?php if($vo["id"] == $shwcid): ?>selected<?php endif; ?>><?php echo ($vo["modulename"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
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
              <th>序号</th>
              <th>栏目名称</th>
              <th>列表名称</th>
              <th>用户名</th>
              <th>操作动作</th>
              <th>时间</th>
              <th>登陆IP</th>
              <th>登陆地区</th>
            </tr>
          </thead>
          <tbody data-check="list" data-parent=".table">
            <?php if(is_array($sysloglist)): $i = 0; $__LIST__ = $sysloglist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vlist): $mod = ($i % 2 );++$i;?><tr target="sid_user">	            
	              <td data-title="序号"><?php echo ($key+1+$num); ?></td>
	              <td data-title="操作栏目名称"><?php echo ($vlist["actionid"]); ?></td>
	              <td data-title="操作列表名称"><?php echo ($vlist["program"]); ?></td>
	              <td data-title="操作用户"><?php echo ($vlist["loginid"]); ?></td>
	              <td data-title="操作动作">
	              	<?php if($vlist["opprate"] == '新增'): ?><span  style="color:green;"><?php echo ($vlist["opprate"]); ?></span>
	              	<?php elseif($vlist["opprate"] == '删除'): ?>
	              		<span  style="color:red;"><?php echo ($vlist["opprate"]); ?></span>
              		<?php elseif($vlist["opprate"] == '修改'): ?>
              			<span  style="color:blue;"><?php echo ($vlist["opprate"]); ?></span>
              		<?php else: ?>
              			<span><?php echo ($vlist["opprate"]); ?></span><?php endif; ?>
	              </td>
	              <td data-title="操作时间"><?php echo ($vlist["logtime"]); ?></td>
	              <td data-title="客户端IP"><?php echo ($vlist["clientip"]); ?></td>
	              <td data-title="登陆地区"><?php echo ($vlist["areaname"]); ?></td>
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