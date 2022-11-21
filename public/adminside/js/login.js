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
           name: {
             validators: {
               notEmpty: {
                 message: 'Please enter your name'
               }
             }
           },
           password: {
             validators: {
               notEmpty: {
                 message: 'Please enter your password'
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
        //    submitButton: new FormValidation.plugins.SubmitButton(),
        //    defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
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
        validator.validate().then(function(status) {
            if (status == 'Valid') {
                const name = $("#name").val();
                const password = $("#password").val();

                let _url = "/admin/login";
                let data = {
                    name,
                    password,
                };

                $.ajax({
                    url: _url,
                    type: "POST",
                    data: data,
                    success: function (response) {
                        if (response.code == 200) {
                            location.href = "/admin/";
                        } else {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Warning!',
                                text: response.message,
                                type: 'warning',
                                customClass: {
                                confirmButton: 'btn btn-primary'
                                },
                                buttonsStyling: false
                            })
                        }
                    },
                    error: function (response) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.responseJSON.message,
                            type: 'error',
                            customClass: {
                            confirmButton: 'btn btn-primary'
                            },
                            buttonsStyling: false
                        })
                    
                    },
                });
            }
        });
    })
})