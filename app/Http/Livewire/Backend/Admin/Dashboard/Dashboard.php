<?php

namespace App\Http\Livewire\Backend\Admin\Dashboard;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.backend.admin.dashboard.dashboard')->layout('layouts.admin');
    }
}
