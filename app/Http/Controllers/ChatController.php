<?php

namespace App\Http\Controllers;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Users;
use App\Models\Checkins;
use App\Models\RestaurantMessages;
use App\Models\FriendRequests;
use App\Models\Conversations;
use App\Models\PersonalMessages;
use App\Models\BotMessages;
use App\Services\NotificationService;
use DateTime;

class ChatController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }

    public function sendRestaurantMessage(Request $request){
        try {
            $messagesData = new RestaurantMessages();
            if($request->message != ""){
                $messagesData->message = $request->message;
            }
            if($request->image != ""){
                $messagesData->image = $request->image;
            }
            $messagesData->id_sender = $request->id_sender;
            $messagesData->id_restaurant = $request->id_restaurant;
            $messagesData->saveOrFail();
            
            
            $user_name = DB::table('users')->where('id', $request->id_sender)->value('name');
            if($user_name == null){
                $user_name = 'Anonymous';
            }
            $restaurant_name = DB::table('restaurants')->where('id', $request->id_restaurant)->value('name');
            $service= new NotificationService();
            $callService= $service->sendNotifAndMessageToGroup($request->id_restaurant."chat",'group message',$restaurant_name,$user_name.' : '.$request->message,$request->id_sender,$user_name,$request->message);

           
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

    public function getRestaurantMessages(Request $request){
        try {
            $current_date = new \DateTime();
            $dt = $current_date->format('Y-m-d H:i:s');
            $checkinTime = DB::table('checkins')->where('id', $request->id_checkin)->value('checkin_datetime');

            $data = DB::table('message_restaurants')
            ->join('users', 'message_restaurants.id_sender', '=', 'users.id')
            ->select('message_restaurants.id','users.name','message_restaurants.message','message_restaurants.id_sender','message_restaurants.created_at')
            ->where('message_restaurants.id_restaurant', $request->id_restaurant)
            ->whereBetween('message_restaurants.created_at', [$checkinTime, $current_date])
            ->orderBy('message_restaurants.created_at', 'DESC')
            ->get();

            if($data){
                $statusCode = 200;
                $response = [
                    'error' => false,
                    'message' => 'Data ditemukan',
                    'dataRestaurantMessage' => $data
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

    public function sendFriendRequest(Request $request){
        try {
            $friendRequest = new FriendRequests();
            $friendRequest->id_sender = $request->id_sender;
            $friendRequest->id_receiver = $request->id_receiver;
            $friendRequest->saveOrFail();
            
            $conversations = new Conversations();
            $conversations->id_friendrequest = $friendRequest->id;
            $conversations->saveOrFail();

            $sender_name = DB::table('users')->where('id', $request->id_sender)->value('name');
            if($sender_name == null){
                $sender_name = 'Anonymous';
            }
            $receiverToken = DB::table('users')->where('id', $request->id_receiver)->value('device_token');
            $service= new NotificationService();
            $callService= $service->sendNotifFriendRequest($receiverToken,'friend_request','Friend Request',$sender_name.' want to connect with you.',$conversations->id,$friendRequest->id);

           
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

    public function getFriendRequests(Request $request){
        try {
            $dataAsSender = DB::table('friend_requests')
            ->join('conversations', 'friend_requests.id', '=', 'conversations.id_friendrequest')
            ->select('friend_requests.*','conversations.id AS id_conversations')
            ->where('friend_requests.id_sender', $request->id_user)
            ->where('friend_requests.id_receiver', $request->id_otheruser)
            ->where('friend_requests.is_reject', '0')
            ->where('friend_requests.is_delete', '0')
            ->orderBy('friend_requests.updated_at', 'DESC')
            ->first();

            $dataAsReceiver = DB::table('friend_requests')
            ->join('conversations', 'friend_requests.id', '=', 'conversations.id_friendrequest')
            ->select('friend_requests.*','conversations.id AS id_conversations')
            ->where('friend_requests.id_sender', $request->id_otheruser)
            ->where('friend_requests.id_receiver', $request->id_user)
            ->where('friend_requests.is_reject', '0')
            ->where('friend_requests.is_delete', '0')
            ->orderBy('friend_requests.updated_at', 'DESC')
            ->first();

            if($dataAsSender){
                $statusCode = 200;
                $response = [
                    'error' => false,
                    'message' => 'Data ditemukan',
                    'dataFriendRequestAsSender' => $dataAsSender,
                    // 'dataFriendRequestAsReceiver' => $dataAsReceiver
                ];    
            } else if($dataAsReceiver){
                $statusCode = 200;
                $response = [
                    'error' => false,
                    'message' => 'Data ditemukan',
                    // 'dataFriendRequestAsSender' => [],
                    'dataFriendRequestAsReceiver' => $dataAsReceiver
                ];    
            }else {
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

    public function updateFriendRequests(Request $request){
        try{
            $current_date = new \DateTime();
            $dt = $current_date->format('Y-m-d');

            $data=FriendRequests::where('id','=',$request->id_friendrequest)->first();
            if($request->status == 'reject'){
                $data->is_reject = '1';
                $data->saveOrFail();
                $dataConversation=Conversations::where('id_friendrequest','=',$request->id_friendrequest)->first();
                $dataConversation->is_delete = '1';
                $dataConversation->deleted_at = $dt;
                $dataConversation->saveOrFail();
            }else if($request->status == 'accept'){
                $data->is_accept = '1';
                $data->saveOrFail();
                $dataConversation=Conversations::where('id_friendrequest','=',$request->id_friendrequest)->first();
                $dataConversation->is_active = '1';
                $dataConversation->saveOrFail();
            }else if($request->status == 'delete'){
                $data->is_delete = '1';
                $data->saveOrFail();
                $dataConversation=Conversations::where('id_friendrequest','=',$request->id_friendrequest)->first();
                $dataConversation->is_delete = '1';
                $dataConversation->deleted_at = $dt;
                $dataConversation->saveOrFail();
            }
    
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

    public function getListConversation(Request $request){
        try {
            $dataAsSender = DB::table('conversations')
            ->join('friend_requests', 'conversations.id_friendrequest', '=', 'friend_requests.id')
            ->join('users AS receiver', 'friend_requests.id_receiver', '=', 'receiver.id')
            ->join('users AS sender', 'friend_requests.id_sender', '=', 'sender.id')
            ->select('conversations.*','friend_requests.id_sender','friend_requests.id_receiver','receiver.name AS receiver_name','receiver.image AS receiver_image','sender.name AS sender_name','sender.image AS sender_image')
            ->where('friend_requests.id_sender', $request->id_user)
            ->where('conversations.is_delete', '0')
            ->Orwhere('friend_requests.id_receiver', $request->id_user)
            ->where('conversations.is_delete', '0')
            ->orderBy('conversations.updated_at', 'DESC')
            ->get();

            if(!$dataAsSender->isEmpty()){
                $statusCode = 200;
                $response = [
                    'error' => false,
                    'message' => 'Data ditemukan',
                    'dataListConversation' => $dataAsSender
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

    public function sendPersonalMessage(Request $request){
        try {
            $messagesData = new PersonalMessages();
            if($request->message != ""){
                $messagesData->message = $request->message;
            }
            if($request->image != ""){
                $messagesData->image = $request->image;
            }
            $messagesData->id_sender = $request->id_sender;
            $messagesData->id_receiver = $request->id_receiver;
            $messagesData->id_conversation = $request->id_conversation;
            $messagesData->saveOrFail();

            $dataConvers=Conversations::where('id','=',$request->id_conversation)->first();
            $dataConvers->latest_message = $request->message;
            $dataConvers->saveOrFail();
            
            
            $user_name = DB::table('users')->where('id', $request->id_sender)->value('name');
            if($user_name == null){
                $user_name = 'Anonymous';
            }
            $receiverToken = DB::table('users')->where('id', $request->id_receiver)->value('device_token');
            $service= new NotificationService();
            $callService= $service->sendNotifAndMessageToUser($receiverToken,'personal_message',$user_name,$request->message,$request->message,$request->id_sender,$request->id_receiver,$request->id_conversation);

           
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

    public function getPersonalMessage(Request $request){
        try {
            // $messages = PersonalMessages::where('id_conversation', $request->id_conversation)->get();
            $data = DB::table('message_personals')
            ->select('message_personals.*')
            ->where('message_personals.id_conversation', $request->id_conversation)
            ->orderBy('message_personals.created_at', 'DESC')
            ->get();

            if($data){
                $statusCode = 200;
                $response = [
                    'error' => false,
                    'message' => 'Data ditemukan',
                    'dataPersonalMessages' => $data
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

    public function saveMessageToBot(Request $request){
        try {
            $messagesData = new BotMessages();
            $messagesData->id_sender = $request->id_sender;
            $messagesData->message = $request->message;
            $messagesData->saveOrFail();

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

    
}