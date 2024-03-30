<?php
namespace Dataexport\Controller;

class SalaryController extends BaseController{

    public function calculateBdac(){
        $file_name = I('filename','');
        $file_month = I('filemonth',0);
        $file_name = urldecode($file_name);

        $file_path = SITE_TP_PATH.'/Public/uploads/'.$file_name;
        vendor("PHPExcel.PHPExcel.IOFactory");
        vendor("PHPExcel.PHPExcel");
        $inputFileType = \PHPExcel_IOFactory::identify($file_path);
        $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($file_path);
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $m_staff_saletask = new \Admin\Model\StaffPerformanceSaletaskModel();
        for ($row = 2; $row <= $highestRow; $row++) {
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);

            $staff_id = intval($rowData[0][0]);
            $staff_name = trim($rowData[0][1]);
            $job_id = intval($rowData[0][2]);
            $job = trim($rowData[0][3]);
            $deparment_id = intval($rowData[0][4]);
            $deparment = trim($rowData[0][5]);
            $team_sale_num = intval($rowData[0][6]);
            $sale_num = intval($rowData[0][7]);
            $cost = intval($rowData[0][8]);
            $team_cost = intval($rowData[0][9]);
            $ssdata = array('staff_id'=>$staff_id,'staff_name'=>$staff_name,'job_id'=>$job_id,'job'=>$job,
                'deparment_id'=>$deparment_id,'deparment'=>$deparment,'team_sale_num'=>$team_sale_num,
                'sale_num'=>$sale_num,'cost'=>$cost,'team_cost'=>$team_cost,'add_month'=>$file_month
            );
            $m_staff_saletask->add($ssdata);
        }

        $group_task_percent = 0.15;
        $group_up_per_money = 10;
        $begin_month = '2024-03-01';
        $jt_lastmonth = date('Y-m-01',strtotime('-6 month'));
        if($jt_lastmonth<$begin_month){
            $jt_lastmonth = $begin_month;
        }
        $jt_lastmonth_time = "$jt_lastmonth 00:00:00";
        $m_area  = new \Admin\Model\AreaModel();
        $area_arr = $m_area->getAllArea();
        $all_area = array();
        foreach ($area_arr as $v){
            $all_area[$v['id']] = $v['region_name'];
        }
        $all_static_month = array();
        $m_staff_config = new \Admin\Model\StaffPerformanceConfigModel();

        /* 没到4月份需要暂时修改
        for($i=6;$i<=1;$i--){
            $static_month = date('Ym',strtotime("-$i month"));
            $month_sdate = date('Y-m-01',strtotime("-$i month"));
            $month_edate = date('Y-m-t',strtotime("-$i month"));
            if($month_sdate>=$begin_month){
                $config = $m_staff_config->getInfo(array('add_month'=>$static_month));
                if(!empty($config)){
                    $config['payback_day_commission'] = json_decode($config['payback_day_commission'],true);
                }
                $all_static_month[$static_month]=array('month'=>$static_month,'config'=>$config,'sdate'=>$month_sdate,'edate'=>$month_edate);
            }
        }
        $static_month = date('Ym',strtotime('-1 month'));
        $month_sdate = date('Y-m-01',strtotime('-1 month'));
        $month_edate = date('Y-m-t',strtotime('-1 month'));
        */

        //暂时使用
        $static_month = date('Ym',strtotime('2024-03-01 15:00:12'));
        $month_sdate = date('Y-m-01',strtotime('2024-03-01 15:00:12'));
        $month_edate = date('Y-m-t',strtotime('2024-03-01 15:00:12'));
        $config = $m_staff_config->getInfo(array('add_month'=>$static_month));
        if(!empty($config)){
            $config['payback_day_commission'] = json_decode($config['payback_day_commission'],true);
        }
        $all_static_month[$static_month]=array('month'=>$static_month,'config'=>$config,'sdate'=>$month_sdate,'edate'=>$month_edate);
        //end


