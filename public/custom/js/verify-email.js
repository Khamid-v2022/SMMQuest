$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    $("#resend_btn").on('click', function(){
        $(this).css("display", "none");
        $(".fa-spinner").css("display", "inline-block");
        
        let _url = "/auth/register/resend-verify-email/" + $("#email").val();
        $.ajax({
            url: _url,
            type: "get",
            success: function (response) {
                if (response.code == 200) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        type: 'success',
                        customClass: {
                          confirmButton: 'btn btn-primary'
                        },
                        buttonsStyling: false
                    });
                    $("#resend_btn").css("display", "inline");
                    $(".fa-spinner").css("display", "none");
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
                    $("#resend_btn").css("display", "inline");
                    $(".fa-spinner").css("display", "none");
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
                $("#resend_btn").css("display", "inline");
                $(".fa-spinner").css("display", "none");
            },  
        });
    })
})