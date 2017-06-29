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
  <form class="countform" onsubmit="return navTabSearch(this);" class="pagefbai" id="pagerForm" action="<?php echo ($host_name); ?>/testtuireport/rplist" method="post" >
    <input type="hidden" name="hostname" value="<?php echo ($host_name); ?>"/>
    <div class="tabsContent">
      <ul class="nav nav-tabs" id="getmy">
        <li  class="box active"><a href="#tab1" data-toggle="tab"><span>APP包间首次互动数据(互动)</span></a></li>
       <!--  <li class="box" ><a href="#tab2" data-toggle="tab"><span>酒楼首次打开APP数据(下载)</span></a></li> -->
      </ul>
      <div class="tab-content">
        <div id="tab1" class="tab-pane active fade in">

          <input type="hidden" name="pageNum" value="<?php echo ($pageNum); ?>"/>
          <input type="hidden" name="numPerPage" value="<?php echo ($numPerPage); ?>"/>
          <input type="hidden" name="_order" value="<?php echo ($_order); ?>"/>
          <input type="hidden" name="_sort" value="<?php echo ($_sort); ?>"/>
          <div class="form-inline" style="margin-top:3px;">
            <div class="form-group">

              <label style="margin-left: 3px;width:50px;" class="col-xs-1 col-sm-1 control-label col-md-2">
                日期：
              </label>

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


            <div class="form-group">
              <div class="input-group input-group-sm pull-right">
                <input type="text" class="form-control" name="hotelname" value="<?php echo ($hotelname); ?>" placeholder="酒楼名称">
            <span class="input-group-btn">
              <button class="btn btn-primary" type="submit" id="choosedata"><i class="fa fa-search"></i></button>
            </span>
              </div>
            </div>

            <div class="input-group input-group-sm pull-right">
              <a class="btn btn-success btn-sm add" href="<?php echo ($host_name); ?>/excel/expint_final?datetype=<?php echo ($dtype); ?>&start=<?php echo ($s_time); ?>&end=<?php echo ($e_time); ?>&hname=<?php echo ($hotelname); ?>" title="APP包间首次互动数据" target="_blank" mask="true"><i class="fa fa-plus"></i> 导出APP包间首次互动数据</a>
            </div>


          </div>

        </div>
        <div id="tab2" class="tab-pane fade">
          <input type="hidden" name="pageNumfmd" value="<?php echo ($pageNumfmd); ?>"/>
          <input type="hidden" name="numPerPagefmd" value="<?php echo ($numPerPagefmd); ?>"/>
          <input type="hidden" name="_orderfmd" value="<?php echo ($_orderfmd); ?>"/>
          <input type="hidden" name="_sortfmd" value="<?php echo ($_sortfmd); ?>"/>
          <div class="form-inline" style="margin-top:3px;">
            <div class="form-group">

              <label style="margin-left: 3px;width:50px;" class="col-xs-1 col-sm-1 control-label col-md-2">
                日期：
              </label>

            </div>
            <div class="form-group" id="timegis" style="">
              <div class="input-group input-group-sm date form_datetime" data-date="<?php echo ($vinfo["log_time"]); ?>" data-ymd="true">
                <input name="starttimefmd" type="text" size="14" class="form-control date" placeholder="开始日期" value="<?php echo ($s_timefmd); ?>" readonly>
                        <span class="input-group-btn" style="padding:0px;margin:0px;" >
                    <button class="btn default date-reset  btn-sm" type="button"><i class="fa fa-times"></i></button>
                    <button  class="btn btn-success date-set  btn-sm" type="submit"><i class="fa fa-calendar"></i></button>
                  </span>
              </div>


              <div class="input-group input-group-sm date form_datetime" data-date="<?php echo ($vinfo["log_time"]); ?>" data-ymd="true">
                <input name="endtimefmd" type="text" size="14" class="form-control date" placeholder="结束日期" value="<?php echo ($e_timefmd); ?>" readonly>
                     <span class="input-group-btn" style="padding:0px;margin:0px;" >
                    <button class="btn default date-reset  btn-sm" type="button"><i class="fa fa-times"></i></button>
                    <button class="btn btn-success date-set  btn-sm" type="button"><i class="fa fa-calendar"></i></button>
                  </span>
              </div>
            </div>


            <div class="form-group">
              <div class="input-group input-group-sm pull-right">
                <input type="text" class="form-control" name="hotelnamefmd" value="<?php echo ($hotelnamefmd); ?>" placeholder="酒楼名称">
            <span class="input-group-btn">
              <button class="btn btn-primary" type="submit" id="choosedata"><i class="fa fa-search"></i></button>
            </span>
              </div>
            </div>

            <div class="input-group input-group-sm pull-right">
              <a class="btn btn-success btn-sm add" href="<?php echo ($host_name); ?>/excel/expfirstmd?datetype=<?php echo ($dtype); ?>&start=<?php echo ($s_timefmd); ?>&end=<?php echo ($e_timefmd); ?>&hname=<?php echo ($hotelnamefmd); ?>" title="酒楼首次打开数据" target="_blank" mask="true"><i class="fa fa-plus"></i> 导出酒楼首次打开数据</a>
            </div>


          </div>
        </div>
        <input type="hidden" id="dtype" name="dtyp" value="<?php echo ($dtype); ?>" />
      </div>

    </div>
  </form>
