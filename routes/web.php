<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->post('/register', 'AuthController@registerOrLogin');
$router->post('/login-email-or-facebook', 'AuthController@loginEmaiOrFacebook');
$router->post('/register-or-login', 'AuthController@registerOrLogin');
$router->put('/logout', 'AuthController@logout');
$router->get('/loginRestaurantOfficer', 'AuthController@loginRestaurantOfficer');
$router->post('/registerRestaurantOfficer', 'AuthController@register');
$router->post('/update-data-user', 'UserController@updateDataUser');


$router->get('/get-data-user/{idUser}', 'UserController@getDataUser');
$router->get('/get-list-deals', 'DashboardController@getDealsList');
$router->get('/get-data-restaurant', 'RestaurantController@getRestaurantData');
$router->get('/get-restaurant-hour', 'RestaurantController@getOpeningHours');
$router->get('/get-menu-categories', 'RestaurantController@getMenuCategory');
$router->get('/get-menu', 'RestaurantController@getMenu');
$router->post('/checkin', 'DashboardController@Checkin');
$router->put('/checkout', 'RestaurantController@checkout');
$router->post('/order', 'RestaurantController@placeOrder');
$router->post('/call', 'RestaurantController@callStaff');
$router->get('/getOrderHistory', 'RestaurantController@getOrderHistory');
$router->get('/anaberkah', 'RestaurantController@callAnaberkahNotif');
$router->get('/getOrderDetail', 'RestaurantController@getOrderDetail');
$router->get('/getFeedbacks', 'RestaurantController@getFeedbackQuestion');
$router->post('/answerFeedbacks', 'RestaurantController@answerFeedback');
$router->get('/getCustomers', 'RestaurantController@getCustomers');
$router->post('/sendRestaurantMessage', 'ChatController@sendRestaurantMessage');
$router->get('/getRestaurantName', 'RestaurantController@getRestaurantName');
$router->get('/getRestaurantCustomers', 'RestaurantController@getRestaurantCustomers');
$router->get('/getCheckinStatus', 'RestaurantController@getCheckinStatus');
$router->get('/getRestaurantResponseTime', 'RestaurantController@getRestaurantResponseTime');
$router->put('/updateRestaurantResponseTime', 'RestaurantController@updateRestaurantResponseTime');
$router->get('/getCallingList', 'RestaurantController@getCallingList');
$router->put('/respondCalling', 'RestaurantController@respondCalling');
$router->get('/getIncomingOrderList', 'RestaurantController@getIncomingOrderList');
$router->get('/getProcessedOrderList', 'RestaurantController@getProcessedOrderList');
$router->get('/getDoneOrderList', 'RestaurantController@getDoneOrderList');
$router->get('/getItemOrders', 'RestaurantController@getItemOrders');
$router->put('/cancelItemOrder', 'RestaurantController@cancelItemOrder');
$router->put('/updateOrderStatus', 'RestaurantController@updateOrderStatus');
$router->get('/getRestaurantMessages', 'ChatController@getRestaurantMessages');
$router->get('/getListConversation', 'ChatController@getListConversation');
$router->post('/sendFriendRequest', 'ChatController@sendFriendRequest');
$router->post('/sendPersonalMessage', 'ChatController@sendPersonalMessage');
$router->get('/getPersonalMessage', 'ChatController@getPersonalMessage');
$router->get('/getFriendRequests', 'ChatController@getFriendRequests');
$router->put('/updateFriendRequests', 'ChatController@updateFriendRequests');
$router->post('/saveMessageToBot', 'ChatController@saveMessageToBot');
$router->get('/getCallingHistory', 'RestaurantController@getCallingHistory');

//API untuk CMS
$router->post('/RegisterSuperAdmin', 'CMSController@RegisterSuperAdmin');
$router->post('/AddRestaurant', 'CMSController@AddRestaurant');
$router->post('/registerRestaurantOfficers', 'CMSController@registerRestaurantOfficers');
$router->get('/loginAdmin', 'CMSController@loginAdmin');
$router->get('/loginRestaurantAdmin', 'CMSController@loginRestaurantAdmin');



