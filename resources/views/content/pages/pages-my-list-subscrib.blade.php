@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'My Lists')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />
<!-- Row Group CSS -->
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.css')}}">

<style type="text/css">
    .accordion-header {
        padding: 10px 20px;
    }
    .accordion-title {
        display: flex;
        justify-content: space-between;
        width: 100%;
        margin-right: 20px;
    }
    .accordion-title span {
        padding: 4px 0;
    }

    .accordion-title span.created-date {
        font-size: 16px;
        padding: 8px 0;
    }
    .accordion-title .accordion-action {
        display: flex;
    }
    .accordion-title .accordion-action button {
        margin-right: 20px;
        z-index: 9;
    }
    .accordion-title .accordion-action a.accordion-button {
        width: 50px;
    }

    .btn-icon-custom {
        display: table-cell!important;
        padding-top: 3px;
    }

    .form-check-input[type=checkbox] {
        width: 16px;
        height: 16px;
    }

    label.service-cost-label {
        display: block;
        margin-top: 5px;
    }


    .layout-page {
        position: relative;
    }
    .sticky-wrapper {
        position: fixed;
        bottom: 0px;
        width: 100%;
        left: 0px;
        display: none;
        z-index: 9;
    }

    .sticky-element {
        padding: 12px 16px;
    }

    .input-error {
        border-color: red!important;
    }
</style>
@endsection


@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables/jquery.dataTables.js')}}"></script>
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('custom/js/my-list.js')}}"></script>
@endsection

@section('content')
<h4>My Lists</h4>
<div class="my-lists">
    <div class="accordion" id="lists_wrraper">
    </div>
    <div class="dataTables_wrapper text-end mt-2">
        <div class="dataTables_paginate paging_simple_numbers" id="paginate">
        </div>
    </div>
</div>
<div class="sticky-wrapper">
    <div class="sticky-element bg-label-secondary d-flex justify-content-sm-between align-items-sm-center flex-column flex-sm-row">
        <span>Services Selected: <span id="selected_count" class="badge bg-label-success">0</span></span>
        <span>Expected Cost: <span id="expected_cost" class="badge bg-label-success">0</span></span>
        <button class="btn btn-primary" id="start_test_order">
            Start Test Order
            <i class="fas fa-spinner fa-spin" style="display:none"></i>
        </button>
    </div> 
</div>
@endsection