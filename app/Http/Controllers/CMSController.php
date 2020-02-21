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
use App\Models\Admins;
use App\Models\RestaurantAdmins;
use App\Models\RestaurantOfficers;
use DateTime;
use App\Services\NotificationService;
use LaravelFCM\Message\Topics;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;

class CMSController extends Controller
{
    protected $image_profile_folder = '/public/deals_picture/';


    public function __construct(){
        $this->middleware('auth');
    }

    //AUTH
    public function RegisterSuperAdmin(Request $request){
        try {

            $admin=Admins::where('email','=',$request->email)->first();
            if(!$admin){
                $user = new Admins();
                $user->email = $request->email;
                $user->password = Hash::make($request->password);
                $user->name = $request->name;
                $user->gender = $request->gender;
                $user->image = $request->image;
                $user->birth_date = $request->birth_date;
                $user->saveOrFail();

                $statusCode = 200;
                $response = [
                    'error' => false,
                    'message' => 'Berhasil mendaftar',
                    'dataAdmin' => [$user]
                ];    
            }else{
                $statusCode = 404;
                $response = [
                    'error' => true,
                    'message' => 'Email Sudah Terdaftar',
                ]; 
            }
        } catch (Exception $e) {
            $statusCode = 404;
            $response = [
                'error' => true,
                'message' => 'Gagal mendaftar',
            ];    
        }finally{
            return response($response,$statusCode)->header('Content-Type','application/json');
        }
    }

    public function AddRestaurant(Request $request){
        try {
            $admin=RestaurantAdmins::where('email','=',$request->email)->first();
            if(!$admin){
                $user = new Restaurants();
                $user->name = $request->restaurant_name;
                $user->description = $request->description;
                $user->address = $request->address;
                $user->phone = $request->phone;
                $user->image = $request->restaurant_image;
                $user->is_chat_enable = '1';
                $user->saveOrFail();
    
                $admin = new RestaurantAdmins();
                $admin->email = $request->email;
                $admin->password = Hash::make($request->password);
                $admin->name = $request->name;
                $admin->gender = $request->gender;
                $admin->image = $request->image;
                $admin->birth_date = $request->birth_date;
                $admin->id_restaurant = $user->id;
                $admin->saveOrFail();
    
    
                $statusCode = 200;
                $response = [
                    'error' => false,
                    'message' => 'Berhasil mendaftar',
                    'dataAdminRestaurant' => [$admin]
                ];    
            }else{
                $statusCode = 404;
                $response = [
                    'error' => true,
                    'message' => 'Email Sudah Terdaftar',
                ]; 
            }
        } catch (Exception $e) {
            $statusCode = 404;
            $response = [
                'error' => true,
                'message' => 'Gagal mendaftar',
            ];    
        }finally{
            return response($response,$statusCode)->header('Content-Type','application/json');
        }
    }

    public function loginAdmin(Request $request){
        $password = $request->password;
        try{
            $Admin=Admins::where('email','=',$request->email)->first();

            if(!$Admin){
                $statusCode = 404;                    
                $response = [
                ];       
            } else {
                $dataPassword = $Admin->password;
                if(Hash::check($password, $dataPassword)){
                    $statusCode = 200;
                    $response = [
                        'error' => false,
                        'message' => 'Login Berhasil',
                        'dataAdmin' => [$Admin]
                    ];    
                } else {
                    $statusCode = 404;                    
                    $response = [
                        'error' => true,
                        'message' => 'Password atau Email Salah'
                    ];       
                }
            }
        } catch (Exception $ex) {
            $statusCode = 404;
            $response['message'] = 'Login Gagal';
        } finally {
            return response($response,$statusCode)->header('Content-Type','application/json');
        }
    }

    public function loginRestaurantAdmin(Request $request){
        $password = $request->password;
        try{
            $Admin=RestaurantAdmins::where('email','=',$request->email)->first();

            if(!$Admin){
                $statusCode = 404;                    
                $response = [
                ];       
            } else {
                $dataPassword = $Admin->password;
                if(Hash::check($password, $dataPassword)){
                    $statusCode = 200;
                    $response = [
                        'error' => false,
                        'message' => 'Login Berhasil',
                        'dataAdminRestaurant' => [$Admin]
                    ];    
                } else {
                    $statusCode = 404;                    
                    $response = [
                        'error' => true,
                        'message' => 'Password atau Email Salah'
                    ];       
                }
            }
        } catch (Exception $ex) {
            $statusCode = 404;
            $response['message'] = 'Login Gagal';
        } finally {
            return response($response,$statusCode)->header('Content-Type','application/json');
        }
    }

    public function registerRestaurantOfficers(Request $request){
        try {

            $admin=RestaurantOfficers::where('email','=',$request->email)->first();
            if(!$admin){
                $user = new RestaurantOfficers();
                $user->email = $request->email;
                $user->password = Hash::make($request->password);
                $user->name = $request->name;
                $user->gender = $request->gender;
                $user->image = $request->image;
                $user->birth_date = $request->birth_date;
                $user->id_restaurant = $request->id_restaurant;
                $user->saveOrFail();

                $statusCode = 200;
                $response = [
                    'error' => false,
                    'message' => 'Berhasil mendaftar',
                    'dataAdmin' => [$user]
                ];    
            }else{
                $statusCode = 404;
                $response = [
                    'error' => true,
                    'message' => 'Email Sudah Terdaftar',
                ]; 
            }
        } catch (Exception $e) {
            $statusCode = 404;
            $response = [
                'error' => true,
                'message' => 'Gagal mendaftar',
            ];    
        }finally{
            return response($response,$statusCode)->header('Content-Type','application/json');
        }
    }

    

}
