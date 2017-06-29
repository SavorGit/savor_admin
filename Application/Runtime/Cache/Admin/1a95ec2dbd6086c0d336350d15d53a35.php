<?php if (!defined('THINK_PATH')) exit();?><script>  
    if(!window.jQuery){
      var path = window.location.pathname;
      path = path.replace("/admin/","");
      console.log(path);
      window.location.href = "<?php echo ($host_name); ?>#" + path;
    }
</script>

<style type="text/css">
  .xuan{
    width: 120px;
    height: 30px;
    border: 1px solid black;
    margin: 20px;
    text-align: center;
    line-height: 30px;
    float: left;
  }
  .marg{
    margin-left: 5px;
  }
  .active{
    background-color: #bababa;
  }
  .mod_top{
    width: 100%;
  }
  .nr{
    width: 100%;
    height: 300px;
  }
</style>
<!--显示列表样式1 start-->
<div class="pageHeader">
  <form onsubmit="return navTabSearch(this);" id="pagerForm" action="<?php echo ($host_name); ?>/tag/rplist" method="post" >
    <input type="hidden" name="pageNum" value="<?php echo ($pageNum); ?>"/>
    <input type="hidden" name="numPerPage" value="<?php echo ($numPerPage); ?>"/>
    <input type="hidden" name="_order" value="<?php echo ($_order); ?>"/>
    <input type="hidden" name="_sort" value="<?php echo ($_sort); ?>"/>
    <div class="searchBar">




      <a class="btn btn-success btn-sm add" href="<?php echo ($host_name); ?>/tag/addtag" title="添加标签" target="dialog" mask="true"><i class="fa fa-plus"></i>添加标签</a>

      <input type="text" style="margin-left: 100px;height: 28px;" name="tagname"/>
        			<span class="input-group-btn" style="display:inline-block;">
              <button class="btn btn-primary" style="height: 26px;line-height: 2px; background-color: #aed316;" type="submit"><i class="fa fa-search"></i></button>
            </span>
      <!--<button type="button" class="btn btn-primary del_active" style="margin-left: 96px; background-color: #aed316;height: 25px;line-height: 12px;">选中删除</button>-->

    </div>
  </form>
</div>
<div class="pageContent" id="pagecontent">
  <div id="w_list_print">
    <div class="quanbu">
      <?php if(is_array($datalist)): $i = 0; $__LIST__ = $datalist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vlist): $mod = ($i % 2 );++$i;?><div class="xuan">


          <a  target="navTab" title="标签文章列表" rel="tag/articleTagList" href="<?php echo ($host_name); ?>/tag/articleTagList?tagid=<?php echo ($vlist["id"]); ?>&tagname=<?php echo ($vlist["tagname"]); ?>">
            <?php echo ($vlist["tagname"]); ?> <i style="display: none;" class="fa fa-bullhorn"></i>
          </a>


          <a warn="警告" data-tip="删除" title="你确定要删除吗？" target="ajaxTodo" href="<?php echo ($host_name); ?>/tag/deltag?tagid=<?php echo ($vlist["id"]); ?>">
          <i class="fa fa-trash"></i>
        </a>
        </div><?php endforeach; endif; else: echo "" ;endif; ?>
    </div></div> <?php echo ($page); ?></div>