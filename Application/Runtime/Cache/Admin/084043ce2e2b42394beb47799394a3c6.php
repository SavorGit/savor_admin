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
  <form onsubmit="return navTabSearch(this);" id="pagerForm" action="<?php echo ($host_name); ?>/advert/adsList" method="post" >
    <input type="hidden" name="pageNum" value="<?php echo ($pageNum); ?>"/>
    <input type="hidden" name="numPerPage" value="<?php echo ($numPerPage); ?>"/>
    <input type="hidden" name="_order" value="<?php echo ($_order); ?>"/>
    <input type="hidden" name="_sort" value="<?php echo ($_sort); ?>"/>
    <div class="searchBar">
      <div class="clearfix">
      <div class="col-xs-12 col-sm-2 col-md-2">
          <div class="tools-group">
            <a class="btn btn-success btn-sm add" href="<?php echo ($host_name); ?>/advert/addAdvert" title="新增" target="dialog" mask="true"><i class="fa fa-plus"></i>新增</a>
          </div>
        </div>
       <div class="col-xs-12 col-sm-4 col-md-2">
          <div class="input-group input-group-sm">
            <input type="text" class="form-control" name="keywords" style="width: 150px;" value="<?php echo ($keywords); ?>" placeholder="输入名称查找">
            <span class="input-group-btn">
            	<select name="adstype" class="form-control bs-select class-filter" data-style="btn-success btn-sm" data-container="body">
					<option value="0" data-content='全部' <?php if($adstype == 0): ?>selected<?php endif; ?> >全部<?php echo ($adstype); ?></option>
					<?php $_result=C('ADS_TYPE');if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($key != 3): ?><option value="<?php echo ($key); ?>" data-content='<?php echo ($vo); ?>'<?php if($key == $adstype): ?>selected<?php endif; ?> ><?php echo ($vo); ?></option><?php endif; endforeach; endif; else: echo "" ;endif; ?>
				</select>
			</span>
          </div>
        </div>
        <div class="col-xs-12 col-sm-4 col-md-5" style="margin-left:100px;">
          <div class="form-inline">
            <div class="form-group">
              <label>上传时间：</label>
              <div class="input-group input-group-sm date form_datetime" data-date="<?php echo ($timeinfo["now_time"]); ?>" data-ymd="true">
                <input name="begin_time" type="text" size="14" class="form-control date" placeholder="开始日期" value="<?php echo ($timeinfo["begin_time"]); ?>" readonly>
                         <span class="input-group-btn">
                           <button class="btn default btn-sm date-reset" type="button" style="display:none"><i class="fa fa-times"></i></button>
                           <button class="btn btn-success btn-sm date-set" type="button"><i class="fa fa-calendar"></i></button>
                         </span>
              </div>
            </div>
            <div class="input-group input-group-sm date form_datetime" data-date="<?php echo ($timeinfo["now_time"]); ?>" data-ymd="true">
              <input name="end_time" type="text" size="14" class="form-control date" placeholder="结束日期" value="<?php echo ($timeinfo["end_time"]); ?>" readonly>
                     <span class="input-group-btn">
                       <button class="btn default btn-sm date-reset" type="button" style="display:none"><i class="fa fa-times"></i></button>
                       <button class="btn btn-success btn-sm date-set" type="button"><i class="fa fa-calendar"></i></button>
                     </span>
                     <span class="input-group-btn">
              			<button class="btn btn-primary" type="submit" id="choosedata"><i class="fa fa-search">搜索</i></button>
            		</span>
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
              <th>ID</th>
              <th>名称</th>
              <th>地址</th>
              <th>简介</th>
              <th>创建者</th>
              <th>创建时间</th>
              <th>类型</th>
              <th>状态</th>
              <th class="table-tool">操作</th>
            </tr>
          </thead>
          <tbody data-check="list" data-parent=".table">
            <?php if(is_array($datalist)): $i = 0; $__LIST__ = $datalist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vlist): $mod = ($i % 2 );++$i;?><tr target="sid_user">
              <td data-title="ID"><?php echo ($vlist["id"]); ?></td>
              <td data-title="名称"><?php echo ($vlist["name"]); ?></td>
              <td data-title="地址"><a href="<?php echo ($vlist["oss_addr"]); ?>" target='__blank'>预览</a></td>
              <td data-title="简介"><?php echo ($vlist["description"]); ?></td>
              <td data-title="创建者"><?php echo ($vlist["creator_name"]); ?></td>
              <td data-title="创建时间"><?php echo ($vlist["create_time"]); ?></td>
              <td data-title="类型"><?php echo ($vlist["type_str"]); ?></td>
              <td data-title="状态">
              <?php if($vlist['state'] == 1): ?><a data-tip="已审核" target="ajaxTodo" href="<?php echo ($host_name); ?>/advert/operateStatus?adsid=<?php echo ($vlist["id"]); ?>&atype=1&flag=0" calback="navTabAjaxDone" class="btn btn-default btn-icon"><span>
                <i class="fa fa-toggle-on"></i></span></a>
              <?php else: ?>
              <a data-tip="未审核" target="ajaxTodo" href="<?php echo ($host_name); ?>/advert/operateStatus?adsid=<?php echo ($vlist["id"]); ?>&atype=1&flag=1" calback="navTabAjaxDone" class="btn btn-default btn-icon"><span>
                <i class="fa fa-toggle-off"></i></span></a><?php endif; ?>
              
              </td>
              <td class="table-tool" data-title="操作">
                <div class="tools-edit">
                  <a data-tip="修改" target="dialog" mask="true" href="<?php echo ($host_name); ?>/advert/editAds?adsid=<?php echo ($vlist["id"]); ?>" class="btn btn-success btn-icon">
                    <i class="fa fa-pencil"></i>
                  </a>
                  <a style="display:none;" data-tip="删除" target="ajaxTodo" href="<?php echo ($host_name); ?>/advert/operateStatus?adsid=<?php echo ($vlist["id"]); ?>&atype=2" calback="navTabAjaxDone" class="btn btn-success btn-icon">
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