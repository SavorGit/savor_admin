<?php
namespace Crontab\Controller;
use Think\Controller;

class U8custdocController extends Controller{

    public function syncdata(){
        $sync_time = date('Y-m-d H:i:s',time()-3600*4);
        $map_def1 = array('0'=>'HZCT','1'=>'GYSJ','2'=>'TGKH');//def1 0:餐厅、1:供应商、2:团购客户、3:其他

        $field = 'hotel.id,hotel.name,hotel.area_id,hotel.short_name,ext.u8_pk_id,0 as def1';
        $where = array('hotel.state'=>1,'hotel.flag'=>0,'ext.is_salehotel'=>1);
        $where['hotel.update_time'] = array('egt',$sync_time);
        $m_hotel = new \Admin\Model\HotelModel();
        $res_hotels = $m_hotel->getHotelDatas($field,$where,'hotel.id asc');
        $all_syncdata = array();
        if(empty($res_hotels)){
           echo "hzct nodata \r\n";
        }else{
            $all_syncdata = $res_hotels;
        }
        $m_user = new \Admin\Model\Smallapp\UserModel();
        $fields = "a.id,a.nickName,a.mobile,a.sale_uid,a.u8_pk_id,duser.level,duser.parent_id,duser.op_sysuser_id,2 as def1";
        $where = array('a.sale_uid'=>array('gt',0));
        $where['a.u8_pk_id'] = array('eq','');
        $where['a.mobile'] = array('neq','');
        $where['a.customer_time'] = array('egt',$sync_time);
        $res_user = $m_user->alias('a')
            ->join('savor_smallapp_distribution_user duser on a.sale_uid=duser.id','left')
            ->field($fields)
            ->where($where)
            ->order('a.id desc')
            ->select();
        if(empty($res_user)){
            echo "tgkh nodata \r\n";
        }else{
            $all_syncdata = array_merge($all_syncdata,$res_user);
        }

        $m_supplier = new \Admin\Model\FinanceSupplierModel();
        $field = 'id,name,city_id as area_id,u8_pk_id,1 as def1';
        $where = array('status'=>1,'city_id'=>array('gt',0));
        $where['update_time'] = array('egt',$sync_time);
        $res_supplier = $m_supplier->getDataList($field,$where,'id asc');
        if(empty($res_supplier)){
            echo "gysj nodata \r\n";
        }else{
            $all_syncdata = array_merge($all_syncdata,$res_supplier);
        }

        $u8cloud = new \Common\Lib\U8cloud();
        $m_hotel_ext = new \Admin\Model\HotelExtModel();
        $m_ops_staff = new \Admin\Model\OpsstaffModel();
        $m_duser = new \Admin\Model\Smallapp\DistributionUserModel();
        foreach ($all_syncdata as $v){
            $custcode = $map_def1["{$v['def1']}"];
            switch ($v['def1']){
                case 0:
                    if(empty($v['short_name'])){
                        $v['short_name'] = $v['name'];
                    }
                    break;
                case 1:
                    $v['short_name'] = $v['name'];
                    break;
                case 2:
                    if($v['parent_id']==0){
                        $op_sysuser_id = $v['op_sysuser_id'];
                    }else{
                        $res_duser = $m_duser->getInfo(array('id'=>$v['parent_id']));
                        $op_sysuser_id = $res_duser['op_sysuser_id'];
                    }
                    $awhere = array('sysuser_id'=>$op_sysuser_id);
                    $res_ops_area = $m_ops_staff->getAll('area_id',$awhere,0,1,'id desc');
                    $v['area_id'] = intval($res_ops_area[0]['area_id']);
                    $v['name'] = $v['mobile'];
                    $v['short_name'] = $v['name'];
                    break;
            }
            if($v['area_id']==0){
                echo "$custcode:{$v['id']},area_id:{$v['area_id']},error \r\n";
                continue;
            }
            $info = array(
                'parentvo'=>array('pk_areacl'=>"{$v['area_id']}",'custcode'=>"$custcode{$v['id']}",
                'custshortname'=>"{$v['short_name']}",'custname'=>"{$v['name']}",'def1'=>"{$v['def1']}")
            );
            if(!empty($v['u8_pk_id'])){
                $info['parentvo']['pk_cubasdoc'] = $v['u8_pk_id'];
            }
            $params = array(
                'cbdocvo'=>array($info)
            );
            $action = 'add';
            if(!empty($v['u8_pk_id'])){
                $resp_apidata = $u8cloud->editCustdoc($params);
                $action = 'edit';
            }else{
                $resp_apidata = $u8cloud->addCustdoc($params);
            }
            $res_data = json_decode($resp_apidata['result'],true);
            $res_u8data = json_decode($res_data['data'],true);
            if(empty($res_u8data[0]['parentvo']['pk_cubasdoc'])){
                echo "$custcode:{$v['id']},action:$action,error:{$resp_apidata['result']} \r\n";
                continue;
            }

            $parentvo = $res_u8data[0]['parentvo']['pk_cubasdoc'];
            switch ($v['def1']){
                case 0:
                    $m_hotel_ext->updateData(array('hotel_id'=>$v['id']),array('u8_pk_id'=>$parentvo));
                    break;
                case 1:
                    $m_supplier->updateData(array('id'=>$v['id']),array('u8_pk_id'=>$parentvo));
                    break;
                case 2:
                    $m_user->updateInfo(array('id'=>$v['id']),array('u8_pk_id'=>$parentvo));
                    break;
            }
            if($action=='add'){
                $assign_params = array('custbasvo'=>array(array('pk_corp'=>'02','custprop'=>'2','pk_cubasdoc'=>$parentvo)));
                $u8cloud->assignCustdoc($assign_params);
            }
            echo "$custcode:{$v['id']},action:$action,parentvo:{$parentvo} ok \r\n";
        }



    }
}
