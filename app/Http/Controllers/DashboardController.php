<?php

namespace App\Http\Controllers;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Users;
use App\Models\Deals;
use App\Models\Checkins;
use DateTime;

class DashboardController extends Controller
{
    protected $image_profile_folder = '/public/deals_picture/';


    public function __construct(){
        $this->middleware('auth');
    }

    public function getDealsList(){
        try {
            $dataDeals = Deals::where('is_active', '1')->get();
            if(!$dataDeals->isEmpty()){
                $statusCode = 200;
                $response = [
                    'error' => false,
                    'message' => 'Data ditemukan',
                    'dataDeals' => $dataDeals
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

    public function Checkin(Request $request){
        $currentDate = new DateTime();
        try {
            $restaurantID = DB::table('tables')->where('id', $request->id_table)->value('id_restaurant');
            $tableNumber = DB::table('tables')->where('id', $request->id_table)->value('table_number');
            $checkins = new Checkins();
            $checkins->id_restaurant = $restaurantID;
            $checkins->id_table = $request->id_table;
            $checkins->id_user = $request->id_user;
            $checkins->checkin_datetime = $currentDate;
            $checkins->saveOrFail();

            
            $statusCode = 200;
            $response = [
                'error' => false,
                'message' => 'Success',
                'checkinData' => $checkins,
                'tableNumber' => $tableNumber
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

    
}