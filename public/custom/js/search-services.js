var dt_basic;
var all_show_table;
var service_type_html = '<option value="-1">All</option>';

var send_data = null;

var result_services = [];
let min_array_opt = [];
let max_array_opt = [];
let providers_opt = [];
let types_opt = [];

var previous_selected_providers = ["0"];
$(function () {
    $(".load-more").css("display", "none");
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    $(".select2").wrap('<div class="position-relative"></div>').select2({
        placeholder: 'Select providers',
        dropdownParent: $(".select2").parent()
    });

    const collapseElementList = [].slice.call(document.querySelectorAll('.card-collapsible'));
    collapseElementList.map(function (collapseElement) {
        collapseElement.addEventListener('click', event => {
            event.preventDefault();
            // Collapse the element
            new bootstrap.Collapse(collapseElement.closest('.card').querySelector('.collapse'));
            // Toggle collapsed class in `.card-header` element
            collapseElement.closest('.card-header').classList.toggle('collapsed');
            // Toggle class bx-chevron-down & bx-chevron-up
            Helpers._toggleClass(collapseElement.firstElementChild, 'bx-chevron-down', 'bx-chevron-up');
        });
    })


    $('.selectpicker').selectpicker();
    const includeEl = document.querySelector('#include');
    const TagifyInclude = new Tagify(includeEl, 
        // { delimiters: [',', ' ']}
    );
    const includeEx = document.querySelector('#exclude');
    const TagifyExclude = new Tagify(includeEx, 
        // { delimiters: [',', ' ']}
    );

    // DataTable with buttons
    // --------------------------------------------------------------------
    $('.datatables-basic thead tr').clone(true).appendTo('.datatables-basic thead');
    $('.datatables-basic thead tr:eq(1) th').each(function (i) {
        var title = $(this).text();
        if(i == 0){
            let html = '<select class="form-select" id="search_provider">';
            html += '<option value="-1">All</option>';  
            html += '</select>';
            $(this).html(html);
        }
        else if(i == 4) {
            let html = '<select class="form-select" id="search_type">';
            html += '<option value="-1">All</option>';  
            html += '</select>';
            $(this).html(html);
        } 
        else if(i == 6) {
            let html = '<select class="form-select" id="search_min">';
            html += '<option value="-1">All</option>';  
            html += '</select>';
            $(this).html(html);
        } else if(i == 7) {
            let html = '<select class="form-select" id="search_max">';
            html += '<option value="-1">All</option>';  
            html += '</select>';
            $(this).html(html);
        } else if(i == 8 || i == 9 || i == 10 ){
            let html = '<select class="form-select search-status">';
            html += '<option value="-1">All</option>';
            html += '<option value="Yes">Yes</option>';
            html += '<option value="No">No</option>';   
            html += '</select>';
            $(this).html(html);
        } else {
            $(this).html('<input type="text" class="form-control" placeholder="Search ' + title + '" />');
        }

        // $('input', this).on('keyup change', function () {
        $('input', this).on('change', function () {
            blockDataTable();
            if (dt_basic.column(i).search() !== this.value) {
                dt_basic.column(i).search(this.value).draw();
            }
            $("#data_table").unblock();
        });

        $('select.search-status', this).on('change', function () {
            blockDataTable();
            if(this.value == -1){
                dt_basic.column(i).search("").draw();
            } else {
                if (dt_basic.column(i).search() !== this.value) {
                    dt_basic.column(i).search(this.value).draw();
                }
            }
            $("#data_table").unblock();
        });

        $('#search_provider', this).on('change', function () {
            blockDataTable();
            if(this.value == -1){
                dt_basic.column(11).search("").draw();
                dt_basic.column(0).search("").draw();
            } else if(this.value == 0) {
                // favorite provider only
                dt_basic.column(11).search(1).draw();
            } else {
                dt_basic.column(11).search("").draw();
                if (dt_basic.column(0).search() !== this.value) {
                    dt_basic.column(0).search(this.value ? '^' + this.value + '$' : '', true, false).draw()
                }
            }
            $("#data_table").unblock();
        });

        $('#search_type', this).on('change', function () {
            blockDataTable();
            if(this.value == -1){
                dt_basic.column(i).search("").draw();
            } else {
                if (dt_basic.column(i).search() !== this.value) {
                    dt_basic.column(i).search(this.value ? '^' + this.value + '$' : '', true, false).draw()
                }
            }
            $("#data_table").unblock();
        });

        $('#search_min', this).on('change', function () {
            blockDataTable();
            if(this.value == -1){
                dt_basic.column(i).search("").draw();
            } else {
                if (dt_basic.column(i).search() !== this.value) {
                    dt_basic.column(i).search(this.value ? '^' + this.value + '$' : '', true, false).draw()
                }
            }
            $("#data_table").unblock();
        });

        $('#search_max', this).on('change', function () {
            blockDataTable();
            if(this.value == -1){
                dt_basic.column(i).search("").draw();
            } else {
                if (dt_basic.column(i).search() !== this.value) {
                    dt_basic.column(i).search(this.value ? '^' + this.value + '$' : '', true, false).draw()
                }
            }
            $("#data_table").unblock();
        });

    });

    dt_basic = $('.datatables-basic').DataTable({
        columns: [
            { data: 'domain'},
            { data: 'category' },
            { data: 'service' },
            { data: 'name' },
            { data: 'type'},
            { data: 'rate'},
            { data: 'min'},
            { data: 'max'},
            { data: 'dripfeed'},
            { data: 'refill'},
            { data: 'cancel'},
            { data: 'is_favorite'}
        ],
        columnDefs: [
            {
                className: 'service-domain',
                targets: 0,
                render: function (data, type, full, meta) {
                    let domain = data;
                    if(full.is_favorite == 1)
                        domain += '<i class="bx bxs-like text-warning ms-1" style="display:inline"></i>';
                    return domain;
                }
            },
            {
                className: 'service-category',
                targets: 1
            },
            {
                className: 'service-id',
                targets: 2
            },
            {
                className: 'service-name',
                targets: 3
            },
            {
                className: 'service-type',
                targets: 4,
            },
            {
                className: 'service-rate text-end',
                targets: 5,
                render: function(data){
                    // if(data && data.toString().includes("≈")){
                    //     num = data.toString().split("≈");
                    //     if(num.length >= 1){
                    //         let _number = num[1].trim();
                    //         return "≈ " + parseFloat(_number).toLocaleString('en-US', {maximumFractionDigits:5});
                    //     }
                    // } else{
                        return data ? data.toLocaleString('en-US', {maximumFractionDigits:5}) : '';
                    // }
                        
                }
            },
            {
                className: 'service-min text-end',
                targets: 6,
                render: function (data, type, full, meta) {
                    return data ? data.toLocaleString('en-US') : '';
                }
            },
            {
                className: 'service-max text-end',
                targets: 7,
                render: function (data, type, full, meta) {
                    
                    return data ? data.toLocaleString('en-US') : '';
                }
            },
            {
                className: 'service-dripfeed text-center',
                searchable: true,
                targets: 8,
                render: function (data, type, full, meta) {
                    if(data == 1)
                        return '<span class="badge bg-label-success">Yes</span>';
                    else 
                        return '<span class="badge bg-label-warning">No</span>';
                },
            },
            {
                className: 'service-refill text-center',
                searchable: true,
                targets: 9,
                render: function (data, type, full, meta) {
                    if(data == 1)
                        return '<span class="badge bg-label-success">Yes</span>';
                    else 
                        return '<span class="badge bg-label-warning">No</span>'
                },
            },
            {
                className: 'service-cancel text-center',
                searchable: true,
                targets: 10,
                render: function (data, type, full, meta) {
                    if(data == 1)
                        return '<span class="badge bg-label-success">Yes</span>';
                    else 
                        return '<span class="badge bg-label-warning">No</span>'
                },
            }
        ],
        order: [[5, 'asc']],
        orderCellsTop: true,
        // ordering: false,
        // paging: false,
        // lengthChange: false,
        displayLength: 1000,
        lengthMenu: [1000, 2500, 5000],
        // scrollY: '700px',
        // scrollX: false,
        dom: '<"row"<"col-sm-12 col-md-6"l>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
    });
    // hide category, type column as default
    dt_basic.column(1).visible(false);
    dt_basic.column(4).visible(false);
    dt_basic.column(11).visible(false);
  
    // Filter form control to default size
    // ? setTimeout used for multilingual table initialization
    setTimeout(() => {
        $('.dataTables_filter .form-control').removeClass('form-control-sm');
        $('.dataTables_length .form-select').removeClass('form-select-sm');
    }, 300);

    $("#providers").on('change', function(){
        const selected_providers = $(this).val();
        // if select other provider not "all" 
        if(selected_providers.length > 1 && previous_selected_providers.includes("0")){
            // remove "all" item
            const index = selected_providers.indexOf("0");
            if (index > -1) { 
                selected_providers.splice(index, 1); 
                $(this).val(selected_providers).trigger("change");
                previous_selected_providers = selected_providers;
                return;
            }
        }

        if(!previous_selected_providers.includes("0") && selected_providers.includes("0") && selected_providers.length > 1){
            $(this).val(['0']).trigger("change");
            previous_selected_providers = selected_providers;
            return;
        }

        if(selected_providers.length > 1 && previous_selected_providers.includes("-1")){
            // remove "favorite provider" item
            const index = selected_providers.indexOf("-1");
            if (index > -1) { 
                selected_providers.splice(index, 1); 
                $(this).val(selected_providers).trigger("change");
                previous_selected_providers = selected_providers;
                return;
            }
        }

        if(!previous_selected_providers.includes("-1") && selected_providers.includes("-1") && selected_providers.length > 1){
            $(this).val(['-1']).trigger("change");
            previous_selected_providers = selected_providers;
            return;
        }       
    })

    $(".show-column-item").on("click", function(){
        blockDataTable();
        // Get the column API object
        var column = dt_basic.column($(this).attr('data-column-index'));
        // Toggle the visibility
        column.visible(!column.visible());
        $("#search_type").html(service_type_html);
        $("#data_table").unblock();
    })

    $("#search_form").on("submit", function(e){
        e.preventDefault();

        $(".load-more").attr("data-page", 0);
        $(".load-more").css("display", "none");
        send_data = null;

        const providers = $("#providers").val();
        const type = $("#type").val();
        const min = $("#min").val();
        const max = $("#max").val();

        let include_val, exclude_val;
        if($("#include").val())
            include_val = JSON.parse($("#include").val());
        else 
            include_val = [];
        
        if($("#exclude").val())
            exclude_val = JSON.parse($("#exclude").val());
        else 
            exclude_val = [];

        let include = [], exclude = [];

        if(include_val.length < 2 || (include_val.length == 2 && (include_val[0].value.length < 2 || include_val[1].value.length < 2))){
            Swal.fire({
                icon: 'warning',
                title: '',
                text: "At least two word must be entered in the Words Included field.",
                customClass: {
                  confirmButton: 'btn btn-primary'
                },
                buttonsStyling: false
            }).then(function(){
                setTimeout(function(){
                    $("#include").focus();
                }, 50);
            })
            return;
        }

        include_val.forEach((item) => {
            include.push(item.value)
        })

        exclude_val.forEach((item) => {
            exclude.push(item.value)
        })

        send_data = {
            providers,
            type,
            include,
            exclude,
            min,
            max,
            min_rate: $("#min_rate").val(),
            max_rate: $("#max_rate").val(),
            currency: $("#currency").val()
        }

        dt_basic.clear().draw();

        // loadMore_with_ajax(0);

        const _url = "/search-services";

        $(".data-submit").attr("disabled", true);
        $(".data-submit .fa-spinner").css("display", "inline-block");
        
        blockDataTable();
        $.ajax({
            url: _url,
            type: "POST",
            data: send_data,
            success: function (response) {
                if (response.code == 200) {
                    console.log(response);
                    
                    result_services = response.services;
                    
                    min_array_opt = [];
                    max_array_opt = [];
                    providers_opt = [];
                    types_opt = [];

                    drawTableWithAPI(0);
                    
                    let collapseElement = document.getElementById("close_card");
                    new bootstrap.Collapse(collapseElement.closest('.card').querySelector('.collapse'));
                    // Toggle collapsed class in `.card-header` element
                    collapseElement.closest('.card-header').classList.toggle('collapsed');
                    // Toggle class bx-chevron-down & bx-chevron-up
                    Helpers._toggleClass(collapseElement.firstElementChild, 'bx-chevron-down', 'bx-chevron-up');

                    $(".data-submit .fa-spinner").css("display", "none");
                    $(".data-submit").removeAttr("disabled");
                    $("#data_table").unblock();
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: '',
                        text: response.message,
                        customClass: {
                          confirmButton: 'btn btn-primary'
                        },
                        buttonsStyling: false
                    })
                    
                    let sel_html = '<option value="-1">All</option>';
                    $("#search_min").html(sel_html);
                    $("#search_max").html(sel_html);
                    $("#search_provider").html(sel_html);
                    resetSearchFilterOfDataTable();
                    dt_basic.columns.adjust().draw();

                    $(".data-submit .fa-spinner").css("display", "none");
                    $(".data-submit").removeAttr("disabled");
                    $("#data_table").unblock();
                    return;
                }
            },
            error: function (response) {
                console.log(response);
                Swal.fire({
                    icon: 'warning',
                    title: '',
                    text: "There are too many results. Please refine your search a bit more",
                    customClass: {
                      confirmButton: 'btn btn-primary'
                    },
                    buttonsStyling: false
                })

                let sel_html = '<option value="-1">All</option>';
                $("#search_min").html(sel_html);
                $("#search_max").html(sel_html);
                $("#search_provider").html(sel_html);
                resetSearchFilterOfDataTable();
                dt_basic.columns.adjust().draw();
                
                $(".data-submit .fa-spinner").css("display", "none");
                $(".data-submit").removeAttr("disabled");
                $("#data_table").unblock();
                return;
            },
        });
          
    })

    $(".load-more").on("click", function(){
        const page = $(this).attr("data-page");
        // loadMore_with_ajax(page);
        $(".load-more").attr("disabled", true);
        $(".load-more .fa-spinner").css("display", "inline-block");
        blockDataTable();

        setTimeout(function(){
            drawTableWithAPI(page)
        }, 200);

        // $(".load-more").removeAttr("disabled");
        // $(".load-more .fa-spinner").css("display", "none");
        // $("#data_table").unblock();
    })

    $("#search_form input, #search_form select").on('change', function(){
        // hide load more button
        $(".load-more").css("display", "none");
    })
    
});

