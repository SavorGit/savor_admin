<?php
namespace Crontab\Controller;
use Think\Controller;

class U8accsubjController extends Controller{
    
    //新增修改
    public function saveAccsubj(){
        
        $u8 = new \Common\Lib\U8cloud();
        $m_brand = new \Admin\Model\FinanceBrandModel();
        
        $where['status'] = 1;
        $brand_list = $m_brand->getAllData('id,name brand_name,u8_pk_id',$where);
        
        //print_r($brand_list);exit;
        foreach($brand_list as $key=>$v){
            
            $data = [];
            $params = [];
            
            if(empty($v['u8_pk_id'])){//新增 品牌会计科目
                $data['accsubjParentVO']['pk_corp']      = '0001'; //公司编码  0001集团 由于本接口只支持集团新增，所以这里固定填0001
                $data['accsubjParentVO']['pk_glorgbook'] = '0001'; //会计主体账簿 0001集团 由于本接口只支持集团新增，所以这里固定填0001
                $data['accsubjParentVO']['pk_subjscheme']= '0001'; //科目方案编码
                $data['accsubjParentVO']['pk_subjtype']  = '00010000000000000001';  //科目类型  选择资产
                $data['accsubjParentVO']['cashbankflag'] = 0;      //现金分类 0-其他;1-现金科目;2-银行科目;3-现金等价物;
                $data['accsubjParentVO']['beginyear'] = '2023';
                
                if($v['id']<10){
                    $data['accsubjParentVO']['subjcode']     = '1405'.'0'.$v['id'];
                }else {
                    $data['accsubjParentVO']['subjcode']     = '1405'.$v['id'];
                }
                $data['accsubjParentVO']['subjname'] = $v['brand_name'];
                
                
                
                $params['billvo'] = $data;
                $ret = $u8->addAccsubj($params);
                
                $result = $ret['result'];
                $result = json_decode($result,true);
                if($result['status']=='success'){//新增成功
                    
                    $map = [];
                    $info= [];
                    $map['id'] = $v['id'];
                    $ret_data = json_decode($result['data'],true);
                    
                    $info['u8_pk_id'] = $ret_data[0]['accsubjParentVO']['pk_accsubj'];
                    $rts = $m_brand->updateData($map, $info);
                }
            }else {//编辑品牌会计科目
                $data['accsubjParentVO']['pk_accsubj'] = $v['u8_pk_id'];
                $data['accsubjParentVO']['subjname']   = $v['brand_name'];
                $params['billvo'] = $data;
                $ret = $u8->editAccsubj($params);
                
            }
            
        }
        
        $m_goods = new \Admin\Model\FinanceGoodsModel();
        $where = [];
        $where['a.status'] = 1;
        $where['brand.status'] = 1;
        
        $goods_list = $m_goods->alias('a')
                ->join('savor_finance_brand brand on a.brand_id=brand.id','left')
                ->field('a.id,a.name,a.brand_id,a.u8_subjcode,brand.u8_pk_id')
                ->where($where)
                ->select();
        
        foreach($goods_list as $key=>$v){
            
            $params = [];
            $data   = [];
            if(empty($v['u8_subjcode'])){//新增
                $data['accsubjParentVO']['pk_corp']      = '0001'; //公司编码  0001集团 由于本接口只支持集团新增，所以这里固定填0001
                $data['accsubjParentVO']['pk_glorgbook'] = '0001'; //会计主体账簿 0001集团 由于本接口只支持集团新增，所以这里固定填0001
                $data['accsubjParentVO']['pk_subjscheme']= '0001'; //科目方案编码
                $data['accsubjParentVO']['pk_subjtype']  = '00010000000000000001';  //科目类型  选择资产
                $data['accsubjParentVO']['cashbankflag'] = 0;      //现金分类 0-其他;1-现金科目;2-银行科目;3-现金等价物;
                $data['accsubjParentVO']['beginyear'] = '2023';
                
                $brand_id = $v['brand_id'];
                if($brand_id<10){
                    $brand_id = '0'.$brand_id;
                }
                
                if($v['id']<10){
                    $data['accsubjParentVO']['subjcode']     = '1405'.$brand_id.'0'.$v['id'];
                }else {
                    $data['accsubjParentVO']['subjcode']     = '1405'.$brand_id.$v['id'];
                }
                $data['accsubjParentVO']['subjname'] = $v['name'];
                $params['billvo'] = $data;
                $ret = $u8->addAccsubj($params);
                $result = $ret['result'];
                $result = json_decode($result,true);
                if($result['status']=='success'){//新增成功
                    
                    $map = [];
                    $info= [];
                    $map['id'] = $v['id'];
                    $ret_data = json_decode($result['data'],true);
                    
                    $info['u8_subjcode'] = $ret_data[0]['accsubjParentVO']['pk_accsubj'];
                    $rts = $m_goods->updateData($map, $info);
                }
            }else {//编辑
                $data['accsubjParentVO']['pk_accsubj'] = $v['u8_pk_id'];
                $data['accsubjParentVO']['subjname']   = $v['name'];
                $params['billvo'] = $data;
                $ret = $u8->editAccsubj($params);
            }
        }
        echo date('Y-m-d H:i:s').' OK';
    }
    
    public function alterAccsubj(){
        
        $u8 = new \Common\Lib\U8cloud();
        $m_brand = new \Admin\Model\FinanceBrandModel();
        
        $where['status'] = 1;
        $where['u8_pk_id'] = array('neq','');
        $brand_list = $m_brand->getAllData('id,name brand_name,u8_pk_id',$where);
        
        //print_r($brand_list);exit;
        foreach($brand_list as $key=>$v){
            
            $data = [];
            $params = [];
            $data['alterVO']['alterSubjPk'] = $v['u8_pk_id'];
            $data['alterVO']['alterType']   = 0;
            $data['alterVO']['alterYear']   = '2023';
            
            $params['billvo'][] = $data;
            $ret = $u8->alterAccsbj($params);
            $result = $ret['result'];
            $result = json_decode($result,true);
            print_r($result);exit;
        }
    }
    
}