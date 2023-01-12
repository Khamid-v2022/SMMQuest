$(function () {

    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    loadHistoryLists();

});

function loadHistoryLists() {
    const selected_currency = $("#selected-currency").attr("data-currency");
    const _url = "/order-history";

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

function drawingListTable() {
    
}

function drawingListTable(list){
    let html = "";
    let index = 0;
    Object.entries(list).forEach(([key, val]) => {
        index++;
        html += '<div class="card accordion-item" data-list_id="' + key + '">';
            html += '<h5 class="accordion-header">';
                html += '<div class="accordion-title">';
                    html += '<span>Order ID: ' + val[0].order_serial_id + ' - ' + val.length + ' Services</span>';
                    html += '<div class="accordion-action">';
                        html += '<span class="created-date">' + val[0].order_created_at + '</span>';
                        html += '<a class="accordion-button ' + (index > 1 ? 'collapsed' : '') + '" type="button" data-bs-toggle="collapse" aria-expanded="' + (index == 1 ? 'true' : 'false') + '"  data-bs-target="#accordion-' + key + '" aria-controls="accordion-' + key + '"></a>';
                    html += "</div>";
                html += "</div>";
            html += "</h5>";
            html += '<div id="accordion-' + key + '" class="accordion-collapse collapse ' + (index == 1 ? 'show' : '')+ '">';
                html += '<div class="card-datatable table-responsive">';
                    html += '<table class="table table-striped border-top" style="font-size: .9rem;">';
                        html += '<thead><tr>';
                            html += '<th class="">Provider</th>';
                            html += '<th class="">ID</th>';
                            html += '<th class="">Name</th>';
                            html += '<th class="text-end">Price</th>';
                            html += '<th class="text-end">Min</th>';
                            html += '<th class="text-end">Max</th>';
                            html += '<th class="text-center">In Progress</th>';
                            html += '<th class="text-center">Completed</th>';
                            html += '<th class=""></th>';
                        html += '</tr></thead>';
                        html += "<tbody>";
                            val.forEach((service) => {
                                let price = Math.round(service.paid_price * 1000000) / 1000000;
                                html += '<tr data-detail_id="' + service.id + '">';
                                    html += '<td>' + service.provider + '<i class="bx bx-check-circle text-success ms-1" style="display:inline" data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"></i>' + '</td>';
                                    html += '<td>' + service.service + '</td>';
                                    html += '<td>' + service.name + '</td>';
                                    html += '<td class="text-end">' + price + '</td>';
                                    html += '<td class="text-end">' + service.service_min + '</td>';
                                    html += '<td class="text-end">' + service.service_max + '</td>';
                                    html += '<td class="text-center">'; 
                                        if(service.status == 4)             // Rejected
                                            html += '<span class="badge bg-label-danger">Rejected</span>';
                                        else 
                                            html += '<span class="badge bg-label-warning">' + service.in_progress_minute + 'm</span>';
                                    html += '</td>';
                                    html += '<td class="text-center">';
                                        if(service.status == 5)             // Canceled
                                            html += '<span class="badge bg-label-danger">Canceled</span>';
                                        else if(service.status == 4)        // Rejected
                                            html += '';
                                        else
                                            html += '<span class="badge bg-label-success">' + service.completed_minute + 'm</span>';
                                    html += '</td>';
                                    html += '<td class="">';
                                        html += '<a href="javascript:void(0);" class="btn-icon-custom card-collapsible collapse-detail-box-btn"><i class="tf-icons bx bxs-chevron-down"></i></a>';
                                    html += '</td>';
                                html += '</tr>';

                                html += '<tr class="collapse" data-detail_id="' + service.id + '">';
                                    html += '<td colspan="9">';
                                        if(service.status == 4){
                                            html +='<span class="badge bg-label-danger custom-badge">' + service.error_message + '</span>';
                                        }
                                        else {
                                            let cost = price;
                                            if(service.quantity){
                                                cost = Math.round(price * service.quantity * 1000000) / 1000000;
                                            }
                                            // check comments type
                                            if(service.comments){
                                                let comments = service.comments.split("\n");
  
                                                let real_comments = [];
                                                comments.forEach((item) => {
                                                    if(item.trim() != '')
                                                        real_comments.push(item);
                                                })
                                                cost = Math.round(price * real_comments.length * 1000000) / 1000000;
                                            }
                                            
                                            html += '<div>';
                                                html += '<span>Creation Date: </span>' + service.created_at;
                                                html += '<br><span>Order ID: </span>' + (service.order_id ? service.order_id : '');
                                                html += '<br><span>Cost: </span>' + cost;
                                                html += service.quantity ? ('<br><span>Quantity: </span>' + service.quantity) : '';
                                                html += service.link ? ('<br><span>Link: </span>' + service.link) : '';
                                                html += service.comments ? ('<br><span>Comments: </span>' + service.comments) : '';
                                                html += service.usernames ? ('<br><span>Usernames: </span>' + service.usernames) : '';
                                                html += service.username ? ('<br><span>Username: </span>' + service.username) : '';
                                                html += service.hashtags ? ('<br><span>Hashtags: </span>' + service.hashtags) : '';
                                                html += service.hashtag ? ('<br><span>Hashtag: </span>' + service.hashtag) : '';
                                                html += service.media ? ('<br><span>Media: </span>' + service.media) : '';
                                                html += service.answer_number ? ('<br><span>Answer Number: </span>' + service.answer_number) : '';
                                                html += service.groups ? ('<br><span>Groups: </span>' + service.groups) : '';
                                                html += service.min ? ('<br><span>Min: </span>' + service.min) : '';
                                                html += service.max ? ('<br><span>Max: </span>' + service.max) : '';
                                                html += service.posts ? ('<br><span>Posts: </span>' + service.posts) : '';
                                                html += service.old_posts ? ('<br><span>Old Posts: </span>' + service.old_posts) : '';
                                                html += service.expiry ? ('<br><span>Expiry: </span>' + service.expiry) : '';
                                                html += service.delay ? ('<br><span>Delay: </span>' + service.delay) : '';
                                                html += service.start_count ? ('<br><span>Start Count: </span>' + service.start_count) : '';
                                                html += service.remains ? ('<br><span>Remains: </span>' + service.remains) : '';
                                            html += '</div>';
                                        }
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
            let data_detail_id = collapseElement.closest('tr').getAttribute("data-detail_id");
            // Collapse the element
            new bootstrap.Collapse(collapseElement.closest('tbody').querySelector('.collapse[data-detail_id="' + data_detail_id + '"]'));
            // Toggle collapsed class in `.card-header` element
            collapseElement.closest('tr').classList.toggle('collapsed');
            // Toggle class bx-chevron-down & bx-chevron-up
            Helpers._toggleClass(collapseElement.firstElementChild, 'bxs-chevron-down', 'bxs-chevron-up');
        });
    })
}