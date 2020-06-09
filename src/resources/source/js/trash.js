jQuery.expr[':'].contains = function (a, i, m) {
    return jQuery(a).text().toUpperCase().indexOf(m[3].toUpperCase()) >= 0;
};

$(function () {
    var checked = {};

    var getElements = function (item, params) {
        var form = $('form[name="search-form"]');
        var itemContainer = $('div.item[data-item="' + item + '"]');
        var url = itemContainer.data('url');
        var formData = new FormData(form[0]);
        var data = {
            item: item
        };

        for (var pair of formData.entries()) {
            data[pair[0]] = pair[1];
        }

        if (params) {
            for (var index in params) {
                data[index] = params[index];
            }
        }

        $.blockUI();

        $.ajax({
            url: url,
            data: data
        }).done(function (response) {
            $.unblockUI();

            if (response.html) {
                itemContainer.html(response.html);
            }
        }).fail(function (response) {
            $.unblockUI();
            $.alert(response.statusText);
        });
    };

    var submit = function (item, page) {
        var form = $('form[name="search-form"]');

        form.find('input:hidden[name="page"]').val(page);
        form.submit();
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

    $('.search-form-links div.link').click(function () {
        var searchFormContainer = $(this).parents('div.search-form[data-item]');
        var item = searchFormContainer.data('item');
        var name = $(this).data('name');
        var active = ! $(this).hasClass('active');

        $(this).toggleClass('active');
        $('.search-form-params div.block[data-name="' + name + '"]').toggleClass('active');

        if (active) {
            $('.search-form-params div.block[data-name="' + name + '"]')
                .find('input, select')
                .removeAttr('disabled');
        } else {
            $('.search-form-params div.block[data-name="' + name + '"]')
                .find('input, select')
                .attr('disabled', 'disabled');
        }

        $.post('/moonlight/search/active', {
            item: item,
            name: name,
            active: active
        });
    });

    $('.search-form-params div.close').click(function () {
        var searchFormContainer = $(this).parents('div.search-form[data-item]');
        var item = searchFormContainer.data('item');
        var name = $(this).data('name');

        $('.search-form-links div.link[data-name="' + name + '"]').removeClass('active');
        $('.search-form-params div.block[data-name="' + name + '"]').removeClass('active');
        $('.search-form-params div.block[data-name="' + name + '"]')
            .find('input, select')
            .attr('disabled', 'disabled');

        $.post('/moonlight/search/active', {
            item: item,
            name: name,
            active: false
        });
    });

    $('.search-form-params div.block[data-name]:not(.active)')
        .find('input, select')
        .attr('disabled', 'disabled');

    $('.search-form-params input.date').calendar({
        dateFormat: '%Y-%m-%d'
    });

    $('.search-form-params input.one').each(function () {
        var searchFormContainer = $(this).parents('div.search-form[data-item]');
        var item = searchFormContainer.data('item');
        var parent = $(this).parents('div.row');
        var name = $(this).data('property');
        var width = $(this).outerWidth() - 2;

        $(this).autocomplete({
            serviceUrl: '/moonlight/elements/autocomplete',
            params: {
                item: item,
                mode: 'trash'
            },
            onSelect: function (suggestion) {
                parent.find('input:hidden[name="' + name + '"]').val(suggestion.id);
            },
            width: width,
            minChars: 0
        });
    });

    $('.search-form-params .addition.unset[data-property]').click(function () {
        var parent = $(this).parents('div.row');
        var name = $(this).data('property');

        parent.find('input:hidden[name="' + name + '"]').val('');
        parent.find('input:text[name="' + name + '_autocomplete"]').val('');
    });

    $('body').on('click', 'table.elements th span[data-reset-order]', function () {
        var itemContainer = $(this).parents('div.item[data-item]');
        var item = itemContainer.data('item');

        getElements(item, {
            resetorder: true
        });
    });

    $('body').on('click', 'table.elements th span[data-order][data-direction]', function () {
        var itemContainer = $(this).parents('div.item[data-item]');
        var item = itemContainer.data('item');
        var order = $(this).data('order');
        var direction = $(this).data('direction');

        getElements(item, {
            order: order,
            direction: direction
        });
    });

    $('body').on('click', 'th.check', function () {
        var itemContainer = $(this).parents('div.item[data-item]');
        var item = itemContainer.data('item');
        var tr = $(this).parent();
        var table = tr.parents('table');

        if (typeof checked[item] === 'undefined') {
            checked[item] = [];
        }

        if (tr.hasClass('checked')) {
            checked[item] = [];

            tr.removeClass('checked');

            table.find('tbody tr').each(function () {
                $(this).removeClass('checked');
            });
        } else {
            tr.addClass('checked');

            table.find('tbody tr').each(function () {
                var elementId = $(this).data('element-id');
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

    $('body').on('click', 'td.check', function () {
        var itemContainer = $(this).parents('div.item[data-item]');
        var item = itemContainer.data('item');
        var tr = $(this).parent();
        var elementId = tr.data('element-id');

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

    $('body').on('click', '.button.restore.enabled', function () {
        var itemContainer = $(this).parents('div.item[data-item]');
        var item = itemContainer.data('item');

        $.confirm(null, 'div.item[data-item="' + item + '"] .confirm[data-confirm-type="restore"]');
    });

    $('body').on('click', '.button.delete.enabled', function () {
        var itemContainer = $(this).parents('div.item[data-item]');
        var item = itemContainer.data('item');

        $.confirm(null, 'div.item[data-item="' + item + '"] .confirm[data-confirm-type="delete"]');
    });

    $('body').on('click', '.confirm .btn.restore, .confirm .btn.remove', function () {
        var confirmContainer = $(this).parents('.confirm');
        var itemContainer = $(this).parents('div.item[data-item]');
        var item = itemContainer.data('item');
        var url = confirmContainer.data('url');

        $.confirmClose();
        $.blockUI();

        $.post(url, {
            item: item,
            checked: checked[item]
        }, function (response) {
            $.unblockUI(function () {
                if (response.error) {
                    $.alert(response.error);
                } else if (response.deleted) {
                    getElements(item);
                }
            });
        });
    });

    $('body').on('click', 'ul.pager > li[data-link="prev"].active', function () {
        var itemContainer = li.parents('div.item[data-item]');
        var item = itemContainer.data('item');
        var pager = $(this).parent();
        var page = parseInt(pager.data('page')) - 1;

        if (page < 1) page = 1;

        submit(item, page);
    });

    $('body').on('click', 'ul.pager > li[data-link="first"].active', function () {
        var itemContainer = $(this).parents('div.item[data-item]');
        var item = itemContainer.data('item');
        var pager = $(this).parent();

        submit(item, 1);
    });

    $('body').on('keydown', 'ul.pager > li.page > input', function (event) {
        var itemContainer = $(this).parents('div.item[data-item]');
        var item = itemContainer.data('item');
        var pager = $(this).parents('ul.pager');
        var page = parseInt($(this).val());
        var last = parseInt(pager.data('last'));
        var code = event.keyCode || event.which;

        if (code === 13) {
            if (isNaN(page) || page < 1) page = 1;
            if (page > last) page = last;

            submit(item, page);
        }
    });

    $('body').on('click', 'ul.pager > li[data-link="last"].active', function () {
        var itemContainer = $(this).parents('div.item[data-item]');
        var item = itemContainer.data('item');
        var pager = $(this).parent();
        var last = pager.data('last');

        submit(item, last);
    });

    $('body').on('click', 'ul.pager > li[data-link="next"].active', function () {
        var itemContainer = $(this).parents('div.item[data-item]');
        var item = itemContainer.data('item');
        var pager = $(this).parent();
        var page = parseInt(pager.data('page')) + 1;
        var last = parseInt(pager.data('last'));

        if (page > last) page = last;

        submit(item, page);
    });

    $('body').on('click', 'li.column-toggler', function () {
        var li = $(this);
        var dropdown = li.find('.dropdown');
        var display = li.data('display');

        if (display == 'show') {
            li.data('display', 'hide').removeClass('open');
            dropdown.fadeOut(200);
        } else {
            li.data('display', 'show').addClass('open');
            dropdown.fadeIn(200);
        }
    });

    $('body').on('click', 'li.column-toggler .dropdown', function (e) {
        e.stopPropagation();
    });

    $('body').on('click', 'li.column-toggler .dropdown ul > li', function (e) {
        var itemContainer = $(this).parents('div.item[data-item]');
        var item = itemContainer.data('item');
        var li = $(this);
        var name = li.data('name');
        var show = li.data('show');

        if (show == true) {
            li.data('show', false).removeClass('checked');
            show = false;
        } else {
            li.data('show', true).addClass('checked');
            show = true;
        }

        $.post('/moonlight/column', {
            item: item,
            name: name,
            show: show
        });
    });

    $('body').on('keyup change', 'li.column-toggler .dropdown .perpage input', $.debounce(function () {
        var itemContainer = $(this).parents('div.item[data-item]');
        var item = itemContainer.data('item');
        var input = $(this);
        var perpage = input.val();

        $.post('/moonlight/perpage', {
            item: item,
            perpage: perpage
        });
    }, 500));

    $('body').on('keypress', 'li.column-toggler .dropdown .perpage input', function (event) {
        var itemContainer = $(this).parents('div.item[data-item]');
        var item = itemContainer.data('item');
        var input = $(this);
        var perpage = input.val();

        if (! event) event = window.event;

        if (event.keyCode) {
            var code = event.keyCode;
        } else if (event.which) {
            var code = event.which;
        }

        if (code === 13) {
            $.post('/moonlight/perpage', {
                item: item,
                perpage: perpage
            }, function () {
                $('li.column-toggler .dropdown .btn').click();
            });
        }
    });

    $('body').on('click', 'li.column-toggler .dropdown .btn', function (e) {
        var itemContainer = $(this).parents('div.item[data-item]');
        var item = itemContainer.data('item');
        var li = $(this).parents('li.column-toggler');
        var dropdown = li.find('.dropdown');

        li.data('display', 'hide');

        dropdown.fadeOut(200, function () {
            var url = new URL(location.href);
            var query_string = url.search;
            var search_params = new URLSearchParams(query_string);

            search_params.set('page', '1');
            url.search = search_params.toString();

            if (window.history.replaceState) {
                window.history.replaceState({}, null, url.toString());
                getElements(item);
            } else {
                window.location.href = url.toString();
            }
        });
    });
});
