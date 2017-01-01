![WingDoc](https://github.com/jilieryuyi/wing-doc/blob/master/template/img/s1.png?raw=true)
# wing-doc
>wing doc using regular expressions to achieve,
no context dependent,
so it can use in any php file system

# how to use?
####1、Install chrome extension from directory /chrome/WingDoc or file /chrome/WingDoc.crx
####2、Use the following code to create a document
    include "vendor/autoload.php";
    $app = new \Wing\Doc\Doc("/Users/yuyi/Web/xiaoan/api",__DIR__."/doc");
    
    $app->addExcludePath([
        "vendor/*","Config/*","config/*",
        "public/*","database/*","tests/*"
    ]);
    $app->addExcludeFileName([
        "artisan","composer","app.php","web.php"
    ]);
    
    $app->run();
# http api doc demo
     string(6,64) means that the password form key length must be between 6-64
     /**
      * user login api
      *
      * @url http://api.local.com/user/login
      * @request number(11,11) phone 手机号码
      * @request string(6,64) password 密码
      * @response json
      */

####use json template and datetime format
      
      json template like this : * @request json topics [{"id":0,"name":"${string(0,0)}"},{"id":0,"name":"${string(0,0)}"}] 悬赏话题
      datetime format like this : * @request datetime time ${Y-m-d H:i:s} 时间

      /**
       * 发布悬赏
       *
       * @url http://api.xsl.com/reward/publish
       * @header string token
       * @request string title 悬赏标题
       * @request string describe 问题描述
       * @request json topics [{"id":0,"name":"${string(0,0)}"},{"id":0,"name":"${string(0,0)}"}] 悬赏话题
       * @request datetime time ${Y-m-d H:i:s} 时间
       * @request double money 悬赏金额
       * @response json
       */


