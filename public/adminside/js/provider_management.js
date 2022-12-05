'use strict';
let formvali, copy_past_fv, file_fv;
document.addEventListener('DOMContentLoaded', function (e) {
    (function () {
        const formAddNewRecord = document.getElementById('form-add-new-record'); 
        // Form validation for Add new record
        formvali = FormValidation.formValidation(formAddNewRecord, {
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
                // api_key: {
                //     validators: {
                //         notEmpty: {
                //             message: 'API key is required'
                //         }
                //     }
                // },
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

        const formCopyPaste = document.getElementById('form-import-copy');
        copy_past_fv = FormValidation.formValidation(formCopyPaste, {
            fields: {
                providers_list: {
                validators: {
                    notEmpty: {
                    message: 'The field is required'
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

        const formfile = document.getElementById('form-import-file');
        file_fv = FormValidation.formValidation(formfile, {
            fields: {
                formFile: {
                validators: {
                    notEmpty: {
                    message: 'Please select excel file'
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
    
    $('#add-new-record').on('hidden.bs.offcanvas', function () {
        $(this).find('form').trigger('reset');
    })
    $('#import_copy_modal').on('hidden.bs.offcanvas', function () {
        $(this).find('form').trigger('reset');
    })
    $('#import_file_modal').on('hidden.bs.offcanvas', function () {
        $(this).find('form').trigger('reset');
    })


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
                // render: function (data, type, full, meta) {
                //     return (
                //         '<a href="javascript:;" class="btn btn-sm btn-icon item-edit" title="Edit"><i class="bx bxs-edit"></i></a>' +
                //         '<a href="javascript:;" class="btn btn-sm btn-icon item-delete" title="Delete"><i class="bx bx-trash"></i></a>' 
                        
                //     );
                // }
            }
        ],
        // order: [[2, 'desc']],
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        displayLength: 100,
        lengthMenu: [100, 250, 500]
    });
  
    // Add New record
    // ? Remove/Update this code as per your requirements
    formvali.on('core.form.valid', function () {
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
        const end_point = $(this).parents('tr').attr("data-endpoint");
        $("#domain_name").val(domain);
        $("#end_point").val(end_point);
        $("#m_selected_id").val(sel_id);
        $("#m_action_type").val("edit");
        $("#exampleModalLabel").html("Update Provider");
        $("#submit_btn_title").html("Update");
        
        let offCanvasElement = document.querySelector('#add-new-record');
        let offCanvasEl = new bootstrap.Offcanvas(offCanvasElement);
        offCanvasEl.show();

    });

    // Import Provider from Copy/Past Form Submit
    copy_past_fv.on('core.form.valid', function () {
        let providers = $("#providers_list").val().trim().split(/\n/);
  
        let provider_list = [];
        if(providers.length == 0){
            Swal.fire({
                icon: 'warning',
                title: '',
                text: "Domain and End point is required",
                type: 'warning',
                customClass: {
                confirmButton: 'btn btn-primary'
                },
                buttonsStyling: false
            })
            return;
        }

        for(let index = 0; index < providers.length; index++){
            const item =  providers[index];
            const provider_arr = item.trim().split(";");
            // if(provider_arr.length < 3){
            //     Swal.fire({
            //         icon: 'warning',
            //         title: '',
            //         text: "Domain and End point is required",
            //         type: 'warning',
            //         customClass: {
            //         confirmButton: 'btn btn-primary'
            //         },
            //         buttonsStyling: false
            //     })
            //     return;
            // }
            const domain = provider_arr[0];
            const end_point = provider_arr[1];
            if(!domain || !end_point){
                Swal.fire({
                    icon: 'warning',
                    title: '',
                    text: "Domain and End point is required",
                    type: 'warning',
                    customClass: {
                    confirmButton: 'btn btn-primary'
                    },
                    buttonsStyling: false
                })
                return;
            }
            const key = provider_arr[2]?provider_arr[2]:'';
            provider_list.push({"domain": domain, "end_point": end_point, "key": key});
        }

       
        if(provider_list.length == 0){
            Swal.fire({
                icon: 'warning',
                title: '',
                text: " Domain and End point is required",
                type: 'warning',
                customClass: {
                confirmButton: 'btn btn-primary'
                },
                buttonsStyling: false
            })
            return;
        }

        let _url = "/admin/provider-management/import_list";

        let data = {
            list: JSON.stringify(provider_list)
        };
  
        $(".data-submit-copy").attr("disabled", true);
        $(".data-submit-copy .fa-spinner").css("display", "inline-block");
        
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
                        icon: 'warning',
                        title: '',
                        text: response.message,
                        type: 'warning',
                        customClass: {
                        confirmButton: 'btn btn-primary'
                        },
                        buttonsStyling: false
                    })
                    $(".data-submit-copy .fa-spinner").css("display", "none");
                    $(".data-submit-copy").removeAttr("disabled");
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
                $(".data-submit-copy .fa-spinner").css("display", "none");
                $(".data-submit-copy").removeAttr("disabled");
                return;
            },
        });
    });
  
    // Import Provider from file Form Submit
    file_fv.on('core.form.valid', function () {
        var fileUpload = document.getElementById("formFile");
   
        //Validate whether File is valid Excel file.
        var regex = /^([a-zA-Z0-9\s_\\.\-:])+(.xls|.xlsx)$/;
        
        if (!regex.test(fileUpload.value.toLowerCase())) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Please upload a valid Excel file.',
                type: 'error',
                customClass: {
                    confirmButton: 'btn btn-primary'
                },
                buttonsStyling: false
            })
            return;
        }
  
        read_from_file(fileUpload);
    });

 
     // Filter form control to default size
     // ? setTimeout used for multilingual table initialization
    setTimeout(() => {
        $('.dataTables_filter .form-control').removeClass('form-control-sm');
        $('.dataTables_length .form-select').removeClass('form-select-sm');
    }, 300);
});


function read_from_file(fileUpload){
    if (typeof (FileReader) != "undefined") {
        var reader = new FileReader();
  
        //For Browsers other than IE.
        if (reader.readAsBinaryString) {
            reader.onload = function (e) {
                return GetProdectsFromExcel(e.target.result);
            };
            reader.readAsBinaryString(fileUpload.files[0]);
        } else {
            //For IE Browser.
            reader.onload = function (e) {
                var data = "";
                var bytes = new Uint8Array(e.target.result);
                for (var i = 0; i < bytes.byteLength; i++) {
                    data += String.fromCharCode(bytes[i]);
                }
                return GetProdectsFromExcel(data);
            };
            reader.readAsArrayBuffer(fileUpload.files[0]);
        }
    } else {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'This browser does not support HTML5.',
            type: 'error',
            customClass: {
                confirmButton: 'btn btn-primary'
            },
            buttonsStyling: false
        })
        return false;
    }
}
  
function GetProdectsFromExcel(data, filename) {
    //Read the Excel File data in binary
    var workbook = XLSX.read(data, {
        type: 'binary'
    });
  
    //get the name of First Sheet.
    var Sheet = workbook.SheetNames[0];
  
    //Read all rows from First Sheet into an JSON array.
    var excelRows = XLSX.utils.sheet_to_row_object_array(workbook.Sheets[Sheet]);
  
    var provider_list = [];
  
    //Add the data rows from Excel file.
    for (var i = 0; i < excelRows.length; i++) {
        const domain = excelRows[i].domain;
        const end_point = excelRows[i].endpoint;
        
        if(!domain || !end_point){
            Swal.fire({
                icon: 'warning',
                title: '',
                text: "Some records are missing domains or endpoints",
                type: 'warning',
                customClass: {
                    confirmButton: 'btn btn-primary'
                },
                buttonsStyling: false
            })
            return;
        }

        const key  = excelRows[i].key?excelRows[i].key:'';
        provider_list.push({
            "domain" : domain,
            "end_point": end_point,
            "key": key
        })
    }
  
    if(provider_list.length == 0){
        Swal.fire({
            icon: 'warning',
            title: '',
            text: "No records in Excel file",
            type: 'warning',
            customClass: {
                confirmButton: 'btn btn-primary'
            },
            buttonsStyling: false
        })
        return;
    }

    // upload to the server
    let _url = "/admin/provider-management/import_list";
  
    $(".data-submit-file").attr("disabled", true);
    $(".data-submit-file .fa-spinner").css("display", "inline-block");
    
    $.ajax({
        url: _url,
        type: "POST",
        data: {
            list: JSON.stringify(provider_list)
        },
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
                    icon: 'warning',
                    title: '',
                    text: response.message,
                    type: 'warning',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    },
                    buttonsStyling: false
                })
                $(".data-submit-file .fa-spinner").css("display", "none");
                $(".data-submit-file").removeAttr("disabled");
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
            $(".data-submit-file .fa-spinner").css("display", "none");
            $(".data-submit-file").removeAttr("disabled");
            return;
        },
    });
};