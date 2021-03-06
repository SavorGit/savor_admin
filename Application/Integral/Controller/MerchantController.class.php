<?php
namespace Integral\Controller;
use Admin\Controller\BaseController;

/**
 * @desc 商家管理
 *
 */
class MerchantController extends BaseController {

    public function merchantlist(){
        $page = I('pageNum',1);
        $size   = I('numPerPage',50);
        $area_id = I('area_id',0,'intval');
        $status = I('status',0,'intval');
        $maintainer_id = I('maintainer_id',0,'intval');
        $model_id = I('model_id',0,'intval');
        $hotel_name = I('hotel_name','','trim');

        $m_merchant = new \Admin\Model\Integral\MerchantModel();
        $start  = ($page-1) * $size;
        $fields = 'a.id,hotel.name as hotel_name,area.region_name as city,a.rate_groupid,a.name,a.job,a.mobile,a.sysuser_id,a.status,a.add_time,ext.maintainer_id';
        $where = array();
        if($area_id)    $where['area.id']=$area_id;
        if($status)    $where['a.status']=$status;
        if($maintainer_id)    $where['ext.maintainer_id']=$maintainer_id;
        if($model_id)    $where['a.service_model_id']=$model_id;
        if(!empty($hotel_name)) $where['hotel.name'] = array('like',"%$hotel_name%");

        $result = $m_merchant->getMerchantList($fields,$where,'a.id desc',$start,$size);
        $datalist = $result['list'];
        $group_rate = C('GROUP_RATE');
        $user_ids = array();
        foreach ($datalist as $k=>$v){
            $user_ids[] = $v['sysuser_id'];
            if($v['maintainer_id']){
                $user_ids[] = $v['maintainer_id'];
            }
            if($v['status']==1){
                $status_str = '正常';
            }else{
                $status_str = '冻结';
            }
            $datalist[$k]['status_str'] = $status_str;
            $datalist[$k]['rate'] = $group_rate[$v['rate_groupid']];
        }
        $user_ids = array_unique($user_ids);
        $m_sysuser = new \Admin\Model\UserModel();
        $where = array('id'=>array('in',join(',',$user_ids)));
        $res_user = $m_sysuser->where($where)->order('id desc')->select();
        $user = array();
        foreach ($res_user as $v){
            $user[$v['id']] = $v['remark'];
        }
        $m_staff = new \Admin\Model\Integral\StaffModel();
        foreach ($datalist as $k=>$v){
            $sysuser_id = $v['sysuser_id'];
            $maintaineru_id = $v['maintainer_id'];
            $datalist[$k]['creater'] = $user[$sysuser_id];
            $datalist[$k]['maintainer'] = $user[$maintaineru_id];
            $filter = array('merchant_id'=>$v['id'],'status'=>1);
            $filter['parent_id'] = array('gt',0);
            $res_staff_num = $m_staff->getRow('count(id) as num',$filter);
            $datalist[$k]['staff_num'] = $res_staff_num['num'];
        }

        $m_servicemodel = new \Admin\Model\Integral\ServiceMxModel();
        $fields = 'id,name';
        $where = array('status'=>1);
        $service_models = $m_servicemodel->getDataList($fields,$where,'id asc');
        $m_area  = new \Admin\Model\AreaModel();
        $area_arr = $m_area->getAllArea();
        $opusers = $this->getOpuser($maintainer_id);

        $this->assign('service_models',$service_models);
        $this->assign('area_id',$area_id);
        $this->assign('status',$status);
        $this->assign('maintainer_id',$maintainer_id);
        $this->assign('model_id',$model_id);
        $this->assign('hotel_name',$hotel_name);
        $this->assign('area', $area_arr);
        $this->assign('opusers', $opusers);
        $this->assign('hotel_name',$hotel_name);
        $this->assign('datalist', $datalist);
        $this->assign('page',  $result['page']);
        $this->assign('pageNum',$page);
        $this->assign('numPerPage',$size);
        $this->display('merchantlist');
    }

