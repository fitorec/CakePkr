PkrCake
====================================================================

**PkrCake** is a simple plugin for **CakePHP** that can help you reduce the number of HTTP requests your application makes, as well as provide some nice sugar for **JavaScript** and **CSS** files. It's partly intended as a way to get a 'build' stage without actually making the effort to write build scripts.


Installation
==================================================================
Use a submodule it's a **good idea**!:

  git submodule add https://github.com/fitorec/CakePkr.git [APP]/Plugin/Pkr

Config cakephp, you'll need to load the plugin:

  // in app/Config/bootstrap.php
  CakePlugin::load('Pkr');


Adding a alias for git:

  git config alias.pkr '!php [APP]/Console/cake.php Pkr.run'


Require
==================================================================

### - [**jsmin-php**](https://github.com/rgrove/jsmin-php/)

You should put JSMin in `[APP]/vendors/jsmin/jsmin.php` by default.

### - [**CssMin**](http://code.google.com/p/cssmin/)

The CSSMin library should be placed in `[APP]/vendors/cssmin/CssMin.php`.


Use
==================================================================
use:
  git pkr
and enjoy!

Hooksupport
==================================================================

