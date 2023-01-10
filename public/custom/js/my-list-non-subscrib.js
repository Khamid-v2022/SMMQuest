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
    Object.entries(list).forEach(([key, val]) => {
        html += '<div class="card accordion-item">';
            html += '<h5 class="accordion-header">';
                html += '<div class="accordion-title">';
                    html += '<span>' + val[0].list_name + '</span>';
                    html += '<div class="accordion-action">';
                        html += '<button class="btn btn-sm btn-primary redirect-to-payment" title="Enable for subscribers only" data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" >Start test order</button>';
                        html += '<span class="created-date">' + val[0].created_at + '</span>';
                        html += '<a class="accordion-button collapsed" type="button" data-bs-toggle="collapse" aria-expanded="false"  data-bs-target="#accordion-' + key + '" aria-controls="accordion-' + key + '"></a>';
                    html += "</div>";
                html += "</div>";
            html += "</h5>";
            html += '<div id="accordion-' + key + '" class="accordion-collapse collapse">';
                html += '<div class="card-datatable table-responsive">';
                    html += '<table class="table border-top" style="font-size: .9rem;">';
                        html += '<thead><tr>';
                            html += '<th class="">Provider</th>';
                            html += '<th class="">ID</th>';
                            html += '<th class="">Service Name</th>';
                            html += '<th class="text-end">Price</th>';
                            html += '<th class="text-end">Min</th>';
                            html += '<th class="text-end">Max</th>';
                            html += '<th class=""></th>';
                        html += '</tr></thead>';
                        html += "<tbody>";
                            val.forEach((service) => {
                                html += '<tr data-list_service_id="' + service.list_service_id + '">';
                                    html += '<td>' + service.provider + '</td>';
                                    html += '<td>' + service.service + '</td>';
                                    html += '<td>' + service.name + '</td>';
                                    html += '<td class="text-end">' + service.rate + '</td>';
                                    html += '<td class="text-end">' + service.min + '</td>';
                                    html += '<td class="text-end">' + service.max + '</td>';
                                    html += '<td class="text-center">';
                                        html += '<a href="javascript:;" class="btn btn-sm btn-icon btn-icon-custom delete-service-btn" title="Remove this service from this list" data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"><i class="bx bxs-trash"></i></a>';
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
    const collapseElementList = [].slice.call(document.querySelectorAll('.card-collapsible'));
    collapseElementList.map(function (collapseElement) {
        collapseElement.addEventListener('click', event => {
            event.preventDefault();
            let data_list_service_id = collapseElement.closest('tr').getAttribute("data-list_service_id");
            // Collapse the element
            new bootstrap.Collapse(collapseElement.closest('tbody').querySelector('.collapse[data-list_service_id="' + data_list_service_id + '"]'));
            // Toggle collapsed class in `.card-header` element
            collapseElement.closest('tr').classList.toggle('collapsed');
            // Toggle class bx-chevron-down & bx-chevron-up
            Helpers._toggleClass(collapseElement.firstElementChild, 'bxs-chevron-down', 'bxs-chevron-up');
        });
    })



    // button actions
    $(".redirect-to-payment").on('click', function(){
        location.href = '/payment';
    })

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

  
}