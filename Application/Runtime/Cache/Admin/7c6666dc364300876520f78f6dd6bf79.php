<?php if (!defined('THINK_PATH')) exit();?><!--修改样式2 p元素自适应宽度 start-->
<form onsubmit="return navTabSearch(this);" id="pagerForm" action="<?php echo ($host_name); ?>/menu/selecthotel" method="post" >
<div class="pageHeader">

    <button class="btn btn-success btn-sm add" type="submit" id="choosedata"><i class="fa">新加酒楼</i></button>

  <!-- <a  target="navTab" title="新加酒楼" rel="menu/getlist" href="<?php echo ($host_name); ?>/menu/selecthotel?addhotel2&menuname=<?php echo ($menuname); ?>&menuid=<?php echo ($menuid); ?>" class="btn btn-success btn-icon">
    <i class="fa fa-bullhorn"></i>
  </a> -->

</div>
<div class="pageContent">
  <input type="hidden" name="addhotel" value="2">
    <input type="hidden" name="menuname" value="<?php echo ($menuname); ?>">
    <input type="hidden" name="menuid" value="<?php echo ($menuid); ?>">
    <div id="w_list_print">
    <div class="no-more-tables">

        <table class="table table-bordered table-striped" targetType="navTab" asc="asc" desc="desc">
          <thead>
          <tr id="post">
            <th>酒店名称</th>
            <th>发布时间</th>

          </tr>
          </thead>
          <tbody data-check="list" data-parent=".table">
          <?php if(is_array($vinfo)): $i = 0; $__LIST__ = $vinfo;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr target="sid_user">
              <input type="hidden" value="<?php echo ($vo["hoid"]); ?>" name="inf[]" />
              <td data-title="酒店名称"><?php echo ($vo["honame"]); ?></td>
              <td data-title="预约发布时间"><?php echo ($vo["pub_time"]); ?></td>
            </tr><?php endforeach; endif; else: echo "" ;endif; ?>
          </tbody>
        </table>

      </div>

    <div class="modal-footer">

    </div>

</div>

  </div></form>
<script>
 /* function navTabAjaxDone(json){
    DWZ.ajaxDone(json);
    navTab.reloadFlag('menu/getlist');
    //注意返回的JSON的数据结构
    if (json.status == DWZ.statusCode.ok){
      if (json.navTabId){
        navTab.reload('menu/getlist');
      }


    }

  }*/
</script>