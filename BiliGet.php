<?php
/*
 * @file
 * @ingroup Extensions
 * @author KUMAX
 * @license GPL-3.0-or-later
 */
class BiliGet {
	/*
	 * @param Parser &$parser
	 */
	public static function registerTags( &$parser ) {
		$parser->setHook( 'biliget' , [ __CLASS__ , 'getInfo' ] );
		$parser->setHook( 'biligetraw' , [ __CLASS__ , 'rawInfo' ] );
	}
	/*
	* @see https://www.runoob.com/php/php-ref-curl.html#div-comment-36119
	*/
	public static function getUrl($url) {
		# header( 'Referrer-Policy: no-referrer' ); // 在客户端收到 403 时启用
		$headerArray = array("Content-type:application/json;","Accept:application/json");
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if(!empty($aid))curl_setopt($ch, CURLOPT_REFERER, "https://www.bilibili.com/av$aid");
		else if(!empty($bid))curl_setopt($ch, CURLOPT_REFERER, "https://www.bilibili.com/BV$bvid");
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);
		$output = curl_exec($ch);
		curl_close($ch);
		$output = json_decode($output,true);
		return $output;
	}

	public static function url2aid($url) {
		$id=false;
		$aid_pattern = "#(https?:\/\/(www\.)?bilibili\.com\/video\/)?av(\d+)(\?p=\d{1,3})?#";
		if(preg_match($aid_pattern, $url, $preg)) {
			$id = $preg[3];
			//$id = preg_replace(".*av(\d+).*", "\1", $url);
		}
		return $id;
	}
	public static function url2bvid($url) {
		$id=false;
		$bvid_pattern = "#(https?:\/\/(www\.)?bilibili\.com\/video\/)?BV([a-zA-Z0-9]+)(\?p=\d{1,3})?#";
		if(preg_match($bvid_pattern, $url, $preg)) {
			$id = $preg[3];
			//$id = preg_replace(".*BV([a-zA-Z0-9]+).*", "\1", $url);
		}
		return $id;
	}
	public static function handleDesc($text) {
		$pattern = "##";
		return 0;	//@todo 简介文字的处理
	}

	public static function getInfo($input, $argv, $parser) {
		$parser->getOutput()->addModules('ext.biliget.list');
		if ( !empty( self::url2aid($input) ) ) {
			$aid = self::url2aid($input);
			$id = "av$aid";
			$data = self::getUrl("https://api.bilibili.com/x/web-interface/view?aid=$aid");
			$link = "https://bilibili.com/video/av$aid";
		}
		if ( !empty( self::url2bvid($input) ) ) {
			$bvid = self::url2bvid($input);
			$id = "BV$bvid";
			$data = self::getUrl("https://api.bilibili.com/x/web-interface/view?bvid=$bvid");
			$link = "https://bilibili.com/video/BV$bvid";
		}
		$code = $data['code'];
		if ($code==0) {
			$cover = $data['data']['pic'];
			$uploader = $data['data']['owner']['name'];
			$desc = str_replace("\n", "<br />", $data['data']['desc']);
			$title = $data['data']['title'];
			$pubdate = date("Y-m-d H:i", $data['data']['pubdate']);
			$duration = gmstrftime("%M:%S", $data['data']['pages'][0]['duration']);
			if ( !empty($argv['type']) && $argv['type']=='raw' ) {
				return "$cover`$title`$uploader`$title`$pubdate`$duration";
			}
			else if ( !empty($argv['type'] ) ) {
				$type = array(
					'封面' => $cover,
					'UP主'=> $uploader, 
					'简介' => $desc,
					'标题' => $title,
					'发布日期' => $pubdate,
					'时长' => $duration
					);
				$name = $argv['type'];
				return $type[trim($name)];
			}
			else {
				return "
				<div class=\"bili-info-card\">
					<div class=\"bili-info\">
						<div class=\"bili-info-cover\"><img src=\"$cover\" width=\"160px\" /></div>
						<p class=\"bili-info-duration\">$duration</p>
					</div>
					<div class=\"bili-info\">
						<p class=\"bili-info-title\" style=\"margin-bottom: 0\"><a href=\"$link\" title=\"$id\">$title</a></p>
						<p class=\"bili-info-date\"	style=\"margin-top: 0; margin-bottom: 0\">$pubdate</p>
						<p class=\"bili-info-uploader\" style=\"margin-top: 0;\">UP主：$uploader</p>
						<p class=\"bili-info-desc\">$desc</p>
					</div>
				</div>";
					}
				}
		else {
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
	//@todo 简介折叠、链接和作品号自动变蓝
	}
	public static function rawInfo($input, $argv, $parser, PPFrame $frame) {
		$type = ['type' => 'raw'];
		$info = self::getInfo($input, $type, $parser);
		$output = $parser->recursiveTagParse("{{#vardefine:info|$info}}", $frame);
		return $output;
	}// 仅用于debug
}
