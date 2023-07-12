<?php

namespace App\Http\Controllers;

use App\Events\NewMessageSent;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetMessagesRequest;
use App\Http\Requests\StoreMessageRequest;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;

class ChatMessageController extends Controller
{
    //
    public function index(GetMessagesRequest $getMessagesRequest)
    {
        $data = $getMessagesRequest->validated();

        $chat_id = $data['chat_id'];
        // $currentPage = $data['page'];
        $page_size = $data['page_size'] ?? 15 ;

        $message = ChatMessage::where('chat_id', $chat_id)
            ->with('user')
            ->latest('created_at');
            // ->simplePaginate(
            //     $page_size,
            //     ['*'],
            //     'page',
            //     $currentPage
            // );

        return $this->success($message->get());
    }
    public function store(StoreMessageRequest $storeMessageRequest)
    { 
    
        
        $data=$storeMessageRequest->validated();
             $data['user_id']=auth()->user()->id;
            // return $data['user_id'];
             $chatMessage=ChatMessage::create([
                'user_id'=>$data['user_id'],
                'message'=>$data['message'],
                'chat_id'=>$data['chat_id']
             ]);
             $chatMessage->load('user');


        $this->sendNotificationToOther($chatMessage);
          

           return $this->success($chatMessage,'Message send Succcessfulyy');
    }

    private function sendNotificationToOther(ChatMessage $chatMessage): void
    {

        // TODO move this event broadcast to observer
        broadcast(new NewMessageSent($chatMessage))->toOthers();

        $user = auth()->user();
        $userId = $user->id;

        $chat = Chat::where('id', $chatMessage->chat_id)
            ->with(['participants' => function ($query) use ($userId) {
                $query->where('user_id', '!=', $userId);
            }])
            ->first();
        if (count($chat->participants) > 0) {
            $otherUserId = $chat->participants[0]->user_id;

            $otherUser = User::where('id', $otherUserId)->first();
            // $otherUser->sendNewMessageNotif([
            //     'messageData' => [
            //         'senderName' => $user->username,
            //         'message' => $chatMessage->message,
            //         'chatId' => $chatMessage->chat_id
            //     ]
            // ]);
        }
    }
}
