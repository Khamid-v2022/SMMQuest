@php
$configData = Helper::appClasses();
@endphp

@extends('content/adminside/layouts/layoutMaster')

@section('title', 'Users')

@section('vendor-style')
    <link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
    <link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
    <link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css')}}">
    <link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
    <link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />
    <!-- Row Group CSS -->
    <link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.css')}}">
    <!-- Form Validation -->
    <link rel="stylesheet" href="{{asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css')}}" />
@endsection


@section('vendor-script')
    <script src="{{asset('assets/vendor/libs/datatables/jquery.dataTables.js')}}"></script>
    <script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
    <script src="{{asset('assets/vendor/libs/datatables-responsive/datatables.responsive.js')}}"></script>
    <script src="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.js')}}"></script>
    <script src="{{asset('assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.js')}}"></script>
    <script src="{{asset('assets/vendor/libs/datatables-buttons/datatables-buttons.js')}}"></script>
    <script src="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.js')}}"></script>
    <script src="{{asset('assets/vendor/libs/jszip/jszip.js')}}"></script>
    <script src="{{asset('assets/vendor/libs/pdfmake/pdfmake.js')}}"></script>
    <script src="{{asset('assets/vendor/libs/datatables-buttons/buttons.html5.js')}}"></script>
    <script src="{{asset('assets/vendor/libs/datatables-buttons/buttons.print.js')}}"></script>
    <!-- Flat Picker -->
    <script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
    <script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>
    <!-- Row Group JS -->
    <script src="{{asset('assets/vendor/libs/datatables-rowgroup/datatables.rowgroup.js')}}"></script>
    <script src="{{asset('assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.js')}}"></script>
    <!-- Form Validation -->
    <script src="{{asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js')}}"></script>
    <script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js')}}"></script>
    <script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('adminside/js/user_management.js')}}"></script>
@endsection

<style type="text/css">
    .datatables-basic {
        font-size: .9rem;
    }
    .fa-spinner {
        display: none
    }
    .switch .switch-toggle-slider i {
        top: 2.65px!important;
    }
</style>

@section('content')
<h4>Users</h4>
<!-- DataTable with Buttons -->
<div class="card">
    <div class="card-datatable table-responsive">
        <table class="datatables-basic table border-top">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Email</th>
                    <th>Verified</th>
                    <th>Status</th>
                    <th>Last Auth</th>
                    <th>Created</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @php $index = 0; @endphp
                @foreach($users as $user)
                    @php $index++; @endphp
                    <tr data-user_id= {{ $user->id }} data-email={{ $user->email }} data-status={{ $user->is_delete }}>
                        <td>{{ $index }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if($user->verified == 1)
                                <span class="badge bg-label-success">Verified</span>
                            @else
                                <span class="badge bg-label-danger">Not verified</span>
                            @endif
                        </td>
                        <td>
                            @if($user->is_delete == 0)
                                <span class="badge bg-label-success">Enabled</span>
                            @else
                                <span class="badge bg-label-danger">Disabled</span>
                            @endif
                        </td>
                        <td>{{ $user->last_auth_at }}</td>
                        <td>{{ $user->created_at }}</td>
                        <td></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal to add new record -->
<div class="offcanvas offcanvas-end" id="add-new-record">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="exampleModalLabel">Update User</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body flex-grow-1">
        <form class="add-new-record pt-0 row g-2" id="form-add-new-record" onsubmit="return false">
            <input type="hidden" id="m_selected_id">
            <input type="hidden" id="m_action_type">
            <div class="col-sm-12">
                <label class="form-label" for="m_user_email">Email</label>
                <div class="input-group input-group-merge">
                    <!-- <span class="input-group-text"><i class="bx bx-user"></i></span> -->
                    <input type="email" id="m_user_email" class="form-control" name="m_user_email" placeholder="Email" aria-label="Email" aria-describedby="Email" />
                </div>
            </div>
            <div class="col-sm-12">
                <div class="">Status</div>
                <label class="switch">
                    <input type="checkbox" class="switch-input" id="m_is_delete"/>
                    <span class="switch-toggle-slider">
                        <span class="switch-on">
                            <i class="bx bx-check"></i>
                        </span>
                        <span class="switch-off">
                            <i class="bx bx-x"></i>
                        </span>
                    </span>
                </label>
            </div>
            <div class="col-sm-12 mt-3">
                <button type="button" class="btn btn-warning me-sm-3 me-1" id="reset_password_btn">
                    <span>Reset Password</span>
                    <i class="fas fa-spinner fa-spin" style="display:none"></i>
                </button>
            </div>
            
            <div class="col-sm-12 mt-3">
                <button type="submit" class="btn btn-primary data-submit me-sm-3 me-1">
                    <span id="submit_btn_title">Submit</span>
                    <i class="fas fa-spinner fa-spin" style="display:none"></i>
                </button>
                <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
            </div>
        </form>

    </div>
</div>
<!--/ DataTable with Buttons -->
@endsection
