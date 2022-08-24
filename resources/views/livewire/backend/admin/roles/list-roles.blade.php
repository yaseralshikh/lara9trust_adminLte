<div>
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Roles</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard')}}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Roles</li>
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
                        <button wire:click.prevent='addNewRole' class="ml-1 btn btn-sm btn-primary">
                            <i class="mr-2 fa fa-plus-circle" aria-hidden="true">
                                <span>Add New Role</span>
                            </i>
                        </button>
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
                                <input type="search" wire:model="searchTerm" class="form-control"
                                    placeholder="Search for..." value="Lorem ipsum">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-default">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <span class="bg-secondary badge badge-light button-badge">Total : {{ $roles->total() }}</span>
                    </div>

                    <div class="table-responsive">
                        <table id="example2"  class="table text-center table-bordered table-hover dataTable dtr-inline" aria-describedby="example2_info">
                            <thead class="bg-light">
                                <tr>
                                    <th>#</th>
                                    <th>
                                        Name
                                        <span wire:click="sortBy('name')" class="text-sm float-sm-right" style="cursor: pointer;font-size:10px;">
                                            <i class="mr-1 fa fa-arrow-up" style="color:{{ $sortColumnName === 'name' && $sortDirection === 'asc' ? '#90EE90' : '' }}"></i>
                                            <i class="fa fa-arrow-down" style="color : {{ $sortColumnName === 'name' && $sortDirection === 'desc' ? '#90EE90' : '' }}"></i>
                                        </span>
                                    </th>
                                    <th>display_name</th>
                                    <th>description</th>
                                    <th>permissions</th>
                                    <th colspan="2">actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($roles as $role)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $role->name }}</td>
                                        <td>{{ $role->display_name }}</td>
                                        <td>{{ $role->description }}</td>
                                        <td>{{ $role->permissions()->count() }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button wire:click.prevent="edit({{ $role }})" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i></button>
                                                <button wire:click.prevent="confirmRoleRemoval({{ $role->id }})" class="btn btn-danger btn-sm"><i class="fa fa-trash bg-danger"></i></button>
                                            </div>
                                        </td>
                                    </tr>

                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No Roles found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            {{-- <tfoot>
                                <tr>
                                    <td colspan="5">
                                        {!! $roles->appends(request()->all())->links() !!}
                                    </td>
                                </tr>
                            </tfoot> --}}
                        </table>
                    </div>
                </div>
                <!-- /.card-body -->
                <div class="card-footer bg-light">
                    {!! $roles->appends(request()->all())->links() !!}
                </div>
                <!-- /.card-footer-->
            </div>
            <!-- /.row (main row) -->
        </div><!-- /.container-fluid -->
    </section>

    <!-- Modal Create or Update Role -->

    <div class="modal fade" id="form" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog" role="document">
            <form autocomplete="off" wire:submit.prevent="{{ $showEditModal ? 'updateRole' : 'createRole' }}">
                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title" id="exampleModalLabel">
                            @if ($showEditModal)
                                <span>Edit Role</span>
                            @else
                            <span>Add New Role</span>
                            @endif
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row h-100 justify-content-center align-items-center">
                            <div class="col-12">

                                <!-- Modal Role Name -->

                                <div class="form-group">
                                    <label for="name">Name</label>
                                    <input type="text" tabindex="1" wire:model.defer="data.name" class="form-control @error('name') is-invalid @enderror" id="name" aria-describedby="nameHelp" placeholder="Enter role name">
                                    @error('name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <!-- Modal Role Display Name -->

                                <div class="form-group">
                                    <label for="display_name">Display Name</label>
                                    <input type="text" tabindex="1" wire:model.defer="data.display_name" class="form-control @error('display_name') is-invalid @enderror" id="display_name" aria-describedby="display_nameHelp" placeholder="Enter role displayname">
                                    @error('display_name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <!-- Modal Role description -->

                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <input type="text" tabindex="1" wire:model.defer="data.description" class="form-control @error('description') is-invalid @enderror" id="description" aria-describedby="descriptionHelp" placeholder="Enter role description">
                                    @error('description')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Modal Role Permissions -->

                        <div id="permissions" class="form-group">
                            <div class="mb-2 card d-flex justify-content-center">
                                <div class="card-header">
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
                                                                    name="role_permissions.{{ $permission->id }}"
                                                                    wire:model.defer="role_permissions.{{ $permission->id }}"
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

    <!-- Modal Delete Role -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5>Delete Role</h5>
                </div>

                <div class="modal-body">
                    <h4>Are you sure you want to delete this role?</h4>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="mr-1 fa fa-times"></i> Cancel</button>
                    <button type="button" wire:click.prevent="deleteRole" class="btn btn-danger"><i class="mr-1 fa fa-trash"></i>Delete Role</button>
                </div>
            </div>
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
                window.addEventListener('show-delete-modal', function (event) {
                    $('#confirmationModal').modal('show');
                });
                window.addEventListener('hide-delete-modal', function (event) {
                    $('#confirmationModal').modal('hide');
                });
            });
        </script>

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
