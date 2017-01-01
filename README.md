![WingDoc](https://github.com/jilieryuyi/wing-doc/blob/master/template/img/s1.png?raw=true)
# wing-doc
>wing doc using regular expressions to achieve,
no context dependent,
so it can use in any php file system

# how to use?
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


