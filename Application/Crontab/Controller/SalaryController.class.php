<?php
namespace Crontab\Controller;
use Think\Controller;

class SalaryController extends Controller{

    public function calculateBdac(){
        $all_job = C('JOB_DEPARTMENT_LIST');
        $group_task_percent = 0.15;
        $group_up_per_money = 10;
        $begin_month = '2024-03-01';
        $jt_lastmonth = date('Y-m-01',strtotime('-6 month'));
        if($jt_lastmonth<$begin_month){
            $jt_lastmonth = $begin_month;
        }
        $jt_lastmonth_time = "$jt_lastmonth 00:00:00";
        $all_static_month = array();
        $m_staff_config = new \Admin\Model\StaffPerformanceConfigModel();
        for($i=6;$i<=1;$i--){
            $static_month = date('Ym',strtotime("-$i month"));
            $month_sdate = date('Y-m-01',strtotime("-$i month"));
            $month_edate = date('Y-m-t',strtotime("-$i month"));
            if($month_sdate<$begin_month){
                break;
            }
            $config = $m_staff_config->getInfo(array('add_month'=>$static_month));
            if(!empty($config)){
                $config['payback_day_commission'] = json_decode($config['payback_day_commission'],true);
            }
            $all_static_month[$static_month]=array('month'=>$static_month,'config'=>$config,'sdate'=>$month_sdate,'edate'=>$month_edate);
        }

        $static_month = date('Ym',strtotime('-1 month'));
        $month_sdate = date('Y-m-01',strtotime('-1 month'));
        $month_edate = date('Y-m-t',strtotime('-1 month'));
        $month_stime = "$month_sdate 00:00:00";
        $month_etime = "$month_edate 23:59:59";
        $reward_config = $all_static_month[$static_month]['config'];
        $m_staff_saletask = new \Admin\Model\StaffPerformanceSaletaskModel();
        $res_staff_sale_task = $m_staff_saletask->getDataList('*',array('add_month'=>$static_month),'id desc');
        $staff_sale_task = array();
        foreach ($res_staff_sale_task as $v){
            $staff_sale_task[$v['staff_id']]=array('id'=>$v['id'],'job_id'=>$v['job_id'],'deparment_id'=>$v['deparment_id'],
                'team_sale_num'=>$v['team_sale_num'],'sale_num'=>$v['sale_num'],'team_cost'=>$v['team_cost'],'cost'=>$v['cost']);
        }
        $field = 'id,remark as real_name,telephone,email,deparment_id,job_id,salary,entry_time,out_time';
        $job_ids = '1,2';//1ac 2bd
        $sql = "select {$field} from savor_sysuser where job_id in ($job_ids) and ((status=1) or (status=2 and out_time>='{$month_sdate}' and out_time<='{$month_edate}'))";
        $m_user = new \Admin\Model\UserModel();
        $res_user = $m_user->query($sql);
        $m_sale = new \Admin\Model\FinanceSaleModel();
        $m_salepaymentrecord = new \Admin\Model\FinanceStockPaymentRecordModel();
        $m_staff_accruesales = new \Admin\Model\StaffAccrueSalesModel();
        foreach ($res_user as $v){
            $residenter_id = $v['id'];
            $residenter_name = $v['real_name'];
            $sale_task = $staff_sale_task[$residenter_id];
            $staff_performance_saletask = $sale_task['id'];
            if(empty($sale_task)){
                continue;
            }

            $task_sale_num = $sale_task['sale_num'];
            $task_group_sale_num = round($task_sale_num*$group_task_percent);
            $task_wo_sale_num = $task_sale_num - $task_group_sale_num;

            //团购
            $salewhere = array('maintainer_id'=>$residenter_id,'type'=>4);
            $salewhere['add_time'] = array(array('egt',$month_stime),array('elt'=>$month_etime));
            $res_groupsale = $m_sale->getAllData('sum(num) as sale_num',$salewhere);
            $group_num = intval($res_groupsale[0]['sale_num']);//个人团购销量
            $group_up_money = 0;
            if($group_num>$task_group_sale_num){
                $group_sale_num = $task_group_sale_num;//个人团购计入任务数
                $group_up_num = $group_num - $task_group_sale_num;//个人团购超额销量
                $group_up_money = $group_up_num*$group_up_per_money;//个人团购提成
            }else{
                $group_sale_num = $group_num;
                $group_up_num = 0;
            }

            //核销售卖
            $wo_where = array('a.residenter_id'=>$residenter_id,'a.type'=>1,'record.wo_reason_type'=>1,'record.wo_status'=>2);
            $wo_where['a.add_time'] = array(array('egt',$month_stime),array('elt'=>$month_etime));
            $res_wosale = $m_sale->getSaleStockRecordList('sum(a.num) as sale_num',$wo_where,'','');
            $wo_sale_num = intval($res_wosale[0]['sale_num']);//个人餐厅核销数
            $all_sale_num = $wo_sale_num+$group_num;//个人实际总销量
            $all_task_sale_num = $wo_sale_num+$group_sale_num;//个人完成任务总数

            $wo_where['a.ptype']=1;
            $res_wosale_data = $m_sale->getSaleStockRecordList('a.id,a.add_time',$wo_where,'','');
            $sale_data = array();
            foreach ($res_wosale_data as $wdv){
                $sale_data[$wdv['id']] = date('Y-m-d',strtotime($wdv['add_time']));
            }
            $jt_sales = array();
            $wo_where['a.ptype']=0;
            $res_wojtsale_data = $m_sale->getSaleStockRecordList('a.id',$wo_where,'','');
            foreach ($res_wojtsale_data as $jtdv){
                $jt_sales[$jtdv['id']] = array('staff_id'=>$residenter_id,'add_month'=>$static_month,'sale_id'=>$jtdv['id']);
            }
            $repay_sale_num = count($sale_data);//个人当月回款瓶数
            $wo_up_num = 0;//个人餐厅核销超额销量
            $wo_up_money = 0;//个人当月回款提成
            $jt_num = 0;//个人当月计提瓶数
            $jt_money = 0;//个人当月计提奖金
            $repay_coefficient = 0;//个人回款系数
            if($all_task_sale_num>$task_sale_num){
                $wo_up_num = $all_task_sale_num-$task_sale_num;

                if($repay_sale_num>0){
                    $jt_num = $wo_sale_num - $repay_sale_num;
                    $sale_ids = array_keys($sale_data);
                    $pwhere = array('a.sale_id'=>array('in',$sale_ids));
                    $pwhere['p.pay_time'] = array(array('egt',$month_sdate),array('elt'=>$month_edate));
                    $res_payrecord = $m_salepaymentrecord->alias('a')->field('sum(a.pay_money) as total_pay_money')
                        ->join('savor_finance_sale_payment p on a.sale_payment_id=p.id','left')
                        ->where($pwhere)->select();
                    $total_pay_money = $res_payrecord[0]['total_pay_money']>0?$res_payrecord[0]['total_pay_money']:0;
                    $res_payrecord = $m_salepaymentrecord->alias('a')->field('a.sale_id,a.pay_money,p.pay_time')
                                    ->join('savor_finance_sale_payment p on a.sale_payment_id=p.id','left')
                                    ->where($pwhere)->select();
                    $repay_day = 0;
                    foreach ($res_payrecord as $prv){
                        $now_pay_day = round((strtotime($prv['pay_time'])-strtotime($sale_data[$prv['sale_id']]))/86400);
                        $repay_day+= $now_pay_day*($prv['pay_money']/$total_pay_money);
                    }

                    foreach ($reward_config['payback_day_commission'] as $rcv){
                        if($repay_day>=$rcv['min'] && $repay_day<=$rcv['max']){
                            $repay_coefficient = $rcv['percent']/100;
                            break;
                        }
                    }
                    //奖励金额=超额销量*(回款总数/总共销量)*基准单瓶奖励*系数*回款提成系数
                    $wo_up_money = $wo_up_num*($repay_sale_num/$all_task_sale_num)*$reward_config['per_botte_award']*$reward_config['award_coefficient']*$repay_coefficient;
                    $jt_money = $wo_up_num*($jt_num/$all_task_sale_num)*$reward_config['per_botte_award']*$reward_config['award_coefficient']*$repay_coefficient;

                    if(!empty($jt_sales)){
                        $m_staff_accruesales->addAll(array_values($jt_sales));
                    }
                }
            }
            $money = $wo_up_money;//个人当月实际发放提成总金额

            $wo_where = array('a.residenter_id'=>$residenter_id,'a.type'=>1,'a.ptype'=>0,'record.wo_reason_type'=>1,'record.wo_status'=>2);
            $wo_where['a.add_time'] = array('egt',$jt_lastmonth_time);
            $res_wosale = $m_sale->getSaleStockRecordList('sum(a.num) as sale_num',$wo_where,'','');
            $all_jt_num = intval($res_wosale[0]['sale_num']);//个人计提剩余瓶数

            $month_datas = array();
            $all_jt_repay_num = 0;//个人计提回款瓶数
            $all_jt_repay_money = 0;//个人计提回款奖金
            foreach ($all_static_month as $amv){
                $h_month = $amv['month'];
                $month_config = $amv['config'];
                if($h_month==$static_month){
                    $jt_repay_num = 0;
                    $jt_repay_money = 0;
                }else{
                    $jt_repay_num = 0;
                    $jt_repay_money = 0;
                    $month_staff_sale_task = $m_staff_saletask->getInfo(array('add_month'=>$h_month,'staff_id'=>$residenter_id));
                    if($month_staff_sale_task['wo_up_num']>0){
                        $res_jt_sales = $m_staff_accruesales->getAllData('sale_id',array('staff_id'=>$residenter_id,'add_month'=>$h_month),'id desc');
                        $jt_sale_ids = array();
                        foreach ($res_jt_sales as $jtsv){
                            $jt_sale_ids[]=$jtsv['sale_id'];
                        }
                        if(!empty($jt_sale_ids)){
                            $jtwhere = array('id'=>array('in',$jt_sale_ids),'residenter_id'=>$residenter_id,'type'=>1,'ptype'=>1);
                            $res_jtpaysale = $m_sale->getAllData('id,add_time',$jtwhere);
                            if(!empty($res_jtpaysale)){
                                $jt_repay_num = count($res_jtpaysale);
                                $monthjt_datas = array();
                                foreach ($res_jtpaysale as $jtp){
                                    $monthjt_datas[$jtp['id']]=$jtp['add_time'];
                                }
                                $monthpjtwhere = array('a.sale_id'=>array('in',$jt_sale_ids));
                                $monthpjtwhere['p.pay_time'] = array(array('egt',$month_sdate),array('elt'=>$month_edate));
                                $res_monthpayrecord = $m_salepaymentrecord->alias('a')->field('sum(a.pay_money) as total_pay_money')
                                    ->join('savor_finance_sale_payment p on a.sale_payment_id=p.id','left')
                                    ->where($monthpjtwhere)->select();
                                $total_month_pay_money = $res_monthpayrecord[0]['total_pay_money']>0?$res_monthpayrecord[0]['total_pay_money']:0;
                                $res_monthpayrecord = $m_salepaymentrecord->alias('a')->field('a.sale_id,a.pay_money,p.pay_time')
                                    ->join('savor_finance_sale_payment p on a.sale_payment_id=p.id','left')
                                    ->where($monthpjtwhere)->select();
                                $month_repay_day = 0;
                                foreach ($res_monthpayrecord as $monthprv){
                                    $now_month_pay_day = round((strtotime($monthprv['pay_time'])-strtotime($monthjt_datas[$monthprv['sale_id']]))/86400);
                                    $month_repay_day+= $now_month_pay_day*($monthprv['pay_money']/$total_month_pay_money);
                                }
                                $month_repay_coefficient = 0;//个人回款系数
                                foreach ($month_config['payback_day_commission'] as $rcv){
                                    if($month_repay_day>=$rcv['min'] && $month_repay_day<=$rcv['max']){
                                        $month_repay_coefficient = $rcv['percent']/100;
                                        break;
                                    }
                                }
                                $month_wo_up_num = $month_staff_sale_task['wo_up_num'];
                                $all_month_task_sale_num = $month_staff_sale_task['wo_sale_num']+$month_staff_sale_task['group_sale_num'];
                                $jt_repay_money = $month_wo_up_num*($jt_repay_num/$all_month_task_sale_num)*$month_config['per_botte_award']*$month_config['award_coefficient']*$month_repay_coefficient;
                            }
                        }
                    }
                }
                $all_jt_repay_num+=$jt_repay_num;
                $all_jt_repay_money+=$jt_repay_money;

                $month_datas[] = array($h_month,$jt_repay_num,$jt_repay_money);
            }
            $updata = array('all_sale_num'=>$all_sale_num,'wo_sale_num'=>$wo_sale_num,'wo_up_num'=>$wo_up_num,'group_num'=>$group_num,
                'group_sale_num'=>$group_sale_num,'group_up_num'=>$group_up_num,'repay_sale_num'=>$repay_sale_num,'repay_coefficient'=>$repay_coefficient,
                'group_up_money'=>$group_up_money,'wo_up_money'=>$wo_up_money,'jt_repay_num'=>$all_jt_repay_num,'jt_repay_money'=>$all_jt_repay_money,
                'jt_repay_data'=>json_encode($month_datas),'money'=>$money,'jt_num'=>$jt_num,'jt_money'=>$jt_money,'all_jt_num'=>$all_jt_num,
                'update_time'=>date('Y-m-d H:i:s')
            );
            $m_staff_saletask->updateData(array('id'=>$staff_performance_saletask),$updata);
            echo "id:$residenter_id,name:$residenter_name \r\n";
        }

        echo "static_month:$static_month ok \r\n";
    }
}
