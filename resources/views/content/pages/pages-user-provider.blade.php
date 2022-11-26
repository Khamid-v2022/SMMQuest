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
                    <th>Index</th>
                    <th>Provider Name</th>
                    <th>Favorite</th>
                    <th>Status</th>
                    <th>Added At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @php $index = 0; @endphp
                @foreach($providers as $provider)
                    @php $index++; @endphp
                    <tr data-provider_id={{ $provider->id }}>
                        <td>{{ $index }}</td>
                        <td>{{ $provider->provider->domain }}</td>
                        <td>{{ $provider->is_favorite }}</td>
                        <td>
                            @if($provider->is_enabled == 1)
                                <span class="badge bg-label-success">Enabled</span>
                            @else
                                <span class="badge bg-label-danger">Disabled</span>
                            @endif 
                            @if($provider->provider->is_hold == 1)
                                <span class="badge bg-label-danger" title="Waiting on Admin Activation">Hold</span>
                            @elseif($provider->is_valid_key == 0)
                                <span class="badge bg-label-warning">Invalid API Key</span>
                            @endif
                        </td>
                        <td>{{ $provider->created_at }}</td>
                        <td>
                            @if($provider->provider->is_hold == 0)
                                <a href="javascript:;" class="btn btn-sm btn-icon item-edit" title="Add/Edit API key"><i class="bx bxs-edit"></i></a>
                            @endif
                        </td>
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
                <label class="form-label" for="domain_name">Provider Name</label>
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
                <button type="submit" class="btn btn-primary data-submit me-sm-3 me-1">
                    Submit
                    <i class="fas fa-spinner fa-spin" style="display:none"></i>
                </button>
                <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
            </div>
        </form>

    </div>
</div>
<!--/ DataTable with Buttons -->

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

@endsection
