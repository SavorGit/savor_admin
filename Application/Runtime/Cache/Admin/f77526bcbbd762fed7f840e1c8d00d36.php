<?php if (!defined('THINK_PATH')) exit();?><!--修改样式2 p元素自适应宽度 start-->
<style type="text/css">
	/* .zhezhao_dtl {
		display: none;
		position: absolute;
		top: 0;
		bottom: 0;
		right: 0;
		left: 0;
		background-color: black;
		opacity: 0.7;
		text-align: center;
		z-index: 999;
	}
	
	.big {
		display: none;
	}
	
	.addbig {
		position: absolute;
		width: 500px;
		height: 500px;
		top: 100px;
		left: 26%;
		z-index: 1000;
	} */
	
	.main_top {
		width: 98%;
		margin: 0 auto;
		margin-top: 15px;
		margin-bottom: 20px;
		background-color: white;
		height: 584px;
		color: #222222;
	}
	.div_top{
		height: 30px;
		width: 100%;

	}
	.div_top h2{
		line-height: 30px;
		margin-left:10px;
		width: 100px;
	}
	.div_left {
		width: 200px;
		height: 220px;
		-moz-box-shadow: 4px 4px 18px #333333;
		-webkit-box-shadow: 4px 4px 18px #333333;
		box-shadow: 4px 4px 18px #333333;
		margin-left: 10px;
		margin-right: 20px;
		margin-top: 10px;
		display: inline-block;
		float: right;
	}
	
	.div_left img {
		width: 150px;
		height: 120px;
		margin-left: 25px;
		margin-top: 13px;
		margin-bottom: 15px;
	}
	
	.div_right {
		width: 78%;
		height: 220px;
		-moz-box-shadow: 4px 4px 16px #333333;
		-webkit-box-shadow: 4px 4px 16px #333333;
		box-shadow: 4px 4px 16px #333333;
		margin-left: 10px;
		margin-top: 10px;
		display: inline-block;
	}
	
	.div_right .ul1 {
		width: 55%;
		display: inline-block;
		margin-left: 20px;
		border-right: 1px solid #F1F1F1;
	}
	
	.div_right .ul1 li {
		margin-top: 35px;
	}
	.div_right .ul2 {
		display: inline-block;
		margin-left: 10px;
	}
	
	.div_right .ul2 li {
		margin-top: 35px;
	}
	.div_bttom {
		width: 97%;
		height: 285px;
		margin: 0 auto;
		-moz-box-shadow: 4px 4px 16px #333333;
		-webkit-box-shadow: 4px 4px 16px #333333;
		box-shadow: 4px 4px 16px #333333;
		margin-top: 20px;
		margin-bottom: 30px;
	}
	.div_bttom .ul3{
		width: 37%;
		display: inline-block;
		margin-left: 20px;
		border-right: 1px solid #F1F1F1;
	}
	.div_bttom .ul3 li {
		margin-top: 26px;
	}
	.div_bttom .ul4{
		width: 30%;
		display: inline-block;
		margin-left: 20px;
		border-right: 1px solid #F1F1F1;
	}
	.div_bttom .ul4 li {
		margin-top: 26px;
	}
	.div_bttom .ul5{
		display: inline-block;
		float: right;
		margin-right: 10%;
	}
	.div_bttom .ul5 li {
		margin-top: 26px;
	}
	.banwei{
		width: 98.4%;
		height: 30px;
		background-color: white;
		margin: 0 auto;
	}
	.banwei h2{
		line-height: 40px;
	}
