<?php
/**
 * @file
 * @ingroup Extensions
 * @author Lehmaning
 * @license GPL-3.0-or-later
 * @todo 并行处理多个请求
 */
class BiliGet {
    /**
     * @param Parser &$parser
     */
    public static function registerTags( &$parser ) {
        $parser->setHook( 'biliget' , [ __CLASS__ , 'getInfo' ] );
    }

   /**
    * @param  string $url
    * @see https://www.runoob.com/php/php-ref-curl.html#div-comment-36119
    */
    public static function getUrl($url) {
        $headerArray = array("Content-type:application/json;","Accept:application/json");
        //$headerArray[] = "Origin: https://www.bilibili.com";
        //$userAgent = "Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.7113.93 Safari/537.36";
        if(!empty($wfSetUserAgent)) curl_setopt($ch, CURLOPT_USERAGENT, $wfSetUserAgent);
        if(!empty($wfSetBiliCookie)) curl_setopt($ch, CURLOPT_COOKIE, $wfSetBiliCookie);
        if(!empty($aid)) curl_setopt($ch, CURLOPT_REFERER, "https://www.bilibili.com/av$aid");
        if(!empty($bid)) curl_setopt($ch, CURLOPT_REFERER, "https://www.bilibili.com/BV$bvid");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);
        $output = curl_exec($ch);
        curl_close($ch);
        $output = json_decode($output,true);
        return $output;
    }

    public static function url2aid($url) {
        $aid_data = [];
        $aid_pattern = "#(?:https?:\/\/(?:www\.)?bilibili\.com\/video\/)?av(\d+)(\?p=\d{1,3})?#";
        if(preg_match($aid_pattern, $url, $preg)) {
            $aid_data = [
                'aid' => $preg[1],
                'part' => $preg[2]
            ];
        }
        return $aid_data;
    }

    public static function url2bvid($url) {
        $bvid_data = [];
        $bvid_pattern = "#(?:https?:\/\/(?:www\.)?bilibili\.com\/video\/)?BV([a-zA-Z0-9]+)(\?p=\d{1,3})?#";
        if(preg_match($bvid_pattern, $url, $preg)) {
            $bvid_data = [
                'bvid' => $preg[1],
                'part' => $preg[2]
            ];
        }
        return $bvid_data;
    }

    public static function handleDesc($text) {
        $vid_pattern = "#(BV[a-zA-Z0-9]+|(av|sm|ac|om)(\d+)|watch\?v=[a-zA-Z0-9])#";
        $url_pattern = "/https?:\/\/(www\.)?[a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([a-zA-Z0-9()@:%_\+.~#?&//=]*)/m"; //@see https://stackoverflow.com/a/3809435
        $output_desc = preg_replace($url_pattern, "<a href=\"$1\">$1</a>", trim($text));
        return $output_desc;
    }

    /*
     * @param Parser &$parser
     * @param string $input
     * @param Array $argv
     * @param PPFrame $frame
     */
    public static function getInfo($input, $argv, $parser, $frame) {
        global $wfSetBiliCookie;
        global $wfSetUserAgent;
        $parser->getOutput()->addModules('ext.biliget.list');
        $input_arr = explode("
        ", $input);
        if ( !empty( self::url2aid($input) ) ) {
            $aid_data = ( count($input_arr) == 1 ) ? self::url2aid($input) : self::url2aid($input_arr); //@todo 使用遍历方式转换视频id
            $aid = $aid_data['aid'];
            $part = $aid_data['part'];
            $id = "av$aid";
            $data = self::getUrl("https://api.bilibili.com/x/web-interface/view?aid=$aid", $wfSetBiliCookie);
            $url = "https://bilibili.com/video/av$aid?p=$part";
            if( !empty($part) ) $link .= "?p=$part";
        }
        if ( !empty( self::url2bvid($input) ) ) {
            $bvid_data = ( count($input_arr) == 1 ) ? self::url2bvid($input) : self::url2bvid($input_arr);
            $bvid = $bvid_data['bvid'];
            $part = $bvid_data['part'];
            $id = "BV$bvid";
            $data = self::getUrl("https://api.bilibili.com/x/web-interface/view?bvid=$bvid");
            $url = "https://bilibili.com/video/BV$bvid";
            if( !empty($part) ) $link .= "?p=$part";
        }
        if ( empty($id) ) return '';

        $code = $data['code']; // 判断响应代码
        if ($code==0) {
            $cover = $data['data']['pic'];
            $uploader = $data['data']['owner']['name'];
            $desc = str_replace("\n", "<br />", $data['data']['desc']);
            $title = $data['data']['title'];
            $pubdate = date("Y-m-d H:i", $data['data']['pubdate']);
            $duration = gmstrftime("%M:%S", $data['data']['pages'][0]['duration']);
            if ( !empty($argv['type']) && $argv['type']=='raw' ) {
                $output = $parser->recursiveTagParse("
                    {{#vardefine:封面|$cover}}
                    {{#vardefine:UP主|$uploader}}
                    {{#vardefine:简介|$desc}}
                    {{#vardefine:标题|$title}}
                    {{#vardefine:发布日期|$pubdate}}
                    {{#vardefine:视频时长|$duaration}}
                    ", $frame);
                    //@require https://www.mediawiki.org/wiki/Extension:Variables
                return $output;
            }
            else if ( !empty($argv['type'] ) ) { // 处理 type 参数
                $type = array(
                    '封面' => $cover,
                    'UP主'=> $uploader, 
                    '简介' => $desc,
                    '标题' => $title,
                    '发布日期' => $pubdate,
                    '时长' => $duration
                    );
                if ( strpos($argv['type'], " ") ) {
                    $output = '';
                    foreach ($type as $key => $value) {
                        $output .= "{{#vardefine:$key|$value}}";
                    }
                    return $parser->recursiveTagParse($output, $frame);
                }
                return $type[trim($argv['type'])];
            }
            else { // 默认输出
                return "
                <div class=\"bili-info-card\">
                    <div class=\"bili-info\">
                        <div class=\"bili-info-cover\"><img src=\"$cover\" width=\"160px\" loading=\"lazy\" /></div>
                        <p class=\"bili-info-duration\">$duration</p>
                    </div>
                    <div class=\"bili-info\">
                        <p class=\"bili-info-title\" style=\"margin-bottom: 0\"><a href=\"$url\" title=\"$id\">$title</a></p>
                        <p class=\"bili-info-date\"    style=\"margin-top: 0; margin-bottom: 0\">$pubdate</p>
                        <p class=\"bili-info-uploader\" style=\"margin-top: 0;\">UP主：$uploader</p>
                        <p class=\"bili-info-desc\">$desc</p>
                    </div>
                </div>";
                    }
                }

        else {// 收到错误代码
            $message = $data['message'];
            return "
                <div class=\"bili-info-card\">
                    <div class=\"bili-info\" style=\"width: 80%\">
                        <p>错误码：$code</p>
                    </div>
                    <div class=\"bili-info\">
                        <p class=\"bili-info-title\">$message</p>
                    </div>
                </div>";
        }
    }
}
