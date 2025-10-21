<?php

namespace App\Livewire;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationsBell extends Component
{
    public $unreadNotifications;
    public $unreadCount = 0;

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        if (Auth::check()) {
            $this->unreadNotifications = Auth::user()->notifications()->whereNull('read_at')->get();
            $this->unreadCount = $this->unreadNotifications->count();
        }
    }

    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id === Auth::id()) {
            $notification->read_at = now();
            $notification->save();
        }
        return $this->redirect($notification->link, navigate: true);
    }

    public function render()
    {
        return view('livewire.notifications-bell');
    }
}