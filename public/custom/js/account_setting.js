/**
 * Account Settings - Account
 */

 'use strict';

 document.addEventListener('DOMContentLoaded', function (e) {
    (function () {
        const deactivateAcc = document.querySelector('#formAccountDeactivation'),
        deactivateButton = deactivateAcc.querySelector('.deactivate-account');

        if (deactivateAcc) {
            const fv = FormValidation.formValidation(deactivateAcc, {
                fields: {
                    accountActivation: {
                        validators: {
                            notEmpty: {
                                message: 'Please confirm you want to delete account'
                            }
                        }
                    }
                },
                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap5: new FormValidation.plugins.Bootstrap5({
                        eleValidClass: ''
                    }),
                    submitButton: new FormValidation.plugins.SubmitButton(),
                    fieldStatus: new FormValidation.plugins.FieldStatus({
                        onStatusChanged: function (areFieldsValid) {
                            areFieldsValid
                            ? // Enable the submit button
                            // so user has a chance to submit the form again
                            deactivateButton.removeAttribute('disabled')
                            : // Disable the submit button
                            deactivateButton.setAttribute('disabled', 'disabled');
                        }
                    }),
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

        // Deactivate account alert
        const accountActivation = document.querySelector('#accountActivation');

        // Alert With Functional Confirm Button
        if (deactivateButton) {
            deactivateButton.onclick = function () {
                if (accountActivation.checked == true) {
                    Swal.fire({
                        text: 'Are you sure you would like to deactivate your account?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes',
                        customClass: {
                        confirmButton: 'btn btn-primary me-2',
                        cancelButton: 'btn btn-label-secondary'
                        },
                        buttonsStyling: false
                    }).then(function (result) {
                        if (result.value) {
                            let _url = "/profile";

                            $.ajax({
                                url: _url,
                                type: "DELETE",
                                success: function (response) {
                                    if (response.code == 200) {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Deleted!',
                                            text: 'Your profile has been deleted.',
                                            customClass: {
                                            confirmButton: 'btn btn-success'
                                            }
                                        }).then(function(result){
                                            location.href = "/auth/logout";
                                        })
                                        
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: '',
                                            text: 'Something went wrong. Please try again later',
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
                                        text: 'Something went wrong. Please try again later',
                                        customClass: {
                                        confirmButton: 'btn btn-success'
                                        }
                                    })
                                },
                            });
                            
                        } else if (result.dismiss === Swal.DismissReason.cancel) {
                            Swal.fire({
                                title: 'Cancelled',
                                text: 'Deactivation Cancelled!!',
                                icon: 'error',
                                customClass: {
                                confirmButton: 'btn btn-success'
                                }
                            });
                        }
                    });
                }
            };
        }

        // CleaveJS validation

        const phoneNumber = document.querySelector('#phoneNumber'),
        zipCode = document.querySelector('#zipCode');
        // Phone Mask
        if (phoneNumber) {
            new Cleave(phoneNumber, {
                phone: true,
                phoneRegionCode: 'US'
            });
        }

        // Pincode
        if (zipCode) {
            new Cleave(zipCode, {
                delimiter: '',
                numeral: true
            });
        }

        // Update/reset user image of account page
        let accountUserImage = document.getElementById('uploadedAvatar');
        const fileInput = document.querySelector('.account-file-input'),
        resetFileInput = document.querySelector('.account-image-reset');

        if (accountUserImage) {
            const resetImage = accountUserImage.src;
            fileInput.onchange = () => {
                if (fileInput.files[0]) {
                    accountUserImage.src = window.URL.createObjectURL(fileInput.files[0]);
                }
            };
            resetFileInput.onclick = () => {
                fileInput.value = '';
                accountUserImage.src = resetImage;
            };
        }
    })();
});
 
const reset_country = $("#country").val();
const reset_timezone = $("#timezone").val();

$(function () {
    
    $('.select2').select2();
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    $("#formAccountSettings").on("submit", function(e){
        e.preventDefault();
        
        let data = new FormData();
        
        data.append('first_name', $("#firstName").val());
        data.append('last_name', $("#lastName").val());
        data.append('organization', $("#organization").val());
        data.append('phone', $("#phoneNumber").val());
        data.append('address', $("#address").val());
        data.append('state', $("#state").val());
        data.append('zip_code', $("#zipCode").val());
        data.append('timezone', $("#timezone").val());
        data.append('country', $("#country").val());

        if($('#upload')[0].files[0]){
            data.append('file', $('#upload')[0].files[0]);
            var imgname  =  $('#upload').val();
            var size  =  $('#upload')[0].files[0].size;

            var ext =  imgname.substr( (imgname.lastIndexOf('.') +1) );
            if(ext != 'jpg' && ext != 'jpeg' && ext != 'png' && ext != 'gif' && ext != 'PNG' && ext != 'JPG' && ext != 'JPEG')
            {
                fileInput.value = '';
                accountUserImage.src = resetImage;
                $("#img-warning").css("color", "red");
                return;
            }
            if(size > 800000) { 
                fileInput.value = '';
                accountUserImage.src = resetImage;
                $("#img-warning").css("color", "red");
                return;
            }
        }
        
        $("#submit_btn").attr("disabled", true);

        $("#img-warning").css("color", "#a1acb8 !important");
        let _url = "/profile";
        $.ajax({
            url: _url,
            type: "POST",
            data: data,
            enctype: 'multipart/form-data',
            processData: false,  // tell jQuery not to process the data
            contentType: false   // tell jQuery not to set contentType
        }).done(function(data) {
            $("#submit_btn").removeAttr("disabled");
            if(data.code == 200) {
                Swal.fire({
                    icon: 'success',
                    title: 'Updated!',
                    text: '',
                    customClass: {
                        confirmButton: 'btn btn-success'
                    }
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: 'Somethig went wrong!',
                    icon: 'error',
                    customClass: {
                        confirmButton: 'btn btn-success'
                    }
                });
            }
        });
       
    })

    $("#reset_btn").on("click", function(){
        $('#country').val(reset_country).select2();
        $('#timezone').val(reset_timezone).select2();
    })
 });
 