</div>
</div>
<div class="pageContent" id="pagecontent1" style="margin-top:100px;">
  <div id="w_list_print">
    <div class="no-more-tables">
      <form method="post" action="#" id="del-form" class="pageForm required-validate" enctype="multipart/form-data" onsubmit="return iframeCallback(this, dialogAjaxDone)">
        <table class="table table-bordered table-striped" targetType="navTab" asc="asc" desc="desc">
          <thead>
          <tr id="post">
            <th>序号</th>
            <th>酒楼名称</th>
            <th>机顶盒名称</th>
            <th>首次连接（次）</th>
            <th>时间</th>
          </tr>
          </thead>
          <tbody data-check="list" data-parent=".table">
          <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vlist): $mod = ($i % 2 );++$i;?><tr target="sid_user">
              <td data-title="序号"><?php echo ($vlist["indnum"]); ?></td>
              <td data-title="酒楼名称"><?php echo ($vlist["hotel_name"]); ?></td>
              <td data-title="机顶盒名称"><?php echo ($vlist["box_name"]); ?></td>
              <td data-title="首次连接（次）"><?php echo ($vlist["count"]); ?></td><!-- <td data-title="分区"><?php echo ($vlist["pt"]); ?></td> -->
              <td data-title="时间"><?php echo ($vlist["date_time"]); ?> </td>
            </tr><?php endforeach; endif; else: echo "" ;endif; ?>
          </tbody>
        </table>
      </form>

    </div>
  </div>
 <?php echo ($page); ?>

</div>


<div class="pageContent" id="pagecontent2" style="display: none;margin-top:100px;">
  <div id="w_list_print">
    <div class="no-more-tables">
      <form method="post" action="#" id="del-form" class="pageForm required-validate" enctype="multipart/form-data" onsubmit="return iframeCallback(this, dialogAjaxDone)">
        <table class="table table-bordered table-striped" targetType="navTab" asc="asc" desc="desc">
          <thead>
          <tr id="post">
            <th>序号</th>
            <th>酒楼名称</th>
            <!-- <th>机顶盒数量</th> -->
            <th>首次打开（次）</th>
            <!-- <th>平均下载量</th> -->
            <th>时间</th>
          </tr>
          </thead>
          <tbody data-check="list" data-parent=".table">
          <?php if(is_array($listfmd)): $i = 0; $__LIST__ = $listfmd;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vlist): $mod = ($i % 2 );++$i;?><tr target="sid_user">
              <td data-title="序号"><?php echo ($vlist["indnum"]); ?></td>
              <td data-title="酒楼名称"><?php echo ($vlist["hotel_name"]); ?></td>
              <!-- <td data-title="机顶盒数量"><?php echo ($vlist["bct"]); ?></td> -->
              <td data-title="首次打开（次）"><?php echo ($vlist["dct"]); ?></td><!-- <td data-title="分区"><?php echo ($vlist["pt"]); ?></td> -->
              <!-- <td data-title="平均下载量"><?php echo ($vlist["ave"]); ?></td> -->
              <td data-title="时间"><?php echo ($vlist["time"]); ?> </td>
            </tr><?php endforeach; endif; else: echo "" ;endif; ?>
          </tbody>
        </table>
      </form>

    </div>
  </div>
  <?php echo ($pagefmd); ?>