function blockDataTable() {
    $("#data_table").block({
        message:
          '<div class="spinner-border text-primary" role="status"></div>',
        css: {
          backgroundColor: 'transparent',
          border: '0'
        },
        overlayCSS: {
            backgroundColor: '#fff',
            opacity: 0.8
        }
    });
}

function drawTableWithAPI(page){
    let records = [];

    if(result_services.length == 0 || result_services.length <= page * 5000) {
        return;
    }

    // let show_services = result_services.splice(page * 5000, 5000);
    let show_services = result_services.slice(page * 5000, (parseInt(page) + 1) * 5000);

    show_services.forEach((service) => {
        records.push({
            domain: service.domain,
            category: service.category,
            service: service.service,
            name: service.name,
            type: service.type,
            rate: service.rate,
            min: service.min,
            max: service.max,
            dripfeed: service.dripfeed,
            refill: service.refill,
            cancel: service.cancel,
            is_favorite: service.is_favorite
        });

        if(!providers_opt.includes(service.domain))
            providers_opt.push(service.domain);
        if(!types_opt.includes(service.type))
            types_opt.push(service.type);
        if(!min_array_opt.includes(service.min))
            min_array_opt.push(service.min);
        if(!max_array_opt.includes(service.max))
            max_array_opt.push(service.max);
    })

    dt_basic.rows.add(records);

    min_array_opt.sort((a, b) => a - b);
    max_array_opt.sort((a, b) => a - b);

    let min_sel_html = '<option value="-1">All</option>';
    let max_sel_html = '<option value="-1">All</option>';
    let providers_html = '<option value="-1">All</option><option value="0">Favorite Providers Only</option>';
    service_type_html = '<option value="-1">All</option>';
    
    min_array_opt.forEach((item) => {
        min_sel_html += '<option value="' + item.toLocaleString('en-US') + '">' + item.toLocaleString('en-US') + '</option>';
    })

    max_array_opt.forEach((item) => {
        max_sel_html += '<option value="' + item.toLocaleString('en-US') + '">' + item.toLocaleString('en-US') + '</option>';
    })

    providers_opt.forEach((item) => {
        providers_html += '<option value="' + item + '">' + item + '</option>';
    })

    if(types_opt.length > 0 && types_opt.includes("Default")){
        const index = types_opt.indexOf("Default");
        types_opt.splice(index, 1);
        types_opt = ["Default"].concat(types_opt);
    }

    types_opt.forEach((item) => {
        service_type_html += '<option value="' + item + '">' + item + '</option>';
    })

    $("#search_min").html(min_sel_html);
    $("#search_max").html(max_sel_html);
    $("#search_provider").html(providers_html);
    $("#search_type").html(service_type_html);

    resetSearchFilterOfDataTable();

    dt_basic.columns.adjust().draw();

    $(".load-more").css("display", "inline");
    $(".load-more").attr("data-page", (parseInt($(".load-more").attr("data-page")) + 1));

    let remain_count = result_services.length - (parseInt(page) + 1) * 5000 > 0 ? (result_services.length - (parseInt(page) + 1) * 5000) : 0;
    $(".load-more .btn-txt").html("There are " + remain_count + " more results. Load More..");
    if(remain_count <= 0){
        // hide load more button
        $(".load-more").css("display", "none");
    }

    $(".load-more").removeAttr("disabled");
    $(".load-more .fa-spinner").css("display", "none");
    $("#data_table").unblock();

    
}

