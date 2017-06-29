<?php if (!defined('THINK_PATH')) exit();?><script>  
    if(!window.jQuery){
      var path = window.location.pathname;
      path = path.replace("/admin/","");
      console.log(path);
      window.location.href = "<?php echo ($host_name); ?>#" + path;
    }
</script>

<!--显示列表样式1 start-->
<style type="text/css">
  .searchBar label{
    width: auto;
    text-align: center;
    margin-top: 15px;
    line-height: 2px;
  }
</style>
<div class="pageHeader">
  <form onsubmit="return navTabSearch(this);" id="pagerForm" action="<?php echo ($host_name); ?>/hotel/manager" method="post" >
    <input type="hidden" name="pageNum" value="<?php echo ($pageNum); ?>"/>
    <input type="hidden" name="numPerPage" value="<?php echo ($numPerPage); ?>"/>
    <input type="hidden" name="_order" value="<?php echo ($_order); ?>"/>
    <input type="hidden" name="_sort" value="<?php echo ($_sort); ?>"/>
    <div class="searchBar">
      <div class="clearfix">
      </div>
      <div class="form-inline" style="margin-top:3px;">
        <div class="form-group">

          <div class="form-group">
            <div class="input-group-sm input-group">
              <label class="col-xs-1 col-sm-1 control-label">
                城&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;市：
              </label>

                <span class="input-group-btn input-group-sm">
              <select name="area_v" style="width: 20px" class="form-control bs-select class-filter" data-style="btn-success btn-sm" data-container="body" size="15">
                <option value=0 >所有城市</option>
                <?php if(is_array($area)): $i = 0; $__LIST__ = $area;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>" <?php if($vo["id"] == $area_k): ?>selected<?php endif; ?>><?php echo ($vo["region_name"]); ?></option><br><?php endforeach; endif; else: echo "" ;endif; ?>
              </select>
            </span>
            </div>
          </div>

          <div class="form-group">
            <div class="input-group-sm input-group">
              <label>级别：</label>
                <span class="input-group-btn input-group-sm">
              <select name="level_v" style="width: 20px" class="form-control bs-select class-filter" data-style="btn-success btn-sm" data-container="body">
                <option value=0 >全部</option>
                <?php $_result=C('HOTEL_LEVEL');if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>" <?php if($key == $level_k): ?>selected<?php endif; ?>><?php echo ($vo); ?></option><br><?php endforeach; endif; else: echo "" ;endif; ?>
              </select>
            </span>
            </div>
          </div>
          <div class="form-group">
            <div class="input-group-sm input-group">
              <label>
                状态：
              </label>
               <span class="input-group-btn input-group-sm">
              <select name="state_v" style="width: 20px" class="form-control bs-select class-filter" data-style="btn-success btn-sm" data-container="body">
                <option value=0 >全部</option>
                <?php $_result=C('HOTEL_STATE');if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>" <?php if($key == $state_k): ?>selected<?php endif; ?>><?php echo ($vo); ?></option><br><?php endforeach; endif; else: echo "" ;endif; ?>
              </select>
            </span>
            </div>
          </div>

         
          <div class="form-group">
            <div class="input-group-sm input-group">
              <label class="control-label" style="">
                合作维护人：
              </label>
                <span class="input-group-btn input-group-sm">
                <input type="text" class="form-control" name="main_v" value="<?php echo ($main_k); ?>" placeholder="合作维护人" style="width:95px;">
            </span>
            </div>
          </div>
		</div>
		<div class="form-inline" style="margin-top:3px;">

          <div class="form-group">
            <div class="input-group-sm input-group">
              <label>
                机顶盒类型：
              </label>
               <span class="input-group-btn input-group-sm">
              <select name="hbt_v" style="width: 20px" class="form-control bs-select class-filter" data-style="btn-success btn-sm" data-container="body">
                <option value=0 >全部</option>
                <?php $_result=C('hotel_box_type');if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>" <?php if($key == $hbt_k): ?>selected<?php endif; ?>><?php echo ($vo); ?></option><br><?php endforeach; endif; else: echo "" ;endif; ?>
              </select>
            </span>
            </div>
          </div>
			<div class="form-group">
          <div class="input-group-sm input-group">
            <label class="col-xs-1 col-sm-1 control-label">
              重点：
            </label>
                <span class="input-group-btn">
              <select name="key_v" class="form-control bs-select class-filter" data-style="btn-success btn-sm" data-container="body">
                <option value=0 >全部</option>
                <?php $_result=C('HOTEL_KEY');if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>" <?php if($key == $key_k): ?>selected<?php endif; ?>><?php echo ($vo); ?></option><br><?php endforeach; endif; else: echo "" ;endif; ?>
              </select>
            </span>
          </div>
        </div>

        <div class="input-group input-group-sm date form_date" data-ymd="true" data-date="<?php echo ($vinfo["log_time"]); ?>">
          <input style="margin-left: 6px;" name="starttime" type="text" size="16" class="form-control date" placeholder="开始日期" value="<?php echo ($vinfo["log_time"]); ?>" readonly>
                  <span class="input-group-btn">
                    <button class="btn default date-reset  btn-sm" type="button"><i class="fa fa-times"></i></button>
                    <button class="btn btn-success date-set  btn-sm" type="button"><i class="fa fa-calendar"></i></button>
                  </span>
        </div>

        <div class="input-group input-group-sm date form_date" data-ymd="true" data-date="<?php echo ($vinfo["log_time"]); ?>" style="width:135px;">

          <input style="width:73px;padding:0px;margin:0px;margin-left: 15px;"  name="endtime" type="text" size="16" class="form-control date" placeholder="结束日期" value="<?php echo ($vinfo["log_time"]); ?>" readonly>
                  <span class="input-group-btn" style="padding:0px;margin:0px;" >
                    <button class="btn default date-reset  btn-sm" type="button"><i class="fa fa-times"></i></button>
                    <button class="btn btn-success date-set  btn-sm" type="button"><i class="fa fa-calendar"></i></button>
                  </span>
        </div>
        </div>
      </div>

      <div class="form-inline" style="margin-top:3px;">

         <div class="form-group">
            <div class="input-group-sm input-group">
              <label>
                包含酒楼：
              </label>
                <span class="input-group-btn input-group-sm">
              <select id="inch" name="include_v[]" style="width: 20px" class="form-control bs-select class-filter" data-style="btn-success btn-sm" data-container="body" multiple="multiple" >
                <?php if(is_array($include)): $i = 0; $__LIST__ = $include;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"
                  <?php if(is_array($include_k)): $p = 0; $__LIST__ = $include_k;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$kvo): $mod = ($p % 2 );++$p; if($kvo["p"] == $vo.id): ?>selected<?php endif; endforeach; endif; else: echo "" ;endif; ?>
                  ><?php echo ($vo["menu_name"]); ?>
                  </option><br><?php endforeach; endif; else: echo "" ;endif; ?>
              </select>
            </span>
            </div>
          </div>

        <div class="form-group">
          <div class="input-group-sm input-group">
            <label style="margin-left: 3px;" class="col-xs-1 col-sm-1 control-label">
              排除酒楼：
            </label>
                <span class="input-group-btn">
              <select id="exch" name="exc_v[]" style="width: 20px" class="form-control bs-select class-filter" data-style="btn-success btn-sm" data-container="body" multiple="multiple" >
                <?php if(is_array($include)): $i = 0; $__LIST__ = $include;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>" <?php if($vo["id"] == $exc_k): ?>selected<?php endif; ?>><?php echo ($vo["menu_name"]); ?></option><br><?php endforeach; endif; else: echo "" ;endif; ?>
              </select>
            </span>
          </div>
        </div>

        <div class="input-group input-group-sm">
          <input type="text" class="form-control" name="name" value="<?php echo ($name); ?>" placeholder="酒店名称">
            <span class="input-group-btn">
              <button class="btn btn-primary" type="submit" id="choosedata"><i class="fa fa-search"></i></button>
            </span>
        </div>
        <div class="input-group input-group-sm pull-right">
          <a class="btn btn-success btn-sm add" href="<?php echo ($host_name); ?>/hotel/add?acttype=0" title="新增酒店" target="dialog" mask="true"><i class="fa fa-plus"></i> 新增酒店</a>
          <a class="btn btn-success btn-sm add" href="<?php echo ($host_name); ?>/excel/hotelinfo" title="导出资源总表" target="_blank" mask="true"><i class="fa fa-plus"></i> 导出酒楼资源总表</a>
        </div>
      </div>
    </div>
  </form>
