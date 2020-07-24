<?php
namespace Admin\Model;

class BoxStaticgradeconfigModel extends BaseModel{
	protected $tableName='box_staticgrade_config';

	public function config(){
        $res = $this->getDataList('*',array(),'cnum asc');
        $config = array();
        foreach ($res as $v){
            //type 1netty重连,2投屏成功分数,3心跳分数,4上传网速分数,5下载网速分数
            //score_type 分数类型 10标准投屏,20极简投屏,11总分数-标准投屏,21总分数-极简投屏,12样本-标准投屏,22样本-极简投屏
            switch ($v['score_type']){
                case 10:
                case 20:
                    $config[$v['score_type']][$v['type']][] = $v;
                    break;
                case 11:
                case 21:
                    $config[$v['score_type']][$v['type']] = $v['weights']/100;
                    break;
                case 12:
                case 22:
                    $config[$v['score_type']][$v['type']] = $v['sample_num'];
                    break;
            }
        }
        return $config;
    }
}