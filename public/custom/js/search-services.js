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
        if(i == 8 || i == 9 || i == 10 ){
            let html = '<select class="form-select">';
            html += '<option value="-1">All</option>';
            html += '<option value="TRUE">TRUE</option>';
            html += '<option value="FALSE">FALSE</option>';   
            html += '</select>';
            $(this).html(html);
        }
        else {
            $(this).html('<input type="text" class="form-control" placeholder="Search ' + title + '" />');
        }

        // $('input', this).on('keyup change', function () {
        $('input', this).on('change', function () {
            if (dt_basic.column(i).search() !== this.value) {
                dt_basic.column(i).search(this.value).draw();
            }
        });

        $('select', this).on('change', function () {
            if(this.value == -1){
                dt_basic.column(i).search("").draw();
            } else {
                if (dt_basic.column(i).search() !== this.value) {
                    dt_basic.column(i).search(this.value).draw();
                }
            }
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
            { data: 'cancel'}
        ],
        columnDefs: [
            {
                className: 'service-domain',
                targets: 0
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
                        return '<span class="badge bg-label-success">True</span>';
                    else 
                        return '<span class="badge bg-label-warning">False</span>';
                },
            },
            {
                className: 'service-refill text-center',
                searchable: true,
                targets: 9,
                render: function (data, type, full, meta) {
                    if(data == 1)
                        return '<span class="badge bg-label-success">True</span>';
                    else 
                        return '<span class="badge bg-label-warning">False</span>'
                },
            },
            {
                className: 'service-cancel text-center',
                searchable: true,
                targets: 10,
                render: function (data, type, full, meta) {
                    if(data == 1)
                        return '<span class="badge bg-label-success">True</span>';
                    else 
                        return '<span class="badge bg-label-warning">False</span>'
                },
            },

        ],
        // order: [[2, 'desc']],
        orderCellsTop: true,
        paging: false,
        lengthChange: false,
        // dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>><"table-responsive"t><"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
    });
    // hide category, type column as default
    dt_basic.column(3).visible(false);
    dt_basic.column(7).visible(false);
  
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
        // Get the column API object
        var column = dt_basic.column($(this).attr('data-column-index'));
        // Toggle the visibility
        column.visible(!column.visible());
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
        $.ajax({
            url: _url,
            type: "POST",
            data: data,
            success: function (response) {
                if (response.code == 200) {
                    const services = response.services;

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
                        });
                    })
                    
                    dt_basic.columns.adjust().draw();

                    let collapseElement = document.getElementById("close_card");
                    new bootstrap.Collapse(collapseElement.closest('.card').querySelector('.collapse'));
                    // Toggle collapsed class in `.card-header` element
                    collapseElement.closest('.card-header').classList.toggle('collapsed');
                    // Toggle class bx-chevron-down & bx-chevron-up
                    Helpers._toggleClass(collapseElement.firstElementChild, 'bx-chevron-down', 'bx-chevron-up');

                    $('.datatables-basic').DataTable();
                    $(".data-submit .fa-spinner").css("display", "none");
                    $(".data-submit").removeAttr("disabled");
                } else {
                    dt_basic.columns.adjust().draw();
                    $(".data-submit .fa-spinner").css("display", "none");
                    $(".data-submit").removeAttr("disabled");
                    return;
                }
            },
            error: function (response) {
                console.log(response);
                dt_basic.columns.adjust().draw();
               
                $(".data-submit .fa-spinner").css("display", "none");
                $(".data-submit").removeAttr("disabled");
                return;
            },
        });
          
    })
    
});