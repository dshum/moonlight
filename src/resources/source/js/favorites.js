$(function() {
    if (typeof jQuery !== 'undefined') {
        jQuery.fn.sortable = function (options) {
            return this.each(function () {
                var $el = $(this);
                var sortable = $el.data('sortable');

                if (! sortable && options instanceof Object) {
                    sortable = new Sortable(this, options);
                    $el.data('sortable', sortable);
                }

                if (sortable && (options in sortable)) {
                    sortable[sortable].apply(sortable, [].slice.call(arguments, 1));
                }
            })
        };
    }

    $('.favorites').sortable({
        handle: '.title',
        chosenClass: 'chosen',
        dragClass: 'dragging',
        onEnd: function (event) {
            if (event.newIndex === event.oldIndex) return false;

            var order = [];

            $(event.to).find('.elements[data-rubric]').each(function() {
                var rubric = $(this).data('rubric');

                order.push(rubric);
            });

            $.blockUI();

            $.post('/moonlight/favorites/order/rubrics', {
                order: order
            }, function(data) {
                $.unblockUI();
            });
        }
    });

    $('.elements ul').sortable({
        handle: '.element',
        chosenClass: 'chosen',
        dragClass: 'dragging',
        onEnd: function (event) {
            if (event.newIndex === event.oldIndex) return false;

            var order = [];

            $(event.to).find('li').each(function() {
                var favorite = $(this).data('favorite');

                order.push(favorite);
            });

            $.blockUI();

            $.post('/moonlight/favorites/order/favorites', {
                order: order
            }, function() {
                $.unblockUI();
            });
        }
    });

    $('body').on('click', '.elements[data-rubric] > .h2 > .remove.enabled', function() {
        var block = $(this).parents('.elements');
        var rubric = block.data('rubric');

        $.blockUI();

        $.post('/moonlight/favorites/delete/rubric', {
            rubric: rubric
        }, function(response) {
            $.unblockUI();

            if (response.error) {
                $.alert(response.error);
            } else if (response.deleted) {
                block.fadeOut(200).remove();
            }
        });
    });

    $('li[data-favorite] > .remove.enabled').click(function() {
        var li = $(this).parents('li');
        var ul = $(this).parents('ul');
        var block = $(this).parents('.elements');
        var favorite = li.data('favorite');

        $.blockUI();

        $.post('/moonlight/favorites/delete/favorite', {
            favorite: favorite
        }, function(response) {
            $.unblockUI();

            if (response.error) {
                $.alert(response.error);
            } else if (response.deleted) {
                li.fadeOut(200).remove();

                if (! ul.find('li').length) {
                    block.find('.remove').addClass('enabled');
                }
            }
        });
    });
});
