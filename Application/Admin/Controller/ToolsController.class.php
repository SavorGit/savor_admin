<?php
namespace Admin\Controller;
use Think\Controller;

/**
 * 工具类
 *
 */
class ToolsController extends Controller{

    public function icons(){
        $icons_file = "Public/admin/assets/plugins/font-awesome/css/font-awesome.css";
        $parsed_file = file_get_contents($icons_file);
        preg_match_all("/fa\-([a-zA-z0-9\-]+[^\:\.\,\s])/", $parsed_file, $matches);
        $exclude_icons = array("fa-lg", "fa-2x", "fa-3x", "fa-4x", "fa-5x", "fa-ul", "fa-li", "fa-fw", "fa-border", "fa-pulse", "fa-rotate-90", "fa-rotate-180", "fa-rotate-270", "fa-spin", "fa-flip-horizontal", "fa-flip-vertical", "fa-stack", "fa-stack-1x", "fa-stack-2x", "fa-inverse");
        $icons = (object) array("icons" => $this->array_delelement($matches[0], $exclude_icons));
        $this->show(json_encode($icons),'utf-8', 'text/json');
    }
    
    private function getCss($path){
        $css = '';
        $root = '.'.$path; //directory where the css lives
        $files = explode(',',$_SERVER['QUERY_STRING']);

        if(sizeof($files)){
          foreach($files as $file){
            $css.= (is_file($root.$file.'.css') ? file_get_contents($root.$file.'.css') : '');
          }
        }
        $css = str_replace($path.'\'', '\''.$path, str_replace('url(', 'url('.$path, str_replace(': ', ':', str_replace(';}', '}', str_replace('; ',';',str_replace(' }','}',str_replace(' {', '{', str_replace('{ ','{',str_replace(array("\r\n","\r","\n","\t",'  ','    ','    '),"",preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!','',$css))))))))));
        return $css;
    }
    
    private function array_delelement($array, $element) {
        return (is_array($element)) ? array_values(array_diff($array, $element)) : array_values(array_diff($array, array($element)));
    }
    
}