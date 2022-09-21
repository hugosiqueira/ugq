$(function () {
    $('.wc_mobile_nav').click(function () {
        $('.wc_imobi_nav').slideToggle();
    });

    $('.jwc_select').click(function () {
        if (!$(this).hasClass('active')) {
            $('.jwc_select').removeClass('active');
            $(this).addClass('active');

            var image = $(this).find('img').attr('src');
            $('.jwc_target').fadeTo(300, 0.5, function () {
                $(this).attr('src', image).fadeTo(300, 1);
            });
        }
    });
});