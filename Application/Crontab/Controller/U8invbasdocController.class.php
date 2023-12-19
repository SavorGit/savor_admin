<?php
namespace Crontab\Controller;
use Think\Controller;

class U8invbasdocController extends Controller{
    
    //存货基本档案
    public function saveGoods(){
        $m_goods = new \Admin\Model\FinanceGoodsModel();
        $u8 = new \Common\Lib\U8cloud();
        $where = [];
        $where['status'] =1;
        $goods_list = $m_goods->getAllData('id,name,brand_id,series_id,u8_pk_id',$where);
        foreach($goods_list as $key=>$v){
            
            $params = [];
            $data   = [];
            
            $data['parentvo']['invcode'] = $v['id'];
            $data['parentvo']['invname'] = $v['name'];
            if($v['series_id']<10){
                $data['parentvo']['pk_invcl'] = '0'.$v['series_id'];       //存货分类编码
            }else {
                $data['parentvo']['pk_invcl'] = $v['series_id'];           //存货分类编码
            }
            $data['parentvo']['pk_measdoc']   = '01';    //计量单位
            
            if(empty($v['u8_pk_id'])){//新增
                
                $data['parentvo']['pk_taxitems']  = '201';    //税率
                $data['parentvo']['invspec']      = '';
                $data['parentvo']['invmnecode']   = '';
                $data['childrenvo'] = [];
                $params['invbasdoc'][] = $data;
                
                
                $ret = $u8->addGoods($params);
                $result = json_decode($ret['result'],true);
                $status = $result['status'];
                if($status=='success'){//同步成功
                    $ret_data = json_decode($result['data'],true);
                    $map  = [];
                    $map['id'] = $v['id'];
                    $info = [];
                    $info['u8_pk_id'] = $ret_data[0]['parentvo']['pk_invbasdoc'];
                    $m_goods->updateData($map, $info);
                    
                }
                
            }else {//编辑
                
                $childrenvo = [];
                $childrenvo['mainmeasrate'] = '1.00';
                $childrenvo['pk_measdoc']   = '01'; //计量单位
                $data['childrenvo'][]= $childrenvo;
                
                $params['invbasdoc'][] = $data;
                $ret = $u8->editGoods($params);
                
            }
        }
        echo date('Y-m-d H:i:s').' OK';
    }
}