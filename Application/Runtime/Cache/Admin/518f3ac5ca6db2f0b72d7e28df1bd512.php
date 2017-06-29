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
  <form onsubmit="return navTabSearch(this);" id="pagerForm" action="<?php echo ($host_name); ?>/content/getlist" method="post" >
    <input type="hidden" name="pageNum" value="<?php echo ($pageNum); ?>"/>
    <input type="hidden" name="numPerPage" value="<?php echo ($numPerPage); ?>"/>
    <input type="hidden" name="_order" value="<?php echo ($_order); ?>"/>
    <input type="hidden" name="_sort" value="<?php echo ($_sort); ?>"/>
    <div class="searchBar" >
      <div class="clearfix">
      </div>
      
      <div class="form-inline" style="margin-top:3px;">
      
      
      	<div class="form-group ">
              <div class="input-group-sm input-group">
                <label>分类：</label>
                <span class="input-group-btn input-group-sm">
                <select name="category_id" style="width: 20px" class="form-control bs-select class-filter" data-style="btn-success btn-sm" data-container="body">
	                <option value=0 >全部</option>
	                <?php if(is_array($vcainfo)): $i = 0; $__LIST__ = $vcainfo;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>" <?php if($vo["id"] == $category_id): ?>selected<?php endif; ?>><?php echo ($vo["name"]); ?></option><br><?php endforeach; endif; else: echo "" ;endif; ?>
              </select>
              </span>
              </div>
        </div>
        <div class="form-group">
              <div class="input-group-sm input-group">
                <label>内容类型：</label>
                <span class="input-group-btn input-group-sm">
                <select name="content_type" style="width: 20px" class="form-control bs-select class-filter" data-style="btn-success btn-sm" data-container="body">
	                <option value="10">全部</option>
	                <?php if(is_array($content_type_arr)): $i = 0; $__LIST__ = $content_type_arr;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>"
                      <?php if($key == $content_type): ?>selected<?php endif; ?>
	                  ><?php echo ($vo); ?></option><br><?php endforeach; endif; else: echo "" ;endif; ?>
              </select>
              </span>
              </div>
        </div>
         <div class="form-group">

          <div class="input-group input-group-sm date form_date" data-ymd="true" data-date="<?php echo ($timeinfo["now_time"]); ?>">
              <input style="margin-left: 6px;" name="begin_time" type="text" size="16" class="form-control date" placeholder="开始日期" value="<?php echo ($timeinfo["begin_time"]); ?>" readonly>
                  <span class="input-group-btn">
                    <button class="btn default date-reset" type="button"><i class="fa fa-times"></i></button>
                    <button class="btn btn-success date-set" type="button"><i class="fa fa-calendar"></i></button>
                  </span>
            </div>

            <div class="input-group input-group-sm date form_date" data-ymd="true" data-date="<?php echo ($timeinfo["now_time"]); ?>" style="width:135px;">

              <input style="width:73px;padding:0px;margin:0px;margin-left: 15px;"  name="end_time" type="text" size="16" class="form-control date" placeholder="结束日期" value="<?php echo ($timeinfo["end_time"]); ?>" readonly>
                  <span class="input-group-btn" style="padding:0px;margin:0px;" >
                    <button class="btn default date-reset  btn-sm" type="button"><i class="fa fa-times"></i></button>
                    <button class="btn btn-success date-set  btn-sm" type="button"><i class="fa fa-calendar"></i></button>
                  </span>
            </div>
      
      </div>
      
      
      <div class="form-inline" style="margin-top:3px;">
		
           
       
		
			
	<div class="form-group">
            <div class="input-group-sm input-group">
              <label style="margin-left: 3px;" class="col-xs-1 col-sm-1 control-label">
                查询筛选：
              </label>
                <span class="input-group-btn">
               <select name="type" style="width: 20px" class="form-control bs-select class-filter" data-style="btn-success btn-sm" data-container="body">
                <option value=10 >全部</option>
                <?php $_result=C('CONTENT_TYPE');if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>" <?php if($key == $ctype): ?>selected<?php endif; ?>><?php echo ($vo); ?></option><br><?php endforeach; endif; else: echo "" ;endif; ?>
              </select>
            </span>
            </div>
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


      <div class="form-inline" style="margin-top:30px;">
        <div class="tools-group s2">
          <a class="btn btn-success btn-sm add" href="<?php echo ($host_name); ?>/article/addpictures"  title="发布图集" target="dialog" mask="true">
            <i class="fa fa-plus"></i><span>发布图集</span></a>
          <a class="btn btn-success btn-sm add" href="<?php echo ($host_name); ?>/article/addarticle" title="发布文章" target="dialog" mask="true">
            <i class="fa fa-sign-in"></i><span>发布文章</span></a>
          <a class="btn btn-success btn-sm add" href="<?php echo ($host_name); ?>/article/addvideo"  title="发布视频" target="dialog" mask="true">
            <i class="fa fa-plus"></i><span>发布视频</span></a>
          <a class="btn btn-success btn-sm add" href="<?php echo ($host_name); ?>/article/homemanager" rel="article/homemanager"  title="首页内容管理" target="navTab" mask="true">
            <i class="fa fa-plus"></i><span>首页内容管理</span></a>



        </div>
      </div>




    </div>
  </form>
