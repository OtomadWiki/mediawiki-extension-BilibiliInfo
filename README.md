# BiliGet
用于获取 bilibili 视频信息的 MediaWiki 插件

暂未完善，请勿使用。

已知请求多次会被 412 以及 403，需要改进办法。请勿在同一页面内过多使用。

## 安装
1. 进入 ```mediawiki/extensions``` 目录，使用该命令：```git clone https://github.com/OtomadWiki/mediawiki-extension-BilibiliInfo BiliGet```
2. 在 ```LocalSettings.php``` 中添加一行：```wfLoadExtension( 'BIliGet' );```

## 使用方法
```
\<div class="bili-info-container"><br />
&emsp;\<biliget>*av号或者BV号 或者B站视频地址*\</biliget><br />
&emsp;\<biliget>*av号或者BV号 或者B站视频地址*\</biliget><br />
\</div>
```
<!--
或者：
&emsp;\<biliget>
*av号或者BV号 或者B站视频地址*
*av号或者BV号 或者B站视频地址*
\</biliget><br />
-->

### 指定输出类型
```
\<biliget type="*指定的信息类型*">*av号或者BV号 或者B站视频地址*\</biliget><br />
```
目前允许输出的基本信息：
* 封面 - 将会输出原始的超链接文本
* UP主 - 将会输出该视频UP主的用户名
* 标题 - 将会输出完整的视频标题
* 简介
* 时长
* 发布日期

**范例：**
```
\<biliget type="标题"\>av2333\</biliget><br />
```
将会输出：
```【还是K歌向】新华保险入店歌```

## TODO
- [x] 输出单独信息
- [ ] 视频简介折叠
- [ ] 简介中的链接和作品号自动变蓝
- [ ] 允许同一标签内放置多个作品号或者链接
- [ ] 并行发送请求
- [ ] 请求返回结果缓存
- [ ] 客户端调用api
