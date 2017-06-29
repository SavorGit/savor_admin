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
  <form onsubmit="return navTabSearch(this);" id="pagerForm" action="<?php echo ($host_name); ?>/report/contAndProm" method="post" >
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
                
          </div>
        </div>
		<!-- <div class="form-group">
            <div class="input-group-sm input-group">
              <label class="control-label" style="">
                酒楼名称：
              </label>
                <span class="input-group-btn input-group-sm">
                <input type="text" class="form-control" name="hotel_name" value="<?php echo ($hotel_name); ?>" placeholder="请输入酒楼名称" >
            </span>
            </div>
          </div> -->
        <!-- <div class="form-group">
            <div class="input-group-sm input-group">
              <label class="control-label" style="">
                维护人：
              </label>
                <span class="input-group-btn input-group-sm">
                <input type="text" class="form-control" name="guardian" value="<?php echo ($guardian); ?>" placeholder="请输入维护人姓名" style="width:120px;">
            </span>
            </div>
          </div> -->

          <div class="form-group"  >
           
			<div class="input-group input-group-sm date form_date" data-ymd="true" data-date="<?php echo ($start_date); ?>" style="width:135px;">

          <input id="contandprom_s_time" style="width:130px;padding:0px;margin:0px;margin-left: 15px;"  name="start_date" type="text" size="17" class="form-control date" placeholder="开始日期" value="<?php echo ($start_date); ?>" readonly>
                  <span class="input-group-btn" style="padding:0px;margin:0px;" >
                    <button class="btn default date-reset  btn-sm" type="button"><i class="fa fa-times"></i></button>
                    <button class="btn btn-success date-set  btn-sm" type="button"><i class="fa fa-calendar"></i></button>
                  </span>
        </div>

            <div class="input-group input-group-sm date form_date" data-ymd="true" data-date="<?php echo ($end_date); ?>" style="width:135px;">

          <input id="contandprom_e_time" style="width:130px;padding:0px;margin:0px;margin-left: 15px;"  name="end_date" type="text" size="17" class="form-control date" placeholder="结束日期" value="<?php echo ($end_date); ?>" readonly>
                  <span class="input-group-btn" style="padding:0px;margin:0px;" >
                    <button class="btn default date-reset  btn-sm" type="button"><i class="fa fa-times"></i></button>
                    <button class="btn btn-success date-set  btn-sm" type="button"><i class="fa fa-calendar"></i></button>
                  </span>
        </div>
          </div>
		  <div class="form-group">
            <div class="input-group-sm input-group">
              <label style="margin-left: 13px;" class="col-xs-1 col-sm-1 control-label">
                	编辑：
              </label>
                <span class="input-group-btn input-group-sm">
                <select name="userid" style="width: 20px" class="form-control bs-select class-filter" data-style="btn-success btn-sm" data-container="body" size="15">
                <option value="">请选择</option>
                <?php if(is_array($user_list)): $i = 0; $__LIST__ = $user_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vlist): $mod = ($i % 2 );++$i;?><option <?php if($vlist["id"] == $userid): ?>selected<?php endif; ?> value="<?php echo ($vlist["id"]); ?>"><?php echo ($vlist["remark"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                </select>
            </span>
            </div>
          </div> 
		<div class="form-group">
            <div class="input-group-sm input-group">
              <label style="margin-left: 13px;" class="col-xs-1 col-sm-1 control-label">
                	分类：
              </label>
                <span class="input-group-btn input-group-sm">
                <select name="category_id" style="width: 20px" class="form-control bs-select class-filter" data-style="btn-success btn-sm" data-container="body" size="15">
                <option value="">请选择</option>
                <?php if(is_array($category_list)): $i = 0; $__LIST__ = $category_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vlist): $mod = ($i % 2 );++$i;?><option <?php if($vlist["id"] == $category_id): ?>selected<?php endif; ?> value="<?php echo ($vlist["id"]); ?>" ><?php echo ($vlist["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                </select>
            </span>
            </div>
          </div> 
          <br>
		<div class="form-group">
            <div class="input-group input-group-sm">
              <label style="margin-left: 3px;" class="col-xs-1 col-sm-1 control-label">
               文章标题：
              </label>
                <span class="input-group-btn input-group-sm">
                <input style="margin-left: 18px;" type="text" class="form-control" name="content_name" value="<?php echo ($content_name); ?>" placeholder="请输入内容标题" >
            </span>
            </div>
          </div> 
          <div class="input-group">
            <span class="input-group-btn">
              <button class="btn btn-primary" type="submit" id="choosedata"><i class="fa fa-search"></i></button>
            </span>
          </div>

          <div class="input-group input-group-sm pull-right">
            <a class="btn btn-success btn-sm add" href="<?php echo ($host_name); ?>/excel/excelContAndProm?start_date=<?php echo ($start_date); ?>&end_date=<?php echo ($end_date); ?>&userid=<?php echo ($userid); ?>&category_id=<?php echo ($category_id); ?>&content_name=<?php echo ($content_name); ?>" title="导出APP下载统计总表" target="_blank" mask="true"><i class="fa fa-plus"></i> 导出下载点播次数统计</a>
          </div> 

          </div>

        </div>

        </div>
      </div>
  </form>
</div>
<div class="pageContent" id="pagecontent" style="margin-top:25px;">
  <div id="w_list_print">
    <div class="no-more-tables">
      <form method="post" action="#" id="del-form" class="pageForm required-validate" rel="second"
enctype="multipart/form-data" onsubmit="return iframeCallback(this, dialogAjaxDoneER)">
        <table class="table table-bordered table-striped" targetType="navTab" asc="asc" desc="desc">
          <thead>
          <tr id="post">
            <th>序号</th>
            <th>文章标题</th>
            <th>分类</th>
            <th>内容类别</th>
            
            <th>编辑</th>
            <th>创建时间</th>
            <th>阅读总次数</th>
            <th>阅读总时长</th>
            <th>点播总次数</th>
            <th>分享总次数</th>
            <th>PV</th>
            <th>UV</th>
            <th>点击数</th>
            <th>外链点击数</th>
          </tr>
          </thead>
          <tbody data-check="list" data-parent=".table">
          <?php if(is_array($list)): $k = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vlist): $mod = ($k % 2 );++$k;?><tr target="sid_user">
              <td data-title="序号"><?php echo ($k); ?></td>
              <td data-title="创建时间"><?php echo ($vlist["content_name"]); ?></td>
              <td data-title="分类"><?php echo ($vlist["category_name"]); ?></td>
              <td data-title="内容类别">
              <?php if($vlist["common_value"] == 0): ?>纯文本
              <?php elseif($vlist["common_value"] == 1): ?>
              	图文
              <?php elseif($vlist["common_value"] == 2): ?>
                                          图集                        
              <?php elseif($vlist["common_value"] == 3): ?>
               	视频<?php endif; ?>	
              </td>
              
              <td data-title="编辑"><?php echo ($vlist["operators"]); ?> </td>
              <td data-title="创建时间"><?php echo ($vlist["create_time"]); ?> </td>
              <td data-title="阅读总次数"><?php if(empty($vlist["read_count"])): ?>0<?php else: echo ($vlist["read_count"]); endif; ?> </td> 
              <td data-title="阅读总时长"><?php if(empty($vlist["read_duration"])): ?>0秒<?php else: echo (changeTimeType($vlist["read_duration"])); endif; ?> </td> 
              <td data-title="点播总次数"><?php if(empty($vlist["demand_count"])): ?>0<?php else: echo ($vlist["demand_count"]); endif; ?> </td> 
              <td data-title="分享总次数"><?php if(empty($vlist["share_count"])): ?>0<?php else: echo ($vlist["share_count"]); endif; ?> </td> 
              <td data-title="PV"><?php if(empty($vlist["pv_count"])): ?>0<?php else: echo ($vlist["pv_count"]); endif; ?> </td> 
              <td data-title="UV"><?php if(empty($vlist["uv_count"])): ?>0<?php else: echo ($vlist["uv_count"]); endif; ?> </td> 
              <td data-title="点击数"><?php if(empty($vlist["click_count"])): ?>0<?php else: echo ($vlist["click_count"]); endif; ?> </td> 
              <td data-title="外链点击数"><?php if(empty($vlist["outline_count"])): ?>0<?php else: echo ($vlist["outline_count"]); endif; ?> </td> 
              
            </tr><?php endforeach; endif; else: echo "" ;endif; ?>
          </tbody>
        </table>
      </form>

    </div>
  </div>
  <?php echo ($page); ?>
</div>
<script type="text/javascript">
 $(function(){
	   

	    $("#contandprom_s_time").datetimepicker({
	      //minView: "month", //选择日期后，不会再跳转去选择时分秒
	      minView:0,
	      language:  'zh-CN',
	      format: 'yyyy-mm-dd hh:ii:ss',
	      todayBtn:  1,
	      autoclose: 1,
	    });
	    $("#contandprom_e_time").datetimepicker({
		      //minView: "month", //选择日期后，不会再跳转去选择时分秒
	    		minView:0,
		      language:  'zh-CN',
		      format: 'yyyy-mm-dd hh:ii:ss',
		      todayBtn:  1,
		      autoclose: 1,
		    });
	  })
</script>