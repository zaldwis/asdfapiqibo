<?php

namespace App\Http\Controllers;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Users;
use App\Models\RestaurantOfficers;


class AuthController extends Controller
{

    public function __construct(){
        $this->middleware('auth');
    }

    public function registerOrLogin(Request $request){
        try {
            $dataUser=Users::where('phone','=',$request->phone)->first();
            if($dataUser){
                $dataUser->device_token = $request->device_token;
                $dataUser->saveOrFail();
                $statusCode = 200;
                $response = [
                    'error' => false,
                    'message' => 'Login Berhasil',
                    'dataUser' => [$dataUser]
                ];    
            } else {
                $registerUser = new Users();
                $registerUser->phone = $request->phone;
                $registerUser->device_token = $request->device_token;
                $registerUser->saveOrFail();

                $statusCode = 200;
                $response = [
                    'error' => false,
                    'message' => 'Register dan login Berhasil',
                    'dataUser' => [$registerUser]
                ];    
            }
        } catch (Exception $e) {
            $statusCode = 404;
            $response = [
                'error' => true,
                'message' => 'Gagal mendaftar atau login',
            ];    
        }finally{
            return response($response,$statusCode)->header('Content-Type','application/json');
        }
    }

    public function logout(Request $request){
        try {
            $dataUser=Users::where('id','=',$request->id)->first();
            $dataUser->device_token = '';
            $dataUser->saveOrFail();
            $statusCode = 200;
            $response = [
                'error' => false,
                'message' => 'Logout Berhasil',
            ];    
        } catch (Exception $e) {
            $statusCode = 404;
            $response = [
                'error' => true,
                'message' => 'Logout Gagal',
            ];    
        }finally{
            return response($response,$statusCode)->header('Content-Type','application/json');
        }
    }

    public function loginRestaurantOfficer(Request $request)
    {
        $password = $request->password;
        try{
            $query=RestaurantOfficers::where('email','=',$request->email)->first();
            if(!$query){
                $statusCode = 404;                    
                $response = [
                ];       
            } else {
                $dataPassword = $query->password;
                if(Hash::check($password, $dataPassword)){
                    $responseTime = DB::table('restaurants')->where('id', $query->id_restaurant)->value('max_response_time');

                    $statusCode = 200;
                    $response = [
                        'error' => false,
                        'message' => 'Login Berhasil',
                        'dataRestaurantOfficer' => [$query],
                        'restaurantResponseTime' => $responseTime
                    ];    
                } else {
                    $statusCode = 404;                    
                    $response = [
                        'error' => true,
                        'message' => 'Password atau Email Salah',
                        'dataRestaurantOfficer' => []
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

    public function register(Request $request){
        try {
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
                'dataRestaurantOfficer' => [$user]
            ];    
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