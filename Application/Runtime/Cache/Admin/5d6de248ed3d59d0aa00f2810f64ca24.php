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
  <form onsubmit="return navTabSearch(this);" id="pagerForm" action="<?php echo ($host_name); ?>/downloadrp/rplist" method="post" >
    <input type="hidden" name="pageNum" value="<?php echo ($pageNum); ?>"/>
    <input type="hidden" name="numPerPage" value="<?php echo ($numPerPage); ?>"/>
    <input type="hidden" name="_order" value="<?php echo ($_order); ?>"/>
    <input type="hidden" name="_sort" value="<?php echo ($_sort); ?>"/>
    <div class="searchBar">
      <div class="clearfix">
        <div class="form-inline" style="margin-top:3px;">
        <div class="form-group">
          <div class="input-group input-group-sm">
            <label style="margin-left: 3px;" class="col-xs-1 col-sm-1 control-label">
              来源筛选：
            </label>
                <span class="input-group-btn">
                 <select name="source_type" style="width: 20px" class="form-control bs-select class-filter" data-style="btn-success btn-sm" data-container="body">
                   <option value="0">全部</option>
                   <?php if(is_array($sce_type)): $i = 0; $__LIST__ = $sce_type;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>"
                     <?php if($key == $sot): ?>selected<?php endif; ?>
                     ><?php echo ($vo); ?></option><br><?php endforeach; endif; else: echo "" ;endif; ?>
                 </select></span>
          </div>
        </div>


          <div class="form-group" id="timegiy" style="">
            <div class="input-group input-group-sm date form_datetime" data-date="<?php echo ($vinfo["log_time"]); ?>" data-ymd="true">
              <input name="starttime" type="text" size="14" class="form-control date" placeholder="开始日期" value="<?php echo ($s_time); ?>" readonly>
                        <span class="input-group-btn" style="padding:0px;margin:0px;" >
                    <button class="btn default date-reset  btn-sm" type="button"><i class="fa fa-times"></i></button>
                    <button  class="btn btn-success date-set  btn-sm" type="submit"><i class="fa fa-calendar"></i></button>
                  </span>
            </div>


            <div class="input-group input-group-sm date form_datetime" data-date="<?php echo ($vinfo["log_time"]); ?>" data-ymd="true">
              <input name="endtime" type="text" size="14" class="form-control date" placeholder="结束日期" value="<?php echo ($e_time); ?>" readonly>
                     <span class="input-group-btn" style="padding:0px;margin:0px;" >
                    <button class="btn default date-reset  btn-sm" type="button"><i class="fa fa-times"></i></button>
                    <button class="btn btn-success date-set  btn-sm" type="button"><i class="fa fa-calendar"></i></button>
                  </span>
            </div>
          </div>



          <div class="input-group">
            <span class="input-group-btn">
              <button class="btn btn-primary" type="submit" id="choosedata"><i class="fa fa-search"></i></button>
            </span>
          </div>

          <div class="input-group input-group-sm pull-right">
            <a class="btn btn-success btn-sm add" href="<?php echo ($host_name); ?>/excel/expdownloadcount?sourcetype=<?php echo ($sot); ?>&start=<?php echo ($s_time); ?>&end=<?php echo ($e_time); ?>" title="导出下载点播次数统计" target="_blank" mask="true"><i class="fa fa-plus"></i> 导出下载点播次数统计</a>
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
      <form method="post" action="#" id="del-form" class="pageForm required-validate" rel="second"
enctype="multipart/form-data" onsubmit="return iframeCallback(this, dialogAjaxDoneER)">
        <table class="table table-bordered table-striped" targetType="navTab" asc="asc" desc="desc">
          <thead>
          <tr id="post">
            <th>序号</th>
            <!-- <th>机顶盒ID</th> -->
            <th>来源</th>
            <th>手机客户端</th>
            <!-- <th>包间ID</th> -->
            <th>设备唯一标识</th>
            <!-- <th>酒楼ID</th> -->
            <th>点击下载设备</th>
            <!-- <th>区域ID</th> -->
            <th>酒楼名称</th>
            <th>酒楼id</th>
            <th>服务员id</th>
            <th>添加时间</th>
          </tr>
          </thead>
          <tbody data-check="list" data-parent=".table">
          <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vlist): $mod = ($i % 2 );++$i;?><tr target="sid_user">
              <td data-title="序号"><?php echo ($vlist["indnum"]); ?></td>
              <!-- <td data-title="机顶盒ID"><?php echo ($vlist["box_id"]); ?></td> -->
              <td data-title="来源"><?php if(is_array($sce_type)): $i = 0; $__LIST__ = $sce_type;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($key == $vlist['source_type']): echo ($vo); endif; endforeach; endif; else: echo "" ;endif; ?></td>
              <td data-title="手机客户端">
                <?php if($vlist['clientid'] == 1): ?>android<?php endif; ?>
                <?php if($vlist['clientid'] == 2): ?>ios<?php endif; ?>
                </td>
              <!-- <td data-title="包间ID"><?php echo ($vlist["room_id"]); ?></td> -->
              <td data-title="设备唯一标识">
                <?php echo ($vlist["deviceid"]); ?>
               </td>
              <!-- <td data-title="酒楼ID"><?php echo ($vlist["hotel_id"]); ?></td> -->
              <td data-title="点击下载设备">

                <?php if($vlist['dowload_device_id'] == 1): ?>android<?php endif; ?>
                <?php if($vlist['dowload_device_id'] == 2): ?>ios<?php endif; ?>
                <?php if($vlist['dowload_device_id'] == 3): ?>pc<?php endif; ?>
               </td>
              <!-- <td data-title="区域ID"><?php echo ($vlist["area_id"]); ?></td> -->
              <td data-title="酒楼名称"><?php echo ($vlist["hotelname"]); ?></td>
              <td data-title="酒楼id"><?php echo ($vlist["hotelid"]); ?></td>
              <td data-title="服务员id"><?php echo ($vlist["waiterid"]); ?></td>
            <td data-title="添加时间"><?php echo ($vlist["add_time"]); ?> </td>
            </tr><?php endforeach; endif; else: echo "" ;endif; ?>
          </tbody>
        </table>
      </form>

    </div>
  </div>
  <?php echo ($page); ?>
</div>

<script type="text/javascript">


  /*function navTabAjaxDone(json){
    // alert(json);
    DWZ.ajaxDone(json);
    alert(33333);
    jap = JSON.stringify(json);
    console.log(jap);
    var idsf = $(".header form").attr('id');
    alert(idsf);
    DWZ.ajaxDone(json);
    navTab.reloadFlag('menu/getlist');
    //注意返回的JSON的数据结构
    if (json.status == DWZ.statusCode.ok){
      if (json.navTabId){
        navTab.reload('menu/getlist');
      }


    }

  }*/



  function get_date(obj){
    var data_type = $("#dty").val();
    if(data_type == 4){
      $("#timegi").css('display','inline-block');
    }else{
      $("#timegi").css('display','none');
    }
  }

  $(function(){
    var tip =  $("#dty").val();
    if(tip == 4){
      $("#timegi").css('display','inline-block');
    }
    /*$("#dty").click(function(){

      var data_type = $(this).val();
      alert(data_type);
      alert('bbb');
      if(data_type == 4){
        $("#timegi").css('display','inline-block');
      }else{
        $("#timegi").css('display','none');
      }
    });*/
  })
</script>