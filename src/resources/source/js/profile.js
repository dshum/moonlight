$(function() {
    $('body').on('change', ':file', function(e) {
        var name = $(this).attr('name');
        var path = e.target.files[0] ? e.target.files[0].name : 'Выберите файл';

        $('.file[name="' + name + '"]').html(path);
        $('[name="drop"]').prop('checked', false);
    });

    $('body').on('click', '.file[name]', function() {
        var name = $(this).attr('name');
        var fileInput = $(':file[name="' + name + '"]');

        fileInput.click();
    });

    $('body').on('click', '.reset', function() {
        var name = $(this).attr('name');

        $('[name="drop"]').prop('checked', false);
        $('.file[name="' + name + '"]').html('Выберите файл');
        $(':file[name="' + name + '"]').val('');
    });

    $('form').submit(function() {
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
                    $('span.error[name="' + field + '"]')
                        .html(response.errors[field])
                        .fadeIn(200);
                }
            } else if (response.saved) {
                var html = response.photo
                    ? '<img src="' + response.photo + '" />'
                    : '';

                $('#photo-container').html(html);
                $('.reset').click();
            }
        }).fail(function (response) {
            $.unblockUI();
            $.alert(response.statusText);
        });

        return false;
    });
});
