<?php
namespace Admin\Model\Smallapp;
use Admin\Model\BaseModel;
use Common\Lib\Page;

class UserForscreenModel extends BaseModel{
	protected $tableName='smallapp_user_forscreen';

    public function getCustomeList($fields="*",$where,$order='',$start=0,$size=5){
        if($start >= 0 && $size){
            $list = $this->alias('a')
                ->join('savor_smallapp_user user on a.user_id=user.id','left')
                ->field($fields)
                ->where($where)
                ->order($order)
                ->limit($start,$size)
                ->select();

            $res_count = $this->alias('a')
                ->join('savor_smallapp_user user on a.user_id=user.id','left')
                ->field($fields)
                ->where($where)
                ->select();
            $count = $res_count[0]['tp_count'];
            $objPage = new Page($count,$size);
            $show = $objPage->admin_page();
            $data = array('list'=>$list,'page'=>$show,'total'=>$count);
        }else{
            $data = $this->alias('a')
                ->join('savor_smallapp_user user on a.user_id=user.id','left')
                ->field($fields)
                ->where($where)
                ->order($order)
                ->select();
        }
        return $data;
    }

    public function getUserForscreenInfo($fields,$where){
        $res = $this->alias('a')
            ->join('savor_smallapp_user user on a.user_id=user.id','left')
            ->field($fields)
            ->where($where)
            ->select();
        return $res;
    }

	public function handle_user_forscreen(){
        $start_time = date('Y-m-d 00:00:00',strtotime('-1 day'));
        $end_time = date('Y-m-d 23:59:59',strtotime('-1 day'));

        $where = array();
        $where['create_time'] = array(array('EGT',$start_time),array('ELT',$end_time));
        $where['small_app_id'] = array('in',array(1,2));
        $where['mobile_brand'] = array('neq','devtools');
        $group = 'openid';
        $field = 'openid,count(id) as forscreen_num,count(DISTINCT hotel_id) as hotel_num';
        $m_forscreen_record = new \Admin\Model\ForscreenRecordModel();
        $res_forscreen = $m_forscreen_record->getAll($field,$where,0,100000,'',$group);
        $m_user = new \Admin\Model\Smallapp\UserModel();
        $m_public = new \Admin\Model\Smallapp\PublicModel();
        foreach ($res_forscreen as $v){
            $openid = $v['openid'];
            $res_user = $m_user->getOne('*',array('openid'=>$openid),'id desc');
            if(!empty($res_user)){
                $user_id = $res_user['id'];
                $forscreen_num = $v['forscreen_num'];
                $hotel_num = $v['hotel_num'];

                $file_where = $where;
                $file_where['openid'] = $openid;
                $file_where['action'] = 30;
                $res_fileforscreen = $m_forscreen_record->getAll('count(id) as file_num',$file_where,0,1,'','');
                $file_num = 0;
                if(!empty($res_fileforscreen)){
                    $file_num = $res_fileforscreen[0]['file_num'];
                }
                $public_num = 0;
                $pwhere = array(array('EGT',$start_time),array('ELT',$end_time));
                $pwhere['openid'] = $openid;
                $pwhere['status'] = 2;
                $res_public = $m_public->getOne('count(id) as public_num',$pwhere,'id desc');
                if(!empty($res_public)){
                    $public_num = $res_public[0]['public_num'];
                }
                $res_uforscreen = $this->getInfo(array('user_id'=>$user_id));
                $row_id = 0;
                if(!empty($res_uforscreen)){
                    $forscreen_num = $forscreen_num + $res_uforscreen['forscreen_num'];
                    $hotel_num = $hotel_num + $res_uforscreen['hotel_num'];
                    $file_num = $file_num + $res_uforscreen['file_num'];
                    $public_num = $public_num + $res_uforscreen['public_num'];
                    $row_id = $res_uforscreen['id'];
                }
                $morehotel_user=$heavy_user=$sale_user=$content_user=$common_user=0;
                if($hotel_num>=2)       $morehotel_user = 1;
                if($forscreen_num>=10)  $heavy_user = 1;
                if($file_num>=1)        $sale_user = 1;
                if($public_num>=2)      $content_user = 1;
                if($morehotel_user==0 && $heavy_user==0 && $sale_user==0 && $content_user==0){
                    $common_user = 1;
                }
                $data = array('user_id'=>$user_id,'hotel_num'=>$hotel_num,'forscreen_num'=>$forscreen_num,'file_num'=>$file_num,
                    'public_num'=>$public_num,'morehotel_user'=>$morehotel_user,'heavy_user'=>$heavy_user,'sale_user'=>$sale_user,
                    'content_user'=>$content_user,'common_user'=>$common_user
                );
                if($row_id){
                    $data['update_time'] = date('Y-m-d H:i:s');
                    $this->updateData(array('id'=>$row_id),$data);
                }else{
                    $this->add($data);
                }
                echo "openid: $openid ok \r\n";
            }else{
                echo "openid: $openid not exist \r\n";
            }


        }

    }
}