        $month_stime = "$month_sdate 00:00:00";
        $month_etime = "$month_edate 23:59:59";
        $reward_config = $all_static_month[$static_month]['config'];
        $cache_file_key = 'cronscript:salaryexcel'.$static_month;
        $cache_salary_acbd_self_key = 'cronscript:salary_acbd_self'.$static_month;
        $cache_salary_acbd_team_key = 'cronscript:salary_acbd_team'.$static_month;
        $redis  =  \Common\Lib\SavorRedis::getInstance();
        $redis->select(1);
        $redis->set($cache_salary_acbd_self_key,time(),86400);

        $res_staff_sale_task = $m_staff_saletask->getDataList('*',array('add_month'=>$static_month),'id desc');
        $staff_sale_task = array();
        $team_sale_task = array();
        foreach ($res_staff_sale_task as $v){
            $info = array('id'=>$v['id'],'staff_id'=>$v['staff_id'],'job_id'=>$v['job_id'],'deparment_id'=>$v['deparment_id'],'job'=>$v['job'],'deparment'=>$v['deparment'],
                'team_sale_num'=>$v['team_sale_num'],'sale_num'=>$v['sale_num'],'team_cost'=>$v['team_cost'],'cost'=>$v['cost']);
            $staff_sale_task[$v['staff_id']] = $info;
            $team_sale_task[$v['deparment_id']][] = $info;
        }

        $now_time = date('Y-m-d H:i:s');
        echo "AC&BD self start,time:$now_time \r\n";
        $field = 'id,remark as real_name,area_id,telephone,email,deparment_id,job_id,salary,entry_time,out_time';
        $job_ids = '1,2';//1ac 2bd
        $sql = "select {$field} from savor_sysuser where job_id in ($job_ids) and ((status=1) or (status=2 and out_time>='{$month_sdate}' and out_time<='{$month_edate}'))";