</style>
<div class="pageContent" style="height:100%;overflow: auto;background-color: #EEEEEE;">
	<form onsubmit="return navTabSearch(this);" id="pagerForm" action="<?php echo ($host_name); ?>/hotel/getdetail" method="post">
		<input type="hidden" name="pageNum" value="<?php echo ($pageNum); ?>" />
		<input type="hidden" name="numPerPage" value="<?php echo ($numPerPage); ?>" />
		<input type="hidden" name="_order" value="<?php echo ($_order); ?>" />
		<input type="hidden" name="_sort" value="<?php echo ($_sort); ?>" />
		<input type="hidden" name="id" value="<?php echo ($vinfo["id"]); ?>">

		<div class="pageFormContent modal-body">


		<div class="main_top">
			<div class="div_top"><h2>基本信息</h2>
				
					<a data-tip="修改详情" style="position: absolute; right: 52px;top: 33px;" target="dialog" mask="true" href="<?php echo ($host_name); ?>/hotel/add?id=<?php echo ($hotelid); ?>&acttype=1" class="btn btn-success btn-icon">
						<i class="fa fa-pencil"></i>
					</a>
				
				
			</div>
			<div class="div_left">
				<a data-target="#modal-file" href="javascript:void(0)">
					<?php if(($vinfo['oss_addr'] == 'NULL') OR $vinfo['oss_addr'] == ''): ?><img  src="/Public/admin/assets/img/noimage.png" border="0" />
						<span  style="text-align: center;"></span>
						<?php else: ?>
						<img id="" src="<?php echo ($vinfo["oss_addr"]); ?>" border="0" /><?php endif; ?>
					<span id="" style="text-align: center;"></span>
				</a>
				<p style="text-align: center;width: 100%;font-weight: 700;"><?php echo ($vinfo["name"]); ?></p>
			</div>
			<div class="div_right">
				<ul class="ul1">
					<li><span>酒店名称：</span><span><?php echo ($vinfo["name"]); ?></span></li>
					<li><span>所属区域：</span><span><?php if(is_array($area)): $i = 0; $__LIST__ = $area;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$row): $mod = ($i % 2 );++$i; if($row['id'] == $vinfo['area_id']): echo ($row['region_name']); endif; endforeach; endif; else: echo "" ;endif; ?></span></li>
					<li><span>酒楼地址：</span><span><?php echo ($vinfo["addr"]); ?></span></li>
					<li><span>酒楼级别：</span><span><?php $_result=C('HOTEL_LEVEL');if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($key == $vinfo['level']): echo ($vo); endif; endforeach; endif; else: echo "" ;endif; ?></span></li>
				</ul>
				<ul class="ul2">
					<li><span>是否重点：</span><span><?php if($vinfo["iskey"] == 1): ?>是<?php else: ?> 否<?php endif; ?></span></li>
					<li><span>安装日期：</span><span><?php echo ($vinfo["install_date"]); ?></span></li>
					<li><span>酒楼状态：</span><span>
						<?php if($vinfo["state"] == 2): ?>冻结
                        <?php elseif($vinfo["state"] == 1): ?>正常
                        <?php elseif($vinfo["state"] == 3): ?>报损
                        <?php else: endif; ?>
					</span></li>
					<li><span>变更说明：</span><span>
						<?php $_result=C('STATE_REASON');if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($key == $vinfo['state_change_reason']): echo ($vo); endif; endforeach; endif; else: echo "" ;endif; ?>
					</span></li>
				</ul>
			</div>
			<div class="div_bttom">
				<ul class="ul3">
					<li><span>酒楼联系人：</span><span><?php echo ($vinfo["contractor"]); ?></span></li>

					<li><span>对账单联系人电话：</span><span><?php echo ($vinfo["bill_tel"]); ?></span></li>
					<li><span>座机：</span><span></span><?php echo ($vinfo["tel"]); ?></li>
					<li><span>手机：</span><span></span><?php echo ($vinfo["mobile"]); ?></li>
					<li><span>合作维护人：</span><span><?php echo ($vinfo["maintainer"]); ?></span></li>
					<li><span>技术运维人：</span><span><?php echo ($vinfo["tech_maintainer"]); ?></span></li>
					<li><span>酒楼wifi名称：</span><span><?php echo ($vinfo["hotel_wifi"]); ?></span></li>
				</ul>
				<ul class="ul4">
					<li><span>对账单联系人：</span><span><?php echo ($vinfo["bill_per"]); ?></span></li>
					<li><span>机顶盒类型：</span><span>
						<?php $_result=C('hotel_box_type');if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($key == $vinfo['hotel_box_type']): echo ($vo); endif; endforeach; endif; else: echo "" ;endif; ?>
					</span></li>
					<li><span>酒楼位置坐标：</span><span></span><?php echo ($vinfo["gps"]); ?></li>
					<li><span>小平台存放位置：</span><span></span><?php echo ($vinfo["server_location"]); ?></li>
					<li><span>小平台MAC地址：</span><span><?php echo ($vinfo["mac_addr"]); ?></span></li>
					<li><span>小平台远程ID：</span><span><?php echo ($vinfo["remote_id"]); ?></span></li>
					<li><span>酒楼wifi密码：</span><span><?php echo ($vinfo["hotel_wifi_pas"]); ?></span></li>
				</ul>
				<ul class="ul5">
					<li><span>删除状态：</span><span><?php if($vinfo["flag"] == 0): ?>正常<?php elseif($vinfo["flag"] == 1): ?>删除<?php else: endif; ?></span></li>
					<li><span>包间数量：</span><span></span><a title="<?php echo ($vinfo["name"]); ?>包间列表" target="navTab" rel="hotel/room" href="<?php echo ($host_name); ?>/hotel/room?hotel_id=<?php echo ($vinfo["id"]); ?>"><?php echo ($vinfo["room_num"]); ?></a></li>
					<li><span>机顶盒数量：</span><span></span>
						<?php if($vinfo["box_num"] > 0): ?><a title="<?php echo ($vinfo["name"]); ?>机顶盒列表" target="navTab" rel="device/box" href="<?php echo ($host_name); ?>/device/box?hotel_id=<?php echo ($vinfo["id"]); ?>"><?php echo ($vinfo["box_num"]); ?></a>
                        <?php else: ?>
                        <?php echo ($vinfo["box_num"]); endif; ?>
					</li>
					<li><span>电视数量：</span><span>
						<?php if($vinfo["tv_num"] > 0): ?><a title="<?php echo ($vinfo["name"]); ?>电视列表" target="navTab" rel="device/tv" href="<?php echo ($host_name); ?>/device/tv?hotel_id=<?php echo ($vinfo["id"]); ?>"><?php echo ($vinfo["tv_num"]); ?></a>
                        <?php else: ?>
                        <?php echo ($vinfo["tv_num"]); endif; ?>
					</span></li>
					<li><span>节目单：</span><span><?php echo ($vinfo["menu_name"]); ?></span></li>
				</ul>
			</div>
			<div style="clear: both;"></div>
            
		</div>
			<div class="banwei" style="position:relative;height:44px;">
				<h2>版位信息</h2>
				<a data-tip="批量新增版位" target="navTab" style="position: absolute; right: 52px;top: 3%;" title="批量新增" rel="ceshipilianxin" href="<?php echo ($host_name); ?>/hotel/batchposition?hotel_id=<?php echo ($vinfo["id"]); ?>&name=<?php echo ($vinfo["name"]); ?>" class="btn btn-success btn-icon">
						<i class="fa fa-plus-square"></i>
				</a>
			</div>
		<div id="w_list_printdetail">
			<div class="no-more-tables">
				<table class="table table-bordered table-striped" targetType="navTab" asc="asc" desc="desc">
					<thead>
						<tr id="post">
							<!-- <th class="table-checkbox">
                              <input type="checkbox" data-check="all" data-parent=".table" />
                            </th> -->
							<th>ID</th>
							<th>包间名称</th>
							<th>包间位置</th>
							<th>机顶盒名称</th>
							<th>mac地址</th>
							<th>切换时间</th>
							<th>音量</th>
							<th>电视机品牌</th>
							<th>电视机尺寸</th>
							<th>信号源</th>
							<th>电视状态</th>
							<th class="table-tool">操作</th>
						</tr>
					</thead>
					<tbody data-check="list" data-parent=".table">
						<?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vlist): $mod = ($i % 2 );++$i;?><tr target="sid_user">
								<td data-title="序号"><?php echo ($vlist["indnum"]); ?></td>
								<td data-title="包间名称"><?php echo ($vlist["room_name"]); ?></td>
								<td data-title="包间位置"><?php echo ($vlist["rtp"]); ?></td>
								<td data-title="机顶盒名称"><?php echo ($vlist["bna"]); ?></td>
								<td data-title="mac地址"><?php echo ($vlist["bmac"]); ?></td>
								<td data-title="切换时间"><?php echo ($vlist["bstime"]); ?></td>

								<td data-title="音量"><?php echo ($vlist["bvm"]); ?></td>
								<td data-title="电视机品牌"><?php echo ($vlist["tbr"]); ?></td>
								<td data-title="电视机尺寸"><?php echo ($vlist["tsize"]); ?></td>
								<td data-title="信号源"><?php echo ($vlist["tsource"]); ?></td>

								<td data-title="电视状态"><?php echo ($vlist["tstate"]); ?></td>

								<td class="table-tool" data-title="操作">
									<div class="tools-edit">
										<a data-tip="修改包间" target="dialog" mask="true" href="<?php echo ($host_name); ?>/hotel/editRoom?id=<?php echo ($vlist["rid"]); ?>&acttype=1" class="btn btn-success btn-icon">
											<i class="fa fa-building"></i>
										</a>
										<a data-tip="修改机顶盒" target="dialog" mask="true" href="<?php echo ($host_name); ?>/device/editBox?hotel_id=<?php echo ($vinfo["id"]); ?>&id=<?php echo ($vlist["bid"]); ?>&acttype=1" class="btn btn-success btn-icon">
											<i class="fa fa-cube"></i>
										</a>
										<a data-tip="修改电视" target="dialog" mask="true" href="<?php echo ($host_name); ?>/device/addTv?id=<?php echo ($vlist["tid"]); ?>&acttype=1" class="btn btn-success btn-icon">
											<i class="fa fa-desktop"></i>
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

</form>
</div>
<script type="text/javascript">
if($(window).width()<=1366){
	$('.pageContent').css('width','1165px')
}
</script>