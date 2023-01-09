$(function () {

    loadCurrency();
  

    $(".currency-item").on('click', function(){
        const selected_currency = $(this).attr("data-currency");
        const fl_class = $(this).attr("data-icon-class");
        
        const saved_currency = localStorage.getItem('currency');

        if(saved_currency == selected_currency)
            return;
        
        localStorage.setItem('currency', selected_currency);
        localStorage.setItem('currency_flag', fl_class);
        $("#selected-currency").attr("data-currency", selected_currency);
        $("#selected-currency").html("<i class='fi " + fl_class + " fis rounded-circle fs-3 me-1'></i><span class='align-middle'>" + selected_currency + "</span>");

        // might be reload
        location.reload();
    })
})

function loadCurrency() {
    const currency = localStorage.getItem('currency');
    const flag_class = localStorage.getItem('currency_flag');

    if(!currency){
        // use default currency - USD
        localStorage.setItem('currency', 'USD');
        localStorage.setItem('currency_flag', 'fi-us');

        $("#selected-currency").attr("data-currency", 'USD');
        $("#selected-currency").html("<i class='fi fi-us fis rounded-circle fs-3 me-1'></i> <span class='align-middle'>USD</span>");
        return;
    } 
    
    $("#selected-currency").attr("data-currency", currency);
    $("#selected-currency").html("<i class='fi " + flag_class + " fis rounded-circle fs-3 me-1'></i> <span class='align-middle'>" + currency + "</span>");
}