jQuery.expr[':'].contains = function(a, i, m) {
    return jQuery(a).text().toUpperCase().indexOf(m[3].toUpperCase()) >= 0;
};

$(function() {
    $('#filter').keyup(function () {
        var str = $(this).val();

        if (str.length > 0) {
            $('table.permissions.elements tbody tr:not(:contains("' + str + '"))').hide();
            $('table.permissions.elements tbody tr:contains("' + str + '")').show();

            if ($('table.permissions.elements tbody tr:contains("' + str + '")').length > 0) {
                $('table.permissions.elements').fadeIn(200);
            } else {
                $('table.permissions.elements').fadeOut(200);
            }
        } else {
            $('table.permissions.elements').fadeIn(200);
            $('table.permissions.elements tbody tr').show();
        }
    }).change(function () {
        var str = $(this).val();

        if (str.length > 0) {
            $('table.permissions.elements tbody tr:not(:contains("' + str + '"))').hide();
            $('table.permissions.elements tbody tr:contains("' + str + '")').show();

            if ($('table.permissions.elements tbody tr:contains("' + str + '")').length > 0) {
                $('table.permissions.elements').fadeIn(200);
            } else {
                $('table.permissions.elements').fadeOut(200);
            }
        } else {
            $('table.permissions.elements').fadeIn(200);
            $('table.permissions.elements tbody tr').show();
        }
    });

    $('table.permissions.elements tbody tr td[permission]').click(function() {
        if ($(this).hasClass('active')) return false;

        var url = $('input[name="url"]').val();
        var group = $('input[name="group"]').val();
        var item = $(this).parent('tr').attr('item');
        var permission = $(this).attr('permission');

        $.blockUI();

        $.post(
            url,
            {
                item: item,
                permission: permission
            },
            function(data) {
                $.unblockUI();

                if (data.error) {
                    $.alert(data.error);
                } else {
                    $('table.permissions.elements tbody tr[item="' + item + '"] td[permission]').removeClass('active');
                    $('table.permissions.elements tbody tr[item="' + item + '"] td[permission="' + permission + '"]').addClass('active');
                }
            }
        );
    });
});