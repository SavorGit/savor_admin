<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2013 http://topthink.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace Think;
/**
 * ThinkPHP系统钩子实现
 */
class MinifyHtml {

    public $compress_css;
    public $compress_js;
    public $info_comment;
    public $remove_comments;
    public $shorten_urls;
    public $SS = '"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'';
    public $CC = '\/\*[\s\S]*?\*\/';
    public $CH = '<\!--[\s\S]*?-->';
    
    // Variables
    public $html = '';
    
    
    static public function test(){
        return "abc";
    }

    public function __construct($html, $compress_css=true, $compress_js=true, $info_comment=false, $remove_comments=true, $shorten_urls=true)
    {
        if ($html !== '')
        {
            $this->compress_css = $compress_css;
            $this->compress_js = $compress_js;
            $this->info_comment = $info_comment;
            $this->remove_comments = $remove_comments;
            $this->shorten_urls = $shorten_urls;
            
            $this->html = $this->minifyHTML($html);
            
            if ($this->info_comment)
            {
                $this->html .= "\n" . $this->bottomComment($html, $this->html);
            }
        }
    }
    
    
    
    public function __toString()
    {
        return $this->html;
    }
    
    
    
    public function bottomComment($raw, $compressed)
    {
        $raw = strlen($raw);
        $compressed = strlen($compressed);
        
        $savings = ($raw-$compressed) / $raw * 100;
        
        $savings = round($savings, 2);
        
        return '<!--Bytes before:'.$raw.', after:'.$compressed.'; saved:'.$savings.'%-->';
    }
    
    
    
    public function callback_HTML_URLs($matches)
    {
        // [2] is an attribute value that is encapsulated with "" and [3] with ''
        $url = (!isset($matches[3])) ? $matches[2] : $matches[3];
        
        return $matches[1].'="'.absolute_to_relative_url($url).'"';
    }

