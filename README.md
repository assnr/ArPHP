ArPHP
=====

ArPHP For Coders

ArPHP框架,单文件框架 for coder
高性能 & 极度轻巧 & 組件化 & 伸縮性


回望第一次arphp提交到现在都三年多了，时间过的真快，期间
arphp开发了很多优秀项目稳定运行。那么如果你想告别复杂冗余的大型框架，告别新手实习生都会的大众框架，
用一个高性能的极简框架来开发掌控项目，它有多种模式，架构？二次开发？嵌入开发？切入开发？
定向开发？这些arphp统统的都可以拿下，何不让自己潇洒的做一次统帅？选择arphp，你值得拥有。
更新一次不容易，一个人写文档，维护官网,一个群，开发一堆东西，文档更新太晚了, 说声sorry

还是不容易的，喜欢？用过？了解过？麻烦各位看官赏脸star一下

2017/08/30  arphp4.0.1
加入控制器service ，命名空间 ，ason配置，目录灵活配置，支持php7，需要php5.3以上，ATML全新模板引擎，更优化的代码结构，
WEB目录隔离，更加安全，全新的框架，文档制作中。。。

注意：start方式        {folder}/ArPHP/init.php
                     {folder}/web/index.php      <?php  include '../ArPHP/init.php';

赶快更新吧，arphp官方群里附上最新demo
ArPHP开源群 : 259956472    wwww.arphp.org(待更新...)

git : https://github.com/assnr/ArPHP.git（最新版）

2017/06/20 更新 3.0版本
加入ATML模板引擎，
加入WEB CLI模式

不兼容arphp3之前的版本，PHP 版本需要5.3及以上

ATML风格一缆

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



2017/5/9更新 arphp2.0发布

增加Lib库文件目录，推荐公用Model ，Module放入目录,支持命名空间加载公共库
如 arModule('Lib/Hello')->sayworld()  调用 namespace Lib/Module/Hello
单文件框架保持同步
优化代码增强性能
增加ArView核心视图动态渲染模板主题加载，解决传统资源文件引入混乱问题，前后端不统一资源引入问题等
增加微信公众号组件

2017/5/26    arphp2.0.1 支持php5.3以前的版本

增加 WEB_CLI模式
// 以web cli 方式运行项目 入口文件cli.php
define('AR_AS_WEB_CLI', true);
include 'arphp.php';


运行   php cli.php /main/User/info     执行main/UserController/infoAction 方法



更多使用帮助及例子请加官方qq群，





基本使用：


初始化页面
只要在入口文件里包含ArPHP初始化文件 init.php
或者引入arphp.php(单文件框架和主框架功能一模一样压缩版便于携带切入开发)



index.php

代码

1   include_once ArPHP/init.php

2   include_once arphp.php(单文件框架和主框架功能一模一样压缩版便于携带切入开发)



访问index.php即可件 hello ArPHP!


