$(function() {
    $('form[data-save="true"]').submit(function() {
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

            if (response.error) {
                $.alert(response.error);
            } else if (response.errors) {
                for (var field in response.errors) {
                    $('span.error[data-name="' + field + '"]')
                        .html(response.errors[field])
                        .fadeIn(200);
                }
            } else if (response.added) {
                var backUrl = $('input[name="back"]').val();
                location.href = backUrl;
            } else if (response.saved) {
                var name = $('input[name="login"]').val();
                $('.path > .part > span').html(name);
            }
        }).fail(function (response) {
            $.unblockUI();
            $.alert(response.statusText);
        });

        return false;
    });
});
