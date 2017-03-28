<?php
$min_enableBuilder = false;
$min_concatOnly = false;
$min_builderPassword = 'admin';
$min_errorLogger = false;
$min_allowDebugFlag = false;
$cache_path = C('MINIFY_CACHE_PATH');
if(!empty($cache_path)){
	$min_cachePath = C('MINIFY_CACHE_PATH');
}
$min_libPath = dirname(__FILE__) . '/lib';
$min_documentRoot = $_SERVER['SUBDOMAIN_DOCUMENT_ROOT'];
$min_cacheFileLocking = true;
$min_serveOptions['bubbleCssImports'] = false;
$min_serveOptions['maxAge'] = 31536000;
$min_serveOptions['minifiers']['text/css'] = array('Minify_CSSmin', 'minify');
$min_serveOptions['minApp']['groupsOnly'] = false;
$min_symlinks = array();
$min_uploaderHoursBehind = 0;
ini_set('zlib.output_compression', '0');