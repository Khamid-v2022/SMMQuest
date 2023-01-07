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
<script src="{{asset('custom/js/my-list.js')}}"></script>
@endsection

@section('content')
<h4>My Lists</h4>
<ul class="nav nav-pills flex-column flex-md-row mb-3">
    <li class="nav-item"><a class="nav-link active" href="javascript:void(0);"><i class="bx bxs-stopwatch me-1"></i>Ready</a></li>
    <li class="nav-item"><a class="nav-link" href="{{url('/my-started-list')}}"><i class="bx bxs-plane-alt me-1"></i>Started</a></li>
</ul>
<div class="my-lists">
    <div class="accordion">
        @php $index = 0; @endphp
        @foreach($list as $item)
            @php $index++ @endphp
            <div class="card accordion-item" data-list_id="{{$item->id}}">
                <h5 class="accordion-header">
                    <div class="accordion-title">    
                        <span>{{ $item->list_name }}</span>
                        <div class="accordion-action">
                            <button class="btn btn-sm btn-primary start-order-btn">Start test order</button>
                            <span class="created-date">{{ $item->created_at }}</span>
                            <a class="accordion-button {{$index > 1 ? 'collapsed':''}}" type="button" data-bs-toggle="collapse" {{ $index == 1 ? 'aria-expanded="true"':'' }} data-bs-target="#accordion-{{$item->id}}" aria-controls="accordion-{{$item->id}}">
                            </a>
                        </div>
                    </div>
                </h5>
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
                                                <a href="javascript:;" class="btn btn-sm btn-icon btn-icon-custom delete-service-btn" title="Delete serviced from this list" data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="left"><i class="bx bxs-trash"></i></a>
                                                <a href="javascript:void(0);" class="btn-icon-custom card-collapsible collapse-detail-box-btn"><i class="tf-icons bx bxs-chevron-up"></i></a>
                                                <!-- <a href="javascript:;" class="btn btn-sm btn-icon collapse-detail-box-btn"><i class="bx bxs-trash"></i></a> -->
                                            </td>
                                        </tr>
                                        <tr class="collapse" data-list_service_id="{{$service->id}}" data-template="{{$service->api_template}}">
                                            <td colspan="7">
                                                <form class="order-details" data-list_service_id="{{$service->id}}" data-service_id="{{$service->service_id}}" data-template="{{$service->api_template}}">
                                                    <div class="row">
                                                        <div class="col-sm-4">
                                                            <label class="form-label" for="quantity_for_{{$service->id}}">Quantity:</label>
                                                            <input type="number" id="quantity_for_{{$service->id}}" class="form-control form-control-sm quantity-input" placeholder="Quantify" value="{{$service->quantity}}">
                                                        </div>
                                                        <div class="col-sm-4">
                                                            <label class="form-label" for="link_for_{{$service->id}}">Link:</label>
                                                            <input type="text" id="link_for_{{$service->id}}" class="form-control form-control-sm link-input" placeholder="Link" value="{{$service->link}}">
                                                        </div>
                                                    </div>
                                                </form>
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