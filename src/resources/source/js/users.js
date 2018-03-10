$(function() {
    $('tr[user] > td.remove:not(.disabled)').click(function() {
        var url = $(this).attr('url');
        var name = $(this).attr('name');
        var html = 'Удалить пользователя &laquo;' + name + '&raquo;?';

        $('.confirm .remove').attr('url', url);
        
        $.confirm(html);
    });

    $('.confirm .remove').click(function() {
        var url = $(this).attr('url');

        if (! url) return false;

        $.confirmClose();
        $.blockUI();

        $.post(
            url,
            {},
            function(data) {
                $.unblockUI();

                if (data.error) {
                    $.alert(data.error);
                } else if (data.user) {
                    $('tr[user="' + data.user + '"]').remove();
                }
            }
        );
    });
});