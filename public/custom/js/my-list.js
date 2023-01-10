$(function () {

    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    loadMyLists();


});

function loadMyLists(){
    const selected_currency = $("#selected-currency").attr("data-currency");
    const _url = "/my-list";

    $.ajax({
        url: _url,
        type: "POST",
        data: {
            currency: selected_currency
        },
        success: function (response) {
            if (response.code == 200) {
                const list = response.lists;
                drawingListTable(list);
                initializeButtons();
            }
        },
        error: function (response) {
        
        }
    });
}

function drawingListTable(list){
    let html = "";
    let index = 0;
    Object.entries(list).forEach(([key, val]) => {
        index++;
        html += '<div class="card accordion-item" data-list_id="' + key + '">';
            html += '<h5 class="accordion-header">';
                html += '<div class="accordion-title">';
                    html += '<span>' + val[0].list_name + ' - ' + val.length + ' / 100</span>';
                    html += '<div class="accordion-action">';
                        // html += '<button class="btn btn-sm btn-primary start-order-btn">Start test order</button>';
                        html += '<span class="created-date">' + val[0].created_at + '</span>';
                        html += '<a class="accordion-button ' + (index > 1 ? 'collapsed' : '') + '" type="button" data-bs-toggle="collapse" aria-expanded="' + (index == 1 ? 'true' : 'false') + '"  data-bs-target="#accordion-' + key + '" aria-controls="accordion-' + key + '"></a>';
                    html += "</div>";
                html += "</div>";
            html += "</h5>";
            html += '<div id="accordion-' + key + '" class="accordion-collapse collapse ' + (index == 1 ? 'show' : '')+ '">';
                html += '<div class="card-datatable table-responsive">';
                    html += '<table class="table dataTable border-top" style="font-size: .9rem;">';
                        html += '<thead><tr>';
                            html += '<th class="">Provider</th>';
                            html += '<th class="">ID</th>';
                            html += '<th class="">Name</th>';
                            html += '<th class="text-end">Price</th>';
                            html += '<th class="text-end">Min</th>';
                            html += '<th class="text-end">Max</th>';
                            html += '<th class=""><input type="checkbox" class="dt-checkboxes form-check-input check-all"></th>';
                        html += '</tr></thead>';
                        html += "<tbody>";
                            val.forEach((service) => {
                                html += '<tr data-list_service_id="' + service.list_service_id + '">';
                                    html += '<td>' + service.provider + '<i class="bx bx-check-circle text-success ms-1" style="display:inline" data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" title="' + $("#selected-currency").attr("data-currency") + " " + (service.user_balance ? service.user_balance : 0) + '"></i>' + '</td>';
                                    html += '<td>' + service.service + '</td>';
                                    html += '<td>' + service.name + '</td>';
                                    html += '<td class="text-end">' + service.rate + '</td>';
                                    html += '<td class="text-end">' + service.min + '</td>';
                                    html += '<td class="text-end">' + service.max + '</td>';
                                    html += '<td class="">';
                                        html += '<span class="btn-icon-custom"><input type="checkbox" class="dt-checkboxes form-check-input collapse-detail-box-btn"></span>';
                                        html += '<a href="javascript:;" class="btn btn-sm btn-icon btn-icon-custom delete-service-btn" title="Remove this service from this list" data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"><i class="bx bxs-trash"></i></a>';
                                        // html += '<a href="javascript:void(0);" class="btn-icon-custom card-collapsible collapse-detail-box-btn"><i class="tf-icons bx bxs-chevron-down"></i></a>';
                                    html += '</td>';
                                html += '</tr>';
                                html += '<tr class="collapse" data-list_service_id="' + service.list_service_id + '" data-template="' + service.api_template + '" data-service_id="' + service.service_id + '">';
                                    html += '<td colspan="7">';
                                        html += '<form class="order-details" data-list_service_id="' + service.list_service_id + '" data-service_id="' + service.service_id + '" data-template="' + service.api_template + '" data-balance="' + (service.user_balance ? service.user_balance : 0) + '" data-min="' + service.min + '" data-max="' + service.max + '" data-price="' + service.rate + '">';
                                            html += htmlByServiceType(service.api_template, service.type, service.rate, service.service.user_balance ? service.user_balance : 0);    
                                                // html += '<div class="col-sm-2">';
                                                //     html += '<label class="form-label service-cost-label">Cost:</label>';
                                                //     html += '<span type="text" class="badge bg-label-success service-cost-item">' + '0' + '</span>';
                                                // html += '</div>';
                                                // html += '<div class="col-sm-4">';
                                                //     html += '<label class="form-label">Quantity:</label>';
                                                //     html += '<input type="number" class="form-control form-control-sm quantity-input" placeholder="Quantify" value="">';
                                                // html += '</div>';
                                                // html += '<div class="col-sm-4">';
                                                //     html += '<label class="form-label">Link:</label>';
                                                //     html += '<input type="text" class="form-control form-control-sm link-input" placeholder="Link" value="">';
                                                // html += '</div>';
                                        html += '</form>';
                                    html += '</td>';
                                html += '</tr>';
                            })
                        html += "</tbody>";
                    html += ' </table>';
                html += '</div>';
            html += '</div>';
        html += '</div>';
    });

    $("#lists_wrraper").html(html);
    $("[data-bs-toggle='tooltip']").tooltip({
        html: true
    });
}