    public function merchantadd(){
        $merchant_id = I('merchant_id',0,'intval');
        $id = I('id',0,'intval');
        if($merchant_id){
            $id = 0;
            $m_merchant = new \Admin\Model\Integral\MerchantModel();
            $res = $m_merchant->getInfo(array('id'=>$merchant_id));

            $merchant_info = array(1=>$res['hotel_id'],2=>$res['service_model_id']);
            $merchant_info[3] = array('channel_id'=>$res['channel_id'],'rate_groupid'=>$res['rate_groupid']);
            $merchant_info[4] = array('name'=>$res['name'],'job'=>$res['job'],'mobile'=>$res['mobile']);

            $m_hotel = new \Admin\Model\HotelModel();
            $res = $m_hotel->getOne($res['hotel_id']);
            $merchant_info['hotel_name'] = $res['name'];
        }else{
            if(empty($id)){
                $id = getMillisecond();
                session($id,array());
            }else{
                $merchant_info = session($id);
            }
        }
        $mtype = 1;
        if(!empty($merchant_info)){
            if(!empty($merchant_info[1])){
                $mtype = $merchant_info[1]['mtype'];
                if($mtype==1){
                    $m_hotel = new \Admin\Model\HotelModel();
                    $res = $m_hotel->getOne($merchant_info[1]['hotel_id']);
                    $merchant_info['step1'] = $res['name'];
                }else{
                    $merchant_info['step1'] = $merchant_info[1]['hotel_name'];
                }
            }
            if(!empty($merchant_info[2])){
                $m_servicemodel = new \Admin\Model\Integral\ServiceMxModel();
                $res_model = $m_servicemodel->getInfo(array('id'=>$merchant_info[2]));
                $merchant_info['step2'] = $res_model['name'];
            }
            if(!empty($merchant_info[3])){
                $channel_merchant = C('CHANNEL_MERCHANT');
                $group_rate = C('GROUP_RATE');
                $channel_id = $merchant_info[3]['channel_id'];
                $rate_groupid = $merchant_info[3]['rate_groupid'];
                $cash_rate = $merchant_info[3]['cash_rate'];
                $recharge_rate = $merchant_info[3]['recharge_rate'];

                $merchant_info['step3'] = "渠道商：{$channel_merchant[$channel_id]}，商城汇率：{$group_rate[$rate_groupid]}，兑现汇率：{$cash_rate}，充值汇率：{$recharge_rate}";
            }
            if(!empty($merchant_info[4])){
                if($merchant_info[1]['mtype']==1){
                    $merchant_info['step4'] = "姓名：{$merchant_info[4]['name']}，职务：{$merchant_info[4]['job']}，手机号码：{$merchant_info[4]['mobile']}";
                }else{
                    $merchant_info['step4'] = "客服电话：{$merchant_info[4]['tel']}";
                }
            }
        }
        $this->assign('merchant_info',$merchant_info);
        $this->assign('id',$id);
        $this->assign('merchant_id',$merchant_id);
        $this->assign('mtype',$mtype);
        $this->display('merchantadd');
    }