function resetSearchFilterOfDataTable(){
    $(".datatables-basic th select").val(-1).trigger('change');
    $(".datatables-basic th input").val("").trigger('change');
}

// function loadMore_with_ajax(page){

//     if(!send_data)
//         return;

//     const _url = "/search-services";


//     $(".data-submit").attr("disabled", true);
//     $(".data-submit .fa-spinner").css("display", "inline-block");
//     $(".load-more").attr("disabled", true);
//     $(".load-more .fa-spinner").css("display", "inline-block");
    
//     blockDataTable();

//     send_data.page = page;
    
//     $.ajax({
//         url: _url,
//         type: "POST",
//         data: send_data,
//         success: function (response) {
//             if (response.code == 200) {
//                 console.log(response);
//                 const services = response.services;

//                 drawTableWithAPI();????????????
                
//                 let collapseElement = document.getElementById("close_card");
//                 new bootstrap.Collapse(collapseElement.closest('.card').querySelector('.collapse'));
//                 // Toggle collapsed class in `.card-header` element
//                 collapseElement.closest('.card-header').classList.toggle('collapsed');
//                 // Toggle class bx-chevron-down & bx-chevron-up
//                 Helpers._toggleClass(collapseElement.firstElementChild, 'bx-chevron-down', 'bx-chevron-up');

