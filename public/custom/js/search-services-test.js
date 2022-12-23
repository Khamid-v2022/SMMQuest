var dt_basic = null;
var all_show_table;
var service_type_html = '<option value="-1">All</option>';

var send_data = null;

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
    drawTable("");
   
  
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

        loadUsingAjax();
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

function resetSearchFilterOfDataTable(){
    $(".dt-column-search th select").val(-1).trigger('change');
    $(".dt-column-search th input").val("").trigger('change');
}

function loadUsingAjax(){

    if(!send_data)
        return;

    const _url = "/search-services-test";


    $(".data-submit").attr("disabled", true);
    $(".data-submit .fa-spinner").css("display", "inline-block");
    $(".load-more").attr("disabled", true);
    $(".load-more .fa-spinner").css("display", "inline-block");
    
    blockDataTable();

    $.ajax({
        url: _url,
        type: "POST",
        data: send_data,
        success: function (response) {
            if (response.code == 200) {
                console.log(response.tbody)
                
                // document.getElementById("tbl-body").innerHTML = response.tbody;
                drawTable(response.tbody);
                
                let collapseElement = document.getElementById("close_card");
                new bootstrap.Collapse(collapseElement.closest('.card').querySelector('.collapse'));
                // Toggle collapsed class in `.card-header` element
                collapseElement.closest('.card-header').classList.toggle('collapsed');
                // Toggle class bx-chevron-down & bx-chevron-up
                Helpers._toggleClass(collapseElement.firstElementChild, 'bx-chevron-down', 'bx-chevron-up');

                // $('.datatables-basic').DataTable();
                $(".data-submit .fa-spinner").css("display", "none");
                $(".data-submit").removeAttr("disabled");
                
                $("#data_table").unblock();
            } else {
                
                let sel_html = '<option value="-1">All</option>';
                $("#search_min").html(sel_html);
                $("#search_max").html(sel_html);
                $("#search_provider").html(sel_html);
                resetSearchFilterOfDataTable();
                
                drawTable("");
                
                $(".data-submit .fa-spinner").css("display", "none");
                $(".data-submit").removeAttr("disabled");

                $("#data_table").unblock();
                return;
            }
        },
        error: function (response) {
            
            let sel_html = '<option value="-1">All</option>';
            $("#search_min").html(sel_html);
            $("#search_max").html(sel_html);
            $("#search_provider").html(sel_html);
            resetSearchFilterOfDataTable();
            
            drawTable("");

            $(".data-submit .fa-spinner").css("display", "none");
            $(".data-submit").removeAttr("disabled");

            $(".load-more").removeAttr("disabled");
            $(".load-more .fa-spinner").css("display", "none");
            return;
        },
    });
}

function drawTable(html){
    if(dt_basic)
        dt_basic.destroy();

    $("#tbl-body").html(html);
    dt_basic = $('.datatables-basic').DataTable({
        // columns: [
        //     { data: 'domain'},
        //     { data: 'category' },
        //     { data: 'service' },
        //     { data: 'name' },
        //     { data: 'type'},
        //     { data: 'rate'},
        //     { data: 'min'},
        //     { data: 'max'},
        //     { data: 'dripfeed'},
        //     { data: 'refill'},
        //     { data: 'cancel'}
        // ],
        // columnDefs: [
        //     {
        //         className: 'service-domain',
        //         targets: 0,
        //         render: function (data, type, full, meta) {
        //             let domain = data;
        //             if(full.is_favorite == 1)
        //                 domain += '<i class="bx bxs-like text-warning ms-1" style="display:inline"></i>';
        //             return domain;
        //         }
        //     },
        //     {
        //         className: 'service-category',
        //         targets: 1
        //     },
        //     {
        //         className: 'service-id',
        //         targets: 2
        //     },
        //     {
        //         className: 'service-name',
        //         targets: 3
        //     },
        //     {
        //         className: 'service-type',
        //         targets: 4,
        //     },
        //     {
        //         className: 'service-rate text-end',
        //         targets: 5,
        //         render: function(data){
        //             if(data && data.toString().includes("≈")){
        //                 num = data.toString().split("≈");
        //                 if(num.length >= 1){
        //                     let _number = num[1].trim();
        //                     return "≈ " + parseFloat(_number).toLocaleString('en-US', {maximumFractionDigits:5});
        //                 }
        //             } else{
        //                 return data ? data.toLocaleString('en-US', {maximumFractionDigits:5}) : '';
        //             }
                        
        //         }
        //     },
        //     {
        //         className: 'service-min text-end',
        //         targets: 6,
        //         render: function (data, type, full, meta) {
        //             return data ? data.toLocaleString('en-US') : '';
        //         }
        //     },
        //     {
        //         className: 'service-max text-end',
        //         targets: 7,
        //         render: function (data, type, full, meta) {
                    
        //             return data ? data.toLocaleString('en-US') : '';
        //         }
        //     },
        //     {
        //         className: 'service-dripfeed text-center',
        //         searchable: true,
        //         targets: 8,
        //         render: function (data, type, full, meta) {
        //             if(data == 1)
        //                 return '<span class="badge bg-label-success">Yes</span>';
        //             else 
        //                 return '<span class="badge bg-label-warning">No</span>';
        //         },
        //     },
        //     {
        //         className: 'service-refill text-center',
        //         searchable: true,
        //         targets: 9,
        //         render: function (data, type, full, meta) {
        //             if(data == 1)
        //                 return '<span class="badge bg-label-success">Yes</span>';
        //             else 
        //                 return '<span class="badge bg-label-warning">No</span>'
        //         },
        //     },
        //     {
        //         className: 'service-cancel text-center',
        //         searchable: true,
        //         targets: 10,
        //         render: function (data, type, full, meta) {
        //             if(data == 1)
        //                 return '<span class="badge bg-label-success">Yes</span>';
        //             else 
        //                 return '<span class="badge bg-label-warning">No</span>'
        //         },
        //     }
        // ],
        // order: [[5, 'asc']],
        ordering: false,
        orderCellsTop: true,
        // paging: false,
        // lengthChange: false,
        displayLength: 1000,
        lengthMenu: [1000, 2500, 5000],
        dom: '<"row"<"col-sm-12 col-md-6"l>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
    });
    // hide category, type column as default
    dt_basic.column(1).visible(false);
    dt_basic.column(4).visible(false);
}