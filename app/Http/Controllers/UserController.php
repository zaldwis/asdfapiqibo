<?php

namespace App\Http\Controllers;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Users;

class UserController extends Controller
{
    protected $image_profile_folder = '/public/profile_picture/';

    public function __construct(){
        $this->middleware('auth');
    }

    public function getDataUser($id){
        try {
            $dataUser=Users::where('id','=',$id)->first();
            if($dataUser){
                $statusCode = 200;
                $response = [
                    'error' => false,
                    'message' => 'Data ditemukan',
                    'dataUser' => [$dataUser]
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

    public function updateDataUser(Request $request){
        try{
            $dataUser=Users::where('id','=',$request->id)->first();
         
            if ($request->hasFile('image')) {
                if ($request->file('image')->isValid()) {
                    $file_ext        = $request->file('image')->getClientOriginalExtension();
                    $file_size       = $request->file('image')->getClientSize();
                    $allow_file_exts = array('jpeg', 'jpg', 'png');
                    $max_file_size   = 1024 * 1024 * 10;
                    if (in_array(strtolower($file_ext), $allow_file_exts) && ($file_size <= $max_file_size)) {
                        $dest_path     = base_path() . $this->image_profile_folder;
                        $file_name     = preg_replace('/\\.[^.\\s]{3,4}$/', '', $request->id);
                        $file_name     = str_replace(' ', '-', $file_name);
                        $profile_image = $file_name  . '.' . $file_ext;
    
                        // move file to serve directory
                        $request->file('image')->move($dest_path, $profile_image);
                        $dataUser->image= $profile_image;
                    }
                }
            }    
        

            if($request->name != ""){
                $dataUser->name = $request->name;
            }
            if($request->gender != ""){
                $dataUser->gender = $request->gender;
            }
            if($request->location != ""){
                $dataUser->location = $request->location;
            }
            if($request->birth_date != ""){
                $dataUser->birth_date = $request->birth_date;
            }
            if($request->gender != ""){
                $dataUser->gender = $request->gender;
            }

            $dataUser->saveOrFail();
    
            $statusCode = 200;
            $response = [
                'error' => false,
                'message' => 'update data berhasil',
            ];

        }catch (Exception $e){

            $statusCode = 404;
            $response = [
                'error' => true,
                'message' => 'Gagal update profil',
            ];    
        }finally{
            return response($response,$statusCode)->header('Content-Type','application/json');
        }
    }
}