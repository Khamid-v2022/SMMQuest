@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Search Services')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/tagify/tagify.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-select/bootstrap-select.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />
<!-- Row Group CSS -->
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.css')}}">
@endsection


@section('vendor-script')
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/tagify/tagify.js')}}"></script>
<script src="{{asset('assets/vendor/libs/bootstrap-select/bootstrap-select.js')}}"></script>
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
@endsection

@section('page-script')
<script src="{{asset('custom/js/search-services.js')}}"></script>
@endsection
<style type="text/css">
    .service-domain {
        max-width:80px;
    }
    .service-min {
        max-width: 30px!important;
        text-align: right;
    } 
    .service-rate, .service-id, .service-max {
        max-width: 60px!important;
        text-align: right;
    }

    .service-dripfeed, .service-refill, .service-cancel {
        max-width: 70px!important;
        padding: 10px 18px!important;
    }
</style>


@section('content')
<h4>Search Services</h4>
<!-- DataTable with Buttons -->
<div class="card">
    <h5 class="card-header">Services</h5>
    <div class="card-body">
        <form id="search_form">
            <div class="row">
                <div class="col-12">
                    <div class="row g-3">
                        <div class="col-12 col-sm-6 col-lg-4">
                            <label class="form-label" for="providers">Providers:</label>
                            <select id="providers" class="select2 form-select" multiple>
                                <option value="0" selected>All</option>
                                @foreach($providers as $provider)
                                    <option value="{{ $provider->provider_id }}">{{ $provider->provider->domain }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-4">
                            <label class="form-label" for="type">Type:</label>
                            <select id="type" class="selectpicker w-100">
                                @foreach($types as $type)
                                    @if($type->type == 'Default')
                                    <option value="{{ $type->type }}" selected>{{ $type->type }}</option>
                                    @else
                                    <option value="{{ $type->type }}">{{ $type->type }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-4">
                            <label class="form-label" for="include">Include:</label>
                            <input id="include" class="form-control"/>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-4">
                            <label class="form-label" for="exclude">Exclude:</label>
                            <input id="exclude" class="form-control"/>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-4">
                            <label class="form-label">Min:</label>
                            <input type="number" id="min" class="form-control dt-input" placeholder="Min">
                        </div>
                        <div class="col-12 col-sm-6 col-lg-4">
                            <label class="form-label">Max:</label>
                            <input type="number" id="max" class="form-control dt-input" placeholder="Max">
                        </div>
                    </div>
                    <div class="text-right pt-4 ">
                        <button class="btn btn-primary me-sm-3 me-1 float-end data-submit" type="submit">
                            <i class='bx bx-search-alt-2'></i> Search
                            <i class="fas fa-spinner fa-spin" style="display:none"></i>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <hr class="mt-0">
    <div class="card-datatable table-responsive">
        <table class="datatables-basic table border-top">
            <thead>
                <tr>
                    <th class='service-domain'> Provider </th>
                    <th class='service-id'>ID</th>
                    <th class='service-name'>Name</th>
                    <th class='service-category'>Category</th>
                    <th class='service-rate'>Rate</th>
                    <th class='service-min'>Min</th>
                    <th class='service-max'>Max</th>
                    <th class='service-type'>Type</th>
                    <th class='service-dripfeed'>Dripfeed</th>
                    <th class='service-refill'>Refill</th>
                    <th class='service-cancel'>Cancel</th>
                </tr>
            </thead>
            <tbody id="tbl-body">
               
            </tbody>
        </table>
    </div>
</div>
@endsection
