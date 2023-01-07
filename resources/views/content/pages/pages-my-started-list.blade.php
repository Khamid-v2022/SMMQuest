@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'My Started Lists')

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
    .accordion-title {
        display: flex;
        justify-content: space-between;
        width: 100%;
        margin-right: 20px;
    }
    .accordion-title .accordion-action button{
        margin-right: 20px;
        z-index: 9;
    }
</style>
@endsection


@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables/jquery.dataTables.js')}}"></script>
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('custom/js/my-list.js')}}"></script>
@endsection

@section('content')
<h4>My Started Lists</h4>
<ul class="nav nav-pills flex-column flex-md-row mb-3">
    <li class="nav-item"><a class="nav-link" href="{{url('/my-list')}}"><i class="bx bxs-stopwatch me-1"></i>Ready</a></li>
    <li class="nav-item"><a class="nav-link active" href="javascript:void(0);"><i class="bx bxs-plane-alt me-1"></i>Started</a></li>
</ul>
<div class="my-lists">
    <div class="accordion">
       
    </div>
</div>
@endsection