</div>
<div class="pageContent" id="pagecontent" style="margin-top:80px;">
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
              <td data-title="序号"><?php echo ($vlist["id"]); ?>
              <?php if($vlist["is_index"] == 1): ?><a data-tip="首页内容" class="btn btn-success btn-icon"><i class="fa fa-home"></i></a><?php endif; ?>
              </td>
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
              <td data-title="状态">

                <?php if($vlist['state'] == 2): ?>审核通过
                  <?php elseif($vlist['state'] == 3): ?>
                  审核未通过
                  <?php else: ?>
                  未审核<?php endif; ?>
              </td>

              <td class="table-tool" data-title="操作">
                <div class="tools-edit">
                  <a data-tip="修改" target="dialog" mask="true"
                  <?php if($vlist['type'] == 3): ?>href="<?php echo ($host_name); ?>/article/addvideo?id=<?php echo ($vlist["id"]); ?>&acttype=3"
                      <?php elseif($vlist['type'] == 2): ?>href="<?php echo ($host_name); ?>/article/editpictures?id=<?php echo ($vlist["id"]); ?>"
                    <?php else: ?>
                    href="<?php echo ($host_name); ?>/article/addarticle?id=<?php echo ($vlist["id"]); ?>&acttype=1"<?php endif; ?> class="btn btn-success btn-icon">
                    <i class="fa fa-pencil"></i>
                  </a>
                </div>

                <div class="tools-edit">
                  <a warn="警告" data-tip="删除" title="你确定要删除吗？" target="ajaxTodo" href="<?php echo ($host_name); ?>/article/delart?id=<?php echo ($vlist["id"]); ?>&acttype=1"  class="btn btn-success btn-icon">
                    <i class="fa fa-trash"></i>
                  </a>
                </div>

                <div class="tools-edit">
                  <a data-tip="预览"

                  href="/content/<?php echo ($vlist["id"]); ?>.html"


                     target="_blank"  class="btn btn-success btn-icon">
                    <i class="fa fa-eye"></i>
                  </a>
                  <a data-tip="添加至首页" target="ajaxTodo" calback="navTabAjaxDone" href="<?php echo ($host_name); ?>/article/doaddhome?artid=<?php echo ($vlist["id"]); ?>&acttype=1"    class="btn btn-success btn-icon">
                    <i class="fa fa-bullhorn"></i>
                  </a>
                  
                </div>
                <div class="tools-edit">
					
					<a id="ins_<?php echo ($vlist["id"]); ?>"  class="btn btn-success btn-icon cpicons"  data-clipboard-text='<?php echo ($vlist["pushdata"]); ?>'  title="复制推送内容" mask="true"  data-original-title="复制推送内容">
					
					<i  class="fa fa-copy"></i>
					</a>
				</div>
              </td>
            </tr><?php endforeach; endif; else: echo "" ;endif; ?>
          </tbody>
        </table>
      </form>
<div class="modal fade" tabindex="-1" role="dialog" id="myModal">
  <div class="modal-dialog" role="document">
    <div class="modal-content" style="width: 357px;height: 160px;">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">提示</h4>
      </div>
      <div class="modal-body">
        <p class="tishi">复制成功&hellip;</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">确定</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
    </div>
  </div>
  <?php echo ($page); ?>
</div>
<script src="Public/admin/assets/js/clipboard.min.js" type="text/javascript" charset="utf-8"></script>
  <script type="text/javascript">
        $(function(){
            $('select').css({ "cssText": "display:none !important" });
          $(".form-control.date").datetimepicker({
            minView: "month", //选择日期后，不会再跳转去选择时分秒
            language:  'zh-CN',
            format: 'yyyy-mm-dd',
            todayBtn:  1,
            autoclose: 1,
          });

          $('.date-reset').click(function(){
            $(this).parent().prev().val('')
          });
          
        });
        $('.tools-edit').on("click",".cpicons",function(){
        	var co;
        	 var ids = $(this).prop("id");
        	 var clipboard = new Clipboard('#'+ids);
            clipboard.on('success', function(e) {
            	//alert('复制成功');
            	$('#myModal').modal('show')
            	
               
            });

            clipboard.on('error', function(e) {
            	$('.tishi').html('复制失败');
            	$('#myModal').modal('show');
               
            }); 
						
        });
        
        /* var clipboard = '';
        function copydata(){
        	clipboard = new Clipboard('.cpicons');

            clipboard.on('success', function(e) {
                alert('复制成功');
                return true;
            });

            clipboard.on('error', function(e) {
                alert("复制失败");
                return false;
            });
        } */
        
        
</script>