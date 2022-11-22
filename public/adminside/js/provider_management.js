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
                        // (offCanvasElement.querySelector('#is_activated').checked = false),
                        (offCanvasElement.querySelector('#api_key').value = '');
                        // Open offCanvas with form
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

// datatable (jquery)
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
        // ajax: assetsPath + '/json/table-datatable.json',
        columnDefs: [
            {
                // API Key
                targets: 2,
                title: 'API Key',
                orderable: false,
                searchable: false,
                render: function (data, type, full, meta) {
                    return ( data +
                        '<a href="javascript:;" data-api_key="' + data + '" class="btn btn-sm btn-icon item-edit ms-3" title="Edit API key"><i class="bx bxs-edit"></i></a>'
                    );
                }
            },
            // {
            //     // Status
            //     targets: 4,
            //     orderable: false,
            //     render: function (data, type, full, meta) {
            //         return (

            //             '<label class="switch">' +
            //                 '<input type="checkbox" class="switch-input activate-btn" ' + (data == 1 ? 'checked' : '') + '/>' +
            //                 '<span class="switch-toggle-slider">' +
            //                     '<span class="switch-on">' +
            //                         '<i class="bx bx-check"></i>' +
            //                     '</span>' +
            //                     '<span class="switch-off">' +
            //                         '<i class="bx bx-x"></i>' +
            //                     '</span>' +
            //                 '</span>' +
            //             '</label>'
            //         );
            //     }
            // },
            {
               // Actions
                targets: -1,
                title: 'Actions',
                orderable: false,
                searchable: false,
                render: function (data, type, full, meta) {
                    return (
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
                api_key: $('#api_key').val()
            };
    
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
                    } else {
                        Swal.fire({
                            icon: 'error',
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
            
            // Hide offcanvas using javascript method
            offCanvasEl.hide();
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
        const api_key = $(this).attr("data-api_key");
        $("#m_selected_id").val(sel_id);
        $("#m_api_key").val(api_key);
        $("#modals-change_key").modal('show');
    });
 
    $("#m_change_api_btn").on("click", function(){
        let _url = "/admin/provider-management/changeAPIKey";
       
        let data = {
            selected_id: $("#m_selected_id").val(),
            api_key: $("#m_api_key").val()
        };
 
        $.ajax({
            url: _url,
            type: "POST",
            data: data,
            success: function (response) {
                if (response.code == 200) {
                    $("#modals-change_key").modal('toggle');
                    location.reload();
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: '',
                        text: response.message,
                        type: 'warning',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        },
                        buttonsStyling: false
                    })
                    return;
                }
            },
            error: function (response) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Something went wrong. Please try again later!',
                    type: 'error',
                    customClass: {
                    confirmButton: 'btn btn-primary'
                    },
                    buttonsStyling: false
                })
                return;
            },
        });
    })
     
    // activate btn 
    $('.datatables-basic tbody').on('click', '.activate-btn', function () {
        const sel_id = $(this).parents('tr').attr("data-provider_id");
        
        const is_active = $(this).prop('checked') ? 1 : 0;
        const parent_this = this;
        
        let _url = "/admin/provider-management/updateActivate";
        
        let data = {
          selected_id: sel_id,
          is_active: is_active
        };
        $.ajax({
            url: _url,
            type: "POST",
            data: data,
            success: function (response) {
            },
            error: function (response) {
            },
        });
    });
     // Filter form control to default size
     // ? setTimeout used for multilingual table initialization
    setTimeout(() => {
         $('.dataTables_filter .form-control').removeClass('form-control-sm');
         $('.dataTables_length .form-select').removeClass('form-select-sm');
    }, 300);
 });
  