    function formValidity(formId) {
        const form = document.getElementById(formId);
        let response = true;
        if (form.checkValidity()) {
            event.preventDefault();
        } else {
            form.reportValidity();
            response = false;
        }
        return response;
    }
    $(".amount").each(function() {
        let amount        = $(this).val();
        let amountNumeric = Number(amount.replace(/[^-0-9\.]+/g,""));
        amountNumeric     = parseFloat(amountNumeric).toFixed(2);
        $(this).val(amountNumeric);
        $(this).attr("value",amountNumeric);
        $(this).select();
    });
    $( ".amount" ).keyup(function() {
        let amount       = $(this).val();
        amount           = (!isNaN(parseFloat(amount)) && isFinite(amount)) ? amount : 0;
        amount           = parseFloat(amount).toFixed(2);
        $(this).attr("value",amount);
    });

    $(".amount").focus(function() {
        let amount        = $(this).val();
        let amountNumeric = Number(amount.replace(/[^-0-9\.]+/g,""));
        amountNumeric     = parseFloat(amountNumeric).toFixed(2);
        $(this).val(amountNumeric);
        $(this).select();
    });
    $(".amount").focusout(function() {
        let amount = $(this).val();
        amount = (!isNaN(parseFloat(amount)) && isFinite(amount)) ? amount : 0;
        amount = parseFloat(amount).toFixed(2);
        $(this).attr("value",amount);
        $(this).val(formatter.format(amount));
    });


    $(".percentage").each(function() {
        let amount        = $(this).val();
        let amountNumeric = Number(amount.replace(/[^-0-9\.]+/g,""));
        amountNumeric     = parseFloat(amountNumeric).toFixed(2);
        $(this).val(amountNumeric);
        $(this).attr("value",amountNumeric);
        $(this).select();
    });
    $( ".percentage" ).keyup(function() {
        let amount       = $(this).val();
        amount           = (!isNaN(parseFloat(amount)) && isFinite(amount)) ? amount : 0;
        amount           = parseFloat(amount).toFixed(2);
        $(this).attr("value",amount);
    });
    
    $(".percentage").focus(function() {
        let amount        = $(this).val();
        let amountNumeric = Number(amount.replace(/[^-0-9\.]+/g,""));
        amountNumeric     = parseFloat(amountNumeric).toFixed(2);
        $(this).val(amountNumeric);
        $(this).select();
    });
    $(".percentage").focusout(function() {
        let amount = $(this).val();
        amount = (!isNaN(parseFloat(amount)) && isFinite(amount)) ? amount : 0;
        amount = parseFloat(amount).toFixed(2);
        $(this).attr("value",amount);
        $(this).val(`${amount}%`);
    });


    $(".no_space").each(function() {
        let string        = $(this).val();
        let stringWOSpace = string.replace(/\s/g, '');
        $(this).val(stringWOSpace);
    });
    $( ".no_space" ).keyup(function() {
        let string        = $(this).val();
        let stringWOSpace = string.replace(/\s/g, '');
        $(this).val(stringWOSpace);
    });
    $(".no_space").focus(function() {
        let string        = $(this).val();
        let stringWOSpace = string.replace(/\s/g, '');
        $(this).val(stringWOSpace);
    });
    $(".no_space").focusout(function() {
        let string        = $(this).val();
        let stringWOSpace = string.replace(/\s/g, '');
        $(this).val(stringWOSpace);
    });

    $(".lowercase").each(function() {
        let string        = $(this).val();
        let stringWOSpace = string.toLowerCase();
        $(this).val(stringWOSpace);
    });
    $( ".lowercase" ).keyup(function() {
        let string        = $(this).val();
        let stringWOSpace = string.toLowerCase();
        $(this).val(stringWOSpace);
    });
    $(".lowercase").focus(function() {
        let string        = $(this).val();
        let stringWOSpace = string.toLowerCase();
        $(this).val(stringWOSpace);
    });
    $(".lowercase").focusout(function() {
        let string        = $(this).val();
        let stringWOSpace = string.toLowerCase();
        $(this).val(stringWOSpace);
    });