    public function merchantaddStep(){
        $step = intval($_REQUEST['step']);
        $id = intval($_REQUEST['id']);
        $merchant_id = intval($_REQUEST['merchant_id']);

        $m_merchant = new \Admin\Model\Integral\MerchantModel();
        $merchant_info = array();
        if($merchant_id){
            $merchant_info = $m_merchant->getInfo(array('id'=>$merchant_id));
        }else{
            $merchant_info = session($id);
        }
        $this->assign('id',$id);
        $this->assign('merchant_id',$merchant_id);
        switch ($step){
            case 1:
                if(IS_POST){
                    $mtype = I('mtype',1,'intval');
                    $hotel_id = I('hotel_id',0,'intval');
                    $hotel_name = I('hotel_name','','trim');
                    $m_merchant = new \Admin\Model\Integral\MerchantModel();
                    $res_merchant = $m_merchant->getInfo(array('hotel_id'=>$hotel_id,'status'=>1));
                    if(!empty($res_merchant)){
                        $this->output('该酒楼已经创建过商家','merchant/merchantadd',2,0);
                    }
                    if($mtype==1){
                        if($merchant_id && $hotel_id){
                            $m_merchant->updateData(array('id'=>$merchant_id),array('hotel_id'=>$hotel_id));
                        }
                        $merchant_info[$step] = array('hotel_id'=>$hotel_id,'hotel_name'=>'','mtype'=>$mtype);
                    }else{
                        if(empty($hotel_name)){
                            $this->output('请输入商家名称','merchant/merchantadd',2,0);
                        }
                        $merchant_info[$step] = array('hotel_id'=>$hotel_id,'hotel_name'=>$hotel_name,'mtype'=>$mtype);
                        $merchant_info[2] = 1;
                        $merchant_info[3] = array('channel_id'=>1,'rate_groupid'=>100,'cash_rate'=>1,'recharge_rate'=>1);
                    }
                    session($id,$merchant_info);
                    $this->output('操作成功', 'merchant/merchantadd');
                }else{
                    if($merchant_id){
                        $hotel_id = $merchant_info['hotel_id'];
                    }else{
                        $hotel_id = $merchant_info[1]['hotel_id'];
                    }
                    $hotel_id = intval($hotel_id);
                    $m_hotel = new \Admin\Model\HotelModel();
                    $where = array('state'=>1,'flag'=>0);
                    $field = 'id,name';
                    $hotels = $m_hotel->getWhereorderData($where,$field,'area_id asc');
                    foreach ($hotels as $k=>$v){
                        if($hotel_id && $v['id']==$hotel_id){
                            $hotels[$k]['is_select'] = 'selected';
                        }else{
                            $hotels[$k]['is_select'] = '';
                        }
                    }
                    $mtype = $merchant_info[1]['mtype'];
                    $hotel_name = $merchant_info[1]['hotel_name'];

                    $this->assign('hotel_id',$hotel_id);
                    $this->assign('hotels',$hotels);
                    $this->assign('mtype',$mtype);
                    $this->assign('hotel_name',$hotel_name);
                    $this->display('choosehotel');
                }
                break;
            case 2:
                if(IS_POST){
                    $smodel_id = I('smodel_id',0,'intval');
                    if(empty($smodel_id)){
                        $this->output('请选择模型', 'merchant/merchantadd',2,0);
                    }
                    if($merchant_id){
                        $m_merchant->updateData(array('id'=>$merchant_id),array('service_model_id'=>$smodel_id));
                    }else{
                        $merchant_info[$step] = $smodel_id;
                        session($id,$merchant_info);
                    }
                    $this->output('操作成功', 'merchant/merchantadd');

                }else{
                    if($merchant_id){
                        $model_id = $merchant_info['service_model_id'];
                    }else{
                        if(isset($merchant_info[2])){
                            $model_id = $merchant_info[2];
                        }else{
                            $model_id = 1;
                        }
                    }

                    $m_servicemodel = new \Admin\Model\Integral\ServiceMxModel();
                    $fields = 'id,name';
                    $where = array('status'=>1);
                    $smodels = $m_servicemodel->getDataList($fields,$where,'id asc');
                    foreach ($smodels as $k=>$v){
                        if($model_id && $v['id']==$model_id){
                            $smodels[$k]['is_select'] = 'selected';
                        }else{
                            $smodels[$k]['is_select'] = '';
                        }
                    }
                    $this->assign('smodels',$smodels);
                    $this->display('chooseservicemodel');
                }
                break;
            case 3:
                if(IS_POST){
                    $channel_id = I('channel_id',0,'intval');
                    $rate_groupid = I('rate_groupid',0,'intval');
                    $cash_rate = I('cash_rate','','trim');
                    $recharge_rate = I('recharge_rate','','trim');
                    if(empty($channel_id)){
                        $this->output('请选择渠道商', 'merchant/merchantadd',2,0);
                    }
                    $add_info = array('channel_id'=>$channel_id,'rate_groupid'=>$rate_groupid,'cash_rate'=>$cash_rate,'recharge_rate'=>$recharge_rate);
                    if($merchant_id){
                        $m_merchant->updateData(array('id'=>$merchant_id),$add_info);
                    }else{
                        $merchant_info[$step] = $add_info;
                        session($id,$merchant_info);
                    }
                    $this->output('操作成功', 'merchant/merchantadd');

                }else{
                    if($merchant_id==0){
                        $merchant_info = $merchant_info[3];
                    }
                    $this->assign('merchant_info',$merchant_info);
                    $this->display('choosechannel');
                }
                break;
            case 4:
                if(IS_POST){
                    $minfo = session($id);
                    if($minfo[1]['mtype']==1){
                        $name = I('name','','trim');
                        $job = I('job','','trim');
                        $mobile = I('mobile','','trim');
                        if(!isMobile($mobile)){
                            $this->output('请输入正确的手机号码', 'merchant/merchantadd',2,0);
                        }
                        $res_merchant_mobile = $m_merchant->getInfo(array('mobile'=>$mobile,'status'=>1));
                        if(!empty($res_merchant_mobile)){
                            $this->output("该手机号码已创建商家", 'merchant/merchantadd',2,0);
                        }

                        $add_info = array('name'=>$name,'job'=>$job,'mobile'=>$mobile);
                        $merchant_info[$step] = $add_info;
                        session($id,$merchant_info);
                        if($merchant_id){
                            $m_merchant->updateData(array('id'=>$merchant_id),$add_info);
                            if($mobile!=$merchant_info['mobile']){
                                $m_hotel = new \Admin\Model\HotelModel();
                                $res_hotel = $m_hotel->getOne($merchant_info['hotel_id']);
                                $sms_config = C('ALIYUN_SMS_CONFIG');
                                $alisms = new \Common\Lib\AliyunSms();
                                $params = array('hotel_name'=>$res_hotel['name'],'code'=>$merchant_info['code']);
                                $template_code = $sms_config['merchant_login_invite_code'];
                                $alisms::sendSms($mobile,$params,$template_code);
                            }
                        }
                    }else{
                        $tel = I('tel','','trim');
                        $add_info = array('name'=>'','job'=>'','mobile'=>'','tel'=>$tel);
                        $merchant_info[$step] = $add_info;
                        session($id,$merchant_info);
                    }
                    $this->output('操作成功', 'merchant/merchantadd');
                }else{
                    if($merchant_id==0){
                        $merchant_info = $merchant_info[4];
                    }
                    $this->assign('merchant_info',$merchant_info);
                    $this->display('bindadmin');
                }
                break;
            case 5:
                $merchant_info = session($id);
                if(empty($merchant_info)){
                    $this->output('请按照操作步骤进行创建', 'merchant/merchantadd',2,0);
                }
                for($i=1;$i<5;$i++){
                    if(empty($merchant_info[$i])){
                        $this->output('请选择操作步骤所需数据', 'merchant/merchantadd',2,0);
                    }
                }
                $userinfo = session('sysUserInfo');
                $sysuser_id = $userinfo['id'];
                $m_hotel = new \Admin\Model\HotelModel();

                $mtype = $merchant_info[1]['mtype'];
                if($mtype==1){
                    $hotel_id = $merchant_info[1]['hotel_id'];
                }else{
                    $res_hinfo = $m_hotel->getInfo('*',array('name'=>$merchant_info[1]['hotel_name']),'id desc','0,1');
                    if(!empty($res_hinfo)){
                        $this->output('步骤一名字重复', 'merchant/merchantadd',2,0);
                    }
                    $pin = new \Common\Lib\Pin();
                    $obj_pin = new \Overtrue\Pinyin\Pinyin();
                    $code_charter = '';
                    if(preg_match('/[a-zA-Z]/', $merchant_info[1]['hotel_name'])){
                        $code_charter = $merchant_info[1]['hotel_name'];
                    }else {
                        $code_charter = $obj_pin->abbr($merchant_info[1]['hotel_name']);
                        $code_charter = strtolower($code_charter);
                        if(strlen($code_charter)==1){
                            $code_charter .=$code_charter;
                        }
                    }
                    $hotel_data = array('name'=>$merchant_info[1]['hotel_name'],'tel'=>$merchant_info[4]['tel'],'state'=>1,'flag'=>0,
                        'type'=>3,'sysuser_id'=>$sysuser_id);
                    if($code_charter){
                        $hotel_data['pinyin'] = strtolower($code_charter);
                    }
                    $hotel_id = $m_hotel->add($hotel_data);
                }

                $data = array('hotel_id'=>$hotel_id,'service_model_id'=>$merchant_info[2],'channel_id'=>$merchant_info[3]['channel_id'],
                    'rate_groupid'=>$merchant_info[3]['rate_groupid'],'cash_rate'=>$merchant_info[3]['cash_rate'],'recharge_rate'=>$merchant_info[3]['recharge_rate'],
                    'name'=>$merchant_info[4]['name'],'job'=>$merchant_info[4]['job'],'mobile'=>$merchant_info[4]['mobile'],'mtype'=>$mtype,
                    'sysuser_id'=>$sysuser_id,'status'=>1);

                if($mtype==1){
                    $res_hotel = $m_hotel->getOne($data['hotel_id']);
                    $code_charter = '';
                    $s_hotel_name = mb_substr($res_hotel['name'], 0,2,'utf8');
                    if(preg_match('/[a-zA-Z]/', $s_hotel_name)){
                        $code_charter = $s_hotel_name;
                    }else {
                        $pin = new \Common\Lib\Pin();
                        $obj_pin = new \Overtrue\Pinyin\Pinyin();
                        $code_charter = $obj_pin->abbr($s_hotel_name);
                        $code_charter  = strtolower($code_charter);
                        if(strlen($code_charter)==1){
                            $code_charter .=$code_charter;
                        }
                    }
                    $code_charter  = strtolower($code_charter);
                    $m_hotel_invite_code = new \Admin\Model\HotelInviteCodeModel();
                    $invite_code = '';
                    $flag = 0;
                    while ($flag <20){
                        $code_num = generate_code(6);
                        $invite_code = $code_charter.$code_num;
                        $where = array('code'=>$invite_code);
                        $nums = $m_hotel_invite_code->countNums($where);
                        if(empty($nums)){
                            break;
                        }
                        $flag ++;
                    }

                    $invite_data = array('code'=>$invite_code,'hotel_id'=>$data['hotel_id'],'bind_mobile'=>$data['mobile'],
                        'bind_time'=>date('Y-m-d H:i:s'),'type'=>2,'creator_id'=>$sysuser_id,'state'=>1);
                    $ret = $m_hotel_invite_code->addInfo($invite_data);
                    $res_merchant = false;
                    if($ret){
                        $m_merchant = new \Admin\Model\Integral\MerchantModel();
                        $tmp_merchant = $m_merchant->getAll('*',array('hotel_id'=>$data['hotel_id'],'status'=>2),0,1,'id desc');
                        $tmp_merchant_id = 0;
                        $is_takeout = 0;
                        $m_dishgoods = new \Admin\Model\Smallapp\DishgoodsModel();
                        if(!empty($tmp_merchant)){
                            $res_dishgoods = $m_dishgoods->getInfo(array('merchant_id'=>$tmp_merchant[0]['id']));
                            if(!empty($res_dishgoods)){
                                $tmp_merchant_id = $tmp_merchant[0]['id'];
                                $is_takeout = $tmp_merchant[0]['is_takeout'];
                            }
                        }
                        $data['code'] = $invite_code;
                        $data['is_takeout'] = $is_takeout;
                        $res_merchant = $m_merchant->addData($data);
                        if($tmp_merchant_id){
                            $m_dishgoods->updateData(array('merchant_id'=>$tmp_merchant_id),array('merchant_id'=>$res_merchant));
                        }
                    }
                }else{
                    $res_merchant = $m_merchant->addData($data);
                }
                if($res_merchant){
                    if($mtype==1){
                        //发送短信
                        $sms_config = C('ALIYUN_SMS_CONFIG');
                        $alisms = new \Common\Lib\AliyunSms();
                        $params = array('hotel_name'=>$res_hotel['name'],'code'=>$invite_code);
                        $template_code = $sms_config['merchant_login_invite_code'];
                        $alisms::sendSms($data['mobile'],$params,$template_code);

                        $message = "邀请码已通过短信的方式发送给了“{$res_hotel['name']}“的管理员请提醒其注意查收！";
                    }else{
                        $message = "非合作酒楼商家已注册成功";
                    }
                    $this->output($message,'merchant/merchantlist');
                }else{
                    $this->output('商家创建失败', 'merchant/merchantadd',2,0);
                }
                break;
        }
    }

