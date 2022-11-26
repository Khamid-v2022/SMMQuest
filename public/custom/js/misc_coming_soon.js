'use strict';
const form = document.querySelector('#subscribe_form');
let validator;
document.addEventListener('DOMContentLoaded', function (e) {
    (function () {
    // Form validation for Add new record
        validator = FormValidation.formValidation(form, {
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

    })();
});

$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    $("#subscribe_form").on("submit", function(e){
        e.preventDefault();

        validator.validate().then(function (a) {
           
            if ("Valid" == a) {
                $(".fa-spinner").css("display", "inline-block");
                $("#submit_btn").attr("disabled", true);

                let _url = `/coming-soon`;
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
                                title: 'Thank you!',
                                text: '',
                                customClass: {
                                confirmButton: 'btn btn-success'
                                }
                            }).then(function(result){
                                $("#email").val("");
                            });
                        } else {
                            Swal.fire({
                                icon: 'warning',
                                title: '',
                                text: response.message,
                                customClass: {
                                    confirmButton: 'btn btn-success'
                                }
                            })
                        }
                        $(".fa-spinner").css("display", "none");  
                        $("#submit_btn").removeAttr("disabled");
                    },
                    error: function (response) {
                        Swal.fire({
                            icon: 'warning',
                            title: '',
                            text: "Something went wrong. Please try again later",
                            customClass: {
                            confirmButton: 'btn btn-success'
                            }
                        })
                        $(".fa-spinner").css("display", "none");
                        $("#submit_btn").removeAttr("disabled");
                    },
                });
            }
        });
    })

})