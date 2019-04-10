<?php
namespace Admin\Model;
use Common\Lib\Page;

class CategoryModel extends BaseModel{
    protected $tableName='category';

    public function getCustomList($where,$orderby='id desc',$start=0,$size=0){
        if($start >= 0 && $size){
            $list = $this->where($where)->order($orderby)->limit($start, $size)->select();
            if(isset($where['name']) && count($where)==1){
                if(!empty($list) && $list[0]['level']==1){
                    $category = $list[0]['id'];
                    unset($where['name']);
                    $where['trees'] = array('like',"%,$category,%");
                }
            }
            if(isset($where['parent_id'])){
                unset($where['parent_id']);
            }
            if(isset($where['id'])){
                $category = $where['id'];
                unset($where['id']);
                $where['trees'] = array('like',"%,$category,%");
            }
            $count = $this->where($where)->count();
            $page = new Page($count, $size);
            $show = $page->admin_page();
            $data = array('list'=>$list,'page'=>$show,'total'=>$count);
        }else{
            $data = $this->where($where)->order($orderby)->select();
        }
        return $data;
    }

    public function get_category_tree($category_id=0,$status=0,$type=0){
        $where = array();
        if($status){
            $where['status'] = $status;
        }
        if($type){
            $where['type'] = $type;
        }
        if($category_id){
            $where['trees'] = array('like',"%,$category_id,%");
        }
        $result = $this->getDataList('*',$where,'level asc');
        $trees = $this->category_tree($result);
        return $trees;
    }

    private static function category_tree($data,$pid=0) {
        $trees = array();
        foreach ($data as $k => $v) {
            if(!empty($v['icon'])){
                $v['icon'] = get_image_path($v['icon']);
            }
            if($v['parent_id'] == $pid){
                $html = '';
                $level = $v['level']-1>0?$v['level']-1:0;
                if($level){
                    $html = str_repeat('<em></em>', $level);
                }
                $v['html'] = $html;
                $trees[] = $v;
                $trees = array_merge_recursive($trees,self::category_tree($data,$v['id']));
            }
        }
        return $trees;
    }

    public function getCategory($category_id=0,$level=0,$type=1){
        if($level==1){
            $where = array('status'=>1,'parent_id'=>0);
            if($type){
                $where['type'] = $type;
            }
            $orderby = 'sort desc';
            $res_category = $this->getDataList('*',$where,$orderby);
        }else{
            $res_category = $this->get_category_tree(0,1,$type);
        }
        $category = array();
        foreach ($res_category as $v){
            $category_info = array('id'=>$v['id'],'name'=>$v['name'],'is_select'=>'','html'=>'');
            if($v['id']==$category_id){
                $category_info['is_select'] = 'selected';
            }
            if(isset($v['html'])){
                $category_info['html'] = $v['html'];
            }
            $category[$v['id']] = $category_info;
        }
        return $category;
    }
    /**
     * @desc 生成无限分类树
     * @param array $data array('1'=>array('id'=>1,'name'=>'fenlei');
     * @return array
     */
    public function genTree($data) {
        $trees = array();
        foreach ($data as $v){
            if (isset($data[$v['parent_id']])){
                $data[$v['parent_id']]['son'][] = &$data[$v['id']];
            }else{
                $trees[] = &$data[$v['id']];
            }
        }
        return $trees;
    }
}