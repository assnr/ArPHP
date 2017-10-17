ArPHP
=====
ar a gift for coders
--------------------

## 2017/10/16  整合之前各个版本及arphp5.0.1发布
>后面长期更新版本, 文档后续放出
>支持composer, 支持php7, 长期文档支持版本

### 安装

1. composer require assnr/arphp
2. mkdir ori
3. cd ori
4. 新建入口文件 index.php
>$loader = require '../vendor/autoload.php';

>ar\core\Ar::init($loader);

5. 浏览器访问入口文件

## 2017/08/30  arphp4.0.1 下载方式，交流群
>文档地址：coop云开发（基于arphp4开发）http://www.coopcoder.com/ziyuan-982.shtml 网站改版中，请期待

>加入控制器service ，命名空间 ，ason配置，目录灵活配置，支持php7，需要php5.3以上，ATML全新模板引擎，更优化的代码结构，

>WEB目录隔离，更加安全，全新的框架，文档制作中。。。

>类加载模式：系统


>注意：start方式        {folder}/ArPHP/init.php
>
>                     {folder}/web/index.php      <?php  include '../ArPHP/init.php';



## 2017/06/20 更新 3.0版本 下载方式，交流群
>文档http://www.arphp.org/doc/index.html</br>
>加入ATML模板引擎，
>加入WEB CLI模式

>不兼容arphp3之前的版本，PHP 版本需要5.3及以上

>ATML风格一缆

    ATML纯HTML风格PHP模板引擎，支持模板重用,继承,导出,导入
    <if exp="$a == 123">
        a  {{array.info.user.name}}
    <elseif exp="$b == 111"/>
        b
    <else/>
        <if exp="$c == 123">
        c
        </if>
    </if>

    <for exp="$key in $lists" as="list">
       {{list.id}}
       <p>普通html代码</p>
       {{list.name}}
    </for>



## 2017/5/9更新 arphp2.0发布 下载方式，交流群

>文档http://www.arphp.org/doc/index.html

>增加Lib库文件目录，推荐公用Model ，Module放入目录,支持命名空间加载公共库
>如 arModule('Lib/Hello')->sayworld()  调用 namespace Lib/Module/Hello
>单文件框架保持同步</br>
>优化代码增强性能</br>
>增加ArView核心视图动态渲染模板主题加载，解决传统资源文件引入混乱问题，前后端不统一资源引入问题等</br>
>增加微信公众号组件</br>

## 2017/5/26    arphp2.0.1 支持php5.3以前的版本</br>

>增加 WEB_CLI模式
>// 以web cli 方式运行项目 入口文件cli.php
>define('AR_AS_WEB_CLI', true);
>include 'arphp.php';

>运行   php cli.php /main/User/info     执行main/UserController/infoAction 方法

### 更多使用帮助及例子

- git : https://github.com/assnr/ArPHP.git

- ArPHP开源群 : 259956472

- 讨论： http://www.coopcoder.com/wenda/?c-12.html http://blog.arphp.org/

- coop云开发：http://www.coopcoder.ccom (arphp开发，改版中。。。)
