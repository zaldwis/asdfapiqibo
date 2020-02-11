<?php

namespace App\Services;
use DB;
use DateTime;
use LaravelFCM\Message\Topics;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;

class NotificationService
{
    public function sendNotifToUser($userToken, $type, $title, $body){
        try {      
            $dataBuilder = new PayloadDataBuilder();
            $dataBuilder->addData([
                'value' => $type,
                'title' => $title,
                'body' => $body
            ]);
            $data = $dataBuilder->build();
            $messageResponse = FCM::sendTo($userToken, null, null, $data);
        } catch (Exception $e) {
            return false;
        }finally{
            return true;
        }
    }

    public function sendNotifToGroup($topicdata, $type, $title, $body){
        try {
            $dataBuilder = new PayloadDataBuilder();
            $dataBuilder->addData([
                'value' => $type,
                'title' => $title,
                'body' => $body
            ]);

            $data = $dataBuilder->build();

            $topic = new Topics();
            $topic->topic($topicdata);

            $topicResponse = FCM::sendToTopic($topic, null, null, $data);
        } catch (Exception $e) {
            return false;
        }finally{
            return true;
        }
    }

    public function sendNotifAndMessageToGroup($topicdata, $type, $title, $message,$id_user,$name_user,$chat_message){
        try {
            $dataBuilder = new PayloadDataBuilder();
            $dataBuilder->addData([
                'value' => $type,
                'title' => $title,
                'body' => $message,
                'id_user' => $id_user,
                'name_user' => $name_user,
                'chat_message' => $chat_message
            ]);

            $data = $dataBuilder->build();

            $topic = new Topics();
            $topic->topic($topicdata);

            $topicResponse = FCM::sendToTopic($topic, null, null, $data);
        } catch (Exception $e) {
            return false;
        }finally{
            return true;
        }
    }

    public function sendNotifFriendRequest($userToken, $type, $title, $body,$id_conversation,$id_friendrequest){
        try {      
            $dataBuilder = new PayloadDataBuilder();
            $dataBuilder->addData([
                'value' => $type,
                'title' => $title,
                'body' => $body,
                'id_conversation' => $id_conversation,
                'id_friendrequest' => $id_friendrequest
            ]);
            $data = $dataBuilder->build();
            $messageResponse = FCM::sendTo($userToken, null, null, $data);
        } catch (Exception $e) {
            return false;
        }finally{
            return true;
        }
    }

    public function sendNotifAndMessageToUser($userToken, $type, $title, $body,$chat_message,$id_sender,$id_receiver,$id_conversation){
        try {      
            $dataBuilder = new PayloadDataBuilder();
            $dataBuilder->addData([
                'value' => $type,
                'title' => $title,
                'body' => $body,
                'chat_message' => $chat_message,
                'id_sender' => $id_sender,
                'id_receiver' => $id_receiver,
                'id_conversation' => $id_conversation
            ]);
            $data = $dataBuilder->build();
            $messageResponse = FCM::sendTo($userToken, null, null, $data);
        } catch (Exception $e) {
            return false;
        }finally{
            return true;
        }
    }
}