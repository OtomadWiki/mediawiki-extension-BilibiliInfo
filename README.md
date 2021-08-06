# BiliGet
用于获取 bilibili 视频信息的 MediaWiki 插件

知请求多次会被 412 以及 403，需要改进请求方法。请勿在同一页面内过多使用。

## 安装
1. 进入 ```mediawiki/extensions``` 目录，使用如下命令：
```shell
git clone https://github.com/OtomadWiki/mediawiki-extension-BilibiliInfo BiliGet
```
2. 返回 MediaWiki 主目录，在 ```LocalSettings.php``` 文件中添加一行：<br />```wfLoadExtension( 'BIliGet' );```

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
* 封面 - 将会输出原始的超链接文本
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
- [ ] 设置请求 Cookie
- [ ] 允许同一标签内放置多个作品号或者链接
- [ ] 视频简介折叠
- [ ] 简介中的链接和作品号自动变蓝
- [ ] 并行发送请求
- [ ] 请求返回结果缓存
- [ ] 样式调整
- [ ] 客户端调用api
