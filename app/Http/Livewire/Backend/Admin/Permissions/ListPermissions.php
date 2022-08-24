<?php

namespace App\Http\Livewire\Backend\Admin\Permissions;

use Livewire\Component;
use App\Models\Permission;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Validator;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class ListPermissions extends Component
{
    use WithPagination;
    use LivewireAlert;

    protected $paginationTheme = 'bootstrap';

    public $data = [];

    public $searchTerm = null;
    protected $queryString = ['searchTerm' => ['except' => '']];

    public $sortColumnName = 'name';
    public $sortDirection = 'asc';


    public $pagination;

	public $showEditModal = false;

	public $permissionIdBeingRemoved = null;

    protected $listeners = ['deleteConfirmed' => 'deletePermissions'];

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

    // Show add new pagination modal
    public function addNewPermission()
    {
        $this->reset();

        $this->showEditModal = false;

        $this->dispatchBrowserEvent('show-form');
    }

    public function createPermission()
    {
        $validatedData = Validator::make($this->data, [
			//'name' => 'required|unique:permissions',
			'display_name' => 'required|unique:permissions',
			'description' => 'required',
		])->validate();

        $permission_name = $validatedData['display_name'];
        $permission_name = strtolower($permission_name);
        $permission_name = explode(" ", $permission_name);
        $permission_name = $permission_name[1] . "-" . $permission_name[0];

        $validatedData['name'] = $permission_name ;

        Permission::create($validatedData);

        $this->dispatchBrowserEvent('hide-form');

        $this->alert('success', 'Permission Added Successfully.', [
            'position'  =>  'top-end',
            'timer'  =>  3000,
            'toast'  =>  true,
            'text'  =>  null,
            'showCancelButton'  =>  false,
            'showConfirmButton'  =>  false
        ]);
    }

    public function edit(Permission $permission)
    {
        $this->reset();

		$this->showEditModal = true;

        $this->data = $permission->toArray();

		$this->permission = $permission;

		$this->dispatchBrowserEvent('show-form');
    }

    public function updatePermission()
	{
        try {
            $validatedData = Validator::make($this->data, [
                //'name'              => 'required|unique:permissions,name,'.$this->permission->id,
                'display_name'      => 'required|unique:permissions,display_name,'.$this->permission->id,
                'description'       => 'required',
            ])->validate();

            $permission_name = $validatedData['display_name'];
            $permission_name = strtolower($permission_name);
            $permission_name = explode(" ", $permission_name);
            $permission_name = $permission_name[1] . "-" . $permission_name[0];

            $validatedData['name'] = $permission_name ;

            $this->permission->update($validatedData);

            $this->dispatchBrowserEvent('hide-form');

            $this->alert('success', 'Permission updated Successfully.', [
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

    // Show Modal Form to Confirm Permission Removal

    public function confirmPermissionRemoval($permissionId)
    {
        $this->permissionIdBeingRemoved = $permissionId;

        $this->dispatchBrowserEvent('show-delete-modal');
    }

    // Delete Permission

    public function deletePermission()
    {
        $permission = Permission::findOrFail($this->permissionIdBeingRemoved);

        $permission->delete();

        $this->dispatchBrowserEvent('hide-delete-modal');

        $this->alert('success', 'Permission Deleted Successfully.', [
            'position'  =>  'top-end',
            'timer'  =>  3000,
            'toast'  =>  true,
            'text'  =>  null,
            'showCancelButton'  =>  false,
            'showConfirmButton'  =>  false
        ]);
    }

    public function getPermissionsProperty()
	{
        $permissions = Permission::query()
        ->where('name', 'like', '%'.$this->searchTerm.'%')
        ->orWhere('display_name', 'like', '%'.$this->searchTerm.'%')
        ->orWhere('description', 'like', '%'.$this->searchTerm.'%')
        ->orderBy($this->sortColumnName, $this->sortDirection)
        ->paginate(15);

        return $permissions;
	}

    public function render()
    {
        $permissions = $this->permissions;

        return view('livewire.backend.admin.permissions.list-permissions',[
            'permissions' => $permissions,
        ])->layout('layouts.admin');
    }
}
