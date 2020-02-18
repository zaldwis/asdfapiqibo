<?php

namespace App\Http\Controllers;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Checkins;
use App\Models\Restaurants;
use App\Models\MenuCategories;
use App\Models\Menus;
// use App\Models\Tables;
use App\Models\Orders;
use App\Models\ItemOrders;
use App\Models\Callings;
use App\Models\FeedbackAnswers;
use DateTime;
use App\Services\NotificationService;
use LaravelFCM\Message\Topics;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;

class RestaurantController extends Controller
{
    protected $image_profile_folder = '/public/deals_picture/';


    public function __construct(){
        $this->middleware('auth');
    }

    //START
    //API FOR CUSTOMER APPS

    public function checkout(Request $request){
        $currentDate = new DateTime();
        try {
            $checkout = Checkins::where('id', $request->checkin_id)->first();
            $checkout->checkout_datetime = $currentDate;
            $checkout->is_checkin = '0';
            $checkout->saveOrFail();

            $statusCode = 200;
            $response = [
                'error' => false,
                'message' => 'Checkout Success',
            ];    
           
        } catch (Exception $e) {
            $statusCode = 404;
            $response = [
                'error' => true,
                'message' => 'Checkout Failed',
            ];    
        }finally{
            return response($response,$statusCode)->header('Content-Type','application/json');
        }
    }

    public function getRestaurantData(Request $request){
        try{
            $restaurant = Restaurants::where('id', $request->id_restaurant)->first();
            if(!$restaurant){
                $statusCode = 404;
                $response = [
                    'error' => true,
                    'message' => 'Data Tidak Ada',
                ];
            }else{
                $statusCode = 200;
                $response = [
                    'error' => false,
                    'message' => 'Data Restaurant',
                    'dataRestaurant' => $restaurant,
                ];
            }  
    
        }catch (Exception $e){

            $statusCode = 404;
            $response = [
                'error' => true,
                'message' => 'Gagal',
            ];    
        }finally{
            return response($response,$statusCode)->header('Content-Type','application/json');
        }
    }

    public function getOpeningHours(Request $request){
        $current_date = new DateTime();
        $day = $current_date->format('l');
        try{
            $open = DB::table('opening_hours')->where('id_restaurant', $request->id_restaurant)->value($day);

            if(!$open){
                $statusCode = 404;
                $response = [
                    'error' => true,
                    'message' => 'Data Tidak Ada',
                ];
            }else{
                $statusCode = 200;
                $response = [
                'error' => false,
                'message' => 'Data Opening Hour',
                'dataOpeningHour' => $open,
                ];
            }  
        }catch (Exception $e){
            $statusCode = 404;
            $response = [
                'error' => true,
                'message' => 'Gagal',
            ];    
        }finally{
            return response($response,$statusCode)->header('Content-Type','application/json');
        }
    }

    public function getMenuCategory(Request $request){
        try {
            $dataCategories = MenuCategories::where('id_restaurant', $request->id_restaurant)->get();
            if(!$dataCategories->isEmpty()){
                $statusCode = 200;
                $response = [
                    'error' => false,
                    'message' => 'Data ditemukan',
                    'dataCategories' => $dataCategories
                ];    
            } else {
                $statusCode = 404;
                $response = [
                    'error' => false,
                    'message' => 'Data tidak ditemukan',
                ];    
            }
        } catch (Exception $e) {
            $statusCode = 500;
            $response = [
                'error' => true,
                'message' => 'Server error',
            ];    
        }finally{
            return response($response,$statusCode)->header('Content-Type','application/json');
        }
    }

