const startMenu = $('.start-menu');
const extendMenu = $('.extend-menu');
const modalCookie = $('#modal-cookie');

if (modalCookie.length) {
    $('body').toggleClass('cookies');

    $('.btn-settings').on('click', function () {
        startMenu.hide();
        extendMenu.show();
    });

    $('.btn-return').on('click', function () {
        extendMenu.hide();
        startMenu.show();
    });

    $('.styles_toggle__pDaEI').on('click', function () {
        $(this).toggleClass('styles_checked__25I5K');
    });

    $(document).on('click', '#cookies_cancel', function () {
        $('body').removeClass('cookies');
        modalCookie.remove();
    });

    $(document).on('click', '#cookies_add', function () {
        let date = new Date(Date.now());
        date.setMonth(date.getMonth() + 1);
        date = date.toUTCString();
    
        $('button.AgreementCookie_toogle__2AxAj').each(function () {
            let nameCookie = 'co_Necessary';
    
            // if ($(this).hasClass('styles_checked__25I5K')) {
                $.ajax({
                    url: '/set-cookie-site',
                    type: 'POST',
                    data: {
                        name: nameCookie,
                        value: 1,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        console.log(response.message);
                    }
                });
            // }
        });
    
        $('body').removeClass('cookies');
        $('#modal-cookie').remove();
    });
    
}
