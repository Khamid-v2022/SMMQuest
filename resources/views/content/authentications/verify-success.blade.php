@php
$customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Verify Email')

@section('page-style')
<!-- Page -->
<link rel="stylesheet" href="{{asset('assets/vendor/css/pages/page-auth.css')}}">
@endsection

@section('content')
<div class="authentication-wrapper authentication-basic px-4">
  <div class="authentication-inner">


    <!-- Verify Email -->
    <div class="card">
      <div class="card-body">
        <!-- Logo -->
        <div class="app-brand justify-content-center">
          <a href="{{url('/')}}" class="app-brand-link gap-2">
            <span class="app-brand-logo demo">@include('_partials.macros',['width'=>25,'withbg' => "#696cff"])</span>
            <span class="app-brand-text demo text-body fw-bolder">{{ config('variables.templateName') }}</span>
          </a>
        </div>
        <!-- /Logo -->
        <h3 class="mb-2">Verify your email ✉️</h3>
        <p class="text-start">
            Account activation is successed...
        </p>
        <a class="btn btn-primary w-100 my-3" href="{{url('/')}}">
          Go to login
        </a>
      </div>
    </div>
    <!-- /Verify Email -->
  </div>
</div>
@endsection
