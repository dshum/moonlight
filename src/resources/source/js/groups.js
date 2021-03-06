$(function () {
    $('table.groups.elements > tbody > tr > td.remove:not(.disabled)').click(function () {
        let parent = $(this).parent('tr');
        let url = parent.data('delete-url');
        let name = parent.data('name');
        let html = 'Удалить группу &laquo;' + name + '&raquo;?';

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
                $('table.groups.elements > tbody > tr[data-group="' + response.deleted + '"]').remove();
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
                let name = form.find('input[name="name"]').val();
                $('.path > .part > span').html(name);
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
