<?php

namespace App\Http\Livewire\Backend\Admin\Permissions;

use Livewire\Component;

class ListPermissions extends Component
{
    public function render()
    {
        return view('livewire.backend.admin.permissions.list-permissions')->layout('layouts.admin');
    }
}