    public function minifyX($input) {
        return str_replace(array("\n", "\t", ' '), array(X . '\n', X . '\t', X . '\s'), $input);
    }
    public function minifyV($input) {
        return str_replace(array(X . '\n', X . '\t', X . '\s'), array("\n", "\t", ' '), $input);
    }
    public function preMinifyCss($input){
        if(stripos($input, 'calc(') !== false) {
            $input = preg_replace_callback('#\b(calc\()\s*(.*?)\s*\)#i', function($m) {
                return $m[1] . preg_replace('#\s+#', X . '\s', $m[2]) . ')';
            }, $input);
        }
        // Minify ...
        return preg_replace(
            array(
                // Fix case for `#foo [bar="baz"]` and `#foo :first-child` [^1]
                '#(?<![,\{\}])\s+(\[|:\w)#',
                // Fix case for `[bar="baz"] .foo` and `@media (foo: bar) and (baz: qux)` [^2]
                '#\]\s+#', '#\b\s+\(#', '#\)\s+\b#',
                // Minify HEX color code ... [^3]
                '#\#([\da-f])\1([\da-f])\2([\da-f])\3\b#i',
                // Remove white-space(s) around punctuation(s) [^4]
                '#\s*([~!@*\(\)+=\{\}\[\]:;,>\/])\s*#',
                // Replace zero unit(s) with `0` [^5]
                '#\b(?:0\.)?0([a-z]+\b|%)#i',
                // Replace `0.6` with `.6` [^6]
                '#\b0+\.(\d+)#',
                // Replace `:0 0`, `:0 0 0` and `:0 0 0 0` with `:0` [^7]
                '#:(0\s+){0,3}0(?=[!,;\)\}]|$)#',
                // Replace `background(?:-position)?:(0|none)` with `background$1:0 0` [^8]
                '#\b(background(?:-position)?):(0|none)\b#i',
                // Replace `(border(?:-radius)?|outline):none` with `$1:0` [^9]
                '#\b(border(?:-radius)?|outline):none\b#i',
                // Remove empty selector(s) [^10]
                '#(^|[\{\}])(?:[^\{\}]+)\{\}#',
                // Remove the last semi-colon and replace multiple semi-colon(s) with a semi-colon [^11]
                '#;+([;\}])#',
                // Replace multiple white-space(s) with a space [^12]
                '#\s+#'
            ),
            array(
                // [^1]
                X . '\s$1',
                // [^2]
                ']' . X . '\s', X . '\s(', ')' . X . '\s',
                // [^3]
                '#$1$2$3',
                // [^4]
                '$1',
                // [^5]
                '0',
                // [^6]
                '.$1',
                // [^7]
                ':0',
                // [^8]
                '$1:0 0',
                // [^9]
                '$1:0',
                // [^10]
                '$1',
                // [^11]
                '$1',
                // [^12]
                ' '
            ),
        $input);
    }
    public function minifyCss($input){
        if( ! $input = trim($input)) return $input;
        $SS = '"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'';
        $CC = '\/\*[\s\S]*?\*\/';
        // Keep important white-space(s) between comment(s)
        $input = preg_replace('#(' . $CC . ')\s+(' . $CC . ')#', '$1' . X . '\s$2', $input);
        // Create chunk(s) of string(s), comment(s) and text
        $input = preg_split('#(' . $SS . '|' . $CC . ')#', $input, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $output = "";
        foreach($input as $v) {
            if(trim($v) === "") continue;
            if(
                ($v[0] === '"' && substr($v, -1) === '"') ||
                ($v[0] === "'" && substr($v, -1) === "'") ||
                (strpos($v, '/*') === 0 && substr($v, -2) === '*/')
            ) {
                // Remove if not detected as important comment ...
                if($v[0] === '/' && strpos($v, '/*!') !== 0) continue;
                $output .= $v; // String or comment ...
            } else {
                $output .= self::preMinifyCss($v);
            }
        }
        // Remove quote(s) where possible ...
        $output = preg_replace(
            array(
                // '#(' . $CC . ')|(?<!\bcontent\:|[\s\(])([\'"])([a-z_][-\w]*?)\2#i',
                '#(' . $CC . ')|\b(url\()([\'"])([^\s]+?)\3(\))#i'
            ),
            array(
                // '$1$3',
                '$1$2$4$5'
            ),
        $output);
        return self::minifyV($output);
    }
    public function preMinifyHtml($input) {
        return preg_replace_callback('#<\s*([^\/\s]+)\s*(?:>|(\s[^<>]+?)\s*>)#', function($m) {
            if(isset($m[2])) {
                // Minify inline CSS declaration(s)
                return '<' . $m[1] . preg_replace(
                    array(
                        // From `defer="defer"`, `defer='defer'`, `defer="true"`, `defer='true'`, `defer=""` and `defer=''` to `defer` [^1]
                        '#\s(checked|selected|async|autofocus|autoplay|controls|defer|disabled|hidden|ismap|loop|multiple|open|readonly|required|scoped)(?:=([\'"]?)(?:true|\1)?\2)#i',
                        // Remove extra white-space(s) between HTML attribute(s) [^2]
                        '#\s*([^\s=]+?)(=(?:\S+|([\'"]?).*?\3)|$)#',
                        // From `<img />` to `<img/>` [^3]
                        '#\s+\/$#'
                    ),
                    array(
                        // [^1]
                        ' $1',
                        // [^2]
                        ' $1$2',
                        // [^3]
                        '/'
                    ),
                str_replace("\n", ' ', $m[2])) . '>';
            }
            return '<' . $m[1] . '>';
        }, $input);
    }
    public function minify_html($input) {
        if( ! $input = trim($input)) return $input;
        $CH = '<\!--[\s\S]*?-->';
        // Keep important white-space(s) after self-closing HTML tag(s)
        $input = preg_replace('#(<(?:img|input)(?:\s[^<>]*?)?\s*\/?>)\s+#i', '$1' . X . '\s', $input);
        // Create chunk(s) of HTML tag(s), ignored HTML group(s), HTML comment(s) and text
        $input = preg_split('#(' . $CH . '|<pre(?:>|\s[^<>]*?>)[\s\S]*?<\/pre>|<code(?:>|\s[^<>]*?>)[\s\S]*?<\/code>|<script(?:>|\s[^<>]*?>)[\s\S]*?<\/script>|<style(?:>|\s[^<>]*?>)[\s\S]*?<\/style>|<textarea(?:>|\s[^<>]*?>)[\s\S]*?<\/textarea>|<[^<>]+?>)#i', $input, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $output = "";
        foreach($input as $v) {
            if($v !== ' ' && trim($v) === "") continue;
            if($v[0] === '<' && substr($v, -1) === '>') {
                if($v[1] === '!' && strpos($v, '<!--') === 0) { // HTML comment ...
                    // Remove if not detected as IE comment(s) ...
                    if(substr($v, -12) !== '<![endif]-->') continue;
                    $output .= $v;
                } else {
                    $output .= self::minifyX(self::preMinifyHtml($v));
                }
            } else {
                // Force line-break with `&#10;` or `&#xa;`
                $v = str_replace(array('&#10;', '&#xA;', '&#xa;'), X . '\n', $v);
                // Force white-space with `&#32;` or `&#x20;`
                $v = str_replace(array('&#32;', '&#x20;'), X . '\s', $v);
                // Replace multiple white-space(s) with a space
                $output .= preg_replace('#\s+#', ' ', $v);
            }
        }
        // Clean up ...
        $output = preg_replace(
            array(
                // Remove two or more white-space(s) between tag [^1]
                '#>([\n\r\t]\s*|\s{2,})<#',
                // Remove white-space(s) before tag-close [^2]
                '#\s+(<\/[^\s]+?>)#'
            ),
            array(
                // [^1]
                '><',
                // [^2]
                '$1'
            ),
        $output);
        $output = self::minifyV($output);
        // Remove white-space(s) after ignored tag-open and before ignored tag-close (except `<textarea>`)
        return preg_replace('#<(code|pre|script|style)(>|\s[^<>]*?>)\s*([\s\S]*?)\s*<\/\1>#i', '<$1$2$3</$1>', $output);
    }
    public function minifyHTML($html)
    {
        $pattern = '/<(?<script>script).*?<\/script\s*>|<(?<style>style).*?<\/style\s*>|<!(?<comment>--).*?-->|<(?<tag>[\/\w.:-]*)(?:".*?"|\'.*?\'|[^\'">]+)*>|(?<text>((<[^!\/\w.:-])?[^<]*)+)|/si';

        if (preg_match_all($pattern, $html, $matches, PREG_SET_ORDER) === false)
        {
            // Invalid markup
            return $html;

        }
        
        $overriding = false;
        $raw_tag = false;
        
        // Variable reused for output
        $html = '';
        //print_r($matches);
        foreach ($matches as $token)
        {
            $tag = (isset($token['tag'])) ? strtolower($token['tag']) : null;
            
            $content = $token[0];
            //print_r($token);
            $relate = false;
            $strip = false;
            
            if (is_null($tag))
            {
                if ( !empty($token['script']) )
                {
                    $strip = false;
                    //$content = self::minify_js($content);
                    //if(preg_match("/<script>/",$content) ){
                    $content = JSmin::minify($content);
                        
                    //}
                    //print_r('||'.$content);
                    // Will still end up shortening URLs within the script, but should be OK..
                    // Gets Shortened:   test.href="http://domain.com/wp"+"-content";
                    // Gets Bypassed:    test.href = "http://domain.com/wp"+"-content";
                    //$relate = $this->compress_js;
                }
                else if ( !empty($token['style']) )
                {
                    $strip = false;
                    $content = str_replace(': ', ':', str_replace(';}', '}', str_replace('; ',';',str_replace(' }','}',str_replace(' {', '{', str_replace('{ ','{',str_replace(array("\r\n","\r","\n","\t",'  ','    ','    '),"",preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!','',$content))))))));
                    //$content = self::minifyCss($content);
                    // No sense in trying to relate at this point because currently only URLs within HTML attributes are shortened
                    //$relate = $this->compress_css;
                }else{
                    $strip = true;
                }
            }
            else    // All tags except script, style and comments
            {                                   
                $strip = true;
            }
            
            // Relate URLs
            // if ($relate && $this->shorten_urls)
            // {
            //     $content = preg_replace_callback('/(action|data|href|src)=(?:"([^"]*)"|\'([^\']*)\')/i', array(&$this,'callback_HTML_URLs'), $content);
            // }
            
            if ($strip)
            {
                $content = self::minify_html($content);
                //print_r($content);
            }
            
            $html .= $content;
        }
        
        return $html;
    }
    
    
    
    
}