</div>

<script type="text/javascript">

  /* function navTabAjaxDoneab(json){

    DWZ.ajaxDone(json);
    //注意返回的JSON的数据结构
    if (json.statusCode == DWZ.statusCode.ok){
      if (json.navTabId){
        //把指定navTab页面标记为需要“重新载入”。注意navTabId不能是当前navTab页面的
        navTab.reloadFlag(json.navTabId);

      } else {
        //重新载入当前navTab页面
        navTabPageBreak();

      }
      if ("closeCurrent" == json.callbackType) {

        setTimeout(function(){navTab.closeCurrentTab();}, 100);

      } else if ("forward" == json.callbackType) {

        navTab.reload(json.forwardUrl);

      }

    }

  }

  function navTabSearch(form, navTabId){
    console.log(form);
    console.log(navTabId);
    var $form = $(form);
    if (form[DWZ.pageInfo.pageNum]) form[DWZ.pageInfo.pageNum].value = 1;
    navTab.reload($form.attr('action'), {data: $form.serializeArray(), navTabId:navTabId});
    return false;
  }

  function navTabAjaxDone(json){

    DWZ.ajaxDone(json);
    if(json.statusCode==DWZ.statusCode.ok){
      if(json.navTabId){
        //先判断当前的nav是否有 pagerForm，
        //有，就刷新这个nav 中的分页控件
        var $pageForm = $("form[name="+json.navTabId+"_pageForm]");
        if($pageForm){
          console.log("刷新分页");
          $pageForm.submit();
        }else{
          console.log("刷新本Nav");
          navTab.reloadFlag(json.navTabId);
        }
      }else{
        navTabPageBreak({},json.rel);
      }
      if("closeCurrent"==json.callbackType){
        setTimeout(function(){navTab.closeCurrentTab();},100);
      }else if("forward"==json.callbackType){
        navTab.reload(json.forwardUrl);
      }
    }
  } */


  $(".box").click(function(){
    var $tab = $(this).children("a").eq(0).attr('href');
    var hostname = $("input[name='hostname']").val();
    if($tab == '#tab1'){
      var haddr = hostname+'/tuireport/rplist';
      $(".countform").attr('action',haddr);
      $("#pagecontent2").hide();
      $("#pagecontent1").show();
      $("#tab2").hide();
      $("#tab1").show();
    }else if($tab == '#tab2'){
      var haddr = hostname+'/tuireport/mobile_download#tab2';
      $(".countform").attr('action',haddr);
      $("#pagecontent1").hide();
      $("#pagecontent2").show();
      $("#tab1").hide();
      $("#tab2").show();
    }
    //alert( $("#pagerForm").attr('action'));
  })


  $(function(){
    var tip =  $("#dtype").val();

    var hostname = $("input[name='hostname']").val();
    if(tip == 1) {
      //显示id1
      $("#getmy li:eq(0)").attr('class','active');
      $("#getmy li:eq(1)").attr('class','');
      $("#pagecontent1").show();
      $("#pagecontent2").hide();
      var haddr = hostname+'/tuireport/rplist';
      $(".countform").attr('action',haddr);
      $("#tab1").show();
      $("#tab2").hide();
    } else if(tip == 2){
      //显示id2
      $("#getmy li:eq(0)").attr('class','');
      $("#getmy li:eq(1)").attr('class','active');
      $("#pagecontent1").hide();
      $("#pagecontent2").show();
      var haddr = hostname+'/tuireport/mobile_download';
      $(".countform").attr('action',haddr);
      $("#tab2").prop('class','tab-pane');
      $("#tab2").show();
      $("#tab1").hide();

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