function initializeButtons(){
    // const collapseElementList = [].slice.call(document.querySelectorAll('.card-collapsible'));
    // collapseElementList.map(function (collapseElement) {
    //     collapseElement.addEventListener('click', event => {
    //         event.preventDefault();
    //         let data_list_service_id = collapseElement.closest('tr').getAttribute("data-list_service_id");
    //         // Collapse the element
    //         new bootstrap.Collapse(collapseElement.closest('tbody').querySelector('.collapse[data-list_service_id="' + data_list_service_id + '"]'));
    //         // Toggle collapsed class in `.card-header` element
    //         collapseElement.closest('tr').classList.toggle('collapsed');
    //         // Toggle class bx-chevron-down & bx-chevron-up
    //         Helpers._toggleClass(collapseElement.firstElementChild, 'bxs-chevron-down', 'bxs-chevron-up');
    //     });
    // })

    // check box for every services of list
    $(".collapse-detail-box-btn").on("click", function(){
        const data_list_service_id = $(this).parents('tr').attr("data-list_service_id");
        if($(this).prop("checked")){
            $('.collapse[data-list_service_id="' + data_list_service_id + '"]').addClass("show").removeClass("selected");
        } else {
            $('.collapse[data-list_service_id="' + data_list_service_id + '"]').removeClass("show").removeClass("selected");
        }
        checkServiceSelected();
    })

    // check all checkbox for every list
    $(".check-all").on('click', function(){
        const list_id = $(this).parents('.accordion-item').attr("data-list_id");
        if($(this).prop("checked")){
            $("#accordion-" + list_id + " tbody").find(".collapse-detail-box-btn").map(function() {
                $(this).prop("checked", true);
            })
            $("#accordion-" + list_id + " tbody .collapse").addClass("show").removeClass("selected");
            checkServiceSelected();
        } else {
            $("#accordion-" + list_id + " tbody").find(".collapse-detail-box-btn").map(function() {
                $(this).prop("checked", false);

            })
            $("#accordion-" + list_id + " tbody .collapse").removeClass("show").removeClass("selected");
            checkServiceSelected();
        }
    })

    // Quentity input box
    $(".quantity-input").on("change", function(){
        const val = parseInt($(this).val());   
        if(!val){
            $(this).parents("tr").addClass("selected");
            $(this).parents("form.order-details").find(".service-cost-item").html('0');
            $(this).parents("form.order-details").find("input[type='hidden'].quantity-status").val("0");
        } else {
            const user_balance = parseFloat($(this).parents("form.order-details").attr("data-balance"));
            let cost = (val * parseFloat($(this).parents("form.order-details").attr("data-price"))).toFixed(6);
            if(!cost)
                cost = 0;
            $(this).parents("form.order-details").find(".service-cost-item").html(cost);
            
            if(cost > user_balance){
                $(this).parents("form.order-details").find(".service-cost-item").removeClass("bg-label-success").addClass("bg-label-danger");
                $(this).parents("form.order-details").find("input[type='hidden'].quantity-status").val("0");
                $(this).parents("tr").addClass("selected");
            } else {
                $(this).parents("form.order-details").find(".service-cost-item").removeClass("bg-label-danger").addClass("bg-label-success");
                $(this).parents("form.order-details").find("input[type='hidden'].quantity-status").val("1");
                $(this).parents("tr").removeClass("selected");
            }
        }

        calculateSelectedServices();

        let input_this = this;
        if(val < parseInt($(this).parents("form").attr("data-min")) || val > parseInt($(this).parents("form").attr("data-max"))){
            Swal.fire({
                icon: 'warning',
                title: '',
                text: 'Quantity cannot be lower or over the Min and the Max of the service',
                customClass: {
                  confirmButton: 'btn btn-primary'
                },
                buttonsStyling: false
            }).then(function(){
                $(input_this).parents("form.order-details").find("input[type='hidden'].quantity-status").val("0");
                $(input_this).parents("tr").addClass("selected");
                calculateSelectedServices();
                $(input_this).focus();
            })
        }
    })

    // Link input box
    $(".link-input, .comments-input, .username-input, .usernames-input, .hashtag-input, .hashtags-input, .media-input, .answer-input, .groups-input, .min-input, .max-input, .delay-input").on("change", function(){
        const val = $(this).val();   
        if(!val){
            $(this).parents("tr").addClass("selected");
        } else {
            $(this).parents("tr").removeClass("selected");
        }
    })

   

    // $(".start-order-btn").on("click", function(e){
    //     let check_flag = true;
    //     const list_id = $(this).parents('.accordion-item').attr("data-list_id");
    //     $(this).parents('.accordion-item').find("form.order-details").each(function(){
    //         $(this).find('input').map(function(){
    //             if(!$(this).val())
    //                 check_flag = false;
    //         });
    //     })

    //     if(!check_flag){
    //         Swal.fire({
    //             icon: 'warning',
    //             text: 'Please enter values in the fields for all services in this list',
    //             customClass: {
    //               confirmButton: 'btn btn-primary'
    //             },
    //             buttonsStyling: false
    //         })
    //         return;
    //     }

    //     // start order
    //     let data = [];
    //     $(this).parents('.accordion-item').find("form.order-details").each(function(){
    //         const list_service_id = $(this).attr("data-list_service_id");
    //         const service_id = $(this).attr("data-service_id");
    //         const quantity = $(this).find("input.quantity-input").val();
    //         const link = $(this).find("input.link-input").val();
    //         data.push({list_service_id, service_id, quantity, link});
    //     })

    //     const _url = '/my-list/start_order';
    //     $.ajax({
    //         url: _url,
    //         data: {
    //             list_id: list_id,
    //             orders: data
    //         },
    //         type: "POST",
    //         success: function (response) {
    //             if (response.code == 200) {
    //                 Swal.fire({
    //                     icon: 'success',
    //                     title: '',
    //                     text: "Started this order!",
    //                     customClass: {
    //                         confirmButton: 'btn btn-primary'
    //                     },
    //                     buttonsStyling: false
    //                 }).then(function(){
    //                     // delete this list
    //                     // $(".accordion-item[data-list_id='" + list_id + "']").remove();
    //                 });
    //             }
    //         },
    //         error: function (response) {
    //             Swal.fire({
    //                 icon: 'error',
    //                 title: 'Error!',
    //                 text: 'Something went wrong. Please try again later!',
    //                 customClass: {
    //                   confirmButton: 'btn btn-primary'
    //                 },
    //                 buttonsStyling: false
    //             })
    //             return;
    //         },
    //     });
    // })

    $(".delete-service-btn").on("click", function(){

        const del_id = $(this).parents('tr').attr("data-list_service_id");

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            customClass: {
              confirmButton: 'btn btn-primary me-3',
              cancelButton: 'btn btn-label-secondary'
            },
            buttonsStyling: false
        }).then(function (result) {
            if (result.value) {
                const _url = '/my-list/delete_service_from_list/' + del_id;
                $.ajax({
                    url: _url,
                    type: "DELETE",
                    success: function (response) {
                        if (response.code == 200) {
                            // delete from table
                            $("table tr[data-list_service_id='" + del_id + "']").remove();
                        }
                    },
                    error: function (response) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Something went wrong. Please try again later!',
                            customClass: {
                              confirmButton: 'btn btn-primary'
                            },
                            buttonsStyling: false
                        })
                        return;
                    },
                });
            }
        });
    })


    // submit form inputs: 
    // quentity:    .quantity-input
    // link:        .link-input
    // comments:    .comments-input
    // usernames:   .usernames-input
    // username:    .username-input
    // hashtags:    .hashtags-input
    // hashtag:     .hashtag-input
    // media:       .media-input
    // answer_number .answer-input
    // groups:      .groups-input
    // min:         .min-input
    // max:         .max-input
    // delay:       .delay-input

    $("#start_test_order").on("click", function(){
        calculateSelectedServices();
        let flag = 1;

        // check quentity-input status
        $(".card-datatable tr.collapse.show input[type='hidden'].quantity-status").map(function(){
            if($(this).val() == 0){
                $(this).parents("tr").addClass("selected");
                flag = 0;
            } 
            // else {
            //     $(this).parents("tr").removeClass("selected");
            // }
        })

        // check other input fields is empty
        // $(".card-datatable tr.collapse.show input.link-input, .card-datatable tr.collapse.show textarea").map(
        $(".card-datatable tr.collapse.show input, .card-datatable tr.collapse.show textarea").map(
            function(){
                if(!$(this).val()){
                    $(this).parents("tr").addClass("selected");
                    flag = 0;
                }
            }
        );

        if(!flag){
            Swal.fire({
                icon: 'warning',
                title: '',
                text: 'Make sure that the selected service input fields are filled in correctly.',
                customClass: {
                  confirmButton: 'btn btn-primary'
                },
                buttonsStyling: false
            });
            return;
        }

        console.log("Success");
        
        let params = [];
        // submit order
        $(".card-datatable tr.collapse.show").map(function(){
            let service_id = $(this).attr("data-service_id");
            let quentity =  $(this).find(".quantity-input").length > 0 ? $(this).find(".quantity-input").val() : null;
            let link =      $(this).find(".link-input").length > 0 ? $(this).find(".link-input").val() : null;     
            let comments =  $(this).find(".comments-input").length > 0 ? $(this).find(".comments-input").val() : null;  
            let usernames = $(this).find(".usernames-input").length > 0 ? $(this).find(".usernames-input").val() : null;  
            let username =  $(this).find(".username-input").length > 0 ? $(this).find(".username-input").val() : null; 
            let hashtags =  $(this).find(".hashtags-input").length > 0 ? $(this).find(".hashtags-input").val() : null; 
            let hashtag =   $(this).find(".hashtag-input").length > 0 ? $(this).find(".hashtag-input").val() : null;  
            let media =     $(this).find(".media-input").length > 0 ? $(this).find(".media-input").val() : null; 
            let answer_number = $(this).find(".answer-input").length > 0 ? $(this).find(".answer-input").val() : null;
            let groups =    $(this).find(".groups-input").length > 0 ? $(this).find(".groups-input").val() : null;  
            let min =       $(this).find(".min-input").length > 0 ? $(this).find(".min-input").val() : null;  
            let max =       $(this).find(".max-input").length > 0 ? $(this).find(".max-input").val() : null;  
            let delay =     $(this).find(".delay-input").length > 0 ? $(this).find(".delay-input").val() : null;  
            params.push({
                service_id,
                quentity,
                link,
                comments,
                usernames,
                username,
                hashtags,
                hashtag,
                media,
                answer_number,
                groups,
                min,
                max,
                delay
            })
        })

        console.log(params);
        
    })

    
}

