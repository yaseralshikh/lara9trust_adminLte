<?php

namespace App\Http\Livewire\Backend\Admin\Roles;

use App\Models\Role;
use Livewire\Component;
use App\Models\Permission;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class ListRoles extends Component
{
    use WithPagination;
    use LivewireAlert;

    protected $paginationTheme = 'bootstrap';

    public $data = [];

    public $searchTerm = null;
    protected $queryString = ['searchTerm' => ['except' => '']];

    public $sortColumnName = 'name';
    public $sortDirection = 'asc';


    public $role;

	public $showEditModal = false;

	public $roleIdBeingRemoved = null;

    public $role_permissions = [];

    protected $listeners = ['deleteConfirmed' => 'deleteRoles'];

    // Sort By Column Name

    public function sortBy($columnName)
    {
        if ($this->sortColumnName === $columnName) {
            $this->sortDirection = $this->swapSortDirection();
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortColumnName = $columnName;

    }

    // Swap Sort Direction

    public function swapSortDirection()
    {
        return $this->sortDirection === 'asc' ? 'desc' : 'asc';
    }

    // Updated Search Term
    public function updatedSearchTerm()
    {
        $this->resetPage();
    }

    // Show add new role modal
    public function addNewRole()
    {
        $this->reset();

        $this->showEditModal = false;

        $this->dispatchBrowserEvent('show-form');
    }

    //  create new role
    public function createRole()
    {
        $validatedData = Validator::make($this->data, [
			'name' => 'required|unique:roles',
			'display_name' => 'required|unique:roles',
			'description' => 'required',
		])->validate();

        $role_permissions = [];
        for ($i=0; $i <  count($this->role_permissions); $i++) {
            $permission_value = array_values($this->role_permissions);
            if ($permission_value[$i] != false) {
                array_push($role_permissions, $permission_value[$i]);
            }
        }

		$role = Role::create($validatedData);
        $role->permissions()->attach($role_permissions);

        $this->dispatchBrowserEvent('hide-form');

        $this->alert('success', 'Role Added Successfully.', [
            'position'  =>  'top-end',
            'timer'  =>  3000,
            'toast'  =>  true,
            'text'  =>  null,
            'showCancelButton'  =>  false,
            'showConfirmButton'  =>  false
        ]);
    }

    // show edit role modal
    public function edit(Role $role)
    {
        $this->reset();

		$this->showEditModal = true;

        $this->data = $role->toArray();

		$this->role = $role;

        $this->role_permissions = $role->permissions()->pluck('id','permission_id')->toArray();

		$this->dispatchBrowserEvent('show-form');
    }

    // update role
    public function updateRole()
	{
        try {
            $validatedData = Validator::make($this->data, [
                'name'              => 'required|unique:roles,name,'.$this->role->id,
                'display_name'      => 'required|unique:roles,display_name,'.$this->role->id,
                'description'       => 'required',
            ])->validate();

            $role_permissions = [];
            for ($i=0; $i <  count($this->role_permissions); $i++) {
                $permission_value = array_values($this->role_permissions);
                if ($permission_value[$i] != false) {
                    array_push($role_permissions, $permission_value[$i]);
                }
            }

            $this->role->update($validatedData);
            $this->role->permissions()->sync($role_permissions);

            $this->dispatchBrowserEvent('hide-form');

            $this->alert('success', 'Role updated Successfully.', [
                'position'  =>  'top-end',
                'timer'  =>  3000,
                'toast'  =>  true,
                'text'  =>  null,
                'showCancelButton'  =>  false,
                'showConfirmButton'  =>  false
            ]);

        } catch (\Throwable $th) {
            $message = $this->alert('error', $th->getMessage(), [
                'position'  =>  'top-end',
                'timer'  =>  3000,
                'toast'  =>  true,
                'text'  =>  null,
                'showCancelButton'  =>  false,
                'showConfirmButton'  =>  false
            ]);
            return $message;
        }
	}

    // Show Modal Form to Confirm Role Removal

    public function confirmRoleRemoval($roleId)
    {
        $this->roleIdBeingRemoved = $roleId;

        $this->dispatchBrowserEvent('show-delete-modal');
    }

    // Delete Role

    public function deleteRole()
    {
        $role = Role::findOrFail($this->roleIdBeingRemoved);

        DB::table('permission_role')->where('role_id', $this->roleIdBeingRemoved)->delete();
        $role->delete();

        $this->dispatchBrowserEvent('hide-delete-modal');

        $this->alert('success', 'Role Deleted Successfully.', [
            'position'  =>  'top-end',
            'timer'  =>  3000,
            'toast'  =>  true,
            'text'  =>  null,
            'showCancelButton'  =>  false,
            'showConfirmButton'  =>  false
        ]);
    }

    public function getRolesProperty()
	{
        $roles = Role::query()
        ->where('name', 'like', '%'.$this->searchTerm.'%')
        ->orWhere('display_name', 'like', '%'.$this->searchTerm.'%')
        ->orWhere('description', 'like', '%'.$this->searchTerm.'%')
        ->orderBy($this->sortColumnName, $this->sortDirection)
        ->paginate(15);

        return $roles;
	}

    public function render()
    {
        $roles = $this->roles;
        $permissions = Permission::all();

        return view('livewire.backend.admin.roles.list-roles',[
            'roles' => $roles,
            'permissions' => $permissions,
        ])->layout('layouts.admin');
    }
}
