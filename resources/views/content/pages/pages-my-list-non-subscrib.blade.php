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
<h4>My Lists</h4>
<div class="my-lists">
    <div class="accordion">
        @php $index = 0; @endphp
        @foreach($list as $item)
            @php $index++ @endphp
            <div class="card accordion-item">
                <h2 class="accordion-header">
                    <a class="accordion-button {{$index > 1 ? 'collapsed':''}}" type="button" data-bs-toggle="collapse" {{ $index == 1 ? 'aria-expanded="true"':'' }} data-bs-target="#accordion-{{$item->id}}" aria-controls="accordion-{{$item->id}}">
                        <div class="accordion-title">    
                            <span>{{ $item->list_name }}</span>
                            <div class="accordion-action">
                                <button class="btn btn-sm btn-primary redirect-to-payment" title="Enable for subscribers only" data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" >Start test order</button>
                                <span>{{ $item->created_at }}</span>
                            </div>
                        </div>
                    </a>
                </h2>
                <div id="accordion-{{$item->id}}" class="accordion-collapse collapse {{ $index == 1 ? 'show':''}}">
                    <!-- <div class="accordion-body"> -->
                    <div>
                        <div class="card-datatable table-responsive">
                            <table class="table border-top" style="font-size: .9rem;">
                                <thead>
                                    <tr>
                                        <th class=''>Provider</th>
                                        <th class=''>ID</th>
                                        <th class=''>Service Name</th>
                                        <th class="text-end">Price</th>
                                        <th class="text-end">Min</th>
                                        <th class="text-end">Max</th>
                                        <th class=''></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($item->services as $service)
                                        <tr data-list_service_id="{{$service->id}}">
                                            <td>{{ $service->service->provider }}</td>
                                            <td>{{ $service->service->service }}</td>
                                            <td>{{ $service->service->name }}</td>
                                            <td class="text-end">{{ $service->service->rate . " " . $service->service->default_currency }}</td>
                                            <td class="text-end">{{ $service->service->min }}</td>
                                            <td class="text-end">{{ $service->service->max }}</td>
                                            <td class="text-center">
                                                <a href="javascript:;" class="btn btn-sm btn-icon delete-service-btn" title="Delete serviced from this list" data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="left"><i class="bx bxs-trash"></i></a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        
        @endforeach
    </div>
</div>
@endsection