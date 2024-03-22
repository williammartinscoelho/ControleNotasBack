<?php
global $routes;
$routes = array();

$routes['/users/login'] = '/users/login';
$routes['/users/new'] = '/users/new_record';
$routes['/users/feed'] = '/users/feed';
$routes['/users/{id}'] = '/users/view/:id';
$routes['/users/{id}/photos'] = '/users/photos/:id';
$routes['/users/{id}/follow'] = '/users/follow/:id';

$routes['/photos/random'] = '/photos/random';
$routes['/photos/new'] = '/photos/new_record';
$routes['/photos/{id}'] = '/photos/view/:id';
$routes['/photos/{id}/comment'] = '/photos/comment/:id';
$routes['/photos/{id}/like'] = '/photos/like/:id';

$routes['/comments/{id}'] = '/photos/delete_comment/:id';

//                                        '/controller/action' 
$routes['/products']                    = '/products/index';
$routes['/products/{id}']               = '/products/index/:id';
$routes['/products/{id}/values']        = '/products/values/:id';

$routes['/productValues']               = '/productValues/index';
$routes['/productValues/{id}']          = '/productValues/index/:id';
$routes['/productValues/product/{id}']  = '/productValues/product/:id';

$routes['/invoices']                       = '/invoices/index';
$routes['/invoices/{id}']                  = '/invoices/index/:id';

$routes['/stores']                      = '/stores/index';
$routes['/stores/{id}']                 = '/stores/index/:id';
$routes['/stores/{id}/invoices']           = '/stores/invoices/:id';

$routes['/keys']                        = '/keys/index';