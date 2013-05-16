CakeInfo for CakePHP 2.3.x
======================

CakeInfo is like phpinfo() for CakePHP.


Abstract
------
cakeinfo() helper for CakePHP.
http://bakery.cakephp.org/articles/Siegfried/2007/03/13/cakeinfo-helper-for-cakephp

This cakeinfo is embedded version.


How to use cakeinfo
------

### Controller ###
App::uses('Sanitize', 'Utility');
App::import('Vendor', 'cakeinfo');

class FooController extends AppController {

  public function foo() {
    $info = new CakeInfo();
    $info->execute();
    if (!defined('DATABASE_CONFIG_FLAG')) {
      unset($info->values['Database']);
    }

    $this->layout = null;
    $this->set('info', $info);
}


### View ###

Copy to cakeinfo.ctp to your View path.

