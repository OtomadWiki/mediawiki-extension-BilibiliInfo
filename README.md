# BiliGet
用于获取 bilibili 视频信息的 MediaWiki 插件

已知请求多次会被 412 以及 403，需要改进请求方法。请勿在同一页面内过多使用。

## 需求
* MediaWiki 1.35 (或以上版本)
* php-curl

## 安装
1. 进入 ```mediawiki/extensions``` 目录，使用如下命令：
```shell
git clone https://github.com/OtomadWiki/mediawiki-extension-BilibiliInfo BiliGet
```
2. 返回 MediaWiki 主目录，在 ```LocalSettings.php``` 文件中添加一行：<br />```wfLoadExtension( 'BiliGet' );```

## 配置
### 设置 Cookie
在 ```LocalSettings.php``` 中加入以下内容以设置 Cookie：
```PHP
$wfSetBiliCookie = '在此填入 Cookie'
```

### 设置 UserAgent
在 ```LocalSettings.php``` 中加入以下内容以设置 User-Agent：
```PHP
$wfSetUserAgent = '在此填入 User-Agent'
```

### Hack
**注意：不应在生产环境中使用，可能会导致 MediaWiki 更新出现问题**
若客户遇到了无法接收封面图片的问题，请将以下代码加入到 MediaWiki 主目录的 ```includes/WebStart.php``` 内：
```PHP
header( 'Referrer-Policy: no-referrer' );
```

## 使用方法
```HTML
<div class="bili-info-container">
	<biliget>av号或者BV号 或者B站视频地址</biliget>
	<biliget>av号或者BV号 或者B站视频地址</biliget>
</div>
```
<!--
正式用法：
\<biliget>
*av号或者BV号 或者B站视频地址*
*av号或者BV号 或者B站视频地址*
\</biliget>
-->

### 指定输出类型
```HTML
<biliget type="指定的信息类型">av号或者BV号 或者B站视频地址</biliget>
```
目前允许输出的基本信息：
* 封面 - 将会输出原始的超链接文本（不变蓝）
* UP主 - 将会输出该视频UP主的用户名
* 标题 - 将会输出完整的视频标题
* 简介
* 时长
* 发布日期

**范例：**
```HTML
<biliget type="标题">av2333</biliget>
```
将会输出：```【还是K歌向】新华保险入店歌```

## TODO
- [x] 输出单独信息
- [x] 设置请求 Cookie
- [ ] 允许同一标签内放置多个作品号或者链接
- [x] 视频简介折叠（用 overflow 属性隐藏了多余部分）
- [ ] 简介中的链接和作品号自动变蓝
- [ ] 并行发送请求
- [x] 请求返回结果缓存（实测保存页面后 MediaWiki 会缓存内容一段时间）
- [ ] 样式调整
- [ ] ~~客户端调用api~~（失败了 请求被浏览器阻止）
