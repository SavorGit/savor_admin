<?php
namespace Smallapp\Controller;
use Admin\Controller\BaseController ;
/**
 * @desc 配置管理
 *
 */
class GlobalconfigController extends BaseController {

    public function configdata(){
        $m_sys_config = new \Admin\Model\SysConfigModel();
        $where = array();
        $where['config_key'] = array('in',array('distribution_profit','hotellottery_people_num','seckill_roll_content','vip_coupons','exchange_money'));
        $volume_arr = $m_sys_config->getList($where);
        $info = array();
        $now_roll_content = array();
        foreach($volume_arr as $v){
            $config_value = $v['config_value'];
            if($v['config_key']=='seckill_roll_content'){
                $d_roll_content = json_decode($config_value,true);
                foreach ($d_roll_content as $ck=>$cv){
                    if(!empty($cv)){
                        $now_roll_content[$ck+1] = $cv;
                    }
                }
                $config_value = $now_roll_content;
            }
            $info[$v['config_key']] = $config_value;
        }

        $roll_contents = array();
        $roll_num = 3;
        for($i=1;$i<=$roll_num;$i++){
            $rinfo = array('id'=>$i,'content'=>'');
            if(isset($now_roll_content[$i])){
                $rinfo['content'] = $now_roll_content[$i];
            }
            $roll_contents[] = $rinfo;
        }
        $info['seckill_roll_content'] = $roll_contents;

        $coupon_ids = array();
        if(!empty($info['vip_coupons'])){
            $coupon_ids = json_decode($info['vip_coupons'],true);
        }
        $m_coupons = new \Admin\Model\Smallapp\CouponModel();
        $res_coupons = $m_coupons->getDataList('id,name,money,min_price',array('status'=>1,'type'=>2),'id desc');
        $coupons1 = $coupons2 = $coupons3 = array();
        foreach ($res_coupons as $k=>$v){
            $v['name'] = $v['name']."({$v['money']}元-满{$v['min_price']}可用)";

            if(isset($coupon_ids[1])){
                $select = '';
                if(in_array($v['id'],$coupon_ids[1])){
                    $select = 'selected';
                }
                $v['select'] = $select;
                $coupons1[]=$v;
            }
            if(isset($coupon_ids[2])){
                $select = '';
                if(in_array($v['id'],$coupon_ids[2])){
                    $select = 'selected';
                }
                $v['select'] = $select;
                $coupons2[]=$v;
            }

            if(isset($coupon_ids[3])){
                $select = '';
                if(in_array($v['id'],$coupon_ids[3])){
                    $select = 'selected';
                }
                $v['select'] = $select;
                $coupons3[]=$v;
            }
        }

        $this->assign('coupons1',$coupons1);
        $this->assign('coupons2',$coupons2);
        $this->assign('coupons3',$coupons3);
        $this->assign('info',$info);
        $this->display('configdata');
    }

    public function editconfig(){
        $distribution_profit = I('post.distribution_profit',0);
        $hotellottery_people_num = I('post.hotellottery_people_num',0);
        $exchange_money = I('post.exchange_money',0);
        $seckill_roll_content = I('post.seckill_roll_content','');
        $coupon_ids1 = I('post.coupon_ids1','');
        $coupon_ids2 = I('post.coupon_ids2','');
        $coupon_ids3 = I('post.coupon_ids3','');

        $m_sys_config = new \Admin\Model\SysConfigModel();
        $data_distribution_profit = array('config_value'=>$distribution_profit);
        $rts = $m_sys_config->editData($data_distribution_profit, 'distribution_profit');

        $data_hotellottery_people_num = array('config_value'=>$hotellottery_people_num);
        $rts = $m_sys_config->editData($data_hotellottery_people_num, 'hotellottery_people_num');

        $data_exchange_money = array('config_value'=>$exchange_money);
        $rts = $m_sys_config->editData($data_exchange_money, 'exchange_money');

        $now_seckill_roll_content = array();
        if(!empty($seckill_roll_content)){
            foreach ($seckill_roll_content as $v){
                if(!empty($v)){
                    $now_seckill_roll_content[]=trim($v);
                }
            }
        }
        $data_seckill_roll_content = array('config_value'=>json_encode($now_seckill_roll_content));
        $rts = $m_sys_config->editData($data_seckill_roll_content, 'seckill_roll_content');

        $now_coupons = array('1'=>$coupon_ids1,'2'=>$coupon_ids2,'3'=>$coupon_ids3);
        $data_seckill_roll_content = array('config_value'=>json_encode($now_coupons));
        $rts = $m_sys_config->editData($data_seckill_roll_content, 'vip_coupons');

        $sys_list = $m_sys_config->getList(array('status'=>1));
        $redis  =  \Common\Lib\SavorRedis::getInstance();
        $redis->select(12);
        $cache_key = C('SYSTEM_CONFIG');
        $redis->set($cache_key, json_encode($sys_list));
        $this->output('操作成功','globalconfig/configdata');
    }

}