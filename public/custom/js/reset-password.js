/**
 *  Pages Authentication
 */

 'use strict';
 const formAuthentication = document.querySelector('#formAuthentication');
 let validator;
 document.addEventListener('DOMContentLoaded', function (e) {
     (function () {
     // Form validation for Add new record
         if (formAuthentication) {
            validator = FormValidation.formValidation(formAuthentication, {
                fields: {
                    password: {
                        validators: {
                          notEmpty: {
                            message: 'Please enter your password'
                          },
                          stringLength: {
                            min: 6,
                            message: 'Password must be more than 6 characters'
                          }
                        }
                    },
                    'confirm-password': {
                        validators: {
                          notEmpty: {
                            message: 'Please confirm password'
                          },
                          identical: {
                            compare: function () {
                              return formAuthentication.querySelector('[name="password"]').value;
                            },
                            message: 'The password and its confirm are not the same'
                          },
                          stringLength: {
                            min: 6,
                            message: 'Password must be more than 6 characters'
                          }
                        }
                    },
                },
                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap5: new FormValidation.plugins.Bootstrap5({
                        eleValidClass: '',
                        rowSelector: '.mb-3'
                    }),
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
         }
     })();
 });
 
 $(function () {
     $.ajaxSetup({
         headers: {
             "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
         },
    });
 
    $("#formAuthentication").on("submit", function(e){
        e.preventDefault();
 
        validator.validate().then(function (a) {
            if ("Valid" == a) {
                 $(".fa-spinner").css("display", "inline-block");
                 $("#send_link_btn_title").css("display", "none");
                 $("#send_link_btn").attr("disabled", true);
 
                 let _url = `/reset-password`;
                 let data = {
                    email: $("#email").val(),
                    password: $("#password").val()
                 };
 
                $.ajax({
                    url: _url,
                    type: "POST",
                    data: data,
                    success: function (response) {
                        if (response.code == 200) {
                             Swal.fire({
                                icon: 'success',
                                title: 'Updated password',
                                text: '',
                                customClass: {
                                    confirmButton: 'btn btn-success'
                                }
                            }).then(function(result){
                                location.href = "/auth/login";
                            });
                            return;
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: '',
                                text: response.message,
                                customClass: {
                                    confirmButton: 'btn btn-success'
                                }
                            })
                        }
                        $(".fa-spinner").css("display", "none");  
                        $("#send_link_btn_title").css("display", "block");
                        $("#send_link_btn").removeAttr("disabled");
                     },
                     error: function (response) {
                        Swal.fire({
                            icon: 'error',
                            title: '',
                            text: response.message,
                            customClass: {
                                confirmButton: 'btn btn-success'
                            }
                        })
                        $(".fa-spinner").css("display", "none");  
                        $("#send_link_btn_title").css("display", "block");
                        $("#send_link_btn").removeAttr("disabled");
                     },
                });
             }
         });
     })
 
 })