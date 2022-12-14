<?php

namespace App\Http\Livewire\Backend\Admin\Users;

use App\Models\Role;
use App\Models\User;
use Livewire\Component;
use App\Models\Permission;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use App\Exports\UsersExport;
use App\Imports\UsersImport;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

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

    public $selectedRows = [];
	public $selectPageRows = false;
    protected $listeners = ['deleteConfirmed' => 'deleteUsers'];

    public $excelFile = Null;
    public $importTypevalue = 'addNew';

    // Updated Select Page Rows

    public function updatedSelectPageRows($value)
    {
        if ($value) {
            $this->selectedRows = $this->users->pluck('id')->map(function ($id) {
                return (string) $id;
            });
        } else {
            $this->reset(['selectedRows', 'selectPageRows']);
        }
    }

    public function resetSelectedRows()
    {
        $this->reset(['selectedRows', 'selectPageRows']);
    }

    // show Sweetalert Confirmation for Delete

    public function deleteSelectedRows()
    {
        $this->dispatchBrowserEvent('show-delete-alert-confirmation');
    }

    // set All selected User As Active

    public function setAllAsActive()
	{
		User::whereIn('id', $this->selectedRows)->update(['status' => 1]);

        $this->alert('success', 'Users set As Active successfully.', [
            'position'  =>  'top-end',
            'timer'  =>  3000,
            'toast'  =>  true,
            'text'  =>  null,
            'showCancelButton'  =>  false,
            'showConfirmButton'  =>  false
        ]);

		$this->reset(['selectPageRows', 'selectedRows']);
	}

    // set All selected User As InActive

	public function setAllAsInActive()
	{
		User::whereIn('id', $this->selectedRows)->update(['status' => 0]);

        $this->alert('success', 'Users set As Inactive successfully.', [
            'position'  =>  'top-end',
            'timer'  =>  3000,
            'toast'  =>  true,
            'text'  =>  null,
            'showCancelButton'  =>  false,
            'showConfirmButton'  =>  false
        ]);

		$this->reset(['selectPageRows', 'selectedRows']);
	}

    // Delete Selected User with relationship roles And permission

    public function deleteUsers()
    {
        // delete images for users if exists from Storage folder
        $profileImages =User::whereIn('id', $this->selectedRows)->get(['profile_photo']);
        foreach($profileImages as $profileImage){
            $imageFileName = $profileImage->profile_photo;
            if($imageFileName){
                Storage::disk('profile_photos')->delete($imageFileName);
            }
        }

        // delete roles and permissions for selected users from database
        DB::table('role_user')->whereIn('user_id', $this->selectedRows)->delete();
        DB::table('permission_user')->whereIn('user_id', $this->selectedRows)->delete();

        // delete selected users from database
		User::whereIn('id', $this->selectedRows)->delete();

        $this->alert('success', 'All selected users got deleted.', [
            'position'  =>  'top-end',
            'timer'  =>  3000,
            'toast'  =>  true,
            'text'  =>  null,
            'showCancelButton'  =>  false,
            'showConfirmButton'  =>  false
        ]);

		$this->reset();
    }

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
        $this->data['role_id'] = 3;
        $this->data['status'] = 1;
        $this->dispatchBrowserEvent('show-form');
    }

    // Create new user

    public function createUser()
    {
        $validatedData = Validator::make($this->data, [
			'name'          => 'required',
			'username'      => 'required|unique:users',
			'email'         => 'required|email|unique:users',
			'phone'         => 'required|numeric',
			'description'   => ['string', 'max:255'],
			'address'       => ['string', 'max:255'],
            'photo'         => 'image|mimes:jpeg,png,jpg,gif|max:2048',
			'password'      => 'required|confirmed',
            'status'        => 'required',
            'role_id'       => 'required',
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
                'description'           => 'max:255',
                'address'               => 'max:255',
                'photo'                 => 'image|mimes:jpeg,png,jpg,gif|max:2048',
                'status'                => 'required',
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

    // Show user details

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

            // delete roles and permissions for selected users from database
            DB::table('role_user')->where('user_id', $this->userIdBeingRemoved)->delete();
            DB::table('permission_user')->where('user_id', $this->userIdBeingRemoved)->delete();

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

    // Export Excel File

    public function exportExcel()
    {
        return Excel::download(new UsersExport($this->searchTerm,$this->selectedRows), 'users.xlsx');
    }

    // Show Import Excel Form

    public function importExcelForm()
    {
        $this->reset();
		$this->dispatchBrowserEvent('show-import-excel-modal');
    }

    public function importType($value)
    {
        $this->importTypevalue = $value;
    }

    public function importExcel()
    {
        try {

            $this->validate([
                'excelFile' => 'required|mimes:xls,xlsx'
            ]);

            if ($this->importTypevalue == 'addNew') {
                // for add new data
                Excel::import(new UsersImport, $this->excelFile);
            } else {
                // for update data
                //$this->importTypevalue = 'Update';
                $usersData = Excel::toCollection(new UsersImport(), $this->excelFile);
                foreach ($usersData[0] as $user) {
                    User::where('id', $user['id'])->update([
                        'name' => $user['name'],
                        'username' => $user['username'],
                        'phone' => $user['phone'],
                        'email' => $user['email'],
                        'description' => $user['description'],
                        'address' => $user['address'],
                        //'password' => bcrypt($user['password']),
                    ]);
                }
            }

            // method for add Roles to nwe users added

            $usersDoesntHaveRole = User::whereDoesntHave('roles')->get();

            foreach ($usersDoesntHaveRole as $user) {
                DB::table('role_user')->insert([
                    'role_id' => 3,
                    'user_id' => $user->id,
                    'user_type' => 'App\Models\User'
                ]);
            }

            // end method

            $this->alert('success', 'Users Added Successfully.', [
                'position'  =>  'top-end',
                'timer'  =>  3000,
                'toast'  =>  true,
                'text'  =>  null,
                'showCancelButton'  =>  false,
                'showConfirmButton'  =>  false
            ]);

            $this->dispatchBrowserEvent('hide-import-excel-modal');


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

            $this->reset();
            $this->dispatchBrowserEvent('hide-import-excel-modal');
        }
    }

    public function exportPDF()
    {
        return response()->streamDownload(function(){
            if ($this->selectedRows) {
                $users = User::whereIn('id', $this->selectedRows)->orderBy('name', 'asc')->get();
            } else {
                //$users = $this->users;
                $users = User::orderBy('name', 'asc')->get();
            }

            $pdf = PDF::loadView('livewire.backend.admin.users.users_pdf',['users' => $users]);
            return $pdf->stream('users');

        },'users.pdf');
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
