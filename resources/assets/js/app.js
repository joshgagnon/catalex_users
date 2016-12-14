$('.service-well')
    .on('click', 'input, a', function(e){
        e.stopPropagation();
        return true;
    })
    .on('click', function(e){
        $(this).find('input').click();
    });




window.submitCCForm = function() {
    $('form.payment-form').submit();
}