    public function getMenu(Request $request){
        try {
            $dataMenus = Menus::where('id_menu_category', $request->id_menu_category)->get();
            if(!$dataMenus->isEmpty()){
                $statusCode = 200;
                $response = [
                    'error' => false,
                    'message' => 'Data ditemukan',
                    'dataMenus' => $dataMenus
                ];    
            } else {
                $statusCode = 404;
                $response = [
                    'error' => false,
                    'message' => 'Data tidak ditemukan',
                ];    
            }
        } catch (Exception $e) {
            $statusCode = 500;
            $response = [
                'error' => true,
                'message' => 'Server error',
            ];    
        }finally{
            return response($response,$statusCode)->header('Content-Type','application/json');
        }
    }
    
    public function placeOrder(Request $request){
        try {
            $orders = new Orders();
            $orders->id_restaurant = $request->id_restaurant;
            $orders->id_user = $request->id_user;
            $orders->id_table = $request->id_table;
            $orders->status = '1';
            $orders->grand_total = $request->grand_total;
            $orders->saveOrFail();
            $order_id = $orders->id;

            foreach ($request->input('menu') as $key => $value) {               
                $itemorders = new ItemOrders();
                $itemorders->id_order = $order_id;
                $itemorders->id_menu = $value["id"];
                $itemorders->quantity = $value["quantity"];
                $itemorders->unit_price = $value["price"];
                $itemorders->total_price = $value["total_price"];
                if($value["note"]!=' '){
                    $itemorders->order_message = $value["note"];
                }
                $itemorders->saveOrFail();
            }     
            
            $tableNumber = DB::table('tables')->where('id', $request->id_table)->value('table_number');
            $service= new NotificationService();
            $callService= $service->sendNotifToGroup($request->id_restaurant,'order','Pesanan Baru','Pesanan untuk meja '.$tableNumber.', tolong hidangkan yang terbaik.');

           
            $statusCode = 200;
            $response = [
                'error' => false,
                'message' => 'Success'
            ];    
        } catch (Exception $e) {
            $statusCode = 500;
            $response = [
                'error' => true,
                'message' => 'Server error',
            ];    
        }finally{
            return response($response,$statusCode)->header('Content-Type','application/json');
        }
    }

    public function callStaff(Request $request){
        try {
            $callings = new Callings();
            $callings->id_restaurant = $request->id_restaurant;
            $callings->id_table = $request->id_table;
            $callings->id_user = $request->id_user;
            $callings->calling_type = $request->calling_type;
            $callings->saveOrFail();
            
            $tableNumber = DB::table('tables')->where('id', $request->id_table)->value('table_number');
            $service= new NotificationService();
            $callService= $service->sendNotifToGroup($request->id_restaurant,'calling','Pelanggan Butuh Bantuan','Pelanggan dengan nomor meja '.$tableNumber.', butuh bantuan kamu nih.');

           
            $statusCode = 200;
            $response = [
                'error' => false,
                'message' => 'Success'
            ];    
        } catch (Exception $e) {
            $statusCode = 500;
            $response = [
                'error' => true,
                'message' => 'Server error',
            ];    
        }finally{
            return response($response,$statusCode)->header('Content-Type','application/json');
        }
    }

    public function getOrderHistory(Request $request){
        try {
            $data = DB::table('orders')
            ->join('users', 'orders.id_user', '=', 'users.id')
            ->join('tables', 'orders.id_table', '=', 'tables.id')
            ->select('orders.*','tables.table_number')
            ->where('orders.id_restaurant', $request->id_restaurant)
            ->where('orders.id_user', $request->id_user)
            ->orderBy('orders.created_at', 'DESC')
            ->get();

            if(!$data->isEmpty()){
                $statusCode = 200;
                $response = [
                    'error' => false,
                    'message' => 'Data ditemukan',
                    'dataOrders' => $data
                ];    
            } else {
                $statusCode = 404;
                $response = [
                    'error' => false,
                    'message' => 'Data tidak ditemukan',
                ];    
            }
        } catch (Exception $e) {
            $statusCode = 500;
            $response = [
                'error' => true,
                'message' => 'Server error',
            ];    
        }finally{
            return response($response,$statusCode)->header('Content-Type','application/json');
        }
    }

