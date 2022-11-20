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
<script src="{{asset('custom/js/user-provider.js')}}"></script>
@endsection

@section('content')
<h4>Providers Page</h4>
<p>Register your providers.</p>
<!-- DataTable with Buttons -->
<div class="card">
    <div class="card-datatable table-responsive">
        <table class="datatables-basic table border-top">
            <thead>
                <tr>
                    <th>Provider Name</th>
                    <th>Favorite</th>
                    <th>Balance</th>
                    <th>API Key</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($providers as $provider)
                    <tr data-provider_id={{ $provider->id }}>
                        <td>{{ $provider->provider->domain }}</td>
                        <td>{{ $provider->is_favorite }}</td>
                        <td>$ {{ 0 }}</td>
                        <td>{{ $provider->api_key }}</td>
                        <td>{{ $provider->provider->is_activated }}</td>
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
        <h5 class="offcanvas-title" id="exampleModalLabel">New Provider</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body flex-grow-1">
        <form class="add-new-record pt-0 row g-2" id="form-add-new-record" onsubmit="return false">
            <div class="col-sm-12">
                <label class="form-label" for="domain_name">Provider Domain</label>
                <div class="input-group input-group-merge">
                    <!-- <span class="input-group-text"><i class="bx bx-user"></i></span> -->
                    <input type="text" id="domain_name" class="form-control" name="domain_name" placeholder="Domain Name" aria-label="Domain Name" aria-describedby="domain_name" />
                </div>
            </div>
            <div class="col-sm-12">
                <div class="form-check form-check-primary mt-3">
                    <input class="form-check-input" type="checkbox" value="" id="favorite" checked />
                    <label class="form-check-label" for="favorite">Favorite</label>
                </div> 
            </div>

            <div class="col-sm-12">
                <label class="form-label" for="api_key">API Key</label>
                <div class="input-group input-group-merge">
                    <span class="input-group-text">
                        <!-- <i class="bx bx-envelope"></i> -->
                        <i class='bx bxs-key'></i>
                    </span>
                    <input type="text" id="api_key" name="api_key" class="form-control" placeholder="API Key" />
                </div>
                <div class="form-text">
                    You can use it for test
                </div>
            </div>
            <div class="col-sm-12">
                <button type="submit" class="btn btn-primary data-submit me-sm-3 me-1">Submit</button>
                <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
            </div>
        </form>

    </div>
</div>
<!--/ DataTable with Buttons -->
 <!-- Modal template -->
 <div class="modal modal-transparent fade" id="modals-change_key" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <a href="javascript:void(0);" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></a>
                <p class="text-white text-large fw-light mb-3">Edit api key</p>
                <div class="input-group input-group-lg mb-3">
                    <input type="hidden" value="" id="m_selected_id">
                    <input type="text" class="form-control bg-white border-0" id="m_api_key" placeholder="API Key" aria-describedby="">
                    <button class="btn btn-primary" type="button" id="m_change_api_btn">Change</button>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection
