$(function() {
    $('form').submit(function() {
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
                $('div.error').html(response.error).fadeIn(200);
            } else if (response.url) {
                location.href = response.url;
            }
        }).fail(function (response) {
            $.unblockUI();
            $.alert(response.statusText);
        });

        return false;
    });
});
