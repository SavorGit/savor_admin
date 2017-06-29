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
  <form onsubmit="return navTabSearch(this);" id="pagerForm" action="<?php echo ($host_name); ?>/tag/articleTagList" method="post" >
    <input type="hidden" name="pageNum" value="<?php echo ($pageNum); ?>"/>
    <input type="hidden" name="numPerPage" value="<?php echo ($numPerPage); ?>"/>
    <input type="hidden" name="_order" value="<?php echo ($_order); ?>"/>
    <input type="hidden" name="_sort" value="<?php echo ($_sort); ?>"/>
      <input type="hidden" name="tagid" value="<?php echo ($taglistid); ?>"/>
      <input type="hidden" name="tagname" value="{tagarticlename}"/>
      <div class="searchBar">


          <lable class="btn btn-success btn-sm add" href="#" title="<?php echo ($tagarticlename); ?>"  mask="true"><i class="fa "></i><?php echo ($tagarticlename); ?></lable>

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