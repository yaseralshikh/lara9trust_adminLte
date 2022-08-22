<?php

namespace App\Http\Livewire\Backend\Admin\Roles;

use Livewire\Component;

class ListRoles extends Component
{
    public function render()
    {
        return view('livewire.backend.admin.roles.list-roles')->layout('layouts.admin');
    }
}
