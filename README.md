# wing-doc
>wing doc using regular expressions to achieve,
no context dependent,
so it can use in any php file system

#how to use? or you can use composer autoload
    include "Doc.php";
    include "WClass.php";
    include "WDoc.php";
    include "WFile.php";
    include "WFunction.php";
    
    $app = new \Wing\Doc\Doc("input dir","doc output dir");
    $app->run();


