var dt_basic;

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

    $('.selectpicker').selectpicker();
    const includeEl = document.querySelector('#include');
    const TagifyInclude = new Tagify(includeEl);
    const includeEx = document.querySelector('#exclude');
    const TagifyExclude = new Tagify(includeEx);
   
    // DataTable with buttons
    // --------------------------------------------------------------------

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
                searchable: false,
                targets: 0
            },
            {
                className: 'service-id',
                searchable: false,
                targets: 1
            },
            {
                className: 'service-name',
                searchable: true,
                targets: 2
            },
            {
                className: 'service-category',
                searchable: false,
                targets: 3
            },
            {
                className: 'service-rate',
                searchable: false,
                targets: 4,
                render: function(data){
                    return data.toLocaleString('en-US', {maximumFractionDigits:2})
                }
            },
            {
                className: 'service-min',
                searchable: false,
                targets: 5,
                render: function(data){
                    return data.toLocaleString('en-US', {maximumFractionDigits:2})
                }
            },
            {
                className: 'service-max',
                searchable: false,
                targets: 6,
                render: function(data){
                    return data.toLocaleString('en-US', {maximumFractionDigits:2})
                }
            },
            {
                className: 'service-type',
                searchable: false,
                targets: 7,
            },
            {
                className: 'service-dripfeed',
                searchable: false,
                targets: 8,
                render: function (data, type, full, meta) {
                    if(data == 1)
                        return '<span class="badge bg-label-success">True</span>';
                    else 
                        return '<span class="badge bg-label-warning">False</span>'
                },
            },
            {
                className: 'service-refill',
                searchable: false,
                targets: 9,
                render: function (data, type, full, meta) {
                    if(data == 1)
                        return '<span class="badge bg-label-success">True</span>';
                    else 
                        return '<span class="badge bg-label-warning">False</span>'
                },
            },
            {
                className: 'service-cancel',
                searchable: false,
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
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        displayLength: 10,
        lengthMenu: [10, 25, 50, 100],
        buttons: []
    });
  
    // Filter form control to default size
    // ? setTimeout used for multilingual table initialization
    setTimeout(() => {
        $('.dataTables_filter .form-control').removeClass('form-control-sm');
        $('.dataTables_length .form-select').removeClass('form-select-sm');
    }, 300);

    $("#providers").on('change', function(){
        const selected_providers = $(this).val();
        if(selected_providers.includes("0") && selected_providers.length > 1){
            $(this).val(['0']).trigger("change");
        }
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
            max
        }

        const _url = "/search-services";
        
        // console.log(data);
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
                    let tbody = "";
                    let number = 0;
                    services.forEach((service) => {
                        number ++;
                        dt_basic.row.add({
                            domain: service.domain,
                            service: service.service,
                            name: service.name,
                            category: service.category,
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

                    $('.datatables-basic').DataTable();
                    $(".data-submit .fa-spinner").css("display", "none");
                    $(".data-submit").removeAttr("disabled");
                } else {
                    console.log(response);
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
