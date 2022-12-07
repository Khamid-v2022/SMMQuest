var dt_basic;
var all_show_table;

var previous_selected_providers = ["0"];
$(function () {
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
    $('.dt-column-search thead tr').clone(true).appendTo('.dt-column-search thead');
    $('.dt-column-search thead tr:eq(1) th').each(function (i) {
        var title = $(this).text();
        if(i == 0){
            let html = '<select class="form-select" id="search_provider">';
            html += '<option value="-1">All</option>';  
            html += '</select>';
            $(this).html(html);
        }
        else if(i == 5) {
            let html = '<select class="form-select" id="search_min">';
            html += '<option value="-1">All</option>';  
            html += '</select>';
            $(this).html(html);
        } else if(i == 6) {
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
            { data: 'service' },
            { data: 'name' },
            { data: 'category' },
            { data: 'rate'},
            { data: 'min'},
            { data: 'max'},
            { data: 'type'},
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
                        domain += '<i class="bx bxs-like text-warning" ></i>'
                    return domain;
                }
            },
            {
                className: 'service-id',
                targets: 1
            },
            {
                className: 'service-name',
                targets: 2
            },
            {
                className: 'service-category',
                targets: 3
            },
            {
                className: 'service-rate text-end',
                targets: 4,
                render: function(data){
                    return data.toLocaleString('en-US', {maximumFractionDigits:5})
                }
            },
            {
                className: 'service-min text-end',
                targets: 5
            },
            {
                className: 'service-max text-end',
                targets: 6
            },
            {
                className: 'service-type',
                targets: 7,
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
            },
            {
                className: 'service-favorite',
                targets: 11
            },
        ],
        order: [[4, 'asc']],
        orderCellsTop: true,
        paging: false,
        lengthChange: false,
        dom: '<"table-responsive"t><"row"<"col-sm-12 col-md-6"i>>',
    });
    // hide category, type column as default
    dt_basic.column(3).visible(false);
    dt_basic.column(7).visible(false);
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
            // remove all
            const index = selected_providers.indexOf("0");
            if (index > -1) { // only splice array when item is found
                selected_providers.splice(index, 1); // 2nd parameter means remove one item only
                $(this).val(selected_providers).trigger("change");
            }
        }

        if(!previous_selected_providers.includes("0") && selected_providers.includes("0") && selected_providers.length > 1){
            $(this).val(['0']).trigger("change");
        }
        previous_selected_providers = selected_providers;
    })

    $(".show-column-item").on("click", function(){
        blockDataTable();
        // Get the column API object
        var column = dt_basic.column($(this).attr('data-column-index'));
        // Toggle the visibility
        column.visible(!column.visible());
        $("#data_table").unblock();
    })

    $("#check_favorite").on("click", function(){
        blockDataTable();
        const is_favorite = $(this).prop('checked') ? 1 : "";
        if (dt_basic.column(11).search() !== is_favorite) {
            dt_basic.column(11).search(is_favorite).draw();
        }
        $("#data_table").unblock();
    })

    $("#search_form").on("submit", function(e){
        e.preventDefault();

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
        include_val.forEach((item) => {
            include.push(item.value)
        })

        exclude_val.forEach((item) => {
            exclude.push(item.value)
        })

        const data = {
            providers,
            type,
            include,
            exclude,
            min,
            max,
            min_rate: $("#min_rate").val(),
            max_rate: $("#max_rate").val()
        }

        const _url = "/search-services";


        $(".data-submit").attr("disabled", true);
        $(".data-submit .fa-spinner").css("display", "inline-block");
        
        dt_basic.clear().draw();
        blockDataTable();
        $.ajax({
            url: _url,
            type: "POST",
            data: data,
            success: function (response) {
                if (response.code == 200) {
                    const services = response.services;

                    let min_array = [];
                    let max_array = [];
                    let providers = [];

                    services.forEach((service) => {
                        dt_basic.row.add({
                            domain: service.domain,
                            service: service.service,
                            name: service.name,
                            category: service.category,
                            // rate: new Decimal(service.rate),
                            rate: service.rate,
                            min: service.min,
                            max: service.max,
                            type: service.type,
                            dripfeed: service.dripfeed,
                            refill: service.refill,
                            cancel: service.cancel,
                            is_favorite: service.is_favorite
                        });
                        if(!providers.includes(service.domain))
                            providers.push(service.domain);
                        if(!min_array.includes(service.min))
                            min_array.push(service.min);
                        if(!max_array.includes(service.max))
                            max_array.push(service.max);
                    })
                    
                    min_array.sort((a, b) => a - b);
                    max_array.sort((a, b) => a - b);

                    dt_basic.columns.adjust().draw();

                    let min_sel_html = '<option value="-1">All</option>';
                    let max_sel_html = '<option value="-1">All</option>';
                    let providers_html = '<option value="-1">All</option>';
                    
                    min_array.forEach((item) => {
                        min_sel_html += '<option value="' + item + '">' + item + '</option>';
                    })

                    max_array.forEach((item) => {
                        max_sel_html += '<option value="' + item + '">' + item + '</option>';
                    })

                    providers.forEach((item) => {
                        providers_html += '<option value="' + item + '">' + item + '</option>';
                    })

                    $("#search_min").html(min_sel_html);
                    $("#search_max").html(max_sel_html);
                    $("#search_provider").html(providers_html);


                    let collapseElement = document.getElementById("close_card");
                    new bootstrap.Collapse(collapseElement.closest('.card').querySelector('.collapse'));
                    // Toggle collapsed class in `.card-header` element
                    collapseElement.closest('.card-header').classList.toggle('collapsed');
                    // Toggle class bx-chevron-down & bx-chevron-up
                    Helpers._toggleClass(collapseElement.firstElementChild, 'bx-chevron-down', 'bx-chevron-up');

                    $('.datatables-basic').DataTable();
                    $(".data-submit .fa-spinner").css("display", "none");
                    $(".data-submit").removeAttr("disabled");
                    $("#data_table").unblock();
                } else {
                    dt_basic.columns.adjust().draw();
                    let sel_html = '<option value="-1">All</option>';
                    $("#search_min").html(sel_html);
                    $("#search_max").html(sel_html);
                    $("#search_provider").html(sel_html);

                    $(".data-submit .fa-spinner").css("display", "none");
                    $(".data-submit").removeAttr("disabled");
                    $("#data_table").unblock();
                    return;
                }
            },
            error: function (response) {
                console.log(response);
                dt_basic.columns.adjust().draw();
                let sel_html = '<option value="-1">All</option>';
                $("#search_min").html(sel_html);
                $("#search_max").html(sel_html);
                $("#search_provider").html(sel_html);

                $(".data-submit .fa-spinner").css("display", "none");
                $(".data-submit").removeAttr("disabled");
                return;
            },
        });
          
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