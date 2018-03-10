$(function() {
    $('body').on('click', '.main .elements span.open', function() {
        var span = $(this);
        var li = span.parents('li').first();
        var rubric = span.attr('rubric');
        var bind = span.attr('bind');
        var classId = span.attr('classId');
        var display = span.attr('display');

        if (display == 'show') {
            $('.main .elements ul[node="' + classId + '"]').slideUp(200);

            span.attr('display', 'hide');

            $.post('/moonlight/rubrics/node/close', {
                rubric: rubric,
                classId: classId
            });
            
        } else if (display == 'hide') {
            $('.main .elements ul[node="' + classId + '"]').slideDown(200);

            span.attr('display', 'show');

            $.post('/moonlight/rubrics/node/open', {
                rubric: rubric,
                classId: classId
            });
        } else {
            $.blockUI();

            $.getJSON('/moonlight/rubrics/node/get', {
                rubric: rubric,
                bind: bind,
                classId: classId
            }, function(data) {
                $.unblockUI();

                if (data.html) {
                    $(data.html).hide().appendTo(li).slideDown(200);

                    span.attr('display', 'show');
                }
            });
        }
    });
});