    public function detail(){
        $merchant_id = I('merchant_id',0,'intval');
        $m_merchant = new \Admin\Model\Integral\MerchantModel();
        $merchant_info = $m_merchant->getInfo(array('id'=>$merchant_id));
        $is_modify_name = $is_modify_job = $is_modify_mobile = 0;
        if($merchant_info['mtype']==2){
            if(empty($merchant_info['name']))       $is_modify_name = 1;
            if(empty($merchant_info['job']))        $is_modify_job = 1;
            if(empty($merchant_info['mobile']))     $is_modify_mobile = 1;
        }
        $m_servicemodel = new \Admin\Model\Integral\ServiceMxModel();
        $fields = 'id,name';
        $where = array('status'=>1);
        $smodels = $m_servicemodel->getDataList($fields,$where,'id asc');
        foreach ($smodels as $k=>$v){
            if($merchant_info['service_model_id'] && $v['id']==$merchant_info['service_model_id']){
                $smodels[$k]['is_select'] = 'selected';
            }else{
                $smodels[$k]['is_select'] = '';
            }
        }

        $m_hotel = new \Admin\Model\HotelModel();
        $field = 'a.name as hotel_name,area.region_name as city,ext.maintainer_id';
        $where = array('a.id'=>$merchant_info['hotel_id']);
        $res_hotel = $m_hotel->getHotelInfo($field,$where);
        $maintainer = '';
        if($res_hotel['maintainer_id']){
            $m_sysuser = new \Admin\Model\UserModel();
            $res_user = $m_sysuser->find($res_hotel['maintainer_id']);
            $maintainer = $res_user['remark'];
        }
        $merchant_info['maintainer'] = $maintainer;
        $merchant_info['hotel_name'] = $res_hotel['hotel_name'];
        $merchant_info['city'] = $res_hotel['city'];

        $this->assign('is_modify_name',$is_modify_name);
        $this->assign('is_modify_job',$is_modify_job);
        $this->assign('is_modify_mobile',$is_modify_mobile);
        $this->assign('smodels',$smodels);
        $this->assign('merchant_info',$merchant_info);
        $this->display();
    }

