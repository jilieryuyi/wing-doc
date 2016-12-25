# wing-doc
>wing doc using regular expressions to achieve,
no context dependent,
so it can use in any php file system

#how to use? or you can use composer autoload
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


