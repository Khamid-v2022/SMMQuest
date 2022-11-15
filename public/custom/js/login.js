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
                }
            },
            error: function (response) {
                Swal.fire({
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
    })
})