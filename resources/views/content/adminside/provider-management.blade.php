@php
$configData = Helper::appClasses();
@endphp

@extends('content/adminside/layouts/layoutMaster')

@section('title', 'Providers')

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
    <script src="{{asset('assets/vendor/libs/datatables-buttons/datatables-buttons.js')}}"></script>
    <script src="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.js')}}"></script>
    <!-- Form Validation -->
    <script src="{{asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js')}}"></script>
    <script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js')}}"></script>
    <script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('adminside/js/provider_management.js')}}"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.13.5/xlsx.full.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.13.5/jszip.js"></script>
@endsection

<style>
    .datatables-basic {
        font-size: .9rem;
    }
    .fa-spinner {
        display: none
    }
    .btn-icon {
        display: table-cell!important;
    }
    .provider-status {
        min-width: 194px;
    }
</style>

@section('content')
<h4>Providers</h4>
<!-- <p>Register providers.</p> -->
<!-- DataTable with Buttons -->
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title m-0 me-2">Providers</h5>
        <div class="card-element">
            <ul class="list-inline mb-0">
                <li class="list-inline-item">
                    <div class="dropdown">
                        <button class="btn btn-label-primary" type="button" id="timelineWapper" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class='bx bx-import me-sm-2'></i> Import Providers
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="timelineWapper">
                            <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="offcanvas" data-bs-target="#import_copy_modal" aria-controls="offcanvasEnd"><i class='bx bx-copy-alt me-sm-2'></i>Copy/Past</a>
                            <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="offcanvas" data-bs-target="#import_file_modal" aria-controls="offcanvasEnd"><i class='bx bxs-file-import me-sm-2'></i>Excel</a>
                        </div>
                    </div>
                </li>
                <li class="list-inline-item">
                    <button class="create-new btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#add-new-record" aria-controls="offcanvasEnd">
                        <i class="bx bx-plus me-sm-2"></i> <span class="d-none d-sm-inline-block">Add New Provider</span>
                    </button>
                </li>
            </ul>
        </div>
    </div>
    <div class="card-datatable table-responsive">
        <table class="dt-column-search datatables-basic table border-top" id="data_table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>API template</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Updated</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
               
            </tbody>
        </table>
    </div>
</div>

<!-- Modal to add new record -->
<div class="offcanvas offcanvas-end" id="add-new-record">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="exampleModalLabel">New Provider</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body flex-grow-1">
        <form class="add-new-record pt-0 row g-2" id="form-add-new-record" onsubmit="return false">
            <input type="hidden" id="m_selected_id">
            <input type="hidden" id="m_action_type" value="add">
            <div class="col-sm-12">
                <label class="form-label" for="domain_name">Provider Domain</label>
                <div class="input-group input-group-merge">
                    <!-- <span class="input-group-text"><i class="bx bx-user"></i></span> -->
                    <input type="text" id="domain_name" class="form-control" name="domain_name" placeholder="Domain Name" aria-label="Domain Name" aria-describedby="domain_name" />
                </div>
            </div>
            <div class="col-sm-12">
                <label class="form-label" for="end_point">End Point</label>
                <div class="input-group input-group-merge">
                    <!-- <span class="input-group-text"><i class="bx bx-user"></i></span> -->
                    <input type="text" id="end_point" class="form-control" name="end_point" placeholder="/api/v2" aria-label="End Point" aria-describedby="end_point" />
                </div>
            </div>
            <div class="col-sm-12">
                <label class="form-label" for="api_key">API Key</label>
                <div class="input-group input-group-merge">
                    <span class="input-group-text">
                        <!-- <i class="bx bx-envelope"></i> -->
                        <i class='bx bxs-key'></i>
                    </span>
                    <input type="text" id="api_key" name="api_key" class="form-control" placeholder="API Key" autocomplete="off"/>
                </div>
                <div class="form-text">
                    
                </div>
            </div>
            <div class="col-sm-12">
                <button type="submit" class="btn btn-primary data-submit me-sm-3 me-1">
                    <span id="submit_btn_title">Submit</span>
                    <i class="fas fa-spinner fa-spin" style="display:none"></i>
                </button>
                <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
            </div>
        </form>

    </div>
</div>


<!-- Modal to import Providers with Copy/Past -->
<div class="offcanvas offcanvas-end" id="import_copy_modal"  style="z-index: 2002;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="exampleModalLabel">Import Providers</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body flex-grow-1">
        <form class="import-copy-form pt-0 row g-2" id="form-import-copy">
            <div class="col-sm-12 form-text">
                <p>
                    One provider per line separated with ;<br>
                    API Key is not required<br>
                </p>
                <p>
                    Example:
                    <ul style="list-style:none">
                        <li>Website.com;Endpoint;APIKey</li>
                        <li>Website.com;Endpoint;APIKey</li>
                        <li>Website.com;Endpoint</li>
                        <li>Website.com;Endpoint</li>
                    </ul>
                </p>
            </div>
            <div class="col-sm-12">
                <label class="form-label" for="providers_list">Providers</label>
                <textarea id="providers_list" class="form-control" name="providers_list" placeholder="Providers List" rows="20"></textarea>
            </div>
            
            <div class="col-sm-12">
                <button type="submit" class="btn btn-primary data-submit-copy me-sm-3 me-1">
                    Submit
                    <i class="fas fa-spinner fa-spin" style="display:none"></i>
                </button>
                <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
            </div>
        </form>

    </div>
</div>

<!-- Modal to import Providers from file -->
<div class="offcanvas offcanvas-end" id="import_file_modal"  style="z-index: 2002;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="exampleModalLabel">Import Providers</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body flex-grow-1">
        <form class="import-file-form pt-0 row g-2" id="form-import-file">
            <div class="col-sm-12">
                <label for="formFile" class="form-label">Select Excel File</label>
                <input class="form-control" type="file" id="formFile" name="formFile" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
            </div>
            <div class="form-text">
                Column Names: Domain, Endpoint, Key
            </div>
            
            <div class="col-sm-12 mt-4">
                <button type="submit" class="btn btn-primary data-submit-file me-sm-3 me-1">
                    Submit
                    <i class="fas fa-spinner fa-spin" style="display:none"></i>
                </button>
                <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
            </div>
        </form>

    </div>
</div>
<!--/ DataTable with Buttons -->
@endsection
