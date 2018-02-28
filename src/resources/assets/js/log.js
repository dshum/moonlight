$(function() {
    $('[name="dateFrom"]').calendar({
        dateFormat: '%Y-%m-%d',
        selectHandler: function() {
            $('[name="dateFrom"]').val(this.date.print(this.dateFormat));
        }
    });

    $('[name="dateTo"]').calendar({
        dateFormat: '%Y-%m-%d',
        selectHandler: function() {
            $('[name="dateTo"]').val(this.date.print(this.dateFormat));
        }
    });

    $('.reset').click(function() {
        $('[name="dateFrom"]').val(null);
        $('[name="dateTo"]').val(null);
    });

    $('body').on('click', '.next', function() {
        var next = $(this);
        var page = next.attr('page');
        var comments = $('[name="comments"]').val();
        var user = $('[name="user"]').val();
        var type = $('[name="type"]').val();
        var dateFrom = $('[name="dateFrom"]').val();
        var dateTo = $('[name="dateTo"]').val();

        next.addClass('waiting');

        $.blockUI();

        $.getJSON('/moonlight/log/next', {
            comments: comments,
            user: user,
            type: type,
            dateFrom: dateFrom,
            dateTo: dateTo,
            page: page
        }, function(data) {
            $.unblockUI();

            next.remove();

            if (data.html) {
                $('.list-container').append(data.html);
            }
        }).fail(function() {
            $.unblockUI();
            $.alertDefaultError();
        });
    });
});