$(function () {
    $('input[name="txtPhone"]').focus(function () {
        if($(this).val() === '') {
            $(this).val('+91-');
        }
    }).blur(function () {
        if($(this).val() === ''){
            $(this).val('');
        }
    });

    jQuery.validator.addMethod(
        "regex_phone",
        function(value, element, regexp) {
            if (regexp.constructor != RegExp)
                regexp = new RegExp(regexp);
            else if (regexp.global)
                regexp.lastIndex = 0;
            return this.optional(element) || regexp.test(value);
        },"Your mobile invalid"
    );

    jQuery.validator.addMethod(
        "regex_email",
        function(value, element, regexp) {
            if (regexp.constructor != RegExp)
                regexp = new RegExp(regexp);
            else if (regexp.global)
                regexp.lastIndex = 0;
            return this.optional(element) || regexp.test(value);
        },"Your email invalid"
    );

    $('#customerEmail').validate({
        rules: {
            txtName: 'required',
            txtPhone: {
                required: true,
                regex_phone: /^(?:(?:\+|0{0,2})91(\s*[\ -]\s*)?|[0]?)?[789]\d{9}|(\d[ -]?){10}\d$/gm
            },
            txtEmail: {
                required: true,
                regex_email: /^([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?).+$/
            },
            tctQuestion: 'required'
        },
        messages: {
            txtName: 'Please enter your name',
            txtPhone: {
                required: 'Please enter your mobile',
                regex_phone: 'Your mobile invalid'
            },
            txtEmail: {
                required: 'Please enter your email',
                regex_email: 'Your email invalid'
            },
            tctQuestion: 'Please enter type your questions'
        }
    });
});