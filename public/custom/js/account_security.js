/**
 * Account Settings - Security
 */

 'use strict';
 let validator;
 document.addEventListener('DOMContentLoaded', function (e) {
   (function () {
     const formChangePass = document.querySelector('#formAccountSettings');
 
     // Form validation for Change password
     if (formChangePass) {
        validator = FormValidation.formValidation(formChangePass, {
         fields: {
           currentPassword: {
             validators: {
               notEmpty: {
                 message: 'Please current password'
               },
               stringLength: {
                 min: 6,
                 message: 'Password must be more than 6 characters'
               }
             }
           },
           newPassword: {
             validators: {
               notEmpty: {
                 message: 'Please enter new password'
               },
               stringLength: {
                 min: 6,
                 message: 'Password must be more than 6 characters'
               }
             }
           },
           confirmPassword: {
             validators: {
               notEmpty: {
                 message: 'Please confirm new password'
               },
               identical: {
                 compare: function () {
                   return formChangePass.querySelector('[name="newPassword"]').value;
                 },
                 message: 'The password and its confirm are not the same'
               },
               stringLength: {
                 min: 6,
                 message: 'Password must be more than 6 characters'
               }
             }
           }
         },
         plugins: {
           trigger: new FormValidation.plugins.Trigger(),
           bootstrap5: new FormValidation.plugins.Bootstrap5({
             eleValidClass: '',
             rowSelector: '.col-md-6'
           }),
           submitButton: new FormValidation.plugins.SubmitButton(),
           // Submit the form when all fields are valid
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
     }     
   })();
 });
 
 $(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });
    $("#save_btn").on("click", function(e){
        e.preventDefault();
        validator.validate().then(function (a) {
            if ("Valid" == a) {
                let _url = `/profile-security`;
                let data = {
                    current_password: $("#currentPassword").val(),
                    new_password: $("#newPassword").val(),
                };

                $.ajax({
                    url: _url,
                    type: "POST",
                    data: data,
                    success: function (response) {
                        if (response.code == 200) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Updated!',
                                text: '',
                                customClass: {
                                confirmButton: 'btn btn-success'
                                }
                            }).then(function(result){
                                document.getElementById("formAccountSettings").reset();
                                // location.href = "/auth/logout";
                            })
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
                    },
                });
            }
        })
    });
 });
 