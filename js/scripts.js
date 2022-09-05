jQuery(document).ready(function($) {
    $(".bdc_div").on('click', function(){
        $(this).parent().children('.bdc_details').toggle();
    });
});