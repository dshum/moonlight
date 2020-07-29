jQuery.expr[':'].contains = function (a, i, m) {
    return jQuery(a).text().toUpperCase().indexOf(m[3].toUpperCase()) >= 0;
};

$(function () {
    $('#filter').on('keyup change', function () {
        let str = $(this).val();
        let contains = $('table.permissions.elements > tbody > tr:contains("' + str + '")');
        let not_contains = $('table.permissions.elements > tbody > tr:not(:contains("' + str + '"))');

        if (str.length > 0) {
            not_contains.hide();
            contains.show();

            if (contains.length > 0) {
                $('table.permissions.elements').fadeIn(200);
            } else {
                $('table.permissions.elements').fadeOut(200);
            }
        } else {
            $('table.permissions.elements').fadeIn(200);
            $('table.permissions.elements tbody tr').show();
        }
    });

    $('table.permissions.elements > tbody > tr > td[data-permission]').click(function () {
        if ($(this).hasClass('active')) return false;

        let self = $(this);
        let parent = self.parent('tr');

        $.blockUI();

        $.ajax({
            url: parent.data('url'),
            method: 'PUT',
            data: {
                permission: self.data('permission')
            }
        }).done(function () {
            $.unblockUI();

            parent.find('td').removeClass('active');
            self.addClass('active');
        }).fail(function (response) {
            $.unblockUI();

            /** @param response.responseJSON */
            if (response.responseJSON.error) {
                $.alert(response.responseJSON.error);
            } else {
                $.alert(response.statusText);
            }
        });
    });
});
