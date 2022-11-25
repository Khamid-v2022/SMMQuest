let fv, offCanvasEl;
document.addEventListener('DOMContentLoaded', function (e) {
    (function () {
        const formAddNewRecord = document.getElementById('form-add-new-record');
  
        // setTimeout(() => {
        //     const newRecord = document.querySelector('.create-new'),
        //         offCanvasElement = document.querySelector('#add-new-record');
        
        //         // To open offCanvas, to add new record
        //     if (newRecord) {
        //         newRecord.addEventListener('click', function () {
        //             offCanvasEl = new bootstrap.Offcanvas(offCanvasElement);
        //             //     // Empty fields on offCanvas open
        //             //     (offCanvasElement.querySelector('#domain_name').value = ''),
        //             //     (offCanvasElement.querySelector('#end_point').value = ''),
        //             //     // (offCanvasElement.querySelector('#is_activated').checked = false),
        //             //     (offCanvasElement.querySelector('#api_key').value = '');
        //             //     // Open offCanvas with form

        //             // $("#m_selected_id").val("");
        //             // $("#m_action_type").val("add");
        //             // $("#submit_btn_title").html("Submit");
        //             // $("#exampleModalLabel").html("New Provider");
        //             offCanvasEl.show();
        //         });
        //     }
        // }, 200);
  
        // Form validation for Add new record
        fv = FormValidation.formValidation(formAddNewRecord, {
            fields: {
                m_user_email: {
                    validators: {
                        notEmpty: {
                            message: 'Email is required'
                        }
                    }
                }
            },
            plugins: {
                trigger: new FormValidation.plugins.Trigger(),
                bootstrap5: new FormValidation.plugins.Bootstrap5({
                    eleValidClass: '',
                    rowSelector: '.col-sm-12'
                }),
                submitButton: new FormValidation.plugins.SubmitButton(),
                autoFocus: new FormValidation.plugins.AutoFocus()
            },
            init: instance => {
                instance.on('plugins.message.placed', function (e) {
                    if (e.element.parentElement.classList.contains('input-group')) {
                        e.element.parentElement.insertAdjacentElement('afterend', e.messageElement);
                    }
                });
            }
        });
    })();
});

