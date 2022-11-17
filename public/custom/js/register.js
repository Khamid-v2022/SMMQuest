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
           email: {
             validators: {
               notEmpty: {
                 message: 'Please enter your email'
               },
               emailAddress: {
                 message: 'Please enter valid email address'
               }
             }
           },
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
           terms: {
             validators: {
               notEmpty: {
                 message: 'Please agree terms & conditions'
               }
             }
           }
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

    $("#singup_btn").on("click", function(e){
        // e.preventDefault();

        validator.validate().then(function(status) {
            if (status == 'Valid') {
                let _url = "/auth/register";
                let data = {
                    email: $("#email").val(),
                    password: $("#password").val(),
                };

                $.ajax({
                    url: _url,
                    type: "POST",
                    data: data,
                    success: function (response) {
                        if (response.code == 200) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: '',
                                type: 'success',
                                customClass: {
                                  confirmButton: 'btn btn-primary'
                                },
                                buttonsStyling: false
                            }).then(function(result){
                                location.href = "/auth/login";
                            })
                            return;
                        }  else {
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
                            text: ' Something went wrong. Please try again later!',
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
       
    })
})