    public function editdetail(){
        $merchant_id = I('merchant_id',0,'intval');
        $service_model_id = I('service_model_id',0,'intval');
        $channel_id = I('channel_id',0,'intval');
        $rate_groupid = I('rate_groupid',0,'intval');
        $cash_rate = I('cash_rate','','trim');
        $recharge_rate = I('recharge_rate','','trim');
        $name = I('name','','trim');
        $job = I('job','','trim');
        $mobile = I('mobile','','trim');
        $status = I('status',1,'intval');

        $m_merchant = new \Admin\Model\Integral\MerchantModel();
        $merchant_info = $m_merchant->getInfo(array('id'=>$merchant_id));

        $m_merchant = new \Admin\Model\Integral\MerchantModel();
        $hotel_id = $merchant_info['hotel_id'];
        $where = array('hotel_id'=>$hotel_id,'status'=>1);
        $where['id'] = array('neq',$merchant_id);
        $res_merchant = $m_merchant->getInfo($where);
        if(!empty($res_merchant)){
            $this->output('当前商家已存在', 'merchant/merchantadd',2,0);
        }

        $userinfo = session('sysUserInfo');
        $sysuser_id = $userinfo['id'];
        $add_info = array('service_model_id'=>$service_model_id,'channel_id'=>$channel_id,'rate_groupid'=>$rate_groupid,
            'cash_rate'=>$cash_rate,'recharge_rate'=>$recharge_rate,'name'=>$name,'job'=>$job,'mobile'=>$mobile,
            'status'=>$status,'sysuser_id'=>$sysuser_id);
        if($mobile!=$merchant_info['mobile']){
            $res_merchant_mobile = $m_merchant->getInfo(array('mobile'=>$mobile,'status'=>1));
            if(!empty($res_merchant_mobile)){
                $this->output("该手机号码已创建商家", 'merchant/editdetail',2,0);
            }
            $m_hotel = new \Admin\Model\HotelModel();
            $res_hotel = $m_hotel->getOne($merchant_info['hotel_id']);

            if($merchant_info['type']==2){
                $code_charter = '';
                $s_hotel_name = mb_substr($res_hotel['name'], 0,2,'utf8');
                if(preg_match('/[a-zA-Z]/', $s_hotel_name)){
                    $code_charter = $s_hotel_name;
                }else {
                    $pin = new \Common\Lib\Pin();
                    $obj_pin = new \Overtrue\Pinyin\Pinyin();
                    $code_charter = $obj_pin->abbr($s_hotel_name);
                    $code_charter  = strtolower($code_charter);
                    if(strlen($code_charter)==1){
                        $code_charter .=$code_charter;
                    }
                }
                $code_charter  = strtolower($code_charter);
                $m_hotel_invite_code = new \Admin\Model\HotelInviteCodeModel();
                $invite_code = '';
                $flag = 0;
                while ($flag <20){
                    $code_num = generate_code(6);
                    $invite_code = $code_charter.$code_num;
                    $where = array('code'=>$invite_code);
                    $nums = $m_hotel_invite_code->countNums($where);
                    if(empty($nums)){
                        break;
                    }
                    $flag ++;
                }
                $invite_data = array('code'=>$invite_code,'hotel_id'=>$merchant_info['hotel_id'],'bind_mobile'=>$mobile,
                    'bind_time'=>date('Y-m-d H:i:s'),'type'=>2,'creator_id'=>$sysuser_id,'state'=>1);
                $ret = $m_hotel_invite_code->addInfo($invite_data);
                if($ret){
                    $add_info['code'] = $invite_code;
                }
            }else{
                $invite_code = $merchant_info['code'];
            }

            $sms_config = C('ALIYUN_SMS_CONFIG');
            $alisms = new \Common\Lib\AliyunSms();
            $params = array('hotel_name'=>$res_hotel['name'],'code'=>$invite_code);
            $template_code = $sms_config['merchant_login_invite_code'];
            $alisms::sendSms($mobile,$params,$template_code);
        }
        $m_merchant->updateData(array('id'=>$merchant_id),$add_info);

        $m_merchantstaff = new \Admin\Model\Integral\StaffModel();
        $res_staff = $m_merchantstaff->getInfo(array('merchant_id'=>$merchant_id));
        if(!empty($res_staff)){
            $m_merchantstaff->updateData(array('merchant_id'=>$merchant_id),array('status'=>$status));
        }
        $this->output('修改成功','merchant/merchantlist');
    }


