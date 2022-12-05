/**
 * DataTables Basic
 */

'use strict';
let fv;
let copy_past_fv, file_fv;

document.addEventListener('DOMContentLoaded', function (e) {
  (function () {
    const formAddNewRecord = document.getElementById('form-add-new-record');

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
         // defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
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
 
 // datatable (jquery)
$(function () {

  $('#add-new-record').on('hidden.bs.offcanvas', function () {
    $(this).find('form').trigger('reset');
  })
  $('#import_copy_modal').on('hidden.bs.offcanvas', function () {
    $(this).find('form').trigger('reset');
  })
  $('#import_file_modal').on('hidden.bs.offcanvas', function () {
    $(this).find('form').trigger('reset');
  })

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
              // Favorite
              targets: 2,
              title: 'Favorite',
              orderable: false,
              searchable: false,
              render: function (data, type, full, meta) {
                if(data == 1)
                  return (
                    '<a href="javascript:;" class="btn btn-sm btn-icon item-favorite"><i class="bx bxs-like text-warning" ></i></a>'
                  );
                else
                  return (
                    '<a href="javascript:;" class="btn btn-sm btn-icon item-favorite"><i class=" bx bxs-like" ></i></a>'
                  );
              }
            },
            {
              // Actions
              targets: -1,
              title: 'Actions',
              orderable: false,
              searchable: false,
            }
        ],
        // order: [[2, 'desc']],
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        displayLength: 100,
        lengthMenu: [100, 250, 500]

    });


    // Add New record
    // ? Remove/Update this code as per your requirements
    fv.on('core.form.valid', function () {

      let _url = "/providers/add";
      let data = {
        domain: $('#domain_name').val(),
        favorite: $('#favorite').prop('checked') ? 1 : 0,
      };

      $(".data-submit").attr("disabled", true);
      $(".data-submit .fa-spin").css("display", "inline-block");
      
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
              type: 'error',
              customClass: {
                confirmButton: 'btn btn-primary'
              },
              buttonsStyling: false
          })
          $(".data-submit .fa-spinner").css("display", "none");
          $(".data-submit").removeAttr("disabled");
          return;
        },
      });
    });
 
    // Delete Record
    $('.datatables-basic tbody').on('click', '.item-delete', function () {
      const del_id = $(this).parents('tr').attr("data-provider_id");
      const parent_this = this;
      let _url = "/providers/delete/" + del_id;
      
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

            $.ajax({
              url: _url,
              type: "GET",
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
                      dt_basic.row($(parent_this).parents('tr')).remove().draw();
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
          }
      });
    });

    // favorite 
    $('.datatables-basic tbody').on('click', '.item-favorite', function () {
      const sel_id = $(this).parents('tr').attr("data-provider_id");
      const is_favorite = !($(this).find("i").hasClass("text-warning") ? 1 : 0);
      const parent_this = this;
      
      let _url = "/providers/favorite";
      
      let data = {
        selected_id: sel_id,
        favorite: (is_favorite?1:0)
      };

      $.ajax({
        url: _url,
        type: "POST",
        data: data,
        success: function (response) {
            if (response.code == 200) {
              if(is_favorite){
                $(parent_this).find("i").addClass("text-warning");
              }else 
                $(parent_this).find("i").removeClass("text-warning");
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
    });

    // edit API key 
    $('.datatables-basic tbody').on('click', '.item-edit', function () {
      const sel_id = $(this).parents('tr').attr("data-provider_id");
      $("#m_selected_id").val(sel_id);
      $("#modals-change_key").modal('show');
    });

    // API key submit
    $("#m_change_api_btn").on("click", function(){
      let _url = "/providers/changeAPIKey";
      
      let data = {
        selected_id: $("#m_selected_id").val(),
        api_key: $("#m_api_key").val()
      };

      $("#m_change_api_btn").attr("disabled", true);
      $("#m_change_api_btn .fa-spin").css("display", "inline-block");
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
                $("#modals-change_key").modal('toggle');
                location.reload();
              })

              $("#m_change_api_btn .fa-spinner").css("display", "none");
              $("#m_change_api_btn").removeAttr("disabled");
             
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
              $("#m_change_api_btn .fa-spinner").css("display", "none");
              $("#m_change_api_btn").removeAttr("disabled");
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
            $("#m_change_api_btn .fa-spinner").css("display", "none");
            $("#m_change_api_btn").removeAttr("disabled");
            return;
        },
      });
    })
    

    // Import Provider from Copy/Past Form Submit
    copy_past_fv.on('core.form.valid', function () {
      let providers = $("#providers_list").val().trim().split(/\n/);

      let provider_list = [];
      providers.forEach((item) => {
        const domain = item.trim().split(";")[0];
        const key = item.trim().split(";")[1]?item.trim().split(";")[1]:'';
        provider_list.push({"domain": domain, "key": key});
      })

      let _url = "/providers/import_list";
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
    const key  = excelRows[i].key?excelRows[i].key:'';
    if(domain){
      provider_list.push({
        "domain" : domain,
        "key": key
      })
    }
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
  let _url = "/providers/import_list";

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