    public function getCallingHistory(Request $request){
        try {
            $data = DB::table('callings')
            ->join('users', 'callings.id_user', '=', 'users.id')
            ->join('tables', 'callings.id_table', '=', 'tables.id')
            ->select('callings.*','tables.table_number')
            ->where('callings.id_restaurant', $request->id_restaurant)
            ->where('callings.id_user', $request->id_user)
            ->orderBy('callings.created_at', 'DESC')
            ->get();

            if(!$data->isEmpty()){
                $statusCode = 200;
                $response = [
                    'error' => false,
                    'message' => 'Data ditemukan',
                    'dataCallings' => $data
                ];    
            } else {
                $statusCode = 404;
                $response = [
                    'error' => false,
                    'message' => 'Data tidak ditemukan',
                ];    
            }
        } catch (Exception $e) {
            $statusCode = 500;
            $response = [
                'error' => true,
                'message' => 'Server error',
            ];    
        }finally{
            return response($response,$statusCode)->header('Content-Type','application/json');
        }
    }

    public function getOrderDetail(Request $request){
        try {
            $dataItemOrder = DB::table('item_orders')
            ->join('menus', 'item_orders.id_menu', '=', 'menus.id')
            ->select('item_orders.*','menus.name','menus.image')
            ->where('item_orders.id_order', $request->id_order)
            ->get();

            if(!$dataItemOrder->isEmpty()){
                $statusCode = 200;
                $response = [
                    'error' => false,
                    'message' => 'Data ditemukan',
                    'dataItemOrders' => $dataItemOrder
                ];    
            } else {
                $statusCode = 404;
                $response = [
                    'error' => false,
                    'message' => 'Data tidak ditemukan',
                ];    
            }
        } catch (Exception $e) {
            $statusCode = 500;
            $response = [
                'error' => true,
                'message' => 'Server error',
            ];    
        }finally{
            return response($response,$statusCode)->header('Content-Type','application/json');
        }
    }

    public function getFeedbackQuestion(Request $request){
        try {
            $data = DB::table('feedbacks')
            ->leftJoin('feedback_optionals', 'feedbacks.id', '=', 'feedback_optionals.id_feedback')
            ->select('feedbacks.*','feedback_optionals.option_A','feedback_optionals.option_B','feedback_optionals.option_C','feedback_optionals.option_D')
            ->where('feedbacks.id_restaurant', $request->id_restaurant)
            ->orderBy('feedbacks.id', 'ASC')
            ->get();

            if(!$data->isEmpty()){
                $statusCode = 200;
                $response = [
                    'error' => false,
                    'message' => 'Data ditemukan',
                    'dataFeedbacks' => $data
                ];    
            } else {
                $statusCode = 404;
                $response = [
                    'error' => false,
                    'message' => 'Data tidak ditemukan',
                ];    
            }
        } catch (Exception $e) {
            $statusCode = 500;
            $response = [
                'error' => true,
                'message' => 'Server error',
            ];    
        }finally{
            return response($response,$statusCode)->header('Content-Type','application/json');
        }
    }

    public function answerFeedback(Request $request){
        try {
            foreach ($request->input('feedback') as $key => $value) {               
                $itemorders = new FeedbackAnswers();
                $itemorders->id_feedback = $value["id_feedback"];
                $itemorders->id_user = $request->id_user;
                if($value["answer"]!=' '){
                    $itemorders->answer = $value["answer"];
                }
                $itemorders->saveOrFail();
            }     
            
            $statusCode = 200;
            $response = [
                'error' => false,
                'message' => 'Success'
            ];    
        } catch (Exception $e) {
            $statusCode = 500;
            $response = [
                'error' => true,
                'message' => 'Server error',
            ];    
        }finally{
            return response($response,$statusCode)->header('Content-Type','application/json');
        }
    }

