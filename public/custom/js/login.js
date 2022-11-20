$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    $("#sign-in_btn").on("click", function(e){
        // e.preventDefault();

        const email = $("#email").val();
        const password = $("#password").val();
        if(!email || !password){
            Swal.fire({
                icon: 'warning',
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
        
        let _url = "/auth/login";
        let data = {
            email,
            password,
        };

        $.ajax({
            url: _url,
            type: "POST",
            data: data,
            success: function (response) {
                if (response.code == 200) {
                    location.href = "/";
                } else if(response.code == 201){
                    Swal.fire({
                        icon: 'warning',
                        title: response.message,
                        type: 'warning',
                        text: '',
                        customClass: {
                            confirmButton: 'btn btn-success'
                        }
                    }).then(function(result){
                        location.href = "/auth/register/send-verify-email/" + email;
                    })
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
                    // text: response.responseJSON.message,
                    text: "Something went wrong. Please try again later",
                    type: 'error',
                    customClass: {
                      confirmButton: 'btn btn-primary'
                    },
                    buttonsStyling: false
                })
               
            },
        });
    })
})