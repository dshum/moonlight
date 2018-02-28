jQuery.expr[':'].contains = function(a, i, m) {
    return jQuery(a).text().toUpperCase().indexOf(m[3].toUpperCase()) >= 0;
};

$(function() {
    var checked = {};

    var getElements = function(item, addition = null) {
        var params = {
            item: item
        };

        if (addition) {
            for (var index in addition) {
                params[index] = addition[index];
            }
        }

        $.blockUI();

        $('form[name="trash-form"]').ajaxSubmit({
            url: '/moonlight/trash/list',
            dataType: 'json',
            data: params,
            success: function(data) {
                $.unblockUI();
            
                if (data.html) {
                    $('.list-container').html(data.html);
                }
            },
            error: function(data) {
                $.unblockUI();
                $.alert(data.statusText);
            }
        });
    };

    var submit = function(page) {
        $('input:hidden[name="page"]').val(page);

        $('form[name="trash-form"]').submit();
    };

    $('body').on('keyup change', '#filter', function () {
        var str = $(this).val();

        if (str.length > 0) {
            $('ul.items > li:not(:contains("' + str + '"))').hide();
            $('ul.items > li:contains("' + str + '")').show();
        } else {
            $('ul.items > li').show();
        }
    });

    $('.search-form-links div.link').click(function() {
        var item = $(this).attr('item');
        var name = $(this).attr('name');
        var active = ! $(this).hasClass('active');

        $(this).toggleClass('active');
        $('.search-form-params div.block[name="' + name + '"]').toggleClass('active');

        $.post('/moonlight/search/active/' + item + '/' + name, {
            active: active
        });
    });

    $('.search-form-params div.close').click(function() {
        var item = $(this).attr('item');
        var name = $(this).attr('name');

        $('.search-form-links div.link[name="' + name + '"]').removeClass('active');
        $('.search-form-params div.block[name="' + name + '"]').removeClass('active');

        $.post('/moonlight/search/active/' + item + '/' + name, {
            active: false
        });
    });

    $('.search-form-params input[name].date').calendar({
        dateFormat: '%Y-%m-%d'
    });

    $('.search-form-params input.one').each(function() {
        var parent = $(this).parents('div.row');
        var item = $(this).attr('item');
        var name = $(this).attr('property');

        $(this).autocomplete({
            serviceUrl: '/moonlight/elements/autocomplete',
            params: {
                item: item
            },
            onSelect: function (suggestion) {
                parent.find('input:hidden[name="' + name + '"]').val(suggestion.id);
            },
            minChars: 0
        });
    });

    $('.search-form-params .addition.unset[property]').click(function() {
        var parent = $(this).parents('div.row');
        var name = $(this).attr('property');

        parent.find('input:hidden[name="' + name + '"]').val('');
        parent.find('input:text[name="' + name + '_autocomplete"]').val('');
    });

    $('body').on('click', 'table.elements th span[resetorder]', function() {
        var itemContainer = $(this).parents('div[item]');
        var item = itemContainer.attr('item');

        getElements(item, {
            resetorder: true
        });
    });

    $('body').on('click', 'table.elements th span[order][direction]', function() {
        var itemContainer = $(this).parents('div[item]');
        var item = itemContainer.attr('item');
        var order = $(this).attr('order');
        var direction = $(this).attr('direction');

        getElements(item, {
            order: order,
            direction: direction
        });
    });

    $('body').on('click', 'th.check', function() {
        var tr = $(this).parent();
        var table = tr.parents('table');
        var itemContainer = $(this).parents('div[item]');
        var item = itemContainer.attr('item');

        if (typeof checked[item] === 'undefined') {
            checked[item] = [];
        }

        if (tr.hasClass('checked')) {
            checked[item] = [];

            tr.removeClass('checked');

            table.find('tbody tr').each(function() {
                $(this).removeClass('checked');
            });
        } else {
            tr.addClass('checked');

            table.find('tbody tr').each(function() {
                var elementId = $(this).attr('elementId');
                var index = checked[item].indexOf(elementId);

                if (index === -1) {
                    checked[item].push(elementId);
                }

                $(this).addClass('checked');
            });
        }

        if (checked[item].length) {
            itemContainer.find('.button.restore').addClass('enabled');
            itemContainer.find('.button.delete').addClass('enabled');
        } else {
            itemContainer.find('.button.restore').removeClass('enabled');
            itemContainer.find('.button.delete').removeClass('enabled');
        }
    });

    $('body').on('click', 'td.check', function() {
        var tr = $(this).parent();
        var itemContainer = $(this).parents('div[item]');
        var item = itemContainer.attr('item');
        var elementId = tr.attr('elementId');

        if (typeof checked[item] === 'undefined') {
            checked[item] = [];
        }

        var index = checked[item].indexOf(elementId);

        if (tr.hasClass('checked')) {
            if (index > -1) {
                checked[item].splice(index, 1);
            }

            tr.removeClass('checked');
        } else {
            if (index === -1) {
                checked[item].push(elementId);
            }

            tr.addClass('checked');
        }

        if (checked[item].length) {
            itemContainer.find('.button.restore').addClass('enabled');
            itemContainer.find('.button.delete').addClass('enabled');
        } else {
            itemContainer.find('.button.restore').removeClass('enabled');
            itemContainer.find('.button.delete').removeClass('enabled');
        }
    });

    $('body').on('click', '.button.restore.enabled', function() {
        var itemContainer = $(this).parents('div[item]');
        var item = itemContainer.attr('item');

        $.confirm(null, '.confirm[id="' + item + '_restore"]');
    });

    $('body').on('click', '.button.delete.enabled', function() {
        var itemContainer = $(this).parents('div[item]');
        var item = itemContainer.attr('item');

        $.confirm(null, '.confirm[id="' + item + '_delete"]');
    });

    $('body').on('click', '.confirm .btn.restore', function() {
        var itemContainer = $(this).parents('div[item]');
        var item = itemContainer.attr('item');

        $.confirmClose();
        $.blockUI();

        $.post(
            '/moonlight/elements/restore',
            {
                item: item,
                checked: checked[item]
            },
            function(data) {
                $.unblockUI(function() {
                    if (data.error) {
                        $.alert(data.error);
                    } else if (data.restored) {
                        getElements(item);
                    }
                });
            }
        );
    });

    $('body').on('click', '.confirm .btn.remove', function() {
        var itemContainer = $(this).parents('div[item]');
        var item = itemContainer.attr('item');

        $.confirmClose();
        $.blockUI();

        $.post(
            '/moonlight/elements/delete/force',
            {
                item: item,
                checked: checked[item]
            },
            function(data) {
                $.unblockUI(function() {
                    if (data.error) {
                        $.alert(data.error);
                    } else if (data.deleted) {
                        getElements(item);
                    }
                });
            }
        );
    });

    $('body').on('click', 'ul.pager > li[prev].active', function () {
        var pager = $(this).parent();
        var item = pager.attr('item');
        var page = parseInt(pager.attr('page')) - 1;

        if (page < 1) page = 1;

        submit(page);
    });

    $('body').on('click', 'ul.pager > li[first].active', function () {
        var pager = $(this).parent();
        var item = pager.attr('item');

        submit(1);
    });

    $('body').on('keydown', 'ul.pager > li.page > input', function (event) {
        var pager = $(this).parents('ul.pager');
        var item = pager.attr('item');
        var page = parseInt($(this).val());
        var last = parseInt(pager.attr('last'));
        var code = event.keyCode || event.which;
        
        if (code === 13) {
            if (page < 1) page = 1;
            if (page > last) page = last;

            submit(page);
        }
    });

    $('body').on('click', 'ul.pager > li[last].active', function () {
        var pager = $(this).parent();
        var item = pager.attr('item');
        var last = pager.attr('last');

        submit(last);
    });

    $('body').on('click', 'ul.pager > li[next].active', function () {
        var pager = $(this).parent();
        var item = pager.attr('item');
        var page = parseInt(pager.attr('page')) + 1;
        var last = parseInt(pager.attr('last'));

        if (page > last) page = last;

        submit(page);
    });
});