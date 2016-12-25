# wing-doc
php auto create doc from file system
wing doc using regular expressions
No context dependent

#how to use? or use composer autoload
    include "Doc.php";
    include "WClass.php";
    include "WDoc.php";
    include "WFile.php";
    include "WFunction.php";
    
    $app = new \Wing\Doc\Doc("input dir","doc output dir");
    $app->run();


