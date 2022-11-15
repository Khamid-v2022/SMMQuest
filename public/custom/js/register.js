$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    $("#singup_btn").on("click", function(e){
        // e.preventDefault();
        const email = $("#email").val();
        const password = $("#password").val();
        const confirm_password = $("#confirm-password").val();
        
        if(!email || !password || password != confirm_password){
            Swal.fire({
                title: 'Warning!',
                text: 'Please fill out the following form',
                type: 'warning',
                customClass: {
                  confirmButton: 'btn btn-primary'
                },
                buttonsStyling: false
            })
            return;
        }

        // validator.validate().then(function(status) {
        //     if (status == 'Valid') {
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
                                title: 'Warning!',
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
        //     } else {
        //         // Show error popup. For more info check the plugin's official documentation: https://sweetalert2.github.io/
        //         Swal.fire({
        //             text: "Sorry, looks like there are some errors detected, please try again.",
        //             icon: "error",
        //             buttonsStyling: false,
        //             confirmButtonText: "Ok, got it!",
        //             customClass: {
        //                 confirmButton: "btn btn-primary"
        //             }
        //         });
        //     }
        // });
       
    })
})