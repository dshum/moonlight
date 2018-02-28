jQuery.expr[':'].contains = function(a, i, m) {
    return jQuery(a).text().toUpperCase().indexOf(m[3].toUpperCase()) >= 0;
};

$(function() {
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
    
    $('.button.enabled.restore').click(function() {
        $.confirm(null, '#restore');
    });

    $('.button.enabled.delete').click(function() {
        $.confirm(null, '#delete');
    });

    $('.confirm .restore').click(function() {
        var url = $(this).attr('url');

        if (! url) return false;

        $.confirmClose('#restore');
        $.blockUI();

        $.post(
            url,
            {},
            function(data) {
                $.unblockUI(function() {
                    if (data.error) {
                        $.alert(data.error);
                    } else if (data.restored && data.url) {
                        location.href = data.url;
                    }
                });
            }
        );
    });

    $('.confirm .remove').click(function() {
        var url = $(this).attr('url');

        if (! url) return false;

        $.confirmClose('#delete');
        $.blockUI();

        $.post(
            url,
            {},
            function(data) {
                $.unblockUI(function() {
                    if (data.error) {
                        $.alert(data.error);
                    } else if (data.deleted && data.url) {
                        location.href = data.url;
                    }
                });
            }
        );
    });

    $('div.item').fadeIn(200);
});