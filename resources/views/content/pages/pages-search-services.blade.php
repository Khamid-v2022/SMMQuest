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
<!-- <link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}"> -->
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />
<!-- Row Group CSS -->
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.css')}}">

<style type="text/css">
    .sticky-wrapper {
        bottom: 0px;
        position: sticky;
        display: none;
        z-index: 9;
    }

    #exiting_list {
        max-height: 200px;
        overflow-y: scroll;
    }
</style>
@endsection


@section('vendor-script')
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/tagify/tagify.js')}}"></script>
<script src="{{asset('assets/vendor/libs/bootstrap-select/bootstrap-select.js')}}"></script>
<script src="{{asset('assets/vendor/libs/datatables/jquery.dataTables.js')}}"></script>
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<!-- <script src="{{asset('assets/vendor/libs/datatables-responsive/datatables.responsive.js')}}"></script>
<script src="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.js')}}"></script> -->
<script src="{{asset('assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.js')}}"></script>
<script src="{{asset('assets/vendor/libs/datatables-buttons/datatables-buttons.js')}}"></script>
<script src="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>
<script src="{{asset('assets/vendor/libs/jquery-sticky/jquery-sticky.js')}}"></script>

<!-- Row Group JS -->
<!-- <script src="{{asset('assets/vendor/libs/datatables-rowgroup/datatables.rowgroup.js')}}"></script>
<script src="{{asset('assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.js')}}"></script> -->
@endsection

@section('page-script')
<script src="{{asset('custom/js/search-services.js')}}"></script>
@endsection

@section('content')
<h4>Search Services</h4>

