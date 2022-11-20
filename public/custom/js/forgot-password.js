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

    $("#send_link_btn").on("click", function(e){
        // e.preventDefault();

        validator.validate().then(function (a) {
           
            if ("Valid" == a) {
                $(".fa-spinner").css("display", "inline-block");
                $("#send_link_btn_title").css("display", "none");
                $("#send_link_btn").attr("disabled", true);

                let _url = `/forgot-password`;
                let data = {
                    email: $("#email").val()
                };

                $.ajax({
                    url: _url,
                    type: "POST",
                    data: data,
                    success: function (response) {
                        if (response.code == 200) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Please check your email box!',
                                text: 'Email sent successfully',
                                customClass: {
                                confirmButton: 'btn btn-success'
                                }
                            });
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
                            text: "Something went wrong. Please try again later",
                            customClass: {
                            confirmButton: 'btn btn-success'
                            }
                        })
                        $(".fa-spinner").css("display", "none");
                        $("#send_link_btn").removeAttr("disabled");
                    },
                });
            }
        });
    })

})