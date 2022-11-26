@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Coming Soon - Pages')

@section('vendor-style')
<!-- Vendor -->
<link rel="stylesheet" href="{{asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css')}}" />
@endsection

@section('page-style')
<!-- Page -->
<link rel="stylesheet" href="{{asset('assets/vendor/css/pages/page-misc.css')}}">
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js')}}"></script>
@endsection

@section('page-script')
<script src="{{ asset('custom/js/misc_coming_soon.js')}}"></script>
@endsection

@section('content')
<!-- Under Maintenance -->
<div class="container-xxl py-3">
  <div class="misc-wrapper">
    <h2 class="mb-2 mx-2">Your #1 Services Research Tool!</h2>
    <p class="mt-4 mx-2">Tired of searching your services manually? We got a solution for you!</p>
    <p class="mb-4 mx-2">Subscribe and you will be the first to be notified!</p>
    <form id="subscribe_form" class="mb-3">
      <div class="d-flex gap-2">
        <div class="form-group ">
            <input type="email" class="form-control" placeholder="email" id="email" name="email" autofocus>
        </div>
        <button type="submit" class="btn btn-primary" id="submit_btn">
            Subscribe
            <i class="fas fa-spinner fa-spin" style="display: none"></i>
        </button>
      </div>
    </form>
  
    <div class="mt-5">
      <img src="{{asset('assets/img/illustrations/boy-with-rocket-'.$configData['style'].'.png')}}" alt="boy-with-rocket-light" width="500" class="img-fluid" data-app-dark-img="illustrations/boy-with-rocket-dark.png" data-app-light-img="illustrations/boy-with-rocket-light.png">
    </div>
  </div>
</div>
<!-- /Under Maintenance -->
@endsection