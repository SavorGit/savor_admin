<?php
namespace Admin\Controller;
class CleanController extends BaseController {
    public function cache(){
        $path = APP_PATH.'/Runtime/';
        $res = $this->getDirectorySize($path);
        $size = $this->sizeFormat($res['size']);
        removeDir($path,$path);
        $ckAfRm = scandir($path); //检查是否已清除文件
        if(count($ckAfRm) == 2){
            $this->output('已清理缓存'.$size, '/',2);
        }else{
            $this->output('清理缓存失败', '/',2,0);
        }
        
    }
    
    private function getDirectorySize($path){
        $totalsize = 0;
        $totalcount = 0;
        $dircount = 0;
        if ($handle = opendir ($path)){
            while (false !== ($file = readdir($handle))){
                $nextpath = $path . '/' . $file;
                if ($file != '.' && $file != '..' && !is_link ($nextpath)){
                    if (is_dir ($nextpath)){
                        $dircount++;
                        $result = $this->getDirectorySize($nextpath);
                        $totalsize += $result['size'];
                        $totalcount += $result['count'];
                        $dircount += $result['dircount'];
                    }elseif (is_file ($nextpath)){
                        $totalsize += filesize ($nextpath);
                        $totalcount++;
                    }
                }
            }
        }
        closedir ($handle);
        $total['size'] = $totalsize;
        $total['count'] = $totalcount;
        $total['dircount'] = $dircount;
        return $total;
    }
    
    private function sizeFormat($size){
        $sizeStr='';
        if($size<1024){
            return $size." bytes";
        }else if($size<(1024*1024)){
            $size=round($size/1024,1);
            return $size." KB";
        }else if($size<(1024*1024*1024)){
            $size=round($size/(1024*1024),1);
            return $size." MB";
        }else{
            $size=round($size/(1024*1024*1024),1);
            return $size." GB";
        }
    }
    
    private function size($byte){
        if($byte < 1024) {
            $unit="B";
        }else if($byte < 10240) {
            $byte=$this->round_dp($byte/1024, 2);
            $unit="KB";
        }else if($byte < 102400) {
            $byte=$this->round_dp($byte/1024, 2);
            $unit="KB";
        }else if($byte < 1048576) {
            $byte=$this->round_dp($byte/1024, 2);
            $unit="KB";
        }else if ($byte < 10485760) {
            $byte=$this->round_dp($byte/1048576, 2);
            $unit="MB";
        }else if ($byte < 104857600) {
            $byte=$this->round_dp($byte/1048576,2);
            $unit="MB";
        }else if ($byte < 1073741824) {
            $byte=$this->round_dp($byte/1048576, 2);
            $unit="MB";
        }else {
            $byte=$this->round_dp($byte/1073741824, 2);
            $unit="GB";
        }
        $byte .= $unit;
        return $byte;
    }
    
    private function round_dp($num , $dp){
        $sh = pow(10 , $dp);
        return (round($num*$sh)/$sh);
    }
    
}