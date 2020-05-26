$(function () {
    $('tr[data-user] > td.remove:not(.disabled)').click(function () {
        var url = $(this).data('url');
        var name = $(this).data('name');
        var html = 'Удалить пользователя &laquo;' + name + '&raquo;?';

        $('.confirm .remove').data('url', url);

        $.confirm(html);
    });

    $('.confirm .remove').click(function () {
        var url = $(this).data('url');

        if (! url) return false;

        $.confirmClose();
        $.blockUI();

        $.post(url, {}, function (response) {
            $.unblockUI();

            if (response.error) {
                $.alert(response.error);
            } else if (response.user) {
                $('tr[data-user="' + response.user + '"]').remove();
            }
        });
    });
});
