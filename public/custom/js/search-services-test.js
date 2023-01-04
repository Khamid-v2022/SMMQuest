var dt_basic = null;
var all_show_table;
var service_type_html = '<option value="-1">All</option>';

var send_data = null;

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

        if(selected_providers.length == 0){
            $(this).val(['0']).trigger("change");
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
    $(".datatables-basic th select").val(-1).trigger('change');
    $(".datatables-basic th input").val("").trigger('change');
}

function loadUsingAjax(){

    if(!send_data)
        return;

    const _url = "/search-services-test";

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
                
                $("#search_min").html(response.min_opt_html);
                $("#search_max").html(response.max_opt_html);
                $("#search_provider").html(response.provider_opt_html);
                $("#search_type").html(response.type_opt_html);
                resetSearchFilterOfDataTable();
                
                drawTable(response.tbody);

                
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
                $("#search_type").html(sel_html);

                resetSearchFilterOfDataTable();
                drawTable("");
                
                $(".data-submit .fa-spinner").css("display", "none");
                $(".data-submit").removeAttr("disabled");
                $("#data_table").unblock();
                return;
            }
        },
        error: function (response) {
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
            $("#search_type").html(sel_html);

            resetSearchFilterOfDataTable();
            drawTable("");

            $(".data-submit .fa-spinner").css("display", "none");
            $(".data-submit").removeAttr("disabled");
            $("#data_table").unblock();
            return;
        },
    });
}

function drawTable(html){
    if(dt_basic)
        dt_basic.destroy();

    $("#tbl-body").html(html);
    dt_basic = $('.datatables-basic').DataTable({
        columnDefs: [
            {
                className: 'text-end',
                targets: 5,
            },
            {
                className: 'text-end',
                targets: 6,
            },
            {
                className: 'text-end',
                targets: 7,
            },
        ],
        order: [[5, 'asc']],
        // ordering: false,
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
    dt_basic.column(11).visible(false);
}