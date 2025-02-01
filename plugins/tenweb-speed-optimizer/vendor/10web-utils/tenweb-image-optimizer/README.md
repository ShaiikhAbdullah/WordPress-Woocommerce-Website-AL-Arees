## Image optimizer package
### Usage

#### Call Init::getInstance() method before plugins_loaded hook. It contains rest init etc.
```angular2html
include_once 'vendor/autoload.php';

\TenWebIO\PreInit::check($origin = 'booster || io');
```

#### This is a method for bulk optimization
```angular2html
use TenWebIO\CompressService;
$compress = new CompressService();
$compress->compressBulk();
```

#### This is a method for single optimization
```angular2html
use TenWebIO\CompressService;
$compress = new CompressService();
$compress->compressOne($post_id);
```

