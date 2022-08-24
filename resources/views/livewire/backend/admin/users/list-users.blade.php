<div>
    @section('style')
        <style>
            .disabled-link{
                cursor: default;
                pointer-events: none;
                text-decoration: none;
                color: rgb(174, 172, 172);
            }
        </style>
    @endsection

    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="mb-2 row">
                <div class="col-sm-6">
                    <h1 class="m-0">Users</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Users</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Small boxes (Stat box) -->
            <div class="card">
                <div class="card-header bg-light">
                    <h3 class="card-title">
                        <button wire:click.prevent='addNewUser' class="ml-1 btn btn-sm btn-primary">
                            <i class="mr-2 fa fa-plus-circle"
                                aria-hidden="true">
                                <span>Add New User</span>
                            </i>
                        </button>

                        <div class="btn-group">
                            <button type="button" class="btn btn-primary btn-sm">Action</button>
                            <button type="button" class="btn btn-primary btn-sm dropdown-toggle dropdown-icon" data-toggle="dropdown" aria-expanded="false">
                                <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <div class="dropdown-menu" role="menu" style="">
                                <a class="dropdown-item" wire:click.prevent="exportExcel" href="#" aria-disabled="true">Export to Excel</a>
                                <a class="dropdown-item" wire:click.prevent="importExcelForm" href="#">Import from Excel</a>
                                <a class="dropdown-item" wire:click.prevent="exportPDF" href="#">Export to PDF</a>
                                <div class="dropdown-divider"></div>
                                {{-- @if ($selectedRows) --}}
                                <a class="dropdown-item {{ $selectedRows ? '' : 'disabled-link' }}" wire:click.prevent="setAllAsActive" href="#">Set as Acive</a>
                                <a class="dropdown-item {{ $selectedRows ? '' : 'disabled-link' }}" wire:click.prevent="setAllAsInActive" href="#">Set as InActive</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item {{ $selectedRows ? 'text-danger' : 'disabled-link' }}  delete-confirm" wire:click.prevent="deleteSelectedRows" href="#">Delete Selected</a>
                                {{-- @endif --}}
                            </div>
                        </div>
                    </h3>

                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                        {{-- <button type="button" class="btn btn-tool" data-card-widget="remove" title="Remove">
                            <i class="fas fa-times"></i>
                        </button> --}}
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="form-group ">
                            {{-- search --}}
                            <div class="input-group" style="width: 200px;">
                                <input type="search" wire:model="searchTerm" class="form-control" placeholder="Search for..." value="Lorem ipsum">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-default">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Roles filter --}}
                        <div class="form-group d-flex align-items-center">
                            <label class="flex-wrap">Roles filter &nbsp &nbsp<span class="bg-secondary badge badge-light button-badge">Total : {{ $users->total() }}</span></label>
                            <select class="form-control bg-secondary flex-wrap" wire:change='filterUsersByRoles($event.target.value)'>
                                <option value="">All</option>
                                @foreach ($roles as $role)
                                    <option  value="{{ $role->name }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    @if ($selectedRows)
                        <span class="mb-2 text-success">
                            <i class="fa fa-user" aria-hidden="true"></i>
                            selected
                            <span class="text-dark font-weight-bold">{{ count($selectedRows) }}</span> {{ Str::plural('user', count($selectedRows)) }}
                            <a class="ml-2 text-gray" href="" wire:click="resetSelectedRows" data-toggle="tooltip" data-placement="top" title="Reset Selected Rows"><i class="fas fa-times"></i></a>
                        </span>
                    @endif

                    <div class="table-responsive">
                        <table id="example2"  class="table text-center table-bordered table-hover dataTable dtr-inline" aria-describedby="example2_info">
                            <thead class="bg-light">
                                <tr>
                                    <th scope="col">
                                        <div class="custom-control custom-checkbox small">
                                            <input type="checkbox" wire:model="selectPageRows" value="" class="custom-control-input" id="customCheck">
                                            <label class="custom-control-label" for="customCheck"></label>
                                        </div>
                                    </th>
                                    <th>#</th>
                                    <th>
                                        Name
                                        <span wire:click="sortBy('name')" class="text-sm float-sm-right" style="cursor: pointer;font-size:10px;">
                                            <i class="mr-1 fa fa-arrow-up" style="color:{{ $sortColumnName === 'name' && $sortDirection === 'asc' ? '#90EE90' : '' }}"></i>
                                            <i class="fa fa-arrow-down" style="color : {{ $sortColumnName === 'name' && $sortDirection === 'desc' ? '#90EE90' : '' }}"></i>
                                        </span>
                                    </th>
                                    <th class="align-middle" scope="col">Photo</th>
                                    <th>Phone</th>
                                    <th>Role</th>
                                    <th>
                                        Status
                                        <span wire:click="sortBy('status')" class="text-sm float-sm-right" style="cursor: pointer;font-size:10px;">
                                            <i class="mr-1 fa fa-arrow-up" style="color:{{ $sortColumnName === 'status' && $sortDirection === 'asc' ? '#90EE90' : '' }}"></i>
                                            <i class="fa fa-arrow-down" style="color : {{ $sortColumnName === 'status' && $sortDirection === 'desc' ? '#90EE90' : '' }}"></i>
                                        </span>
                                    </th>
                                    <th colspan="2">actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($users as $user)
                                    <tr>
                                        <td scope="col">
                                            <div class="custom-control custom-checkbox small">
                                                <input type="checkbox" wire:model="selectedRows" value="{{ $user->id }}" class="custom-control-input" id="{{ $user->id }}">
                                                <label class="custom-control-label" for="{{ $user->id }}"></label>
                                            </div>
                                        </td>
                                        <td>{{ $loop->iteration }}</td>
                                        <td class="dtr-control sorting_1" tabindex="0">{{ $user->name }}</td>
                                        {{-- <td>{{ $user->username }}</td> --}}
                                        <td><img src="{{ $user->profile_url }}" style="width: 50px;" class="img img-circle" alt=""></td>
                                        {{-- <td>{{ $user->email }}</td> --}}
                                        <td>{{ $user->phone }}</td>
                                        <td>{{ $user->roles[0]->name }}</td>
                                        <td>
                                            <span  class="font-weight-bold badge text-white {{ $user->status == 1 ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $user->status() }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button wire:click.prevent="edit({{ $user }})" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i></button>
                                                <button wire:click.prevent="show({{ $user }})" class="btn btn-info btn-sm"><i class="fa fa-user"></i></button>
                                                <button wire:click.prevent="confirmUserRemoval({{ $user->id }})" class="btn btn-danger btn-sm"><i class="fa fa-trash bg-danger"></i></button>
                                            </div>
                                            {{-- <form action="" method="post" id="delete-user-{{ $user->id }}" class="d-none">
                                                @csrf
                                                @method('DELETE')
                                            </form> --}}
                                        </td>
                                    </tr>

                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No Users found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            {{-- <tfoot>
                                <tr>
                                    <td colspan="5">
                                        {!! $users->appends(request()->all())->links() !!}
                                    </td>
                                </tr>
                            </tfoot> --}}
                        </table>
                    </div>
                </div>
                <!-- /.card-body -->
                <div class="card-footer bg-light">
                    {!! $users->appends(request()->all())->links() !!}
                </div>
                <!-- /.card-footer-->
            </div>
            <!-- /.row (main row) -->
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->

    <!-- Modal Create or Update User -->

    <div class="modal fade" id="form" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog" role="document">
            <form autocomplete="off" wire:submit.prevent="{{ $showEditModal ? 'updateUser' : 'createUser' }}">
                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title" id="exampleModalLabel">
                            @if ($showEditModal)
                                <span>Edit User</span>
                            @else
                            <span>Add New User</span>
                            @endif
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        {{-- @if ($errors->hasAny(['image', 'image.*']))
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif --}}
                        <div class="row h-100 justify-content-center align-items-center">
                            <div class="col-6">

                                <!-- Modal User Full Name -->

                                <div class="form-group">
                                    <label for="name">Full Name</label>
                                    <input type="text" tabindex="1" wire:model.defer="data.name" class="form-control @error('name') is-invalid @enderror" id="name" aria-describedby="nameHelp" placeholder="Enter full name">
                                    @error('name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <!-- Modal User Email -->

                                <div class="form-group">
                                    <label for="email">Email address</label>
                                    <input type="email" tabindex="3" wire:model.defer="data.email" class="form-control @error('email') is-invalid @enderror" id="email" aria-describedby="emailHelp" placeholder="Enter email">
                                    @error('email')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <!-- Modal User Password -->

                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <input type="password" tabindex="5" wire:model.defer="data.password" class="form-control @error('password') is-invalid @enderror" id="password" placeholder="Password">
                                    @error('password')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-6">

                                <!-- Modal User Username -->

                                <div class="form-group">
                                    <label for="username">UserName</label>
                                    <input type="text" tabindex="2" wire:model.defer="data.username" class="form-control @error('username') is-invalid @enderror" id="username" aria-describedby="usernameHelp" placeholder="Enter username">
                                    @error('username')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <!-- Modal User Phone -->

                                <div class="form-group">
                                    <label for="phone">Phone</label>
                                    <input type="text" tabindex="4" wire:model.defer="data.phone" class="form-control @error('phone') is-invalid @enderror" id="phone" aria-describedby="phoneHelp" placeholder="Enter Phone">
                                    @error('phone')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <!-- Modal User Password Confirmation -->

                                <div class="form-group">
                                    <label for="passwordConfirmation">Confirm Password</label>
                                    <input type="password" tabindex="6" wire:model.defer="data.password_confirmation" class="form-control" id="passwordConfirmation" placeholder="Confirm Password">
                                </div>
                            </div>
                        </div>

                        <!-- Modal User Address -->

                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" tabindex="7" wire:model.defer="data.address" class="form-control @error('address') is-invalid @enderror" id="address" aria-describedby="addressHelp" placeholder="Enter Address...">
                            @error('address')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Modal User Description -->

                        <div class="form-group">
                            <label for="description">Description</label>
                            <input type="text" tabindex="8" wire:model.defer="data.description" class="form-control @error('description') is-invalid @enderror" id="description" aria-describedby="descriptionHelp" placeholder="Enter description ...">
                            @error('description')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Modal User Roles -->

                        <div class="form-group">
                            <label for="role_id">@lang('roles')</label>
                            <select id="roles" tabindex="9" class="form-control @error('role_id') is-invalid @enderror" wire:model.defer="data.role_id">
                                <option hidden>@lang('Select role ..')</option>
                                @foreach ($roles as $role)
                                    <option class="bg-light" value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                            @error('role_id')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                        <!-- Modal User Photo -->

                        <div class="form-group">
                            <label for="custom-file">User Photo</label>
                            @if ($photo)
                                <img src="{{ $photo->temporaryUrl() }}" class="mb-2 d-block img img-circle" width="100px" alt="">
                            @else
                                <img src="{{ $data['profile_url'] ?? '' }}" class="mb-2 d-block img img-circle" width="100px" alt="">
                            @endif
                            <div class="mb-3 custom-file">
                                <div x-data="{ isUploading: false, progress: 5 }" x-on:livewire-upload-start="isUploading = true" x-on:livewire-upload-finish="isUploading = false; progress = 5" x-on:livewire-upload-error="isUploading = false" x-on:livewire-upload-progress="progress = $event.detail.progress">
                                    <input tabindex="10" wire:model="photo" type="file" class="custom-file-input @error('photo') is-invalid @enderror" id="validatedCustomFile">
                                    {{-- progres bar --}}
                                    <div x-show.transition="isUploading" class="mt-2 rounded progress progress-sm">
                                        <div class="progress-bar bg-primary progress-bar-striped" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" x-bind:style="`width: ${progress}%`">
                                            <span class="sr-only">40% Complete (success)</span>
                                        </div>
                                    </div>
                                </div>
                                <label class="custom-file-label" for="customFile">
                                    @if ($photo)
                                        {{ $photo->getClientOriginalName() }}
                                        <img src="{{ $photo }}" class="img-fluid ${3|rounded-top,rounded-right,rounded-bottom,rounded-left,rounded-circle,|}" alt="">
                                    @else
                                        Choose Image
                                    @endif
                                </label>
                            </div>
                        </div>

                        <!-- Modal User Status -->
                        <div class="form-group clearfix">
                            <label for="statusRadio" class="d-inline">Status :</label>
                            <div class="icheck-primary d-inline ml-2 mr-2">
                                <input type="radio" id="radioPrimary1" wire:model="data.status" value="1">
                                <label for="radioPrimary1">Active</label>
                            </div>
                            <div class="icheck-primary d-inline">
                                <input type="radio" id="radioPrimary2" wire:model="data.status" value="0">
                                <label for="radioPrimary2">InActive</label>
                            </div>
                        </div>

                        <!-- Modal User Permissions -->
                            <div id="permissions" class="form-group">
                                <div class="mb-2 card d-flex justify-content-center">
                                    <div class="card-header bg-light">
                                        <h4 class="text-center">Permissions</h4>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text">
                                            <div class="row">
                                                @foreach ($permissions as $index => $permission)
                                                    <div class="col-4">
                                                        <div class="form-group">
                                                            <div class="custom-control form-checkbox small">
                                                                <label class="items-center" :key="{{ $permission->id }}">
                                                                    <input
                                                                        type="checkbox"
                                                                        name="user_permissions.{{ $permission->id }}"
                                                                        wire:model.defer="user_permissions.{{ $permission->id }}"
                                                                        value="{{ $permission->id }}"
                                                                        class="form-checkbox"
                                                                    />
                                                                    <span class="mr-1">{{ $permission->display_name }}</span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </p>
                                    </div>
                                </div>
                                {{-- @endforeach --}}
                            </div>
                        {{-- @endif --}}
                    </div>

                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="mr-1 fa fa-times"></i> Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="mr-1 fa fa-save"></i>
                            @if ($showEditModal)
                                <span>Save Changes</span>
                            @else
                            <span>Save</span>
                            @endif
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Show User -->

    <div class="modal fade" id="modal-show-user">
        <div class="modal-dialog">
          <div class="modal-content bg-dark">
            <div class="modal-body text-muted">
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile">
                        <div class="text-center">
                            @if ($photo)
                                <img class="profile-user-img img-fluid img-circle" src="{{ $photo->temporaryUrl() }}" alt="User profile picture">
                            @else
                                <img src="{{ $data['profile_url'] ?? '' }}" class="profile-user-img img-fluid img-circle" alt="User profile picture">
                            @endif
                        </div>

                        <h3 class="text-center profile-username">{{ $data['name'] ?? ''  }}</h3>

                        <p class="text-center text-muted">{{ $data['username'] ?? ''  }}</p>

                        <ul class="mb-3 list-group list-group-unbordered">
                            <li class="list-group-item">
                                <b>About: </b> <a class="float-right">{{ $data['description'] ?? ''  }}</a>
                            </li>
                            <li class="list-group-item">
                                <b>Phone:</b> <a class="float-right">{{ $data['phone'] ?? ''  }}</a>
                            </li>
                            <li class="list-group-item">
                                <b>Email:</b> <a class="float-right">{{ $data['email'] ?? ''  }}</a>
                            </li>
                            <li class="list-group-item">
                                <b>Address:</b> <a class="float-right">{{ $data['address'] ?? ''  }}</a>
                            </li>
                            <li class="list-group-item">
                                <b>Member since:</b> <a class="float-right">{{ $data['created_at'] ?? ''  }}</a>
                            </li>
                        </ul>

                        <button type="button" class="btn btn-primary btn-block" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
          </div>
        </div>
    </div>

    <!-- Modal Delete User -->

    <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5>Delete User</h5>
                </div>

                <div class="modal-body">
                    <h4>Are you sure you want to delete this user?</h4>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="mr-1 fa fa-times"></i> Cancel</button>
                    <button type="button" wire:click.prevent="deleteUser" class="btn btn-danger"><i class="mr-1 fa fa-trash"></i>Delete User</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Import Excel File -->
    <div class="modal fade" id="importExcelModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog" role="document">
            <form autocomplete="off" wire:submit.prevent="importExcel">
                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title" id="exampleModalLabel">
                            Import Excel File
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <!-- Modal Excel File -->

                        <div class="form-group">
                            <label for="custom-file">Choose Excel File</label>
                            <div class="mb-3 custom-file">
                                <div x-data="{ isUploading: false, progress: 5 }" x-on:livewire-upload-start="isUploading = true" x-on:livewire-upload-finish="isUploading = false; progress = 5" x-on:livewire-upload-error="isUploading = false" x-on:livewire-upload-progress="progress = $event.detail.progress">
                                    <input wire:model.defer="excelFile" type="file" class="custom-file-input @error('excelFile') is-invalid @enderror" id="validatedCustomFile" required>
                                    {{-- progres bar --}}
                                    <div x-show.transition="isUploading" class="mt-2 rounded progress progress-sm">
                                        <div class="progress-bar bg-primary progress-bar-striped" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" x-bind:style="`width: ${progress}%`"></div>
                                    </div>
                                </div>
                                @error('excelFile')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                                <label class="custom-file-label" for="customFile">
                                    @if ($excelFile)
                                        {{ $excelFile->getClientOriginalName() }}
                                    @else
                                        Choose Excel file
                                    @endif
                                </label>
                            </div>
                        </div>
                        <div class="mb-0 form-group">
                            <label>Import As :</label>
                            <label class="ml-3 radio-inline">
                                <input type="radio" wire:click="importType('addNew')" name="optionsRadiosInline" id="optionsRadiosInline1" value="addNew" checked="checked">Add New
                            </label>
                            <label class="ml-3 radio-inline">
                                <input type="radio" wire:click="importType('Update')" name="optionsRadiosInline" id="optionsRadiosInline2" value="Update">Update
                            </label>
                        </div>
                    </div>

                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="mr-1 fa fa-times"></i> Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="mr-1 fa fa-open"></i> Open</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @section('script')

        <script>
            $(document).ready( function() {

                window.addEventListener('hide-form', function (event) {
                    $('#form').modal('hide');
                });

                window.addEventListener('show-form', function (event) {
                    $('#form').modal('show');
                });

                window.addEventListener('hide-modal-show', function (event) {
                    $('#modal-show-user').modal('hide');
                });

                window.addEventListener('show-modal-show', function (event) {
                    $('#modal-show-user').modal('show');
                });

                window.addEventListener('show-delete-modal', function (event) {
                    $('#confirmationModal').modal('show');
                });

                window.addEventListener('hide-delete-modal', function (event) {
                    $('#confirmationModal').modal('hide');
                });

                window.addEventListener('show-import-excel-modal', function (event) {
                    $('#importExcelModal').modal('show');
                });

                window.addEventListener('hide-import-excel-modal', function (event) {
                    $('#importExcelModal').modal('hide');
                });
            });
        </script>

        {{-- show-delete-alert-confirmation --}}

        <script>
            window.addEventListener('show-delete-alert-confirmation', event =>{
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                    if (result.isConfirmed) {
                        Livewire.emit('deleteConfirmed')
                    }
                })
            })
        </script>
    @endsection

</div>
