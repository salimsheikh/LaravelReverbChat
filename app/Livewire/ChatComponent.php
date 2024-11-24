<?php

namespace App\Livewire;


use App\Models\User;
use App\Models\Message;
use App\Events\MessageSentEvent;
use Livewire\Attributes\On;
use Livewire\Component;

class ChatComponent extends Component
{

    public $user = "";
    public $sender_id = "";
    public $receiver_id = "";
    public $message = "";
    public $messages = [];

    public function render()
    {
        return view('livewire.chat-component');
    }

    public function mount($user_id){
        // dd($user_id);

        $this->sender_id = auth()->user()->id;
        $this->receiver_id = $user_id;

        $messages = Message::where(function($query){
            $query->where('sender_id', $this->sender_id)->where('receiver_id', $this->receiver_id);
        })->orWhere(function($query){
            $query->where('sender_id', $this->receiver_id)->where('receiver_id', $this->sender_id);
        })
        ->with('sender:id,name', 'receiver:id,name')
        ->get();

        // dd($messages->toArray());

        foreach($messages as $message){
            $this->appendChatMessage($message);
        }

        // dd($this->messages);

        $this->message = "";
        $this->user = User::whereId($user_id)->first();
    }

    #[On('echo-private:chat-channel.{sender_id}, MessageSendEvent')]
    public function listionForMessage($event){
       $chatMessage = Message::whereId($event['message']['id'])
       ->with('sender:id,name', 'receiver:id,name')->first();

       $this->appendChatMessage($chatMessage);
    }

    public function appendChatMessage($message){
        $this->messages[] = [
            'id' => $message->id,
            'message' => $message->message,
            'sender' => $message->sender->name,
            'receiver' => $message->receiver->name,
        ];
    }

    public function sendMessage(){
        $chatMessage = new Message();
        $chatMessage->sender_id = $this->sender_id;
        $chatMessage->receiver_id = $this->receiver_id;
        $chatMessage->message = $this->message;
        $chatMessage->save();

        $this->appendChatMessage($chatMessage);
        broadcast(new MessageSentEvent($chatMessage))->toOthers();

        $this->message = '';

    }
}
