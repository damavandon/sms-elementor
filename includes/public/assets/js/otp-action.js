jQuery(document).ready(function ($) {
    $('button[type="submit"').on('click', function (event) {

        if (!$('#payamito_div_otp').length) {

            payamito_el_send();
        }
    });
    $(document).on('click', '#payamito_el_resend', function (event) {

        if ($('#payamito_div_otp').length) {

            payamito_el_send();
        }
    });
    function payamito_el_send() {

        let form_id = $('button[type="submit"').closest("form")[0][1].value;
        let post_id = $('button[type="submit"').closest("form")[0][0].value;
        if (PAYAMITO_EL_OTP_ACTION.form_id === form_id) {

            let field = $("#form-field-field_" + PAYAMITO_EL_OTP_ACTION.field_id).val();
            $.post({
                url: PAYAMITO_EL_OTP_ACTION.ajaxUrl,
                type: 'POST',
                data: {
                    'action': "payamito_el_verification",
                    'nonce': PAYAMITO_EL_OTP_ACTION.nonce,
                    'post_id': post_id,
                    "phone_number": field,
                    'form_id': PAYAMITO_EL_OTP_ACTION.form_id,

                }
            }).done(function (response, s) {
                if (response.error === false || response.show_otp === true) {

                    if (!$('#payamito_div_otp').length) {
                        payamito_el_create_element();
                    }
                    if (response.show_otp !== true) {
                        payamito_el_resend_time();
                    }

                }
            }).fail(function () {

            })
                .always(function (r, s) {

                });
        }
    }

    function payamito_el_create_element() {
        $(".elementor-field-group-field_" + PAYAMITO_EL_OTP_ACTION.field_id).after('<div id="payamito_div_otp" class= elementor-field-group-field_payamito_div_otp elementor-field-group elementor-column  elementor-col-100"> <label for="payamito_el_otp_input" class="elementor-field-label">' + PAYAMITO_EL_OTP_ACTION.otp_text + '</label><input type="number" name="payamito_el_otp_input" id="payamito_el_otp_input" class="elementor-field elementor-size-sm  elementor-field-textual" placeholder="' + PAYAMITO_EL_OTP_ACTION.otp_text + '" aria-invalid="false"> <button id="payamito_el_resend" type="button" style="margin: 1% 0px ; width:30%" class="elementor-button elementor-size-sm" aria-invalid="false">' + PAYAMITO_EL_OTP_ACTION.resend_text + '</button></div>')
    }

    function payamito_el_resend_time() {

        var timer = PAYAMITO_EL_OTP_ACTION.otp_resend_time;
        var innerhtml = $("#payamito_el_resend").html()
        $("#payamito_el_resend").prop('disabled', true);
        var Interval = setInterval(function () {

            seconds = parseInt(timer);
            seconds = seconds < 10 ? "0" + seconds : seconds;
            $("#payamito_el_resend").html(seconds + ":" + PAYAMITO_EL_OTP_ACTION.otp_second_text)
            if (--timer <= 0) {
                timer = 0;
                $("#payamito_el_resend").removeAttr('disabled');
                $("#payamito_el_resend").html(innerhtml);
                clearInterval(Interval);
            }
        }, 1000);
    }
    $(document).on('submit_success', function(){
		$("#payamito_div_otp").remove();
	});
})

