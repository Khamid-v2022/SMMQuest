/**
 * DataTables Basic
 */

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
            (offCanvasElement.querySelector('#favorite').checked = false);
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
                    offCanvasEl.hide();
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
          
          // Hide offcanvas using javascript method
          
        }
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
    
    // Filter form control to default size
    // ? setTimeout used for multilingual table initialization
    setTimeout(() => {
        $('.dataTables_filter .form-control').removeClass('form-control-sm');
        $('.dataTables_length .form-select').removeClass('form-select-sm');
    }, 300);
});
 