$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });
 
    var dt_basic;
    // DataTable with buttons
    // --------------------------------------------------------------------
 
    dt_basic = $('.datatables-basic').DataTable({
        columnDefs: [
            {
               // Actions
                targets: -1,
                title: 'Actions',
                orderable: false,
                searchable: false,
                render: function (data, type, full, meta) {
                    return (
                        '<a href="javascript:;" class="btn btn-sm btn-icon item-edit" title="Edit"><i class="bx bxs-edit"></i></a>' +
                        '<a href="javascript:;" class="btn btn-sm btn-icon item-delete" title="Delete"><i class="bx bx-trash"></i></a>' 
                        
                    );
                }
            }
        ],
        // order: [[2, 'desc']],
        dom: '<"card-header flex-column flex-md-row"<"head-label text-center"><"dt-action-buttons text-end pt-3 pt-md-0"B>><"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        displayLength: 10,
        lengthMenu: [10, 25, 50, 100],
        buttons: []
    });
    $('div.head-label').html('<h5 class="card-title mb-0">Users</h5>');
  
    // Add New record
    // ? Remove/Update this code as per your requirements
    fv.on('core.form.valid', function () {

        let _url = "/admin/user-management";
        let data = {
            email: $('#m_user_email').val(),
            is_delete: $('#m_is_delete').prop('checked') ? 0 : 1,
        };

        if($("#m_action_type").val() == 'edit'){
            data['selected_id'] = $("#m_selected_id").val()
        }
        
        $(".data-submit .fa-spinner").css("display", "inline-block");
        $(".data-submit").attr("disabled", true);
        $.ajax({
            url: _url,
            type: "POST",
            data: data,
            success: function (response) {
                if (response.code == 200) {
                    Swal.fire({
                        icon: 'success',
                        title: '',
                        text: response.message,
                        type: 'success',
                        customClass: {
                        confirmButton: 'btn btn-primary'
                        },
                        buttonsStyling: false
                    }).then( function(){
                        location.reload();
                    })
                    // Hide offcanvas using javascript method
                    offCanvasEl.hide();

                    $(".data-submit .fa-spinner").css("display", "none");
                    $(".data-submit").removeAttr("disabled");
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '',
                        text: response.message,
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        },
                    })
                    
                    $(".data-submit .fa-spinner").css("display", "none");
                    $(".data-submit").removeAttr("disabled");
                    return;
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
                })

                $(".data-submit .fa-spinner").css("display", "none");
                $(".data-submit").removeAttr("disabled");
                return;
            },
        }); 
    });
  
     // Delete Record
    $('.datatables-basic tbody').on('click', '.item-delete', function () {
        const parent_this = this;
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
                const del_id = $(parent_this).parents('tr').attr("data-user_id");
                
                let _url = "/admin/user-management";
                $.ajax({
                    url: _url,
                    type: "DELETE",
                    data: {
                        id: del_id
                    },
                    success: function (response) {
                        if (response.code == 200) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Has been deleted.',
                                customClass: {
                                  confirmButton: 'btn btn-success'
                                }
                            }).then( function(){
                                dt_basic.row($(parent_this).parents('tr')).remove().draw();
                            });
                        } else {
                            Swal.fire({
                                icon: 'warning',
                                title: '',
                                text: response.message,
                                customClass: {
                                confirmButton: 'btn btn-primary'
                                },
                            })
                            return;
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
                        })
                        return;
                    },
                });              
            }
        });
        
    });
 
    // edit User 
    $('.datatables-basic tbody').on('click', '.item-edit', function () {
        const sel_id = $(this).parents('tr').attr("data-user_id");    
        const email = $(this).parents('tr').attr("data-email");
        const status = $(this).parents('tr').attr("data-status");
        m_is_delete
        $("#m_user_email").val(email);
        if(status == 0)
            $("#m_is_delete").prop("checked", true);
        else
            $("#m_is_delete").prop("checked", false);
        $("#m_selected_id").val(sel_id);
        $("#m_action_type").val("edit");

        $("#exampleModalLabel").html("Update User");
        $("#submit_btn_title").html("Update");
        
        let offCanvasElement = document.querySelector('#add-new-record');
        offCanvasEl = new bootstrap.Offcanvas(offCanvasElement);
        offCanvasEl.show();
    });
 
    $("#reset_password_btn").on('click', function(){
        Swal.fire({
            title: 'Are you sure reset password?',
            text: "The user will receive a email to reset password.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes!',
            customClass: {
              confirmButton: 'btn btn-primary me-3',
              cancelButton: 'btn btn-label-secondary'
            },
            buttonsStyling: false
        }).then(function (result) {
            if (result.value) {
                 
                $("#reset_password_btn .fa-spinner").css("display", "inline-block");
                $("#reset_password_btn").attr("disabled", true);

                const user_id = $("#m_selected_id").val();
                
                let _url = "/admin/user-management/reset-password";
                $.ajax({
                    url: _url,
                    type: "POST",
                    data: {
                        id: user_id
                    },
                    success: function (response) {
                        console.log(response);
                        if (response.code == 200) {
                            Swal.fire({
                                icon: 'success',
                                title: '',
                                text: 'Sent an email',
                                customClass: {
                                  confirmButton: 'btn btn-success'
                                }
                            }).then( function(){
                                $("#reset_password_btn .fa-spinner").css("display", "none");
                                $("#reset_password_btn").removeAttr("disabled");
                                offCanvasEl.hide();
                            });
                        } else {
                            Swal.fire({
                                icon: 'warning',
                                title: '',
                                text: response.message,
                                customClass: {
                                confirmButton: 'btn btn-primary'
                                },
                            })
                            $("#reset_password_btn .fa-spinner").css("display", "none");
                            $("#reset_password_btn").removeAttr("disabled");
                            return;
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
                        })
                        $("#reset_password_btn .fa-spinner").css("display", "none");
                        $("#reset_password_btn").removeAttr("disabled");
                        return;
                    },
                });              
            }
        });
    })


    // Filter form control to default size
    // ? setTimeout used for multilingual table initialization
    setTimeout(() => {
        $('.dataTables_filter .form-control').removeClass('form-control-sm');
        $('.dataTables_length .form-select').removeClass('form-select-sm');
    }, 300);
});
  