$(document).ready(function () {
    var $page = $('.authentication');
    var timer;
    var resendSeconds = 60;
    var formTimer;
    var blockFormSeconds = 60;
    var $verifyNumber = $('.verifyNumber');
    $phone = $('input#phoneNumber', $page);
    $phone.intlTelInput({
        nationalMode: false,
        initialCountry: "auto",
        utilsScript: SITE_URL + "/assets/js/utils.js",
        geoIpLookup: function(callback) {
            $.ajax({
                url: "https://api.ipregistry.co/?key=zptkchdsad924t",
                type: "get",
                dataType: "json",
                headers: {
                    'accept': 'application/json',
                    'Access-Control-Allow-Origin': '*',
                    'X-Content-Type-Options': 'nosniff',
                },
                success: function (data) {
                    $('input#country').val(data.location.country.code);
                    callback(data.location.country.code)
                }
            })
        }
    });

    $phone.on("countrychange", GetCountryCode);

    function GetCountryCode() {
        var country = $("input#phoneNumber").intlTelInput("getSelectedCountryData");
        $('input#country').val(country.iso2);
    };

    $('form.auth').on('submit', function(e) {
        e.preventDefault();
        $('.error-message').addClass('d-none');
        let $form = $(this);
        let $button = $('button', $form);
        let route = $form.attr('action');
        $button.attr('disabled', true);
        $('#emailError').html('');
        $('#phoneError').html('');
        $('#passwordError').html('');
        $.ajax({
            url: route,
            type: "POST",
            dataType: "json",
            data: $form.serialize(),
            success: function(response, status) {
                if ('status' in response && response.status !== 'success') {
                    return;
                }
                $button.attr('disabled', false)
                $verifyNumber.modal('show');
                $('input#userId', $verifyNumber).val(response.data.user_id)
                if (response.data.message) {
                    alert(response.data.message.content);
                }
                $('button.resend-code').attr('data-id', response.data.user_id)
                if (response.data.start_counter) {
                    timer = window.setInterval(function() {
                        resendCodeActivation();
                    }, 1000);
                    $('button.resend-code').attr('disabled', true)
                }
            },
            error: function( response) {
                let data = response.responseJSON
                for (let key in data.errors) {
                    $('#' + key + 'Error').html(data.errors[key][0])
                }
                $button.attr('disabled', false)
                if (data.status === 'failed' && data.message.length) {
                    $('.error-message p').text(data.message);
                    $('.error-message').removeClass('d-none')
                }
            }
        })
    })

    $('body').on('submit', 'form.confirmPhone', function(e) {
        e.preventDefault();
        hideModalMessages();
        $('#codeError').html('');
        let $form = $(this);
        let route = $form.attr('action');
        $('button.verify', $form).attr('disabled', true);
        $.ajax({
            url: route,
            type: "POST",
            dataType: "json",
            data: $form.serialize(),
            success: function(response, status) {
                $('button.verify', $form).attr('disabled', false);
                if ('status' in response && response.status !== 'success') {
                    return
                }

                if (response.message) {
                    $('.alert-' + response.message.type, $verifyNumber).removeClass('d-none');
                    $('.alert-' + response.message.type + ' p', $verifyNumber).text(response.message.content);
                }
                if (response.block_form) {
                    formTimer = window.setInterval(function() {
                        disableFormTimer();
                    }, 1000);
                    $('button.verify', $form).attr('disabled', true);
                }
                if (typeof response.errors.code !== undefined) {
                    $('#codeError').html(response.errors.code);
                }
            },
            error: function(response) {
                $('button.verify', $form).attr('disabled', false);
                let data = response.responseJSON
                for (let key in data.errors) {
                    $('#' + key + 'Error').html(data.errors[key][0])
                }
                if (data.status === 'failed' && data.message.length) {
                    $('.alert-danger p', $verifyNumber).text(data.message);
                    $('.alert-danger', $verifyNumber).removeClass('d-none');
                }
            }
        })
    });

    $('body').on('click', 'button.resend-code', function(e) {
        e.preventDefault();
        let userId = $(this).data('id');
        let route = $(this).data('route');
        $(this).attr('disabled', true);
        $.ajax({
            url: route,
            type: "POST",
            dataType: "json",
            data: {
                user_id: userId,
                _token: $('meta[name=csrf-token]').attr('content')
            },
            success: function(response, status) {
                if ('status' in response && response.status !== 'success') {
                    return;
                }
                if (response.data.start_counter) {
                    seconds = 60
                    timer = window.setInterval(function() {
                        resendCodeActivation();
                    }, 1000);
                    $(this).attr('disabled', true)
                }
                if (response.data.message) {
                    alert(response.data.message.content);
                }
            },
            error: function(response) {
                if (response.message) {
                    alert(response.message);
                }
                $('button.resend-code').attr('disabled', false);
            }
        })
    })

    function disableFormTimer() {
        if(blockFormSeconds <= 60) {
            $('button.verify').text('Submit (' + blockFormSeconds + ' seconds)')
          }
          if (blockFormSeconds > 0 ) {
             blockFormSeconds--;
          } else {
             clearInterval(formTimer);
             $('button.verify').text('Submit');
             $('button.verify').attr('disabled', false);
             $.ajax({
                url: SITE_URL + '/attempts',
                type: "POST",
                dataType: "json",
                data: {
                    user_id: $('input#userId').val(),
                    _token: $('meta[name=csrf-token]').attr('content')
                },
                success: function(data) {
                    hideModalMessages();
                    blockFormSeconds = 60
                },
                error: function(data) {

                }
             })
          }
    }

    function resendCodeActivation() {
        if(resendSeconds <= 60) {
          $('button.resend-code').text('Resend Code (' + resendSeconds + ' seconds)')
        }
        if (resendSeconds >0 ) {
           resendSeconds--;
        } else {
           clearInterval(timer);
           resendSeconds = 60;
           $('button.resend-code').attr('disabled', false);
           $('button.resend-code').text('Resend Code');
        }
    }

    function hideModalMessages() {
        $('.verifyNumber div.alert-message').addClass('d-none');
    }

})