    public function getCustomers(Request $request){
        try {
            $data = DB::table('checkins')
            ->Join('users', 'checkins.id_user', '=', 'users.id')
            ->select('checkins.id_user','users.name','users.image')
            ->where('checkins.id_restaurant', $request->id_restaurant)
            ->where('checkins.is_checkin', '1')
            ->orderBy('users.name', 'ASC')
            ->get();

            if(!$data->isEmpty()){
                $statusCode = 200;
                $response = [
                    'error' => false,
                    'message' => 'Data ditemukan',
                    'dataCustomers' => $data
                ];    
            } else {
                $statusCode = 404;
                $response = [
                    'error' => false,
                    'message' => 'Data tidak ditemukan',
                ];    
            }
        } catch (Exception $e) {
            $statusCode = 500;
            $response = [
                'error' => true,
                'message' => 'Server error',
            ];    
        }finally{
            return response($response,$statusCode)->header('Content-Type','application/json');
        }
    }

    public function getCheckinStatus(Request $request){
        try {
            $checkinStatus = DB::table('checkins')->where('id', $request->id_checkin)->value('is_checkin');
            
            $statusCode = 200;
            $response = [
                'error' => false,
                'message' => 'Data ditemukan',
                'checkinStatus' => $checkinStatus
            ];    
        } catch (Exception $e) {
            $statusCode = 500;
            $response = [
                'error' => true,
                'message' => 'Server error',
            ];    
        }finally{
            return response($response,$statusCode)->header('Content-Type','application/json');
        }
    }

    //END
    //API FOR CUSTOMER APPS


    //START
    //API FOR RESTAURANT APPS

    public function getRestaurantName(Request $request){
        try {

            $restaurantName = DB::table('restaurants')->where('id', $request->id_restaurant)->value('name');

            if($restaurantName){
                $statusCode = 200;
                $response = [
                    'error' => false,
                    'message' => 'Data ditemukan',
                    'restaurantName' => $restaurantName
                ];    
            } else {
                $statusCode = 404;
                $response = [
                    'error' => false,
                    'message' => 'Data tidak ditemukan',
                ];    
            }
        } catch (Exception $e) {
            $statusCode = 500;
            $response = [
                'error' => true,
                'message' => 'Server error',
            ];    
        }finally{
            return response($response,$statusCode)->header('Content-Type','application/json');
        }
    }    

    public function getRestaurantCustomers(Request $request){
        try {
            $data = DB::table('checkins')
            ->Join('users', 'checkins.id_user', '=', 'users.id')
            ->Join('tables', 'checkins.id_table', '=', 'tables.id')
            ->select('checkins.id','checkins.id_user','users.name','users.image','tables.table_number','checkins.checkin_datetime')
            ->where('checkins.id_restaurant', $request->id_restaurant)
            ->where('checkins.is_checkin', '1')
            ->orderBy('checkins.checkin_datetime', 'ASC')
            ->get();

            if(!$data->isEmpty()){
                $statusCode = 200;
                $response = [
                    'error' => false,
                    'message' => 'Data ditemukan',
                    'dataCustomers' => $data
                ];    
            } else {
                $statusCode = 404;
                $response = [
                    'error' => false,
                    'message' => 'Data tidak ditemukan',
                ];    
            }
        } catch (Exception $e) {
            $statusCode = 500;
            $response = [
                'error' => true,
                'message' => 'Server error',
            ];    
        }finally{
            return response($response,$statusCode)->header('Content-Type','application/json');
        }
    }


    public function getRestaurantResponseTime(Request $request){
        try {
            $checkinStatus = DB::table('restaurants')->where('id', $request->id_restaurant)->value('max_response_time');
            
            $statusCode = 200;
            $response = [
                'error' => false,
                'message' => 'Data ditemukan',
                'restaurantResponseTime' => $checkinStatus
            ];    
        } catch (Exception $e) {
            $statusCode = 500;
            $response = [
                'error' => true,
                'message' => 'Server error',
            ];    
        }finally{
            return response($response,$statusCode)->header('Content-Type','application/json');
        }
    }