//                 // $('.datatables-basic').DataTable();
//                 $(".data-submit .fa-spinner").css("display", "none");
//                 $(".data-submit").removeAttr("disabled");

//                 $(".load-more").removeAttr("disabled");
//                 $(".load-more .fa-spinner").css("display", "none");

//                 $(".load-more").css("display", "inline");
//                 $(".load-more").attr("data-page", (parseInt($(".load-more").attr("data-page")) + 1));

//                 $(".load-more .btn-txt").html("There are " + response.remain_rows + " more results. Load More..");
//                 if(services.length < 5000){
//                     // hide load more button
//                     $(".load-more").css("display", "none");
//                 }

//                 $("#data_table").unblock();
//             } else {
                
//                 let sel_html = '<option value="-1">All</option>';
//                 $("#search_min").html(sel_html);
//                 $("#search_max").html(sel_html);
//                 $("#search_provider").html(sel_html);
//                 resetSearchFilterOfDataTable();
//                 dt_basic.columns.adjust().draw();

//                 $(".data-submit .fa-spinner").css("display", "none");
//                 $(".data-submit").removeAttr("disabled");

//                 $(".load-more").removeAttr("disabled");
//                 $(".load-more .fa-spinner").css("display", "none");

//                 $("#data_table").unblock();
//                 return;
//             }
//         },
//         error: function (response) {
            
//             let sel_html = '<option value="-1">All</option>';
//             $("#search_min").html(sel_html);
//             $("#search_max").html(sel_html);
//             $("#search_provider").html(sel_html);
//             resetSearchFilterOfDataTable();
//             dt_basic.columns.adjust().draw();
            
//             $(".data-submit .fa-spinner").css("display", "none");
//             $(".data-submit").removeAttr("disabled");

//             $(".load-more").removeAttr("disabled");
//             $(".load-more .fa-spinner").css("display", "none");
//             return;
//         },
//     });
// }