    public function getServiceByModelid(){
        $model_id = I('post.model_id',0,'intval');
        $data = array();
        if($model_id){
            $m_servicemodel = new \Admin\Model\Integral\ServiceMxModel();
            $res_service = $m_servicemodel->getInfo(array('id'=>$model_id));
            $service_ids = json_decode($res_service['service_ids'],true);
            if(!empty($service_ids)){
                $m_service = new \Admin\Model\Integral\IntegralServiceModel();
                $fields = 'id,name,type';
                $where = array('status'=>1);
                $where['id'] = array('in',$service_ids);
                $res_service = $m_service->getDataList($fields,$where,'id desc');
                $base = $values = array();
                foreach ($res_service as $v){
                    if($v['type']==1){
                        $base[] = $v['name'];
                    }else{
                        $values[] = $v['name'];
                    }
                }
                $data['base'] = join('、',$base);
                $data['values'] = join('、',$values);
            }
        }
        echo json_encode($data);
    }




    private function getOpuser($maintainer_id=0){
        $m_opuser_role = new \Admin\Model\OpuserroleModel();
        $fields = 'a.user_id uid,user.remark ';
        $where = array('state'=>1,'role_id'=>1);
        $res_users = $m_opuser_role->getAllRole($fields,$where,'' );

        $opusers = array();
        foreach($res_users as $v){
            $uid = $v['uid'];
            $remark = $v['remark'];
            if($uid==$maintainer_id){
                $select = 'selected';
            }else{
                $select = '';
            }
            $firstCharter = getFirstCharter(cut_str($remark, 1));
            $opusers[$firstCharter][] = array('uid'=>$uid,'remark'=>$remark,'select'=>$select);
        }
        ksort($opusers);
        return $opusers;
    }

}