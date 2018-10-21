<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
/**************************************************
 * MKOnlinePlayer v2.4
 * 后台音乐数据抓取模块
 * 编写：mengkun(https://mkblog.cn)
 * 时间：2018-3-11
 * 特别感谢 @metowolf 提供的 Meting.php
 *************************************************/

/************ ↓↓↓↓↓ 如果网易云音乐歌曲获取失效，请将你的 COOKIE 放到这儿 ↓↓↓↓↓ ***************/
$netease_cookie = '_ntes_nnid=eb1d3368bb4b6c15fe6a67f702f6b2a8,1528997562543; _ntes_nuid=eb1d3368bb4b6c15fe6a67f702f6b2a8; __f_=1539416744257; JSESSIONID-WYYY=OCnxCRrg7qaZVE2MzqNwef2QuXfsFAmWfilAY5CMV3bScNS1WewfOuO4OVfBC9R%2FMFIs6P4gPc9In7mZdPM0OzR%2FE3wNCDDkfUVW5Wy0WrIshRFEj0yv8gsOzI%2BN7Ur7VSny5vfrF0IiiB8Kw2%2FoRgjkPAqUTTKjDE0%5CYB5v85tg9xg0%3A1540041471776; _iuqxldmzr_=32; __utma=94650624.1717456437.1540039673.1540039673.1540039673.1; __utmc=94650624; __utmz=94650624.1540039673.1.1.utmcsr=(direct)|utmccn=(direct)|utmcmd=(none); WM_NI=JjEc7%2Fcc5egrU7Yh1V6S7Drvzkl35glcQ1F06cIVbjB5CTTL1X23zJXst3dl7irHe6Io49sEq83LHplfWnL9WZaGqytOr4uEbZXV5Mp9fIve088bUWW3mrTGT3tva6dHbzU%3D; WM_NIKE=9ca17ae2e6ffcda170e2e6ee8dcf6297b19895eb7ffc8a8fa3d84a878e9ebaf245a1979989c24d93e999b3d32af0fea7c3b92a8ab68b84b625a1bdfb86e67a97e99789b45ef68df9a5f17ebab5a4d4b86db0bd9fd2c53cf6bd87b7e16282eb8db5d748f388fed4f9429cadbcd4e768f5b7bea9e83e8bb1fcdaf24fb3e8fdadcb7cb0ba998fcf339586afa8f473b891bed5f84fa28698b7b847b4e7aaaaf56ff4e8f883d85d95eda393d74d98edbc97e43ea898ae9bd837e2a3; WM_TID=izi2auPDQ9ZBEVVEQActbZISwHTE9mZr; MUSIC_U=6bf3621ebbdf57e332db0d4a4ba6238d56738791566096c9f05c556fc87e8aba4e353008646e25cc1d44fd57ee7cf9362d00396ce29839d4bf122d59fa1ed6a2; __remember_me=true; __csrf=6cf49cd3b186a2891b956671a930fc52; jsessionid-cpta=RkBA40ilXHrjn3XeZ0DUph5qKJAKGdiBeSvg1i1NQgJmflONzVhCZZkqjXyDt5umEVMIR5xF7abp6K6ZquoREqANL44%5C4%5CbLekQjN0Yb8%2F0XcONt%5CXWVkZ8jbI4UyyixVUgEKhE46Fc04fO5TkCBUhAuXEJQ56b7FSViDqQOgw9LfLdR%3A1540040594607; c98xpt_=30; NETEASE_WDA_UID=1646809904#|#1540039728405; __utmb=94650624.3.10.1540039673';
/************ ↑↑↑↑↑ 如果网易云音乐歌曲获取失效，请将你的 COOKIE 放到这儿 ↑↑↑↑↑ ***************/
/**
* cookie 获取及使用方法见 
* https://github.com/mengkunsoft/MKOnlineMusicPlayer/wiki/%E7%BD%91%E6%98%93%E4%BA%91%E9%9F%B3%E4%B9%90%E9%97%AE%E9%A2%98
* 
* 更多相关问题可以查阅项目 wiki 
* https://github.com/mengkunsoft/MKOnlineMusicPlayer/wiki
* 
* 如果还有问题，可以提交 issues
* https://github.com/mengkunsoft/MKOnlineMusicPlayer/issues
**/


define('HTTPS', false);    // 如果您的网站启用了https，请将此项置为“true”，如果你的网站未启用 https，建议将此项设置为“false”
define('DEBUG', false);      // 是否开启调试模式，正常使用时请将此项置为“false”
define('CACHE_PATH', 'cache/');     // 文件缓存目录,请确保该目录存在且有读写权限。如无需缓存，可将此行注释掉

/*
 如果遇到程序不能正常运行，请开启调试模式，然后访问 http://你的网站/音乐播放器地址/api.php ，进入服务器运行环境检测。
 此外，开启调试模式后，程序将输出详细的运行错误信息，方便定位错误原因。
 
 因为调试模式下程序会输出服务器环境信息，为了您的服务器安全，正常使用时请务必关闭调试。
*/



/*****************************************************************************************************/
if(!defined('DEBUG') || DEBUG !== true) error_reporting(0); // 屏蔽服务器错误

require_once('plugns/Meting.php');

use Metowolf\Meting;

$source = getParam('source', 'netease');  // 歌曲源
$API = new Meting($source);

$API->format(true); // 启用格式化功能

if($source == 'kugou' || $source == 'baidu') {
    define('NO_HTTPS', true);        // 酷狗和百度音乐源暂不支持 https
} elseif(($source == 'netease') && $netease_cookie) {
    $API->cookie($netease_cookie);    // 解决网易云 Cookie 失效
}

// 没有缓存文件夹则创建
if(defined('CACHE_PATH') && !is_dir(CACHE_PATH)) createFolders(CACHE_PATH);

