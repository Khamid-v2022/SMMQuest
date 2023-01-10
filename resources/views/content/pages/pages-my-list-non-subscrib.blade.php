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
<!-- Form Validation -->
<link rel="stylesheet" href="{{asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css')}}" />

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
</style>
@endsection


@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables/jquery.dataTables.js')}}"></script>
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('custom/js/my-list-non-subscrib.js')}}"></script>
@endsection

@section('content')
<h4>My Lists</h4>
<div class="my-lists">
    <div class="accordion" id="lists_wrraper">
        
    </div>
</div>
@endsection