$(function() {
    $('form').submit(function() {
        $('div.error').fadeOut(200);
        $('div.ok').fadeOut(200);
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
            } else if (response.ok) {
                $('div.ok').html(response.ok).fadeIn(200);
            }
        }).fail(function (response) {
            $.unblockUI();
            $.alert(response.statusText);
        });

        return false;
    });
});
