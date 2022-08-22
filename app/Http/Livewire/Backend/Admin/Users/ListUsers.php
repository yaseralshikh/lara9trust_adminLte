<?php

namespace App\Http\Livewire\Backend\Admin\Users;

use App\Models\Role;
use App\Models\User;
use Livewire\Component;
use App\Models\Permission;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class ListUsers extends Component
{
    use WithPagination;
    use WithFileUploads;
    use LivewireAlert;

    protected $paginationTheme = 'bootstrap';

    public $data = [];
    public $user;
    public $user_permissions = [];
    public $photo;

    public $searchTerm = null;
    protected $queryString = ['searchTerm' => ['except' => '']];

    public $roleFilter = null;
    public $roleUserCount = Null;

    public $sortColumnName = 'name';
    public $sortDirection = 'asc';

    public $showEditModal = false;

    public $userIdBeingRemoved = null;

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

    //  Filter Users By Roles

    public function filterUsersByRoles($roleFilter = null)
    {
        $this->roleFilter = $roleFilter;
    }

    public function getUsersProperty()
	{
        if ($this->roleFilter) {
            $users = User::whereRelation('roles', 'name',$this->roleFilter )
            ->where('name', 'like', '%'.$this->searchTerm.'%')
            ->orderBy($this->sortColumnName, $this->sortDirection)
            ->paginate(15);
        } else {
            $users = User::query()
            ->where('name', 'like', '%'.$this->searchTerm.'%')
            ->orWhere('username', 'like', '%'.$this->searchTerm.'%')
            ->orWhere('email', 'like', '%'.$this->searchTerm.'%')
            ->orWhere('phone', 'like', '%'.$this->searchTerm.'%')
            ->orderBy($this->sortColumnName, $this->sortDirection)
            ->paginate(15);
        }

        return $users;
	}

    // show add new user form modal

    public function addNewUser()
    {
        $this->reset();
        $this->showEditModal = false;
        $this->dispatchBrowserEvent('show-form');
    }

    // Create new user

    public function createUser()
    {
        $validatedData = Validator::make($this->data, [
			'name' => 'required',
			'username' => 'required|unique:users',
			'email' => 'required|email|unique:users',
			'phone' => 'required|numeric',
			'description' => ['string', 'max:255'],
			'address' => ['string', 'max:255'],
            'photo'    => 'image|mimes:jpeg,png,jpg,gif|max:2048',
			'password' => 'required|confirmed',
            'role_id'   => 'required',
		])->validate();

		$validatedData['password'] = bcrypt($validatedData['password']);

		if ($this->photo) {
			$validatedData['profile_photo'] = $this->photo->store('/', 'profile_photos');
		}

        $user_permissions = [];
        for ($i=0; $i <  count($this->user_permissions); $i++) {
            $permission_value = array_values($this->user_permissions);
            if ($permission_value[$i] != false) {
                array_push($user_permissions, $permission_value[$i]);
            }
        }

		$user = User::create($validatedData);
        $user->attachRole($this->data['role_id']);
        $user->permissions()->attach($user_permissions);

        $this->dispatchBrowserEvent('hide-form');

        $this->alert('success', 'User Added Successfully.', [
            'position'  =>  'top-end',
            'timer'  =>  3000,
            'toast'  =>  true,
            'text'  =>  null,
            'showCancelButton'  =>  false,
            'showConfirmButton'  =>  false
        ]);
    }

    // show Update new user form modal

    public function edit(User $user)
    {
        $this->reset();

		$this->showEditModal = true;

		$this->user = $user;

		$this->data = $user->toArray();

        $this->data['role_id'] = $user->roles[0]->id;

        $this->user_permissions = $user->permissions()->pluck('id','permission_id')->toArray();

		$this->dispatchBrowserEvent('show-form');
    }

    // Update User

    public function updateUser()
    {
        try {
            $validatedData = Validator::make($this->data, [
                'name'                  => 'required',
                'username'              => 'required|unique:users,username,'.$this->user->id,
                'email'                 => 'required|email|unique:users,email,'.$this->user->id,
                'phone'                 => 'required|numeric',
                'description'           => ['string', 'max:255'],
                'address'               => ['string', 'max:255'],
                'photo'                 => 'image|mimes:jpeg,png,jpg,gif|max:2048',
                'role_id'               => 'required',
                'password'              => 'sometimes|confirmed',
            ])->validate();

            if(!empty($validatedData['password'])) {
                $validatedData['password'] = bcrypt($validatedData['password']);
            }

            if ($this->photo) {
                if($this->user->profile_photo){
                    Storage::disk('profile_photos')->delete($this->user->profile_photo);
                }
                $validatedData['profile_photo'] = $this->photo->store('/', 'profile_photos');
            }

            $user_permissions = [];
            for ($i=0; $i <  count($this->user_permissions); $i++) {
                $permission_value = array_values($this->user_permissions);
                if ($permission_value[$i] != false) {
                    array_push($user_permissions, $permission_value[$i]);
                }
            }

            $this->user->update($validatedData);
            $this->user->roles()->sync($this->data['role_id']);
            $this->user->permissions()->sync($user_permissions);

            $this->dispatchBrowserEvent('hide-form');

            $this->alert('success', 'User updated Successfully.', [
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

    public function show(User $user)
    {
        $this->reset();

		$this->user = $user;

		$this->data = $user->toArray();

        $this->data['role_id'] = $user->roles[0]->id;

        $this->data['created_at'] = $user->created_at->format('d-m-Y');

        $this->user_permissions = $user->permissions()->pluck('id','permission_id')->toArray();

		$this->dispatchBrowserEvent('show-modal-show');
    }

    // Show Modal Form to Confirm User Removal

    public function confirmUserRemoval($userId)
    {
        $this->userIdBeingRemoved = $userId;

        $this->dispatchBrowserEvent('show-delete-modal');
    }

    // Delete User

    public function deleteUser()
    {
        try {
            $user = User::findOrFail($this->userIdBeingRemoved);

            $imageFileName = $user->profile_photo;

            if($imageFileName){
                Storage::disk('profile_photos')->delete($imageFileName);
            }

            $user->delete();

            $this->dispatchBrowserEvent('hide-delete-modal');

            $this->alert('success', 'User Deleted Successfully.', [
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

    public function render()
    {
        $userCount= User::count();

        $roleAdminCount= User::whereRelation('roles', 'name', 'admin')->count();
        $roleSuperadminCount= User::whereRelation('roles', 'name', 'superadmin')->count();
        $users = $this->users;

        $roles = Role::all();
        $permissions = Permission::all();

        return view('livewire.backend.admin.users.list-users',[
            'users' => $users,
            'userCount' => $userCount,
            'roleUserCount' => $this->roleUserCount,
            'roleAdminCount' => $roleAdminCount,
            'roleSuperadminCount' => $roleSuperadminCount,
            'roles' => $roles,
            'permissions' => $permissions,
        ])->layout('layouts.admin');
    }
}
