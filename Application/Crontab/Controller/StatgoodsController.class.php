<?php
namespace Crontab\Controller;
use Think\Controller;

class StatgoodsController extends Controller{

    public function trenddata(){
        $model = M();
        $awhere = "is_in_hotel=1 and id not in (246)";//246深圳(无售酒),1北京,9上海,236广州,248佛山
        $sql_area = "select id as area_id,region_name as area_name from savor_area_info where {$awhere} order by id asc ";
        $res_area = $model->query($sql_area);

        $fields = 'goods.id as goods_id,goods.name as goods_name,brand.name as brand_name';
        $where = array('brand.id'=>2);
        $m_goods = new \Admin\Model\FinanceGoodsModel();
        $res_goods = $m_goods->alias('goods')
            ->join('savor_finance_brand brand on goods.brand_id=brand.id','left')
            ->field($fields)
            ->where($where)
            ->order('goods.id asc')
            ->select();
        $goods_list = array();
        foreach ($res_goods as $v){
            $goods_list[$v['goods_id']]=$v;
        }
        $goods_ids = array_keys($goods_list);
        $goods_ids_str = join(',',$goods_ids);
        $all_weeks = $this->getyearweeks();
        $m_qrcode_content = new \Admin\Model\FinanceQrcodeContentModel();
        $m_static_goodstrend = new \Admin\Model\StaticGoodstrendModel();
        $m_static_goodstrend_area = new \Admin\Model\StaticGoodstrendAreadetailModel();

        foreach ($all_weeks as $k=>$v){
            if($k==0){
                continue;
            }
            $week_number = $k;
            $week_start_date = $v['start'];
            $week_end_date = $v['end'];
            if($week_number==42){
                $week_end_date = '2023-10-18';
            }

            $sql_purchase = "select a.goods_id,sum(a.total_amount) as total_amount from savor_finance_purchase_detail as a left join savor_finance_purchase as p 
            on a.purchase_id=p.id where p.purchase_date>='$week_start_date' and p.purchase_date<='$week_end_date'
            and a.goods_id in ($goods_ids_str) and a.status=1 group by a.goods_id";
            $res_purchase = $model->query($sql_purchase);
            $all_purchase_nums = array();
            if(!empty($res_purchase)){
                foreach ($res_purchase as $pv){
                    $all_purchase_nums[$pv['goods_id']]=$pv['total_amount'];
                }
            }
            $week_start_date_time = "$week_start_date 00:00:00";
            $week_end_date_time = "$week_end_date 23:59:59";
            $all_groupby_nums = array();
            $sql_groupby = "select idcode,area_id,goods_id from savor_finance_sale where type in (2,4) and goods_id in (1,6,45,49) and 
            add_time>='$week_start_date_time' and add_time<='$week_end_date_time' order by id desc";
            $res_groupby = $model->query($sql_groupby);
            if(!empty($res_groupby)){
                $tmp_groupby = array();
                foreach ($res_groupby as $gbv){
                    $all_idcodes = explode("\n",$gbv['idcode']);
                    $tmp_groupby[$gbv['goods_id']][]=count($all_idcodes);
                }
                foreach ($tmp_groupby as $tggk=>$tggv){
                    $all_groupby_nums[$tggk] = array_sum($tggv);
                }
            }

            foreach ($goods_list as $gv){
                $goods_id = $gv['goods_id'];
                $goods_name = $gv['goods_name'];
                $purchase_num = isset($all_purchase_nums[$goods_id])?$all_purchase_nums[$goods_id]:0;
                $groupby_num = isset($all_groupby_nums[$goods_id])?$all_groupby_nums[$goods_id]:0;

                $zzc_stock_allnum=$qzc_stock_allnum=$qzc_hotel_allnum=$qzc_wo_allnum=0;
                $all_area_data = array();
                foreach ($res_area as $arv){
                    $area_id = $arv['area_id'];
                    $area_name = $arv['area_name'];

                    $sql_stock = "select a.idcode,a.type,a.amount,a.total_amount from savor_finance_stock_record as a left join savor_finance_stock as stock on a.stock_id=stock.id
                    where stock.area_id=$area_id and a.goods_id=$goods_id and a.add_time<='$week_end_date_time' and a.id in (
                    select max(id) as last_id from savor_finance_stock_record where goods_id=$goods_id and add_time<='$week_end_date_time'
                    group by idcode) and a.type=3";
                    $res_stock = $model->query($sql_stock);
                    $zzc_stock_num=0;
                    $uppack_codes=array();
                    foreach ($res_stock as $sv){
                        if(abs($sv['amount'])==abs($sv['total_amount'])){
                            $uppack_codes[]="'{$sv['idcode']}'";
                        }else{
                            $qrcontent = decrypt_data($sv['idcode'],false);
                            $qr_id = intval($qrcontent);
                            $res_scodes = $m_qrcode_content->getDataList('*',array('parent_id'=>$qr_id),'id desc');
                            $now_scodes = array();
                            foreach ($res_scodes as $nsc){
                                $sv_code = encrypt_data($nsc['id']);
                                $uppack_codes[]="'{$sv_code}'";
                                $now_scodes[]="'$sv_code'";
                            }
                            $now_scodes_str = join(',',$now_scodes);
                            $sql_pcode = "select count(a.idcode) as num from savor_finance_stock_record as a left join savor_finance_stock as stock on a.stock_id=stock.id
                            where stock.area_id=$area_id and a.goods_id=$goods_id and a.add_time<='$week_end_date_time' and a.id in (
                            select max(id) as last_id from savor_finance_stock_record where goods_id=$goods_id and add_time<='$week_end_date_time'
                            group by idcode) and a.idcode in ($now_scodes_str)";
                            $res_pcode = $model->query($sql_pcode);
                            if(empty($res_pcode[0]['num'])){
                                $zzc_stock_num+=6;
                            }
                        }
                    }
                    if(!empty($uppack_codes)){
                        $uppack_codes_str = join(',',$uppack_codes);
                        $sql_stock_ucode = "select count(a.idcode) as num from savor_finance_stock_record as a left join savor_finance_stock as stock on a.stock_id=stock.id
                        where stock.area_id=$area_id and a.goods_id=$goods_id and a.add_time<='$week_end_date_time' and a.id in (
                        select max(id) as last_id from savor_finance_stock_record where goods_id=$goods_id and add_time<='$week_end_date_time'
                        group by idcode) and a.type=3 and a.idcode in ($uppack_codes_str)";
                        $res_stock_ucode = $model->query($sql_stock_ucode);
                        if(!empty($res_stock_ucode[0]['num'])){
                            $zzc_stock_num+=$res_stock_ucode[0]['num'];
                        }
                    }

                    $sql_stock_group = "select a.type,sum(a.total_amount) as total_amount from savor_finance_stock_record as a left join savor_finance_stock as stock on a.stock_id=stock.id
                    where stock.area_id=$area_id and a.goods_id=$goods_id and a.add_time<='$week_end_date_time' and a.id in (
                    select max(id) as last_id from savor_finance_stock_record where goods_id=$goods_id and add_time<='$week_end_date_time'
                    group by idcode) group by a.type";
                    $res_stock_group = M()->query($sql_stock_group);
                    $qzc_stock_num=0;
                    foreach ($res_stock_group as $sgv){
                        if(in_array($sgv['type'],array(2,4,5))){
                            $qzc_stock_num+=abs($sgv['total_amount']);
                        }
                        if($sgv['type']==1){
                            $zzc_stock_num+=abs($sgv['total_amount']);
                        }
                    }

                    $sql_qzchotel = "select COUNT(DISTINCT stock.hotel_id) as hotel_num from savor_finance_stock_record as a left join savor_finance_stock as stock on a.stock_id=stock.id
                    where stock.area_id=$area_id and a.goods_id=$goods_id and a.id in (
                    select max(id) as last_id from savor_finance_stock_record where goods_id=$goods_id and add_time>='$week_start_date_time' and add_time<='$week_end_date_time'
                    group by idcode) and stock.hotel_id>0 and a.type in (2,4,5,7)";
                    $res_qzchotel = $model->query($sql_qzchotel);
                    $qzc_hotel_num=0;
                    if(!empty($res_qzchotel[0]['hotel_num'])){
                        $qzc_hotel_num=$res_qzchotel[0]['hotel_num'];
                    }

                    $sql_qzcwo = "select COUNT(a.id) as num from savor_finance_stock_record as a left join savor_finance_stock as stock on a.stock_id=stock.id
                    where stock.area_id=$area_id and a.goods_id=$goods_id and a.add_time>='$week_start_date_time' and a.add_time<='$week_end_date_time' 
                    and a.type=7 and a.wo_reason_type=1 and a.wo_status in (1,2,4)";
                    $res_qzcwo = $model->query($sql_qzcwo);
                    $qzc_wo_num=0;
                    if(!empty($res_qzcwo[0]['num'])){
                        $qzc_wo_num = $res_qzcwo[0]['num'];
                    }

                    $area_data = array('area_id'=>$area_id,'area_name'=>$area_name,'zzc_stock_num'=>$zzc_stock_num,
                        'qzc_stock_num'=>$qzc_stock_num,'qzc_hotel_num'=>$qzc_hotel_num,'qzc_wo_num'=>$qzc_wo_num);
                    $all_area_data[]=$area_data;

                    $zzc_stock_allnum+=$zzc_stock_num;
                    $qzc_stock_allnum+=$qzc_stock_num;
                    $qzc_hotel_allnum+=$qzc_hotel_num;
                    $qzc_wo_allnum+=$qzc_wo_num;
                }

                $gdata = array('goods_id'=>$goods_id,'goods_name'=>$goods_name,'week_number'=>$week_number,'week_start_date'=>$week_start_date,'week_end_date'=>$week_end_date,
                    'purchase_num'=>$purchase_num,'zzc_stock_allnum'=>$zzc_stock_allnum,'qzc_stock_allnum'=>$qzc_stock_allnum,
                    'qzc_hotel_allnum'=>$qzc_hotel_allnum,'qzc_wo_allnum'=>$qzc_wo_allnum,'groupby_num'=>$groupby_num
                );
                $goodstrend_id = $m_static_goodstrend->add($gdata);
                $add_area_data = array();
                foreach ($all_area_data as $v){
                    $v['goods_id'] = $goods_id;
                    $v['goodstrend_id'] = $goodstrend_id;
                    $add_area_data[]=$v;
                }
                $m_static_goodstrend_area->addAll($add_area_data);

                echo "goods_id:$goods_id,week_number:$week_number,$week_start_date-$week_end_date ok \r\n";
            }
        }
    }

    private function getyearweeks(){
        $currentDate = new \DateTime();
        $startDate = new \DateTime('2023-01-01');
        $endDate = $currentDate;
        $weekRanges = array();

        while ($startDate < $endDate) {
            // 找到自然周的结束日期（下一个周一的前一天）
            $weekEndDate = clone $startDate;
            $weekEndDate->modify('next Monday');
            $weekEndDate->modify('-1 day');
            // 存储自然周的开始和结束日期
            $weekRanges[] = array(
                'start' => $startDate->format('Y-m-d'),
                'end' => $weekEndDate->format('Y-m-d')
            );
            // 移动到下一周的开始日期
            $startDate->modify('next Monday');
        }
        return $weekRanges;
    }
}