    public function updateRestaurantResponseTime(Request $request){
        try{
            $dataUser=Restaurants::where('id','=',$request->id_restaurant)->first();        

            if($request->max_response_time != ""){
                $dataUser->max_response_time = $request->max_response_time;
            }

            $dataUser->saveOrFail();
    
            $statusCode = 200;
            $response = [
                'error' => false,
                'message' => 'update berhasil',
            ];

        }catch (Exception $e){

            $statusCode = 404;
            $response = [
                'error' => true,
                'message' => 'Gagal update',
            ];    
        }finally{
            return response($response,$statusCode)->header('Content-Type','application/json');
        }
    }

    public function getCallingList(Request $request){
        try {
            $data = DB::table('callings')
            ->Join('tables', 'callings.id_table', '=', 'tables.id')
            ->Join('users', 'callings.id_user', '=', 'users.id')
            ->select('callings.id','tables.table_number','users.name','callings.calling_type','callings.created_at')
            ->where('callings.id_restaurant', $request->id_restaurant)
            ->where('callings.is_active', '1')
            ->orderBy('callings.created_at', 'ASC')
            ->get();

            if(!$data->isEmpty()){
                $statusCode = 200;
                $response = [
                    'error' => false,
                    'message' => 'Data ditemukan',
                    'dataCallings' => $data
                ];    
            } else {
                $statusCode = 404;
                $response = [
                    'error' => false,
                    'message' => 'Data tidak ditemukan',
                ];    
            }
        } catch (Exception $e) {
            $statusCode = 500;
            $response = [
                'error' => true,
                'message' => 'Server error',
            ];    
        }finally{
            return response($response,$statusCode)->header('Content-Type','application/json');
        }
    }

    public function respondCalling(Request $request){
        try{
            $data=Callings::where('id','=',$request->id_calling)->first();
            if($data->is_active == "1"){
                $FCMToken = DB::table('users')->where('id', $data->id_user)->value('device_token');
                $service= new NotificationService();
                if($request->status == "Reject"){
                    $data->is_active = "0";
                    $data->saveOrFail();
                    $callService= $service->sendNotifToUser($FCMToken,'calling','Panggilan Ditolak','Staff kami nampaknya sedang sibuk dan tidak bisa menanggapi panggilan anda.');
                }else if($request->status == "Responded"){
                    $data->is_active = "2";
                    $data->saveOrFail();
                    $callService= $service->sendNotifToUser($FCMToken,'calling','Panggilan Diterima','Mohon Tunggu, Staff kami sudah menerima panggilan anda dan akan segera datang.');
                }                
    
                $statusCode = 200;
                $response = [
                    'error' => false,
                    'message' => 'update berhasil',
                ];
            }else{
                $statusCode = 404;
                $response = [
                    'error' => true,
                    'message' => 'Gagal update',
                ];   
            }   
        }catch (Exception $e){

            $statusCode = 404;
            $response = [
                'error' => true,
                'message' => 'Gagal update',
            ];    
        }finally{
            return response($response,$statusCode)->header('Content-Type','application/json');
        }
    }

    public function getIncomingOrderList(Request $request){
        try {
            $data = DB::table('orders')
            ->Join('tables', 'orders.id_table', '=', 'tables.id')
            ->Join('users', 'orders.id_user', '=', 'users.id')
            ->select('orders.id','tables.table_number','users.name','orders.created_at','orders.grand_total')
            ->where('orders.id_restaurant', $request->id_restaurant)
            ->where('orders.status', '1')
            ->orderBy('orders.created_at', 'ASC')
            ->get();

            if(!$data->isEmpty()){
                $statusCode = 200;
                $response = [
                    'error' => false,
                    'message' => 'Data ditemukan',
                    'dataOrders' => $data
                ];    
            } else {
                $statusCode = 404;
                $response = [
                    'error' => false,
                    'message' => 'Data tidak ditemukan',
                ];    
            }
        } catch (Exception $e) {
            $statusCode = 500;
            $response = [
                'error' => true,
                'message' => 'Server error',
            ];    
        }finally{
            return response($response,$statusCode)->header('Content-Type','application/json');
        }
    }

