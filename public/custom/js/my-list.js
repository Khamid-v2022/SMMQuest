$(function () {

    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });


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

    $(".start-order-btn").on("click", function(e){
        let check_flag = true;
        const list_id = $(this).parents('.accordion-item').attr("data-list_id");
        $(this).parents('.accordion-item').find("form.order-details").each(function(){
            $(this).find('input').map(function(){
                // console.log($(this).val());
                if(!$(this).val())
                    check_flag = false;
            });
        })

        if(!check_flag){
            Swal.fire({
                icon: 'warning',
                text: 'Please enter values in the fields for all services in this list',
                customClass: {
                  confirmButton: 'btn btn-primary'
                },
                buttonsStyling: false
            })
            return;
        }

        // start order
        let data = [];
        $(this).parents('.accordion-item').find("form.order-details").each(function(){
            const list_service_id = $(this).attr("data-list_service_id");
            const service_id = $(this).attr("data-service_id");
            const quantity = $(this).find("input.quantity-input").val();
            const link = $(this).find("input.link-input").val();
            data.push({list_service_id, service_id, quantity, link});
        })

        const _url = '/my-list/start_order';
        $.ajax({
            url: _url,
            data: {
                list_id: list_id,
                orders: data
            },
            type: "POST",
            success: function (response) {
                if (response.code == 200) {
                    Swal.fire({
                        icon: 'success',
                        title: '',
                        text: "Started this order!",
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        },
                        buttonsStyling: false
                    }).then(function(){
                        // delete this list
                        $(".accordion-item[data-list_id='" + list_id + "']").remove();
                    });
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
    })
});