jQuery.expr[':'].contains = function (a, i, m) {
    return jQuery(a).text().toUpperCase().indexOf(m[3].toUpperCase()) >= 0;
};

$(function () {
    $('#filter').keyup(function () {
        var str = $(this).val();

        if (str.length > 0) {
            $('ul.items > li:not(:contains("' + str + '"))').hide();
            $('ul.items > li:contains("' + str + '")').show();
        } else {
            $('ul.items > li').show();
        }
    }).change(function () {
        var str = $(this).val();

        if (str.length > 0) {
            $('ul.items > li:not(:contains("' + str + '"))').hide();
            $('ul.items > li:contains("' + str + '")').show();
        } else {
            $('ul.items > li').show();
        }
    });

    $('.button.enabled.restore').click(function () {
        $.confirm(null, '.confirm[data-confirm-type="restore"]');
    });

    $('.button.enabled.delete').click(function () {
        $.confirm(null, '.confirm[data-confirm-type="delete"]');
    });

    $('.confirm .restore, .confirm .remove').click(function () {
        var confirmContainer = $(this).parents('.confirm');
        var url = confirmContainer.data('url');

        $.confirmClose(confirmContainer);
        $.blockUI();

        $.post(url, {}, function (response) {
            $.unblockUI(function () {
                if (response.error) {
                    $.alert(response.error);
                } else if (response.url) {
                    location.href = response.url;
                }
            });
        });
    });

    $('div.item').fadeIn(200);
});
