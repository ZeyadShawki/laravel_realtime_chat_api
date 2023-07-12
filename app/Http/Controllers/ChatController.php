<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetChatRequest;
use App\Http\Requests\StoreChatRequest;
use App\Models\Chat;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
  //
  public function index(GetChatRequest $getChatRequest)
  {
    try {
      $data = $getChatRequest->validated();

      $isPrivate = 1;
      if ($getChatRequest->has('is_private')) {
        $isPrivate = (int) $data['is_private'];
      }
      $chats = Chat::where('is_private', $isPrivate)->HasParticpants(
        auth()->user()->id
      )->with('lastMessage')->with('participants')->latest('updated_at')->get();

      return $this->success($chats);
    } catch (Exception $e) {
      return $e->getMessage();
    }
  }

  public function store(StoreChatRequest $request)
  {
    $data = $this->perpareStoreData($request);
    if ($data['userId'] == $data['otherUserId']) {
      return $this->error('You can not create a chat with your own');
    }

    $previousChat = $this->getPreviousChat($data['otherUserId']);

    if ($previousChat === null) {

      $chat = Chat::create($data['data']);
      $chat->participants()->createMany([
        [
          'user_id' => $data['userId']
        ],
        [
          'user_id' => $data['otherUserId']
        ]
      ]);

      $chat->refresh()->load('lastMessage.user', 'participants.user');
      return $this->success($chat);
    }

    return $this->success($previousChat->load('lastMessage.user', 'participants.user'));
  }

  // check to not create chat again if exists
  private function getPreviousChat(int $otherUserId): mixed
  {

    $userId = auth()->user()->id;

    return Chat::where('is_private', 1)
      ->whereHas('participants', function ($query) use ($userId) {
        $query->where('user_id', $userId);
      })
      ->whereHas('participants', function ($query) use ($otherUserId) {
        $query->where('user_id', $otherUserId);
      })
      ->first();
  }

  public function perpareStoreData(StoreChatRequest $getChatRequest)
  {
    $data = $getChatRequest->validated();
    $otherUserID = (int) $data['user_id'];
    unset($data['user_id']);
    $data['created_by'] = auth()->user()->id;
    return [
      'otherUserId' => $otherUserID,
      'userId' => auth()->user()->id,
      'data' => $data,
    ];
  }

  public function show(Chat $chat): JsonResponse
  {
    $chat->load('lastMessage.user', 'participants.user');
    return $this->success($chat);
  }
}