    public function getProcessedOrderList(Request $request){
        try {
            $data = DB::table('orders')
            ->Join('tables', 'orders.id_table', '=', 'tables.id')
            ->Join('users', 'orders.id_user', '=', 'users.id')
            ->select('orders.id','tables.table_number','users.name','orders.created_at','orders.grand_total')
            ->where('orders.id_restaurant', $request->id_restaurant)
            ->where('orders.status', '2')
            ->orderBy('orders.created_at', 'ASC')
            ->get();

            if(!$data->isEmpty()){
                $statusCode = 200;
                $response = [
                    'error' => false,
                    'message' => 'Data ditemukan',
                    'dataOrders' => $data
                ];    
            } else {
                $statusCode = 404;
                $response = [
                    'error' => false,
                    'message' => 'Data tidak ditemukan',
                ];    
            }
        } catch (Exception $e) {
            $statusCode = 500;
            $response = [
                'error' => true,
                'message' => 'Server error',
            ];    
        }finally{
            return response($response,$statusCode)->header('Content-Type','application/json');
        }
    }

    public function getDoneOrderList(Request $request){
        try {
            $current_date = new \DateTime();
            $dt = $current_date->format('Y-m-d');
            $data = DB::table('orders')
            ->Join('tables', 'orders.id_table', '=', 'tables.id')
            ->Join('users', 'orders.id_user', '=', 'users.id')
            ->select('orders.id','tables.table_number','users.name','orders.created_at','orders.grand_total')
            ->where('orders.id_restaurant', $request->id_restaurant)
            ->where('orders.status', '3')
            ->where('orders.created_at', 'LIKE', '%' . $dt . '%')
            ->orderBy('orders.created_at', 'ASC')
            ->get();

            if(!$data->isEmpty()){
                $statusCode = 200;
                $response = [
                    'error' => false,
                    'message' => 'Data ditemukan',
                    'dataOrders' => $data
                ];    
            } else {
                $statusCode = 404;
                $response = [
                    'error' => false,
                    'message' => 'Data tidak ditemukan',
                ];    
            }
        } catch (Exception $e) {
            $statusCode = 500;
            $response = [
                'error' => true,
                'message' => 'Server error',
            ];    
        }finally{
            return response($response,$statusCode)->header('Content-Type','application/json');
        }
    }

    public function getItemOrders(Request $request){
        try {
            $data = DB::table('item_orders')
            ->Join('menus', 'item_orders.id_menu', '=', 'menus.id')
            ->select('item_orders.id','menus.name','menus.image','item_orders.quantity','item_orders.unit_price','item_orders.total_price','item_orders.order_message','item_orders.is_cancel','item_orders.cancel_message')
            ->where('item_orders.id_order', $request->id_order)
            ->orderBy('menus.name', 'ASC')
            ->get();

            if(!$data->isEmpty()){
                $statusCode = 200;
                $response = [
                    'error' => false,
                    'message' => 'Data ditemukan',
                    'dataItemOrder' => $data
                ];    
            } else {
                $statusCode = 404;
                $response = [
                    'error' => false,
                    'message' => 'Data tidak ditemukan',
                ];    
            }
        } catch (Exception $e) {
            $statusCode = 500;
            $response = [
                'error' => true,
                'message' => 'Server error',
            ];    
        }finally{
            return response($response,$statusCode)->header('Content-Type','application/json');
        }
    }

