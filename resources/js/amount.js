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