$types = getParam('types');
switch($types)   // 根据请求的 Api，执行相应操作
{
    case 'url':   // 获取歌曲链接
        $id = getParam('id');  // 歌曲ID
        
        $data = $API->url($id);
        
        echojson($data);
        break;
        
    case 'pic':   // 获取歌曲链接
        $id = getParam('id');  // 歌曲ID
        
        $data = $API->pic($id);
        
        echojson($data);
        break;
    
    case 'lyric':       // 获取歌词
        $id = getParam('id');  // 歌曲ID
        
        if(($source == 'netease') && defined('CACHE_PATH')) {
            $cache = CACHE_PATH.$source.'_'.$types.'_'.$id.'.json';
            
            if(file_exists($cache)) {   // 缓存存在，则读取缓存
                $data = file_get_contents($cache);
            } else {
                $data = $API->lyric($id);
                
                // 只缓存链接获取成功的歌曲
                if(json_decode($data)->lyric !== '') {
                    file_put_contents($cache, $data);
                }
            }
        } else {
            $data = $API->lyric($id);
        }
        
        echojson($data);
        break;
        
    case 'download':    // 下载歌曲(弃用)
        $fileurl = getParam('url');  // 链接
        
        header('location:$fileurl');
        exit();
        break;
    
    case 'userlist':    // 获取用户歌单列表
        $uid = getParam('uid');  // 用户ID
        
        $url= 'http://music.163.com/api/user/playlist/?offset=0&limit=1001&uid='.$uid;
        $data = file_get_contents($url);
        
        echojson($data);
        break;
        
    case 'playlist':    // 获取歌单中的歌曲
        $id = getParam('id');  // 歌单ID
        
        if(($source == 'netease') && defined('CACHE_PATH')) {
            $cache = CACHE_PATH.$source.'_'.$types.'_'.$id.'.json';
            
            if(file_exists($cache) && (date("Ymd", filemtime($cache)) == date("Ymd"))) {   // 缓存存在，则读取缓存
                $data = file_get_contents($cache);
            } else {
                $data = $API->format(false)->playlist($id);
                
                // 只缓存链接获取成功的歌曲
                if(isset(json_decode($data)->playlist->tracks)) {
                    file_put_contents($cache, $data);
                }
            }
        } else {
            $data = $API->format(false)->playlist($id);
        }
        
        echojson($data);
        break;
     
    case 'search':  // 搜索歌曲
        $s = getParam('name');  // 歌名
        $limit = getParam('count', 20);  // 每页显示数量
        $pages = getParam('pages', 1);  // 页码
        
        $data = $API->search($s, [
            'page' => $pages, 
            'limit' => $limit
        ]);
        
        echojson($data);
        break;
        
    default:
        echo '<!doctype html><html><head><meta charset="utf-8"><title>信息</title><style>* {font-family: microsoft yahei}</style></head><body> <h2>MKOnlinePlayer</h2><h3>Github: https://github.com/mengkunsoft/MKOnlineMusicPlayer</h3><br>';
        if(!defined('DEBUG') || DEBUG !== true) {   // 非调试模式
            echo '<p>Api 调试模式已关闭</p>';
        } else {
            echo '<p><font color="red">您已开启 Api 调试功能，正常使用时请在 api.php 中关闭该选项！</font></p><br>';
            
            echo '<p>PHP 版本：'.phpversion().' （本程序要求 PHP 5.4+）</p><br>';
            
            echo '<p>服务器函数检查</p>';
            echo '<p>curl_exec: '.checkfunc('curl_exec',true).' （用于获取音乐数据）</p>';
            echo '<p>file_get_contents: '.checkfunc('file_get_contents',true).' （用于获取音乐数据）</p>';
            echo '<p>json_decode: '.checkfunc('json_decode',true).' （用于后台数据格式化）</p>';
            echo '<p>hex2bin: '.checkfunc('hex2bin',true).' （用于数据解析）</p>';
            echo '<p>openssl_encrypt: '.checkfunc('openssl_encrypt',true).' （用于数据解析）</p>';
        }
        
        echo '</body></html>';
}

/**
 * 创建多层文件夹 
 * @param $dir 路径
 */
function createFolders($dir) {
    return is_dir($dir) or (createFolders(dirname($dir)) and mkdir($dir, 0755));
}

/**
 * 检测服务器函数支持情况
 * @param $f 函数名
 * @param $m 是否为必须函数
 * @return 
 */
function checkfunc($f,$m = false) {
	if (function_exists($f)) {
		return '<font color="green">可用</font>';
	} else {
		if ($m == false) {
			return '<font color="black">不支持</font>';
		} else {
			return '<font color="red">不支持</font>';
		}
	}
}

/**
 * 获取GET或POST过来的参数
 * @param $key 键值
 * @param $default 默认值
 * @return 获取到的内容（没有则为默认值）
 */
function getParam($key, $default='')
{
    return trim($key && is_string($key) ? (isset($_POST[$key]) ? $_POST[$key] : (isset($_GET[$key]) ? $_GET[$key] : $default)) : $default);
}

/**
 * 输出一个json或jsonp格式的内容
 * @param $data 数组内容
 */
function echojson($data)    //json和jsonp通用
{
    header('Content-type: application/javascript');
    $callback = getParam('callback');
    
    if(defined('HTTPS') && HTTPS === true && !defined('NO_HTTPS')) {    // 替换链接为 https
        $data = str_replace('http:\/\/', 'https:\/\/', $data);
        $data = str_replace('http://', 'https://', $data);
    }
    
    if($callback) //输出jsonp格式
    {
        die(htmlspecialchars($callback).'('.$data.')');
    } else {
        die($data);
    }
}