    public function cancelItemOrder(Request $request){
        try{
            $dataItemOrder=ItemOrders::where('id','=',$request->id_itemorder)->first();        
            $dataItemOrder->is_cancel = $request->is_cancel;
            $dataItemOrder->cancel_message = $request->cancel_message;
            $dataItemOrder->saveOrFail();

            $dataOrder=Orders::where('id','=',$dataItemOrder->id_order)->first();
            $dataOrder->grand_total = $dataOrder->grand_total - $dataItemOrder->total_price;
            $dataOrder->saveOrFail();

            $dataAllItem=ItemOrders::where('id_order','=',$dataItemOrder->id_order)->get();
            $countIsCancel = 0;
            $countItemCanceled = 0;
            foreach ($dataAllItem as $key) {  
                $countIsCancel = $countIsCancel + 1;
                $countItemCanceled = $countItemCanceled + $key->is_cancel;       
            }
            
           
            if($countIsCancel == $countItemCanceled){
                $dataOrder->status = '4';
                $dataOrder->saveOrFail();
            }

            $TokenFCM = DB::table('users')->where('id', $dataOrder->id_user)->value('device_token');
            $service= new NotificationService();
            $callService= $service->sendNotifToUser($TokenFCM,'order_canceled','Pesanan Dibatalkan','Sebagian atau Seluruh pesanan kamu dibatalkan oleh restaurant, tekan untuk lihat detail.');
        
    
            $statusCode = 200;
            $response = [
                'error' => false,
                'message' => 'update berhasil',
            ];

        }catch (Exception $e){

            $statusCode = 404;
            $response = [
                'error' => true,
                'message' => 'Gagal update',
            ];    
        }finally{
            return response($response,$statusCode)->header('Content-Type','application/json');
        }
    }

    public function updateOrderStatus(Request $request){
        try{
            $dataOrder=Orders::where('id','=',$request->id_order)->first();
            if($dataOrder->status != '4'){
                $dataOrder->status = $request->status;
                $dataOrder->saveOrFail();
    
                $TokenFCM = DB::table('users')->where('id', $dataOrder->id_user)->value('device_token');
                $service= new NotificationService();
                if($request->status == '2'){
                    $callService= $service->sendNotifToUser($TokenFCM,'order_updated','Pesanan Diterima','Hore, pesananmu sudah diterima oleh staff kami dan akan disiapkan secepat mungkin.');
                }
                if($request->status == '3'){
                    $callService= $service->sendNotifToUser($TokenFCM,'order_updated','Pesanan Selesai','Yeayy, pesananmu sudah selesai dan siap disajikan.');
                }
            
            
        
                $statusCode = 200;
                $response = [
                    'error' => false,
                    'message' => 'update berhasil',
                ];
            }        
        }catch (Exception $e){

            $statusCode = 404;
            $response = [
                'error' => true,
                'message' => 'Gagal update',
            ];    
        }finally{
            return response($response,$statusCode)->header('Content-Type','application/json');
        }
    }

    public function callAnaberkahNotif(){
        try {      
            $dataBuilder = new PayloadDataBuilder();
            $dataBuilder->addData([
                'value' => 'Pinjaman',
                'title' => 'Test Notif',
                'body' => 'Test Test'
            ]);
            $data = $dataBuilder->build();
            $messageResponse = FCM::sendTo("eAz8jkJ06tk:APA91bG9_ZwyUNdji7Lz-COGJYDJI-MH5tLj6jMouLqLeWK1v6qNYDr214FEJG7La9QEo9crv_Ih-bXA6-W0rw9HjhTM2f9xZuZTb4ByNbp5_FGN50ON5Vrrt7G6uGsE2DH2fGl_fjp0", null, null, $data);
        } catch (Exception $e) {
            echo "Failed";
        }finally{
            echo "Success";
        }
    }

    //END
    //API FOR RESTAURANT APPS


}