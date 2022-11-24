'use strict';
let fv, offCanvasEl;
document.addEventListener('DOMContentLoaded', function (e) {
    (function () {
        const formAddNewRecord = document.getElementById('form-add-new-record');
  
        setTimeout(() => {
            const newRecord = document.querySelector('.create-new'),
                offCanvasElement = document.querySelector('#add-new-record');
        
                // To open offCanvas, to add new record
            if (newRecord) {
                newRecord.addEventListener('click', function () {
                    offCanvasEl = new bootstrap.Offcanvas(offCanvasElement);
                        // Empty fields on offCanvas open
                        (offCanvasElement.querySelector('#domain_name').value = ''),
                        (offCanvasElement.querySelector('#end_point').value = ''),
                        // (offCanvasElement.querySelector('#is_activated').checked = false),
                        (offCanvasElement.querySelector('#api_key').value = '');
                        // Open offCanvas with form

                    $("#m_selected_id").val("");
                    $("#m_action_type").val("add");
                    $("#submit_btn_title").html("Submit");
                    $("#exampleModalLabel").html("New Provider");
                    offCanvasEl.show();
                });
            }
        }, 200);
  
        // Form validation for Add new record
        fv = FormValidation.formValidation(formAddNewRecord, {
            fields: {
                domain_name: {
                    validators: {
                        notEmpty: {
                            message: 'The domain name is required'
                        }
                    }
                },
                end_point: {
                    validators: {
                        notEmpty: {
                            message: 'End Point is required'
                        }
                    }
                },
                api_key: {
                    validators: {
                        notEmpty: {
                            message: 'API key is required'
                        }
                    }
                },
            },
            plugins: {
                trigger: new FormValidation.plugins.Trigger(),
                bootstrap5: new FormValidation.plugins.Bootstrap5({
                    // Use this for enabling/changing valid/invalid class
                    // eleInvalidClass: '',
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
        buttons: [
            {
                text: '<i class="bx bx-plus me-sm-2"></i> <span class="d-none d-sm-inline-block">Add New Provider</span>',
                className: 'create-new btn btn-primary'
            }
        ]
    });
    $('div.head-label').html('<h5 class="card-title mb-0">Providers</h5>');
  
    // Add New record
    // ? Remove/Update this code as per your requirements
    fv.on('core.form.valid', function () {
        if (domain_name != '') {
            let _url = "/admin/provider-management";
            let data = {
                domain: $('#domain_name').val(),
                // is_activated: $('#is_activated').prop('checked'),
                is_activated: 1,
                api_key: $('#api_key').val(),
                end_point: $("#end_point").val(),
                action_type: $("#m_action_type").val(),
                selected_id: $("#m_selected_id").val()
            };
            
            $(".fa-spinner").css("display", "inline-block");
            $(".data-submit").attr("disabled", true);
            $.ajax({
                url: _url,
                type: "POST",
                data: data,
                success: function (response) {
                    console.log(response);
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

                        $(".fa-spinner").css("display", "none");
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
                       
                        $(".fa-spinner").css("display", "none");
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

                    $(".fa-spinner").css("display", "none");
                    $(".data-submit").removeAttr("disabled");
                    return;
                },
            });
            
           
        }
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
                const del_id = $(parent_this).parents('tr').attr("data-provider_id");
                
                let _url = "/admin/provider-management";
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
 
    // edit API key 
    $('.datatables-basic tbody').on('click', '.item-edit', function () {
        const sel_id = $(this).parents('tr').attr("data-provider_id");    
        const domain = $(this).parents('tr').attr("data-domain");
        $("#domain_name").val(domain);

        $("#m_selected_id").val(sel_id);
        $("#m_action_type").val("edit");
        $("#exampleModalLabel").html("Update Provider");
        $("#submit_btn_title").html("Update");
        
        let offCanvasElement = document.querySelector('#add-new-record');
        offCanvasEl = new bootstrap.Offcanvas(offCanvasElement);
        offCanvasEl.show();

    });
 
     // Filter form control to default size
     // ? setTimeout used for multilingual table initialization
    setTimeout(() => {
         $('.dataTables_filter .form-control').removeClass('form-control-sm');
         $('.dataTables_length .form-select').removeClass('form-select-sm');
    }, 300);
});
  