        $m_user = new \Admin\Model\UserModel();
        $res_user = $m_user->query($sql);
        $m_sale = new \Admin\Model\FinanceSaleModel();
        $m_salepaymentrecord = new \Admin\Model\FinanceSalePaymentRecordModel();
        $m_staff_accruesales = new \Admin\Model\StaffAccrueSalesModel();
        $bd_users = array();
        $finish_acbd_self_data = array();
        $excel_self_datas = array();
        foreach ($res_user as $v){
            $residenter_id = $v['id'];
            $residenter_name = $v['real_name'];
            $entry_time = $v['entry_time'];
            $out_time = $v['out_time'];
            $salary = $v['salary'];

            $sale_task = $staff_sale_task[$residenter_id];
            $staff_performance_saletask = $sale_task['id'];
            $deparment_id = $sale_task['deparment_id'];
            if(empty($sale_task)){
                continue;
            }
            if($sale_task['job_id']==2){
                $bd_users[]=$v;
            }

            $task_sale_num = $sale_task['sale_num'];
            $task_group_sale_num = round($task_sale_num*$group_task_percent);
            $task_wo_sale_num = $task_sale_num - $task_group_sale_num;

            //团购
            $salewhere = array('maintainer_id'=>$residenter_id,'type'=>4);
            $salewhere['add_time'] = array(array('egt',$month_stime),array('elt',$month_etime));
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
            $wo_where['a.add_time'] = array(array('egt',$month_stime),array('elt',$month_etime));
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
                    $pwhere['p.pay_time'] = array(array('egt',$month_sdate),array('elt',$month_edate));
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
                    $wo_up_money = $wo_up_num*($repay_sale_num/$all_task_sale_num)*$reward_config['per_botte_award']*$reward_config['person_award_coefficien']*$repay_coefficient;
                    //计算N*(R/(M+P))*105*0.6*S
                    $jt_money = $wo_up_num*($jt_num/$all_task_sale_num)*$reward_config['per_botte_award']*$reward_config['person_award_coefficien']*$repay_coefficient;
                    if(!empty($jt_sales)){
                        $m_staff_accruesales->addAll(array_values($jt_sales));
                    }
                }
            }
            $money = $wo_up_money+$group_up_money;//个人当月实际发放提成总金额

            $wo_where = array('a.residenter_id'=>$residenter_id,'a.type'=>1,'a.ptype'=>array('in','0,2'),'record.wo_reason_type'=>1,'record.wo_status'=>2);
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
                                $monthpjtwhere['p.pay_time'] = array(array('egt',$month_sdate),array('elt',$month_edate));
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
                                $jt_repay_money = $month_wo_up_num*($jt_repay_num/$all_month_task_sale_num)*$month_config['per_botte_award']*$month_config['person_award_coefficien']*$month_repay_coefficient;
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
                'group_up_money'=>$group_up_money,'wo_up_money'=>round($wo_up_money),'jt_repay_num'=>$all_jt_repay_num,'jt_repay_money'=>round($all_jt_repay_money),
                'jt_repay_data'=>json_encode($month_datas),'money'=>round($money),'jt_num'=>$jt_num,'jt_money'=>round($jt_money),'all_jt_num'=>$all_jt_num,
                'update_time'=>date('Y-m-d H:i:s')
            );
            $m_staff_saletask->updateData(array('id'=>$staff_performance_saletask),$updata);
            $finish_acbd_self_data[$deparment_id][]= array('wo_sale_num'=>$wo_sale_num,'group_num'=>$group_num,'all_jt_num'=>$all_jt_num);

            $excel_info = array('month'=>$static_month,'staff_id'=>$sale_task['staff_id'],'staff_name'=>$residenter_name,
                'job'=>$sale_task['job'],'city'=>$all_area[$v['area_id']],'team_name'=>$sale_task['deparment'],'entry_time'=>$entry_time,'out_time'=>$out_time,'salary'=>$salary,
                'cost'=>$sale_task['cost'],'sale_num'=>$sale_task['sale_num'],
                'all_sale_num'=>$all_sale_num,'wo_sale_num'=>$wo_sale_num,'wo_up_num'=>$wo_up_num,'group_num'=>$group_num,
                'group_sale_num'=>$group_sale_num,'group_up_num'=>$group_up_num,'repay_sale_num'=>$repay_sale_num,'repay_coefficient'=>$repay_coefficient,
                'group_up_money'=>$group_up_money,'wo_up_money'=>round($wo_up_money),'money'=>round($money),'jt_num'=>$jt_num,'jt_money'=>round($jt_money),'all_jt_num'=>$all_jt_num,
                'jt_repay_num'=>$all_jt_repay_num,'jt_repay_money'=>round($all_jt_repay_money),
            );
            foreach ($month_datas as $emdv){
                $key_jt_repay_num = 'jt_repay_num'.$emdv[0];
                $excel_info[$key_jt_repay_num] = $emdv[1];
                $key_jt_repay_money = 'jt_repay_money'.$emdv[0];
                $excel_info[$key_jt_repay_money] = $emdv[2];
            }
            $excel_self_datas[]=$excel_info;
        }

        $cell = array(
            array('month','月份'),
            array('staff_id','员工ID'),
            array('staff_name','姓名'),
            array('job','职位'),
            array('city','城市'),
            array('team_name','BD小组'),
            array('entry_time','入职时间'),
            array('out_time','离职时间'),
            array('salary','基本工资'),
            array('cost','成本'),
            array('sale_num','个人任务数'),
            array('all_sale_num','个人实际总销量'),
            array('wo_sale_num','个人餐厅核销数'),
            array('wo_up_num','个人餐厅核销超额销量'),
            array('group_num','个人团购销量'),
            array('group_sale_num','个人团购计入任务数'),
            array('group_up_num','个人团购超额销量'),
            array('repay_sale_num','个人当月回款瓶数'),
            array('repay_coefficient','个人回款系数'),
            array('group_up_money','个人团购提成'),
            array('wo_up_money','个人当月回款提成'),
            array('money','个人当月实际发放提成总金额'),
            array('jt_num','个人当月计提瓶数'),
            array('jt_money','个人当月计提奖金'),
            array('all_jt_num','个人计提剩余瓶数'),
            array('jt_repay_num','个人计提回款瓶数'),
            array('jt_repay_money','个人计提回款数据'),
        );
        foreach ($all_static_month as $exlmv) {
            $exl_month = $exlmv['month'];
            $cell[]=array('jt_repay_num'.$exl_month,$exl_month.'个人计提回款瓶数');
            $cell[]=array('jt_repay_money'.$exl_month,$exl_month.'个人计提回款数据');
        }
        $filename = 'AC和BD个人绩效表';
        $path = $this->exportToExcel($cell,$excel_self_datas,$filename,2);
        $redis->set($cache_salary_acbd_self_key,$path,86400);
        $now_time = date('Y-m-d H:i:s');
        echo "AC&BD self end,time:$now_time \r\n";

        $now_time = date('Y-m-d H:i:s');
        echo "BD team start,time:$now_time \r\n";
        $redis->select(1);
        $redis->set($cache_salary_acbd_team_key,time(),86400);
        $excel_team_datas = array();
        foreach ($bd_users as $v){
            $residenter_id = $v['id'];
            $residenter_name = $v['real_name'];
            $entry_time = $v['entry_time'];
            $out_time = $v['out_time'];
            $salary = $v['salary'];
            $sale_task = $staff_sale_task[$residenter_id];
            $deparment_id = $sale_task['deparment_id'];
            $staff_performance_saletask_id = $sale_task['id'];
            $bd_sale_task = $team_sale_task[$deparment_id];
            if(empty($bd_sale_task)){
                continue;
            }
            $bd_team_uids = array();
            $task_sale_num = 0;
            foreach ($bd_sale_task as $bst){
                $task_sale_num+=$bst['team_sale_num'];
                $bd_team_uids[]=$bst['staff_id'];
            }
            $task_group_sale_num = round($task_sale_num*$group_task_percent);

            $group_num = 0;
            $wo_sale_num = 0;
            $all_jt_num = 0;
            foreach ($finish_acbd_self_data[$deparment_id] as $fishv){
                $group_num+=$fishv['group_num'];
                $wo_sale_num+=$fishv['wo_sale_num'];
                $all_jt_num+=$fishv['all_jt_num'];
            }
            $group_up_money = 0;
            if($group_num>$task_group_sale_num){
                $group_sale_num = $task_group_sale_num;//小组团购计入任务数
                $group_up_num = $group_num - $task_group_sale_num;//小组团购超额销量
                $group_up_money = $group_up_num*$group_up_per_money;//小组团购提成
            }else{
                $group_sale_num = $group_num;
                $group_up_num = 0;
            }
            $all_sale_num = $wo_sale_num+$group_num;//小组实际总销量
            $all_task_sale_num = $wo_sale_num+$group_sale_num;//小组完成任务总数

            $wo_where = array('a.residenter_id'=>array('in',$bd_team_uids),'a.type'=>1,'record.wo_reason_type'=>1,'record.wo_status'=>2);
            $wo_where['a.add_time'] = array(array('egt',$month_stime),array('elt',$month_etime));
            $wo_where['a.ptype']=1;
            $res_wosale_data = $m_sale->getSaleStockRecordList('a.id,a.add_time',$wo_where,'','');
            $sale_data = array();
            foreach ($res_wosale_data as $wdv){
                $sale_data[$wdv['id']] = date('Y-m-d',strtotime($wdv['add_time']));
            }

            $repay_sale_num = count($sale_data);//小组当月回款瓶数
            $wo_up_num = 0;//小组餐厅核销超额销量
            $wo_up_money = 0;//小组当月回款提成
            $jt_num = 0;//小组当月计提瓶数
            $jt_money = 0;//小组当月计提奖金
            $repay_coefficient = 0;//小组回款系数
            if($all_task_sale_num>$task_sale_num){
                $wo_up_num = $all_task_sale_num-$task_sale_num;

                if($repay_sale_num>0){
                    $jt_num = $wo_sale_num - $repay_sale_num;
                    $sale_ids = array_keys($sale_data);
                    $pwhere = array('a.sale_id'=>array('in',$sale_ids));
                    $pwhere['p.pay_time'] = array(array('egt',$month_sdate),array('elt',$month_edate));
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
                    $wo_up_money = $wo_up_num*($repay_sale_num/$all_task_sale_num)*$reward_config['per_botte_award']*$reward_config['team_leader_award_coefficien']*$repay_coefficient;
                    $jt_money = $wo_up_num*($jt_num/$all_task_sale_num)*$reward_config['per_botte_award']*$reward_config['team_leader_award_coefficien']*$repay_coefficient;
                }
            }
            $money = $wo_up_money+$group_up_money;//小组当月实际发放提成总金额

            $month_datas = array();
            $all_jt_repay_num = 0;//小组计提回款瓶数
            $all_jt_repay_money = 0;//小组计提回款奖金
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
                    if($month_staff_sale_task['team_wo_up_num']>0){
                        $res_jt_sales = $m_staff_accruesales->getAllData('sale_id',array('staff_id'=>array('in',$bd_team_uids),'add_month'=>$h_month),'id desc');
                        $jt_sale_ids = array();
                        foreach ($res_jt_sales as $jtsv){
                            $jt_sale_ids[]=$jtsv['sale_id'];
                        }
                        if(!empty($jt_sale_ids)){
                            $jtwhere = array('id'=>array('in',$jt_sale_ids),'residenter_id'=>array('in',$bd_team_uids),'type'=>1,'ptype'=>1);
                            $res_jtpaysale = $m_sale->getAllData('id,add_time',$jtwhere);
                            if(!empty($res_jtpaysale)){
                                $jt_repay_num = count($res_jtpaysale);
                                $monthjt_datas = array();
                                foreach ($res_jtpaysale as $jtp){
                                    $monthjt_datas[$jtp['id']]=$jtp['add_time'];
                                }
                                $monthpjtwhere = array('a.sale_id'=>array('in',$jt_sale_ids));
                                $monthpjtwhere['p.pay_time'] = array(array('egt',$month_sdate),array('elt',$month_edate));
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
                                $month_wo_up_num = $month_staff_sale_task['team_wo_up_num'];
                                $all_month_task_sale_num = $month_staff_sale_task['team_wo_sale_num']+$month_staff_sale_task['team_group_sale_num'];
                                $jt_repay_money = $month_wo_up_num*($jt_repay_num/$all_month_task_sale_num)*$month_config['per_botte_award']*$month_config['team_leader_award_coefficien']*$month_repay_coefficient;
                            }
                        }
                    }
                }
                $all_jt_repay_num+=$jt_repay_num;
                $all_jt_repay_money+=$jt_repay_money;

                $month_datas[] = array($h_month,$jt_repay_num,$jt_repay_money);
            }
            $updata = array('team_all_sale_num'=>$all_sale_num,'team_wo_sale_num'=>$wo_sale_num,'team_wo_up_num'=>$wo_up_num,'team_group_num'=>$group_num,
                'team_group_sale_num'=>$group_sale_num,'team_group_up_num'=>$group_up_num,'team_repay_sale_num'=>$repay_sale_num,'team_repay_coefficient'=>$repay_coefficient,
                'team_group_up_money'=>$group_up_money,'team_wo_up_money'=>round($wo_up_money),'team_jt_repay_num'=>$all_jt_repay_num,'team_jt_repay_money'=>round($all_jt_repay_money),
                'team_jt_repay_data'=>json_encode($month_datas),'team_money'=>round($money),'team_jt_num'=>$jt_num,'team_jt_money'=>round($jt_money),'team_all_jt_num'=>$all_jt_num,
                'update_time'=>date('Y-m-d H:i:s')
            );
            $m_staff_saletask->updateData(array('id'=>$staff_performance_saletask_id),$updata);

            $excel_info = array('month'=>$static_month,'staff_id'=>$sale_task['staff_id'],'staff_name'=>$residenter_name,
                'job'=>$sale_task['job'],'city'=>$all_area[$v['area_id']],'team_name'=>$sale_task['deparment'],'entry_time'=>$entry_time,'out_time'=>$out_time,'salary'=>$salary,
                'cost'=>$sale_task['team_cost'],'sale_num'=>$sale_task['team_sale_num'],
                'all_sale_num'=>$all_sale_num,'wo_sale_num'=>$wo_sale_num,'wo_up_num'=>$wo_up_num,'group_num'=>$group_num,
                'group_sale_num'=>$group_sale_num,'group_up_num'=>$group_up_num,'repay_sale_num'=>$repay_sale_num,'repay_coefficient'=>$repay_coefficient,
                'group_up_money'=>$group_up_money,'wo_up_money'=>round($wo_up_money),'money'=>round($money),'jt_num'=>$jt_num,'jt_money'=>round($jt_money),'all_jt_num'=>$all_jt_num,
                'jt_repay_num'=>$all_jt_repay_num,'jt_repay_money'=>round($all_jt_repay_money),
            );
            foreach ($month_datas as $emdv){
                $key_jt_repay_num = 'jt_repay_num'.$emdv[0];
                $excel_info[$key_jt_repay_num] = $emdv[1];
                $key_jt_repay_money = 'jt_repay_money'.$emdv[0];
                $excel_info[$key_jt_repay_money] = $emdv[2];
            }
            $excel_team_datas[]=$excel_info;
        }

        $cell = array(
            array('month','月份'),
            array('staff_id','员工ID'),
            array('staff_name','姓名'),
            array('job','职位'),
            array('city','城市'),
            array('team_name','BD小组'),
            array('entry_time','入职时间'),
            array('out_time','离职时间'),
            array('salary','基本工资'),
            array('cost','成本'),
            array('sale_num','小组任务数'),
            array('all_sale_num','小组实际总销量'),
            array('wo_sale_num','小组餐厅核销数'),
            array('wo_up_num','小组餐厅核销超额销量'),
            array('group_num','小组团购销量'),
            array('group_sale_num','小组团购计入任务数'),
            array('group_up_num','小组团购超额销量'),
            array('repay_sale_num','小组当月回款瓶数'),
            array('repay_coefficient','小组回款系数'),
            array('group_up_money','小组团购提成'),
            array('wo_up_money','小组当月回款提成'),
            array('money','小组当月实际发放提成总金额'),
            array('jt_num','小组当月计提瓶数'),
            array('jt_money','小组当月计提奖金'),
            array('all_jt_num','小组计提剩余瓶数'),
            array('jt_repay_num','小组计提回款瓶数'),
            array('jt_repay_money','小组计提回款数据'),
        );
        foreach ($all_static_month as $exlmv) {
            $exl_month = $exlmv['month'];
            $cell[]=array('jt_repay_num'.$exl_month,$exl_month.'小组计提回款瓶数');
            $cell[]=array('jt_repay_money'.$exl_month,$exl_month.'小组计提回款数据');
        }
        $filename = 'BD小组绩效表';
        $path = $this->exportToExcel($cell,$excel_team_datas,$filename,2);
        $redis->set($cache_salary_acbd_team_key,$path,86400);
        $redis->set($cache_file_key,time(),600);

        $now_time = date('Y-m-d H:i:s');
        echo "BD team end,time:$now_time \r\n";

        echo "static_month:$static_month ok \r\n";
    }
}