<div class="row mb-3">
    <div class="col-md">
        <div class="card card-action mb-4">
            <div class="card-header">
                <div class="card-action-title">Services Filters</div>
                <div class="card-action-element">
                    <ul class="list-inline mb-0">
                        <li class="list-inline-item">
                            <a href="javascript:void(0);" class="card-collapsible" id="close_card"><i class="tf-icons bx bx-chevron-up"></i></a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="collapse show">
                <div class="card-body">
                    <form id="search_form">
                        <div class="row pt-3">
                            <div class="col-12 col-sm-6 col-lg-4">
                                <label class="form-label" for="providers">Providers:</label>
                                <select id="providers" class="select2 form-select" multiple>
                                    <option value="0" selected>All</option>
                                    <option value="-1">Favorite Providers Only</option>
                                    @foreach($providers as $provider)
                                        <option value="{{ $provider->id }}">{{ $provider->domain }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-lg-4">
                                <label class="form-label" for="type">Service Type:</label>
                                <select id="type" class="selectpicker w-100">
                                    <option value=" " selected>All</option>
                                    @foreach($types as $type)
                                        <option value="{{ $type }}">{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row pt-3">
                            <div class="col-12 col-sm-6 col-lg-4">
                                <label class="form-label" for="include">
                                    Words Included
                                    <span class="badge rounded-pill bg-label-primary" title='Press Enter or "," to add words' data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="right">?</span>
                                </label>
                                <input id="include" class="form-control"/>
                                <!-- <div class="form-text">
                                    *At least two word must be entered
                                </div> -->
                            </div>
                            <div class="col-12 col-sm-6 col-lg-4">
                                <label class="form-label" for="exclude">
                                    Words Excluded
                                    <span class="badge rounded-pill bg-label-primary" title='Press Enter or "," to add words' data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="right">?</span> 
                                </label>
                                <input id="exclude" class="form-control"/>
                            </div>
                        </div>
                        <div class="row pt-3">
                            <div class="col-12 col-sm-6 col-lg-4">
                                <label class="form-label">Minimum Quantity:</label>
                                <input type="number" id="min" class="form-control dt-input" placeholder="Minimum Quantity">
                            </div>
                            <div class="col-12 col-sm-6 col-lg-4">
                                <label class="form-label">Maximum Quantity:</label>
                                <input type="number" id="max" class="form-control dt-input" placeholder="Maximum Quantity">
                            </div>
                        </div>
                        <div class="row pt-3">
                            <div class="col-12 col-sm-6 col-lg-4">
                                <label class="form-label">Minimum Price:</label>
                                <input type="number" id="min_rate" class="form-control dt-input" placeholder="Minimum Price" step="any">
                            </div>
                            <div class="col-12 col-sm-6 col-lg-4">
                                <label class="form-label">Maximum Price:</label>
                                <input type="number" id="max_rate" class="form-control dt-input" placeholder="Maximum Price" step="any">
                            </div>
                            <div class="col-12 col-sm-6 col-lg-4">
                                <label class="form-label">Currency:</label>
                                <select id="currency" class="selectpicker w-100">
                                    @foreach(config('variables.currencies') as $currency)
                                        <option value="{{ $currency }}">{{ $currency }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row pt-3">
                            <div class="col-12 col-sm-6 col-lg-4">
                                <label class="form-label" for="added_after">Added After:</label>
                                <input type="text" id="added_after" class="form-control flatpickr-date" placeholder="Added After">
                            </div>
                            <div class="col-12 col-sm-6 col-lg-4">
                                <label class="form-label" for="added_before">Added Before:</label>
                                <input type="number" id="added_before" class="form-control flatpickr-date" placeholder="Added Before">
                            </div>
                        </div>

                        <div class="row pt-3">
                            <small class="text-light fw-semibold d-block">Show Columns</small>
                            <div class="col-sm">
                                <div class="form-check form-check-primary mt-2">
                                    <input class="form-check-input show-column-item" data-sel-class="service-domain" data-column-index="0" id="check_provider" type="checkbox" checked />
                                    <label class="form-check-label" for="check_provider">Provider</label>
                                </div>
                                <div class="form-check form-check-primary mt-2">
                                    <input class="form-check-input show-column-item" data-sel-class="service-type" data-column-index="4" id="check_type" type="checkbox" />
                                    <label class="form-check-label" for="check_type">Service Type</label>
                                </div>
                                <div class="form-check form-check-primary mt-2">
                                    <input class="form-check-input show-column-item" data-sel-class="service-dripfeed" data-column-index="8" id="check_dripfeed" type="checkbox" checked />
                                    <label class="form-check-label" for="check_dripfeed">Dripfeed</label>
                                </div>
                            </div>
                            <div class="col-sm">
                                <div class="form-check form-check-primary mt-2">
                                    <input class="form-check-input show-column-item" data-sel-class="service-category" data-column-index="1" id="check_category" type="checkbox" />
                                    <label class="form-check-label" for="check_category">Category</label>
                                </div>
                                <div class="form-check form-check-primary mt-2">
                                    <input class="form-check-input show-column-item" data-sel-class="service-rate" data-column-index="5" id="check_rate" type="checkbox" checked />
                                    <label class="form-check-label" for="check_rate">Price</label>
                                </div>
                                <div class="form-check form-check-primary mt-2">
                                    <input class="form-check-input show-column-item" data-sel-class="service-refill" data-column-index="9" id="check_refill" type="checkbox" checked />
                                    <label class="form-check-label" for="check_refill">Refill Button</label>
                                </div>
                               
                            </div>
                            <div class="col-sm">
                                <div class="form-check form-check-primary mt-2">
                                    <input class="form-check-input show-column-item" data-sel-class="service-id" data-column-index="2" id="check_id" type="checkbox" checked />
                                    <label class="form-check-label" for="check_id">ID</label>
                                </div>
                                <div class="form-check form-check-primary mt-2">
                                    <input class="form-check-input show-column-item" data-sel-class="service-min" data-column-index="6" id="check_min" type="checkbox" checked />
                                    <label class="form-check-label" for="check_min">Min</label>
                                </div>
                                <div class="form-check form-check-primary mt-2">
                                    <input class="form-check-input show-column-item" data-sel-class="service-cancel" data-column-index="10" id="check_cancel" type="checkbox" checked />
                                    <label class="form-check-label" for="check_cancel">Cancel Button</label>
                                </div>
                                
                            </div>
                            <div class="col-sm">
                                <div class="form-check form-check-primary mt-2">
                                    <input class="form-check-input show-column-item" data-sel-class="service-name" data-column-index="3" id="check_name" type="checkbox" checked />
                                    <label class="form-check-label" for="check_name">Name</label>
                                </div>
                                <div class="form-check form-check-primary mt-2">
                                    <input class="form-check-input show-column-item" data-sel-class="service-max" data-column-index="7" id="check_max" type="checkbox" checked />
                                    <label class="form-check-label" for="check_max">Max</label>
                                </div>
                                <div class="form-check form-check-primary mt-2">
                                    <input class="form-check-input show-column-item" data-sel-class="service-created_at" data-column-index="11" id="check_created_at" type="checkbox" />
                                    <label class="form-check-label" for="check_created_at">Added Date</label>
                                </div>
                            </div>
                            <div class="col-12 text-right pt-2">
                                <button class="btn btn-primary me-sm-3 me-1 float-end data-submit" type="submit">
                                    <i class='bx bx-search-alt-2'></i> Search
                                    <i class="fas fa-spinner fa-spin" style="display:none"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-datatable table-responsive">
        <button class="btn btn-info load-more" data-page="1" style="position: absolute; right: 15px;top: 15px;z-index:10">
            <span class="btn-txt"> Load More</span>
            <i class="fas fa-spinner fa-spin" style="display:none"></i>
        </button>
        <table class="datatables-basic table border-top" id="data_table" style="font-size: .9rem;">
            <thead>
                <tr>
                    <th class='service-domain'> Provider </th>
                    <th class='service-category'>Category</th>
                    <th class='service-id'>ID</th>
                    <th class='service-name'>Name</th>
                    <th class='service-type'>Service Type</th>
                    <th class='service-rate'>Price</th>
                    <th class='service-min'>Min</th>
                    <th class='service-max'>Max</th>
                    <th class='service-dripfeed'>Dripfeed</th>
                    <th class='service-refill'>Refill Button</th>
                    <th class='service-cancel'>Cancel Button</th>
                    <th class='service-created_at'>Added Date</th>
                    <th class=''>Is Favorite</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="tbl-body">
                
            </tbody>
        </table>
    </div>
    <div class="card-body text-end">
        <button class="btn btn-info load-more" id="load_more" data-page="1">
            <span class="btn-txt">Load More</span>
            <i class="fas fa-spinner fa-spin" style="display:none"></i>
        </button>
    </div>
    <div class="card-footer sticky-element bg-label-secondary d-flex justify-content-sm-between align-items-sm-center flex-column flex-sm-row">
        <h5 class="card-title mb-sm-0 me-2">Services Selected: <span id="selected_count"></span></h5>
        <div class="action-btns">
            <button class="btn btn-primary" id="add_list">
                Add to the List
            </button>
        </div>
    </div> 
</div>

<!-- Add Services to the List Modal -->
<div class="modal fade" id="add_service_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Add Services to List</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form>
                <div class="row">
                    <div class="col mb-4">
                        <label for="new_list_name" class="form-label">New List</label>
                        <input type="text" id="new_list_name" placeholder="New List" class="form-control">
                    </div>
                </div>
                <div class="row">
                    <div class="col mb-3">
                        <div class="d-flex justify-content-sm-between mb-2">
                            <label for="existing_list_wraper" class="form-label">Existing List</label>
                            <button type="button" class="btn btn-sm btn-secondary" id="clear_selected_list">Clear</button>
                        </div>
                        <div id="existing_list_wraper">
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" id="m_save_btn">
                <span>Save</span>
                <i class="fas fa-spinner fa-spin" style="display:none"></i>
            </button>
        </div>
        </div>
    </div>
</div>

@endsection

