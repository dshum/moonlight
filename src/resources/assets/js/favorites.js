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
        handle: 'span.title',
        chosenClass: 'chosen',
        dragClass: 'dragging',
        onEnd: function (event) {
            if (event.newIndex === event.oldIndex) return false;

            var order = [];

            $(event.to).find('.elements[rubric]').each(function() {
                var rubric = $(this).attr('rubric');

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
        handle: 'span.element',
        chosenClass: 'chosen',
        dragClass: 'dragging',
        onEnd: function (event) {
            if (event.newIndex === event.oldIndex) return false;

            var order = [];

            $(event.to).find('li').each(function() {
                var favorite = $(this).attr('favorite');

                order.push(favorite);
            });

            $.blockUI();

            $.post('/moonlight/favorites/order/favorites', {
                order: order
            }, function(data) {
                $.unblockUI();
            });
        }
    });

    $('body').on('click', 'span.enabled[rubric]', function() {
        var block = $(this).parents('.elements');
        var rubric = $(this).attr('rubric');

        $.blockUI();

        $.post('/moonlight/favorites/delete/rubric', {
            rubric: rubric
        }, function(data) {
            $.unblockUI();

            if (data.error) {
                $.alert(data.error);
            } else if (data.deleted) {
                block.fadeOut(200).remove();
            }
        });
    });

    $('span.enabled[favorite]').click(function() {
        var li = $(this).parents('li');
        var ul = $(this).parents('ul');
        var block = $(this).parents('.elements');
        var favorite = $(this).attr('favorite');

        $.blockUI();

        $.post('/moonlight/favorites/delete/favorite', {
            favorite: favorite
        }, function(data) {
            $.unblockUI();

            if (data.error) {
                $.alert(data.error);
            } else if (data.deleted) {
                li.fadeOut(200).remove();

                if (! ul.find('li').length) {
                    block.find('span[rubric]').addClass('enabled');
                }
            }
        });
    });
});