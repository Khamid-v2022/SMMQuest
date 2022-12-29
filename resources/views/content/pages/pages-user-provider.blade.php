@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

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
<!-- <script src="{{asset('assets/vendor/libs/datatables-responsive/datatables.responsive.js')}}"></script>
<script src="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.js')}}"></script> -->
<script src="{{asset('assets/vendor/libs/datatables-buttons/datatables-buttons.js')}}"></script>
<script src="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.js')}}"></script>

<!-- Row Group JS -->
<!-- <script src="{{asset('assets/vendor/libs/datatables-rowgroup/datatables.rowgroup.js')}}"></script>
<script src="{{asset('assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.js')}}"></script> -->
<!-- Form Validation -->
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('custom/js/user-provider.js')}}"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.13.5/xlsx.full.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.13.5/jszip.js"></script>
@endsection

<style type="text/css">
    .datatables-basic {
        font-size: .9rem;
    }
    .light-style .swal2-container {
        z-index: 3000!important;
    }

    .provider-status {
        min-width: 194px;
    }
</style>

@section('content')
<h4>Providers Page</h4>
<p>Register your providers.</p>
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
        <table class="datatables-basic table border-top" id="data_table">
            <thead>
                <tr>
                    <th>Index</th>
                    <th>Provider Name</th>
                    <th>Favorite</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th>Added At</th>
                    <th>Updated At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
              
            </tbody>
        </table>
    </div>
</div>
<!--/ DataTable with Buttons -->

<!-- Modal to add new record -->
<div class="offcanvas offcanvas-end" id="add-new-record">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="exampleModalLabel">New Provider</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body flex-grow-1">
        <form class="add-new-record pt-0 row g-2" id="form-add-new-record" onsubmit="return false">
            <div class="col-sm-12">
                <label class="form-label" for="domain_name">Provider Name</label>
                <div class="input-group input-group-merge">
                    <!-- <span class="input-group-text"><i class="bx bx-user"></i></span> -->
                    <input type="text" id="domain_name" class="form-control" name="domain_name" placeholder="Domain Name" aria-label="Domain Name" aria-describedby="domain_name" />
                </div>
            </div>
            <div class="col-sm-12">
                <div class="form-check form-check-primary mt-3">
                    <input class="form-check-input" type="checkbox" value="" id="favorite" />
                    <label class="form-check-label" for="favorite">Favorite</label>
                </div> 
            </div>
            
            <div class="col-sm-12">
                <button type="submit" class="btn btn-primary data-submit me-sm-3 me-1">
                    Submit
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
                        <li>Website.com;APIKey</li>
                        <li>Website.com;APIKey</li>
                        <li>Website.com</li>
                        <li>Website.com</li>
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
                Column Names: Domain, Key
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


<!-- API Key Modal -->
<div class="modal fade" id="modals-change_key" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="modalCenterTitle">Edit api key</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div class="row">
                <input type="hidden" value="" id="m_selected_id">
                <div class="col mb-3">
                    <label for="m_api_key" class="form-label">API key</label>
                    <input type="text" id="m_api_key" placeholder="API Key" class="form-control">
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" id="m_change_api_btn">
                <span>Save changes</span>
                <i class="fas fa-spinner fa-spin" style="display:none"></i>
            </button>
        </div>
        </div>
    </div>
</div>


<!-- Set Balance Limit Modal -->
<div class="modal fade" id="modals-change_balance_limit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Email Balance Alert</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div class="row">
                <input type="hidden" value="" id="m_sel_id">
                <div class="col mb-3">
                    <label for="m_balance_limit" class="form-label">Balance Alert Limit</label>
                    <input type="number" id="m_balance_limit" placeholder="Balance Alert Limit Amount" class="form-control" step="any">
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" id="m_change_alert_btn">
                <span>Save changes</span>
                <i class="fas fa-spinner fa-spin" style="display:none"></i>
            </button>
        </div>
        </div>
    </div>
</div>
@endsection
