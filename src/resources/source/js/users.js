$(function () {
    $('table.users.elements > tbody > tr > td.remove:not(.disabled)').click(function () {
        let parent = $(this).parent('tr');
        let url = parent.data('delete-url');
        let name = parent.data('name');
        let html = 'Удалить пользователя &laquo;' + name + '&raquo;?';

        $('.confirm .remove').data('url', url);

        $.confirm(html);
    });

    $('.confirm .remove').click(function () {
        let url = $(this).data('url');

        $.confirmClose();
        $.blockUI();

        $.ajax({
            url: url,
            method: "DELETE"
        }).done(function (response) {
            $.unblockUI();

            /** @param response.deleted */
            if (response.deleted) {
                $('table.users.elements > tbody > tr[data-user="' + response.deleted + '"]').remove();
            }
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

    $('form[data-save="true"]').submit(function () {
        let form = $(this);

        $('span.error').fadeOut(200);
        $.blockUI();

        $.ajax({
            url: this.action,
            method: "POST",
            data: new FormData($(this)[0]),
            contentType: false,
            processData: false
        }).done(function (response) {
            $.unblockUI();

            if (response.added && response.redirect_url) {
                location.href = response.redirect_url;
            } else if (response.saved) {
                let login = form.find('input[name="login"]').val();
                $('.path > .part > span').html(login);
            }
        }).fail(function (response) {
            $.unblockUI();

            if (response.responseJSON.error) {
                $.alert(response.responseJSON.error);
            } else if (response.responseJSON.errors) {
                Object.keys(response.responseJSON.errors).forEach(field => {
                    $('span.error[data-name="' + field + '"]').html(response.responseJSON.errors[field])
                        .fadeIn(200);
                });
            } else {
                $.alert(response.statusText);
            }
        });

        return false;
    });
});