function checkServiceSelected() {
    if($(".card-datatable tr.collapse.show").length > 0){
        calculateSelectedServices();
        $(".sticky-wrapper").css("display", "block");
        return true;
    }

    $("#expected_cost").html(0);
    $("#selected_count").html(0);

    $(".sticky-wrapper").css("display", "none");
    return false;
}

function calculateSelectedServices(){
    let total_cost = 0;

    $(".card-datatable tr.collapse.show").find(".service-cost-item").map(function() {
        total_cost += parseFloat($(this).html());
    });

    $("#expected_cost").html(total_cost.toFixed(6));
    $("#selected_count").html($(".card-datatable tr.collapse.show").length);
}


// Important- Need to be Update when add New Panel
function htmlByServiceType(panel, service_type, price, user_balance){
    let html = "";
    if(panel == 'PerfectPanel'){
        switch(service_type){
            case 'Default':
                html += '<div class="row" class="form-content-default">';
                    html += '<div class="col-sm-2">';
                        html += '<label class="form-label service-cost-label">Cost:</label>';
                        html += '<span type="text" class="badge bg-label-success service-cost-item">' + '0' + '</span>';
                    html += '</div>';
                    html += '<div class="col-sm-4">';
                        html += '<label class="form-label">Quantity:</label>';
                        html += '<input type="number" class="form-control form-control-sm quantity-input" placeholder="Quantify" value="">';
                    html += '</div>';
                    html += '<div class="col-sm-4">';
                        html += '<label class="form-label">Link:</label>';
                        html += '<input type="text" class="form-control form-control-sm link-input" placeholder="Link" value="">';
                    html += '</div>';
                    html += '<input type="hidden" class="quantity-status" value="0">';
                html += '</div>';
                return html;
            case 'Package':
                html += '<div class="row" class="form-content-package">';
                    html += '<div class="col-sm-2">';
                        html += '<label class="form-label service-cost-label">Cost:</label>';
                        html += '<span type="text" class="badge ' + (parseFloat(price) > parseFloat(user_balance) ? 'bg-label-danger' : 'bg-label-success') + ' service-cost-item">' + price + '</span>';
                    html += '</div>';
                    html += '<div class="col-sm-4">';
                        html += '<label class="form-label">Link:</label>';
                        html += '<input type="text" class="form-control form-control-sm link-input" placeholder="Link" value="">';
                    html += '</div>';
                    html += '<input type="hidden" class="quantity-status" value="1">';
                html += '</div>';
                return html;
            case 'Custom Comments':
                html += '<div class="row" class="form-content-custom-comments">';
                    html += '<div class="col-sm-2">';
                        html += '<label class="form-label service-cost-label">Cost:</label>';
                        html += '<span type="text" class="badge ' + (parseFloat(price) > parseFloat(user_balance) ? 'bg-label-danger' : 'bg-label-success') + ' service-cost-item">' + price + '</span>';
                    html += '</div>';
                    html += '<div class="col-sm-4">';
                        html += '<label class="form-label">Link:</label>';
                        html += '<input type="text" class="form-control form-control-sm link-input" placeholder="Link" value="">';
                    html += '</div>';
                    html += '<div class="col-sm-4">';
                        html += '<label class="form-label">Comments <span class="badge rounded-pill bg-label-primary" title="Comments list separated by \\r\\n or \\n" data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top">?</span></label>';
                        html += '<textarea type="text" class="form-control form-control-sm comments-input" placeholder="Comments list separated by \\r\\n or \\n"></textarea>';
                    html += '</div>';
                    html += '<input type="hidden" class="quantity-status" value="1">';
                html += '</div>';
                return html;
            case 'Mentions':
                html += '<div class="row" class="form-content-default">';
                    html += '<div class="col-sm-2">';
                        html += '<label class="form-label service-cost-label">Cost:</label>';
                        html += '<span type="text" class="badge bg-label-success service-cost-item">' + '0' + '</span>';
                    html += '</div>';
                    html += '<div class="col-sm-2">';
                        html += '<label class="form-label">Quantity:</label>';
                        html += '<input type="number" class="form-control form-control-sm quantity-input" placeholder="Quantify" value="">';
                    html += '</div>';
                    html += '<div class="col-sm-4">';
                        html += '<label class="form-label">Link:</label>';
                        html += '<input type="text" class="form-control form-control-sm link-input" placeholder="Link" value="">';
                    html += '</div>';
                    html += '<div class="col-sm-4">';
                        html += '<label class="form-label">User Names <span class="badge rounded-pill bg-label-primary" title="Usernames list separated by \\r\\n or \\n" data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top">?</span></label>';
                        html += '<textarea type="text" class="form-control form-control-sm usernames-input" placeholder="Usernames list separated by \\r\\n or \\n"></textarea>';
                    html += '</div>';
                    html += '<input type="hidden" class="quantity-status" value="0">';
                html += '</div>';
                return html;
            case 'Mentions with Hashtags':
                html += '<div class="row" class="form-content-default">';
                    html += '<div class="col-sm-2">';
                        html += '<label class="form-label service-cost-label">Cost:</label>';
                        html += '<span type="text" class="badge bg-label-success service-cost-item">' + '0' + '</span>';
                    html += '</div>';
                    html += '<div class="col-sm-2">';
                        html += '<label class="form-label">Quantity:</label>';
                        html += '<input type="number" class="form-control form-control-sm quantity-input" placeholder="Quantify" value="">';
                    html += '</div>';
                    html += '<div class="col-sm-3">';
                        html += '<label class="form-label">Link:</label>';
                        html += '<input type="text" class="form-control form-control-sm link-input" placeholder="Link" value="">';
                    html += '</div>';
                    html += '<div class="col-sm-2">';
                        html += '<label class="form-label">User Names <span class="badge rounded-pill bg-label-primary" title="Usernames list separated by \\r\\n or \\n" data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top">?</span></label>';
                        html += '<textarea type="text" class="form-control form-control-sm usernames-input" placeholder="Usernames list separated by \\r\\n or \\n"></textarea>';
                    html += '</div>';
                    html += '<div class="col-sm-3">';
                        html += '<label class="form-label">Hashtags <span class="badge rounded-pill bg-label-primary" title="Hashtags list separated by \\r\\n or \\n" data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top">?</span></label>';
                        html += '<textarea type="text" class="form-control form-control-sm hashtags-input" placeholder="Hashtags list separated by \\r\\n or \\n"></textarea>';
                    html += '</div>';
                    html += '<input type="hidden" class="quantity-status" value="0">';
                html += '</div>';
                return html;
            case 'Mentions Custom List':
                html += '<div class="row" class="form-content-default">';
                    html += '<div class="col-sm-2">';
                        html += '<label class="form-label service-cost-label">Cost:</label>';
                        html += '<span type="text" class="badge ' + (parseFloat(price) > parseFloat(user_balance) ? 'bg-label-danger' : 'bg-label-success') + ' service-cost-item">' + price + '</span>';
                    html += '</div>';
                    html += '<div class="col-sm-4">';
                        html += '<label class="form-label">Link:</label>';
                        html += '<input type="text" class="form-control form-control-sm link-input" placeholder="Link" value="">';
                    html += '</div>';
                    html += '<div class="col-sm-4">';
                        html += '<label class="form-label">User Names <span class="badge rounded-pill bg-label-primary" title="Usernames list separated by \\r\\n or \\n" data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top">?</span></label>';
                        html += '<textarea type="text" class="form-control form-control-sm usernames-input" placeholder="Usernames list separated by \\r\\n or \\n"></textarea>';
                    html += '</div>';
                    html += '<input type="hidden" class="quantity-status" value="1">';
                html += '</div>';
                return html;
            case 'Mentions Hashtag':
                html += '<div class="row" class="form-content-default">';
                    html += '<div class="col-sm-2">';
                        html += '<label class="form-label service-cost-label">Cost:</label>';
                        html += '<span type="text" class="badge bg-label-success service-cost-item">' + '0' + '</span>';
                    html += '</div>';
                    html += '<div class="col-sm-2">';
                        html += '<label class="form-label">Quantity:</label>';
                        html += '<input type="number" class="form-control form-control-sm quantity-input" placeholder="Quantify" value="">';
                    html += '</div>';
                    html += '<div class="col-sm-4">';
                        html += '<label class="form-label">Link:</label>';
                        html += '<input type="text" class="form-control form-control-sm link-input" placeholder="Link" value="">';
                    html += '</div>';
                    html += '<div class="col-sm-4">';
                        html += '<label class="form-label">	Hashtag:</label>';
                        html += '<input type="text" class="form-control form-control-sm hashtag-input" placeholder="Hashtag to scrape usernames from">';
                    html += '</div>';
                    html += '<input type="hidden" class="quantity-status" value="0">';
                html += '</div>';
                return html;
            case 'Mentions User Followers':
                html += '<div class="row" class="form-content-default">';
                    html += '<div class="col-sm-2">';
                        html += '<label class="form-label service-cost-label">Cost:</label>';
                        html += '<span type="text" class="badge bg-label-success service-cost-item">' + '0' + '</span>';
                    html += '</div>';
                    html += '<div class="col-sm-2">';
                        html += '<label class="form-label">Quantity:</label>';
                        html += '<input type="number" class="form-control form-control-sm quantity-input" placeholder="Quantify" value="">';
                    html += '</div>';
                    html += '<div class="col-sm-4">';
                        html += '<label class="form-label">Link:</label>';
                        html += '<input type="text" class="form-control form-control-sm link-input" placeholder="Link" value="">';
                    html += '</div>';
                    html += '<div class="col-sm-4">';
                        html += '<label class="form-label">User Name:</label>';
                        html += '<input type="text" class="form-control form-control-sm username-input" placeholder="URL to scrape followers from">';
                    html += '</div>';
                    html += '<input type="hidden" class="quantity-status" value="0">';
                html += '</div>';
                return html;
            case 'Mentions Media Likers':
                html += '<div class="row" class="form-content-default">';
                    html += '<div class="col-sm-2">';
                        html += '<label class="form-label service-cost-label">Cost:</label>';
                        html += '<span type="text" class="badge bg-label-success service-cost-item">' + '0' + '</span>';
                    html += '</div>';
                    html += '<div class="col-sm-2">';
                        html += '<label class="form-label">Quantity:</label>';
                        html += '<input type="number" class="form-control form-control-sm quantity-input" placeholder="Quantify" value="">';
                    html += '</div>';
                    html += '<div class="col-sm-4">';
                        html += '<label class="form-label">Link:</label>';
                        html += '<input type="text" class="form-control form-control-sm link-input" placeholder="Link" value="">';
                    html += '</div>';
                    html += '<div class="col-sm-4">';
                        html += '<label class="form-label">Media:</label>';
                        html += '<input type="text" class="form-control form-control-sm media-input" placeholder="Media URL to scrape likers from">';
                    html += '</div>';
                    html += '<input type="hidden" class="quantity-status" value="0">';
                html += '</div>';
                return html;
            case 'Custom Comments Package':
                html += '<div class="row" class="form-content-custom-comments">';
                    html += '<div class="col-sm-2">';
                        html += '<label class="form-label service-cost-label">Cost:</label>';
                        html += '<span type="text" class="badge ' + (parseFloat(price) > parseFloat(user_balance) ? 'bg-label-danger' : 'bg-label-success') + ' service-cost-item">' + price + '</span>';
                    html += '</div>';
                    html += '<div class="col-sm-4">';
                        html += '<label class="form-label">Link:</label>';
                        html += '<input type="text" class="form-control form-control-sm link-input" placeholder="Link" value="">';
                    html += '</div>';
                    html += '<div class="col-sm-4">';
                        html += '<label class="form-label">Comments <span class="badge rounded-pill bg-label-primary" title="Comments list separated by \\r\\n or \\n" data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top">?</span></label>';
                        html += '<textarea type="text" class="form-control form-control-sm comments-input" placeholder="Comments list separated by \\r\\n or \\n"></textarea>';
                    html += '</div>';
                    html += '<input type="hidden" class="quantity-status" value="1">';
                html += '</div>';
                return html;
            case 'Comments Likes':
                html += '<div class="row" class="form-content-default">';
                    html += '<div class="col-sm-2">';
                        html += '<label class="form-label service-cost-label">Cost:</label>';
                        html += '<span type="text" class="badge bg-label-success service-cost-item">' + '0' + '</span>';
                    html += '</div>';
                    html += '<div class="col-sm-2">';
                        html += '<label class="form-label">Quantity:</label>';
                        html += '<input type="number" class="form-control form-control-sm quantity-input" placeholder="Quantify" value="">';
                    html += '</div>';
                    html += '<div class="col-sm-4">';
                        html += '<label class="form-label">Link:</label>';
                        html += '<input type="text" class="form-control form-control-sm link-input" placeholder="Link" value="">';
                    html += '</div>';
                    html += '<div class="col-sm-4">';
                        html += '<label class="form-label">User Name:</label>';
                        html += '<input type="text" class="form-control form-control-sm username-input" placeholder="Username of the comment owner">';
                    html += '</div>';
                    html += '<input type="hidden" class="quantity-status" value="0">';
                html += '</div>';
                return html;
            case 'Poll':
                html += '<div class="row" class="form-content-default">';
                    html += '<div class="col-sm-2">';
                        html += '<label class="form-label service-cost-label">Cost:</label>';
                        html += '<span type="text" class="badge bg-label-success service-cost-item">' + '0' + '</span>';
                    html += '</div>';
                    html += '<div class="col-sm-2">';
                        html += '<label class="form-label">Quantity:</label>';
                        html += '<input type="number" class="form-control form-control-sm quantity-input" placeholder="Quantify" value="">';
                    html += '</div>';
                    html += '<div class="col-sm-4">';
                        html += '<label class="form-label">Link:</label>';
                        html += '<input type="text" class="form-control form-control-sm link-input" placeholder="Link" value="">';
                    html += '</div>';
                    html += '<div class="col-sm-4">';
                        html += '<label class="form-label">Answer Number:</label>';
                        html += '<input type="number" class="form-control form-control-sm answer-input" placeholder="Answer number of the poll">';
                    html += '</div>';
                    html += '<input type="hidden" class="quantity-status" value="0">';
                html += '</div>';
                return html;
            case 'Comment Replies':
                html += '<div class="row" class="form-content-custom-comments">';
                    html += '<div class="col-sm-2">';
                        html += '<label class="form-label service-cost-label">Cost:</label>';
                        html += '<span type="text" class="badge ' + (parseFloat(price) > parseFloat(user_balance) ? 'bg-label-danger' : 'bg-label-success') + ' service-cost-item">' + price + '</span>';
                    html += '</div>';
                    html += '<div class="col-sm-4">';
                        html += '<label class="form-label">Link:</label>';
                        html += '<input type="text" class="form-control form-control-sm link-input" placeholder="Link" value="">';
                    html += '</div>';
                    html += '<div class="col-sm-2">';
                        html += '<label class="form-label">User Name:</label>';
                        html += '<input type="text" class="form-control form-control-sm username-input" placeholder="User Name">';
                    html += '</div>';
                    html += '<div class="col-sm-4">';
                        html += '<label class="form-label">Comments <span class="badge rounded-pill bg-label-primary" title="Comments list separated by \\r\\n or \\n" data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top">?</span></label>';
                        html += '<textarea type="text" class="form-control form-control-sm comments-input" placeholder="Comments list separated by \\r\\n or \\n"></textarea>';
                    html += '</div>';
                    html += '<input type="hidden" class="quantity-status" value="1">';
                html += '</div>';
                return html;
            case 'Invites from Groups':
                html += '<div class="row" class="form-content-default">';
                    html += '<div class="col-sm-2">';
                        html += '<label class="form-label service-cost-label">Cost:</label>';
                        html += '<span type="text" class="badge bg-label-success service-cost-item">' + '0' + '</span>';
                    html += '</div>';
                    html += '<div class="col-sm-2">';
                        html += '<label class="form-label">Quantity:</label>';
                        html += '<input type="number" class="form-control form-control-sm quantity-input" placeholder="Quantify" value="">';
                    html += '</div>';
                    html += '<div class="col-sm-4">';
                        html += '<label class="form-label">Link:</label>';
                        html += '<input type="text" class="form-control form-control-sm link-input" placeholder="Link" value="">';
                    html += '</div>';
                    html += '<div class="col-sm-4">';
                        html += '<label class="form-label">Groups <span class="badge rounded-pill bg-label-primary" title="Groups list separated by \\r\\n or \\n" data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top">?</span></label>';
                        html += '<textarea type="text" class="form-control form-control-sm groups-input" placeholder="Groups list separated by \\r\\n or \\n"></textarea>';
                    html += '</div>';
                    html += '<input type="hidden" class="quantity-status" value="0">';
                html += '</div>';
                return html;
            case 'Subscriptions':
                html += '<div class="row" class="form-content-package">';
                    html += '<div class="col-sm-2">';
                        html += '<label class="form-label service-cost-label">Cost:</label>';
                        html += '<span type="text" class="badge ' + (parseFloat(price) > parseFloat(user_balance) ? 'bg-label-danger' : 'bg-label-success') + ' service-cost-item">' + price + '</span>';
                    html += '</div>';
                    html += '<div class="col-sm-4">';
                        html += '<label class="form-label">User Name:</label>';
                        html += '<input type="text" class="form-control form-control-sm username-input" placeholder="User Name" value="">';
                    html += '</div>';
                    html += '<div class="col-sm-2">';
                        html += '<label class="form-label">Min:</label>';
                        html += '<input type="number" class="form-control form-control-sm min-input" placeholder="Quantity Min" value="">';
                    html += '</div>';
                    html += '<div class="col-sm-2">';
                        html += '<label class="form-label">Max:</label>';
                        html += '<input type="number" class="form-control form-control-sm max-input" placeholder="Quantity Max" value="">';
                    html += '</div>';
                    html += '<div class="col-sm-2">';
                        html += '<label class="form-label">Delay <span class="badge rounded-pill bg-label-primary" title="Delay in minutes. Possible values: 0, 5, 10, 15, 30, 60, 90, 120, 150, 180, 210, 240, 270, 300, 360, 420, 480, 540, 600" data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top">?</span></label>';
                        html += '<input type="number" class="form-control form-control-sm delay-input" placeholder="Delay in minutes.">';
                    html += '</div>';
                    html += '<input type="hidden" class="quantity-status" value="1">';
                html += '</div>';
                return html;
        }
    }

    return "";
}