</div>
<div class="pageContent" id="pagecontent" style="top:80px;margin-top:20px;">
  <div id="w_list_print">
    <div class="no-more-tables">
      <form method="post" action="#" id="del-form" class="pageForm required-validate" enctype="multipart/form-data" onsubmit="return iframeCallback(this, dialogAjaxDone)">
        <table class="table table-bordered table-striped" targetType="navTab" asc="asc" desc="desc">
          <thead>
          <tr id="post">
            <!-- <th class="table-checkbox">
              <input type="checkbox" data-check="all" data-parent=".table" />
            </th> -->
            <th>ID</th>
            <th>酒店名称</th>
            <th>地址</th>
            <th>联系人</th>
            <th>联系电话</th>
            <th>状态</th>
            <th>合作维护人</th>
            <th>技术运维人</th>
            <th>包间数量</th>
            <th>机顶盒数量</th>
            <th>电视数量</th>
            <th>节目单</th>
            <th class="table-tool">操作</th>
          </tr>
          </thead>
          <tbody data-check="list" data-parent=".table">
          <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vlist): $mod = ($i % 2 );++$i;?><tr target="sid_user">
              <!-- <td class="table-checkbox">
                <input type="checkbox" class="checkboxes" value="30" name="postlist[]">
              </td> -->
              <td data-title="酒店ID"><?php echo ($vlist["id"]); ?></td>
              <td data-title="酒店名称"><?php echo ($vlist["name"]); ?></td>
              <td data-title="地址"><?php echo ($vlist["addr"]); ?></td>
              <td data-title="联系人"><?php echo ($vlist["contractor"]); ?></td>
              <td data-title="联系电话"><?php echo ($vlist["mobile"]); ?></td>
              <td data-title="状态">
                <?php if($vlist["state"] == 1): ?>正常
                  <?php elseif($vlist["state"] == 2): ?> 冻结
                  <?php else: ?>报损<?php endif; ?>
              </td>
              <td data-title="合作维护人"><?php echo ($vlist["maintainer"]); ?></td>
              <td data-title="技术运维人"><?php echo ($vlist["tech_maintainer"]); ?></td>
              <td data-title="包间数量"><a title="<?php echo ($vlist["name"]); ?>包间列表" target="navTab" rel="hotel/room" href="<?php echo ($host_name); ?>/hotel/room?hotel_id=<?php echo ($vlist["id"]); ?>"><?php echo ($vlist["room_num"]); ?></a></td>
              <td data-title="机顶盒数量">
                <?php if($vlist["box_num"] > 0): ?><a title="<?php echo ($vlist["name"]); ?>机顶盒列表" target="navTab" rel="device/box" href="<?php echo ($host_name); ?>/device/box?hotel_id=<?php echo ($vlist["id"]); ?>"><?php echo ($vlist["box_num"]); ?></a>
                  <?php else: ?>
                  <?php echo ($vlist["box_num"]); endif; ?>
              </td>
              <td data-title="电视数量">
                <?php if($vlist["tv_num"] > 0): ?><a title="<?php echo ($vlist["name"]); ?>电视列表" target="navTab" rel="device/tv" href="<?php echo ($host_name); ?>/device/tv?hotel_id=<?php echo ($vlist["id"]); ?>"><?php echo ($vlist["tv_num"]); ?></a>
                  <?php else: ?>
                  <?php echo ($vlist["tv_num"]); endif; ?>
              </td>
              <td data-title="节目单"> <?php if($vlist["menu_id"] > 0): ?><a title="<?php echo ($vlist["name"]); ?>节目单列表" target="dialog" rel="device/tv" href="<?php echo ($host_name); ?>/menu/getdetail?id=<?php echo ($vlist["menu_id"]); ?>&name=<?php echo ($vlist["menu_name"]); ?>"><?php echo ($vlist["menu_name"]); ?></a><?php else: ?>无<?php endif; ?></td>
              <td class="table-tool" data-title="操作">
                <div class="tools-edit">
                  <a data-tip="查看详情" target="navTab" mask="true" href="<?php echo ($host_name); ?>/hotel/getdetail?id=<?php echo ($vlist["id"]); ?>&acttype=1" rel="hotel/detail" title="<?php echo ($vlist["name"]); ?>酒楼详情"class="btn btn-success btn-icon">
                    <i class="fa fa-search-minus"></i>
                  </a>
                  <a data-tip="修改详情" target="dialog" data-placement="left" mask="true" rel="xiugaihotel" href="<?php echo ($host_name); ?>/hotel/add?id=<?php echo ($vlist["id"]); ?>&acttype=1" class="btn btn-success btn-icon">

                    <i class="fa fa-tag"></i>
                  </a>
                  <a data-tip="新增包间" target="dialog" mask="true" href="<?php echo ($host_name); ?>/hotel/addRoom?hotel_id=<?php echo ($vlist["id"]); ?>&acttype=1" class="btn btn-success btn-icon">
                    <i class="fa fa-plus"></i>
                  </a>
                  <a data-tip="宣传片管理" target="navTab" title="宣传片管理列表" rel="hotel/pubmanager" href="<?php echo ($host_name); ?>/hotel/pubmanager?hotel_id=<?php echo ($vlist["id"]); ?>" class="btn btn-success btn-icon">
                    <i class="fa fa-bullhorn"></i>
                  </a>

                  <a data-tip="批量新增版位" data-placement="left"
                     target="navTab" title="批量新增版位" rel="ceshipilianxin"  href="<?php echo ($host_name); ?>/hotel/batchposition?hotel_id=<?php echo ($vlist["id"]); ?>&name=<?php echo ($vlist["name"]); ?>" class="btn btn-success btn-icon">
                    <i class="fa fa-bullhorn"></i>
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

<script type="text/javascript">

  $('.date-reset').click(function(){
    $(this).parent().prev().val('')
  });


  $(function(){
    $("#exch").change(function(){
      var valu = $(this).val();
      if (valu == null) {
        $('#inch').attr("disabled",false);
      } else {
        $('#inch').attr("disabled","disabled");
      }

    });

    $("#inch").change(function(){

      var valu = $(this).val();
      if (valu == null) {
        $('#exch').attr("disabled",false);
      } else {
        $('#exch').attr("disabled","disabled");
      }

    });

    $(".form-control.date").datetimepicker({
      minView: "month", //选择日期后，不会再跳转去选择时分秒
      language:  'zh-CN',
      format: 'yyyy-mm-dd',
      todayBtn:  1,
      autoclose: 1,
    });
  })
</script>