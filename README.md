# KickerRoutes
KickerRoutes is a CodeIgniter 3 (develop) library that gives you a new way to map and name your routes without any modification to the CodeIgniter core.
KickerRoutes also plays nice with any existing routes you may have.

## Credits and Inspiration 
I was inspired to create this library after looking at Jamie Rumbelow's library called Pigeon. You can check his project out by going [here] (https://github.com/jamierumbelow/pigeon/)

# Synopsis
```php
//./application/config/routes.php
$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route = KickerRoutes::map(function ($r) {
    $r->get('product/(:num)','catalog/product_lookup','product_lookup')
      ->post('create_product','catalog/product_create','product_create')
      ->put('product/(:num)/(:any)','catalog/product_create_attribute','product_attribute')
      ->delete('product/(:num)','catalog/product_remove','product_remove')
      ->ALL('product_default/:(num)','catalog/product_desciption','product_info');

    $r->addCollection('rest',array('GET','POST'))
      ->rest('api/v1','api','api-routes')
}, $route);
```

# Installation
The preferred installation method is via Composer:
add:
```
require: "williamknauss/kickerroutes": "1.0"
```
to your composer file then execute
```
composer update
```

# How it works
You send your existing routes, and define new ones via KickerRoutes DSL inside your ./application/config/routes.php file. KickerRoutes builds up the new routes array and returns it to CodeIgniter via the map method.
```php
$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route = KickerRoutes::map(function ($r) {
    //define new easy routes here
},$route);
```

# Basic Routing
The basic routing method is `route`. You pass a http verb, to, from, and a name.
```
$r->route('GET','page_one','welcome/index','default_page');
```
You may also use a more sugar/shorthand syntax by just calling the http verb method
```
$r->get('page_one','welcome/index','default_page');
```

You may also define custom http verbs using the same methodalogy 
```
$r->route('CONNECT','page_one','welcome/index','default_page'); //OR
$r->connect('page_one','welcome/index','default_page');
```

If you want a route to work no matter what the verb is use the `ALL` method
```
$r->ALL('default','welcome/index','default_page');
```

# Verb collections
KickerRoutes allows you to create verb collections in order to keep your code D.R.Y.
To create a collection call `addCollection` with two parameters. The first parameter being the name of 
the collection and the second being an array of http verbs the collection should use.

Then you can call your collection just like a http verb.

```
$r->addCollection('restroutes',array('GET','POST'));
$r->restroutes('welcome','welcome/index','somename');
```

# Using Named Routes
To use a named route you simply call `KickerRoutes::url()` which takes three parameters. The first parameter being the name you gave the route.
The second being an array of parameters the route should use. The third being the http verb that route is named for.

```
KickerRoutes::url('product',array(1),'get');
```
