$(function () {
    var itemCount = 0;
    var empty = true;
    var checked = {};

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

    var init = function (item) {
        var itemContainer = $('div.item[data-item="' + item + '"]');
        var classId = itemContainer.data('class-id');
        var url = itemContainer.data('url');

        $('div.item[data-item="' + item + '"] input.one').each(function () {
            var parent = $(this).parents('div.row');
            var relatedItem = $(this).data('item');
            var propertyName = $(this).data('property');
            var width = $(this).outerWidth() - 2;

            $(this).autocomplete({
                serviceUrl: '/moonlight/elements/autocomplete',
                params: {
                    item: relatedItem
                },
                formatResult: function (suggestion, currentValue) {
                    return suggestion.value + ' <small>(' + suggestion.id + ')</small>';
                },
                onSelect: function (suggestion) {
                    parent.find('input:hidden[name="' + propertyName + '"]').val(suggestion.id);
                    parent.find('span.element-container[data-name="' + propertyName + '"]').html(suggestion.value);
                },
                width: width,
                minChars: 0
            });
        });

        $('div.item[data-item="' + item + '"] table.elements tbody').each(function () {
            var tbody = $(this);

            tbody.sortable({
                handle: '.drag',
                chosenClass: 'chosen',
                dragClass: 'dragging',
                onEnd: function (event) {
                    if (event.newIndex === event.oldIndex) return false;
                    var order = [];

                    $(event.to).find('tr').each(function () {
                        var id = $(this).data('element-id');
                        order.push(id);
                    });

                    $.blockUI();

                    $.post('/moonlight/elements/order', {
                        item: item,
                        class_id: classId,
                        elements: order
                    }, function (response) {
                        $.unblockUI();
                    });
                }
            });
        });
    };

    var loadElements = function (item) {
        var itemContainer = $('div.item[data-item="' + item + '"]');
        var classId = itemContainer.data('class-id');
        var url = itemContainer.data('url');

        $.getJSON(url, {
            item: item,
            class_id: classId
        }, function (data) {
            if (data.html && data.html.length) {
                itemContainer.html(data.html).removeClass('hidden');
                init(item);
                empty = false;
                $(document).trigger('item-loaded', [item, classId]);
            }

            itemCount++;

            if (itemCount == items.length) {
                if (empty) $('div.empty').show();
            } else {
                loadElements(items[itemCount]);
            }
        }).fail(function () {
            $.alertDefaultError();
        });
    };

    var getElements = function (item, params) {
        var itemContainer = $('div.item[data-item="' + item + '"]');
        var classId = itemContainer.data('class-id');
        var url = itemContainer.data('url');
        var data = {
            item: item,
            class_id: classId
        };

        if (params) {
            for (var index in params) {
                data[index] = params[index];
            }
        }

        $.blockUI();

        $.getJSON(url, data, function (response) {
            $.unblockUI();

            if (response.html && response.html.length) {
                itemContainer.html(response.html);
                init(item);
                $(document).trigger('item-loaded', [item, classId]);
            } else {
                $('div.item[data-item="' + item + '"]').fadeOut(200, function () {
                    itemCount--;

                    if (! itemCount) {
                        $('div.empty').show();
                    }
                });
            }
        }).fail(function () {
            $.unblockUI();
            $.alertDefaultError();
        });
    };

    var items = [];

    $('div.item[data-item]').each(function () {
        var item = $(this).data('item');

        items.push(item);
    });

    if (items.length > 0) {
        loadElements(items[0]);
    }

    $('body').on('click', 'div.item[data-item] ul.header > li.h2', function () {
        var itemContainer = $(this).parents('div.item[data-item]');
        var elementsContainer = itemContainer.find('div.list-container');
        var item = itemContainer.data('item');
        var classId = itemContainer.data('class-id');
        var url = itemContainer.data('url');
        var h2 = $(this);
        var display = h2.data('display');

        if (display == 'show') {
            h2.data('display', 'hide');
            elementsContainer.hide();

            $.post('/moonlight/elements/close', {
                item: item,
                class_id: classId
            });
        } else if (display == 'hide') {
            h2.data('display', 'show');
            elementsContainer.show();

            $.post('/moonlight/elements/open', {
                item: item,
                class_id: classId
            });
        } else {
            $.blockUI();

            $.getJSON(url, {
                item: item,
                class_id: classId,
                open: true
            }, function (data) {
                $.unblockUI();

                if (data.html) {
                    $('div.item[data-item="' + item + '"]').html(data.html);
                    init(item);
                    $(document).trigger('item-loaded', [item, classId]);
                }
            });
        }
    });

    $('body').on('click', 'table.elements th span[data-reset-order="true"]', function () {
        var itemContainer = $(this).parents('div.item[data-item]');
        var item = itemContainer.data('item');
        var url = itemContainer.data('url');

        getElements(item, {
            reset_order: true
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

    $('body').on('click', 'table.elements td.editable', function () {
        var itemContainer = $(this).parents('div.item[data-item]');
        var td = $(this);
        var mode = td.data('mode');

        if (mode === 'edit') {
            td.data('mode', 'view');

            td.find('.view-container').show();
            td.find('.edit-container').hide();
            td.find('.edit-container').find('input,select,textarea')
                .attr('disabled', 'disabled');
        } else {
            td.data('mode', 'edit');

            td.find('.view-container').hide();
            td.find('.edit-container').show();
            td.find('.edit-container').find('input,select,textarea')
                .removeAttr('disabled')
                .focus();
        }

        var count = itemContainer.find('td.editable').filter(function () {
            return $(this).data('mode') === 'edit';
        }).length;

        if (count) {
            itemContainer.find('.button.save:not(.disabled)').addClass('enabled');
        } else {
            itemContainer.find('.button.save:not(.disabled)').removeClass('enabled');
        }
    });

    $('body').on('click', 'table.elements td.editable input', function (e) {
        e.stopPropagation();
    });

    $('body').on('click', 'table.elements td.editable select', function (e) {
        e.stopPropagation();
    });

    $('body').on('click', 'table.elements td.editable textarea', function (e) {
        e.stopPropagation();
    });

    $('body').on('click', 'table.elements td.editable div.checkbox', function (e) {
        var checkbox = $(this);
        var td = checkbox.parents('td');
        var input = td.find('input:hidden');

        if (input.val() == 1) {
            $(this).removeClass('checked');
            input.val(0);
        } else {
            $(this).addClass('checked');
            input.val(1);
        }

        e.stopPropagation();
    });

    $('body').on('submit', 'form[name="save"]', function () {
        var itemContainer = $(this).parents('div.item[data-item]');
        var item = itemContainer.data('item');
        var count = itemContainer.find('td.editable').filter(function () {
            return $(this).data('mode') === 'edit';
        }).length;

        if (! count) {
            return false;
        }

        itemContainer.find('td.editable.invalid').removeClass('invalid');

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
            }

            if (response.errors) {
                for (var id in response.errors) {
                    for (var name in response.errors[id]) {
                        itemContainer.find('table.elements tr[data-element-id="' + id + '"] td.editable[data-name="' + name + '"]')
                            .addClass('invalid');
                    }
                }
            }

            if (response.views) {
                for (var id in response.views) {
                    for (var name in response.views[id]) {
                        itemContainer.find('table.elements tr[data-element-id="' + id + '"] td.editable[data-name="' + name + '"]')
                            .replaceWith(response.views[id][name]);
                    }
                }

                var count = itemContainer.find('td.editable').filter(function () {
                    return $(this).data('mode') === 'edit';
                }).length;

                if (! count) {
                    itemContainer.find('.button.save:not(.disabled)').removeClass('enabled');
                }

                $(document).trigger('item-saved', [item]);
            }
        }).fail(function (response) {
            $.unblockUI();
            $.alert(response.statusText);
        });

        return false;
    });

    $('body').on('click', '.button.save.enabled', function () {
        var itemContainer = $(this).parents('div.item[data-item]');

        itemContainer.find('form[name="save"]').submit();

        return false;
    });

    $('body').on('mouseover', 'table.elements td.check', function () {
        var tr = $(this).parent();

        tr.addClass('hover');
    });

    $('body').on('mouseout', 'table.elements td.check', function () {
        var tr = $(this).parent();

        tr.removeClass('hover');
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
            itemContainer.find('.button.copy:not(.disabled)').addClass('enabled');
            itemContainer.find('.button.move:not(.disabled)').addClass('enabled');
            itemContainer.find('.button.bind:not(.disabled)').addClass('enabled');
            itemContainer.find('.button.unbind:not(.disabled)').addClass('enabled');
            itemContainer.find('.button.favorite:not(.disabled)').addClass('enabled');
            itemContainer.find('.button.delete:not(.disabled)').addClass('enabled');
        } else {
            itemContainer.find('.button.copy:not(.disabled)').removeClass('enabled');
            itemContainer.find('.button.move:not(.disabled)').removeClass('enabled');
            itemContainer.find('.button.bind:not(.disabled)').removeClass('enabled');
            itemContainer.find('.button.unbind:not(.disabled)').removeClass('enabled');
            itemContainer.find('.button.favorite:not(.disabled)').removeClass('enabled');
            itemContainer.find('.button.delete:not(.disabled)').removeClass('enabled');
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
            itemContainer.find('.button.copy:not(.disabled)').addClass('enabled');
            itemContainer.find('.button.move:not(.disabled)').addClass('enabled');
            itemContainer.find('.button.bind:not(.disabled)').addClass('enabled');
            itemContainer.find('.button.unbind:not(.disabled)').addClass('enabled');
            itemContainer.find('.button.favorite:not(.disabled)').addClass('enabled');
            itemContainer.find('.button.delete:not(.disabled)').addClass('enabled');
        } else {
            itemContainer.find('.button.copy:not(.disabled)').removeClass('enabled');
            itemContainer.find('.button.move:not(.disabled)').removeClass('enabled');
            itemContainer.find('.button.bind:not(.disabled)').removeClass('enabled');
            itemContainer.find('.button.unbind:not(.disabled)').removeClass('enabled');
            itemContainer.find('.button.favorite:not(.disabled)').removeClass('enabled');
            itemContainer.find('.button.delete:not(.disabled)').removeClass('enabled');
        }
    });

    $('body').on('click', '.addition.unset[data-property]', function () {
        var parent = $(this).parents('div.row');
        var name = $(this).data('property');

        parent.find('input:hidden[name="' + name + '"]').val('');
        parent.find('input:text[name="' + name + '_autocomplete"]').val('');
        parent.find('span[container][name="' + name + '"]').html('Не определено');
    });

    $('body').on('click', '.button.copy.enabled', function () {
        var itemContainer = $(this).parents('div.item[data-item]');
        var item = itemContainer.data('item');

        $.confirm(null, 'div.item[data-item="' + item + '"] .confirm[ data-confirm-type="copy"]');
    });

    $('body').on('click', '.button.move.enabled', function () {
        var itemContainer = $(this).parents('div.item[data-item]');
        var item = itemContainer.data('item');

        $.confirm(null, 'div.item[data-item="' + item + '"] .confirm[ data-confirm-type="move"]');
    });

    $('body').on('click', '.button.bind.enabled', function () {
        var itemContainer = $(this).parents('div.item[data-item]');
        var item = itemContainer.data('item');

        $.confirm(null, 'div.item[data-item="' + item + '"] .confirm[ data-confirm-type="bind"]');
    });

    $('body').on('click', '.button.unbind.enabled', function () {
        var itemContainer = $(this).parents('div.item[data-item]');
        var item = itemContainer.data('item');

        $.confirm(null, 'div.item[data-item="' + item + '"] .confirm[ data-confirm-type="unbind"]');
    });

    $('body').on('click', '.button.favorite.enabled', function () {
        var itemContainer = $(this).parents('div.item[data-item]');
        var item = itemContainer.data('item');

        itemContainer.find('.favorite-title.add, .favorite-list.add').addClass('hidden');
        itemContainer.find('.favorite-list.add div.rubric').addClass('hidden');
        itemContainer.find('.favorite-title.remove, .favorite-list.remove').addClass('hidden');
        itemContainer.find('.favorite-list.remove div.rubric').addClass('hidden');

        for (var id of checked[item]) {
            var tr = itemContainer.find('table.elements tr[data-element-id="' + id + '"]');
            var addedRubrics = tr.data('rubrics').toString().split(',');

            itemContainer.find('.favorite-list.add div.rubric').each(function () {
                var rubricId = $(this).data('rubric').toString();
                var index = addedRubrics.indexOf(rubricId);

                if (index === -1) {
                    $(this).removeClass('hidden');
                    itemContainer.find('.favorite-title.add, .favorite-list.add').removeClass('hidden');
                }
            });

            for (var rubricId of addedRubrics) {
                if (rubricId) {
                    itemContainer.find('.favorite-list.remove div.rubric[data-rubric="' + rubricId + '"]').removeClass('hidden');
                    itemContainer.find('.favorite-title.remove, .favorite-list.remove').removeClass('hidden');
                }
            }
        }

        $.confirm(null, 'div.item[data-item="' + item + '"] .confirm[data-confirm-type="favorite"]');
    });

    $('body').on('click', '.button.delete.enabled', function () {
        var itemContainer = $(this).parents('div.item[data-item]');
        var item = itemContainer.data('item');

        $.confirm(null, 'div.item[data-item="' + item + '"] .confirm[data-confirm-type="delete"]');
    });

    $('body').on('click', '.confirm .btn.copy', function () {
        var confirmContainer = $(this).parents('.confirm');
        var itemContainer = $(this).parents('div.item[data-item]');
        var item = itemContainer.data('item');
        var url = confirmContainer.data('url');
        var name, value;

        confirmContainer.find('input[type="radio"]:checked:not(:disabled), input[type="hidden"]').each(function () {
            name = $(this).data('property');
            value = $(this).val();
        });

        $.confirmClose();
        $.blockUI();

        $.post(url, {
            item: item,
            checked: checked[item],
            name: name,
            value: value
        }, function (response) {
            $.unblockUI(function () {
                if (response.error) {
                    $.alert(response.error);
                } else if (response.copied && response.url) {
                    location.href = response.url;
                }
            });
        });
    });

    $('body').on('click', '.confirm .btn.move', function () {
        var confirmContainer = $(this).parents('.confirm');
        var itemContainer = $(this).parents('div.item[data-item]');
        var item = itemContainer.data('item');
        var url = confirmContainer.data('url');
        var one = null;

        confirmContainer.find('input[type="radio"]:checked:not(:disabled), input[type="hidden"]').each(function () {
            var name = $(this).data('property');
            var value = $(this).val();

            one = {
                name: name,
                value: value
            };
        });

        if (! one) return false;

        $.confirmClose();
        $.blockUI();

        $.post(url, {
            item: item,
            checked: checked[item],
            name: one.name,
            value: one.value
        }, function (response) {
            $.unblockUI(function () {
                if (response.error) {
                    $.alert(response.error);
                } else if (response.moved && response.url) {
                    location.href = response.url;
                }
            });
        });
    });

    $('body').on('click', '.confirm .btn.bind', function () {
        var confirmContainer = $(this).parents('.confirm');
        var itemContainer = $(this).parents('div.item[data-item]');
        var item = itemContainer.data('item');
        var url = confirmContainer.data('url');
        var ones = {}, count = 0;

        confirmContainer.find('input[type="radio"]:checked:not(:disabled), input[type="hidden"]').each(function () {
            var name = $(this).data('property');
            var value = $(this).val();

            if (value) {
                ones[name] = value;
                count++;
            }
        });

        if (! count) return false;

        $.confirmClose();
        $.blockUI();

        $.post(url, {
            item: item,
            checked: checked[item],
            ones: ones
        }, function (response) {
            if (response.error) {
                $.unblockUI(function () {
                    $.alert(response.error);
                });
            } else if (response.attached) {
                getElements(item);
            }
        });
    });

    $('body').on('click', '.confirm .btn.unbind', function () {
        var confirmContainer = $(this).parents('.confirm');
        var itemContainer = $(this).parents('div.item[data-item]');
        var item = itemContainer.data('item');
        var url = confirmContainer.data('url');
        var ones = {}, count = 0;

        confirmContainer.find('input[type="radio"]:checked:not(:disabled), input[type="hidden"]').each(function () {
            var name = $(this).data('property');
            var value = $(this).val();

            if (value) {
                ones[name] = value;
                count++;
            }
        });

        if (! count) return false;

        $.confirmClose();
        $.blockUI();

        $.post(url, {
            item: item,
            checked: checked[item],
            ones: ones
        }, function (response) {
            if (response.error) {
                $.unblockUI(function () {
                    $.alert(response.error);
                });
            } else if (response.detached) {
                getElements(item);
            }
        });
    });

    $('body').on('click', '.confirm .favorite-list.add div.rubric', function () {
        var confirmContainer = $(this).parents('.confirm');
        var itemContainer = $(this).parents('div.item[data-item]');
        var item = itemContainer.data('item');
        var url = confirmContainer.data('url');
        var addRubric = $(this).data('rubric');

        $.confirmClose();
        $.blockUI();

        $.post(url, {
            item: item,
            checked: checked[item],
            add_favorite_rubric: addRubric
        }, function (response) {
            $.unblockUI(function () {
                if (response.error) {
                    $.alert(response.error);
                } else if (response.saved) {
                    getElements(item);
                }
            });
        }).fail(function () {
            $.unblockUI();
            $.alertDefaultError();
        });
    });

    $('body').on('click', '.confirm .favorite-list.remove div.rubric', function () {
        var confirmContainer = $(this).parents('.confirm');
        var itemContainer = $(this).parents('div.item[data-item]');
        var item = itemContainer.data('item');
        var url = confirmContainer.data('url');
        var removedRubric = $(this).data('rubric');

        $.confirmClose();
        $.blockUI();

        $.post(url, {
            item: item,
            checked: checked[item],
            remove_favorite_rubric: removedRubric
        }, function (response) {
            $.unblockUI(function () {
                if (response.error) {
                    $.alert(response.error);
                } else if (response.saved) {
                    getElements(item);
                }
            });
        }).fail(function () {
            $.unblockUI();
            $.alertDefaultError();
        });
    });

    $('body').on('keypress', '.confirm .favorite-new input[type="text"]', function (event) {
        if (! event) event = window.event;

        if (event.keyCode) {
            var code = event.keyCode;
        } else if (event.which) {
            var code = event.which;
        }

        if (code == 13) {
            $(this).parents('.confirm').find('.btn.favorite').click();
        }
    });

    $('body').on('click', '.confirm .btn.favorite', function () {
        var confirmContainer = $(this).parents('.confirm');
        var itemContainer = $(this).parents('div.item[data-item]');
        var item = itemContainer.data('item');
        var url = confirmContainer.data('url');
        var newRubric = confirmContainer.find('.favorite-new input[type="text"]').val();

        if (! newRubric) return false;

        $.confirmClose();
        $.blockUI();

        $.post(url, {
            item: item,
            checked: checked[item],
            new_favorite_rubric: newRubric
        }, function (response) {
            $.unblockUI(function () {
                if (response.error) {
                    $.alert(response.error);
                } else if (response.saved) {
                    getElements(item);
                }
            });
        }).fail(function () {
            $.unblockUI();
            $.alertDefaultError();
        });
    });

    $('body').on('click', '.confirm .btn.remove', function () {
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
        var itemContainer = $(this).parents('div.item[data-item]');
        var item = itemContainer.data('item');
        var pager = $(this).parent();
        var page = parseInt(pager.data('page')) - 1;

        if (page < 1) page = 1;

        getElements(item, {page: page});
    });

    $('body').on('click', 'ul.pager > li[data-link="first"].active', function () {
        var itemContainer = $(this).parents('div.item[data-item]');
        var item = itemContainer.data('item');

        getElements(item, {page: 1});
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

            getElements(item, {page: page});
        }
    });

    $('body').on('click', 'ul.pager > li[data-link="last"].active', function () {
        var itemContainer = $(this).parents('div.item[data-item]');
        var item = itemContainer.data('item');
        var pager = $(this).parent();
        var last = pager.data('last');

        getElements(item, {page: last});
    });

    $('body').on('click', 'ul.pager > li[data-link="next"].active', function () {
        var itemContainer = $(this).parents('div.item[data-item]');
        var item = itemContainer.data('item');
        var pager = $(this).parent();
        var page = parseInt(pager.data('page')) + 1;
        var last = parseInt(pager.data('last'));

        if (page > last) page = last;

        getElements(item, {page: page});
    });

    $('.sidebar .elements .h2 span').click(function () {
        var block = $(this).parents('.elements');
        var rubric = block.data('rubric');
        var display = block.data('display');
        var ul = block.find('ul').first();

        console.log(display);

        if (display == 'show') {
            block.data('display', 'hide');
            ul.hide();

            $.post('/moonlight/rubrics/close', {
                rubric: rubric
            });

        } else if (display == 'hide') {
            block.data('display', 'show');
            ul.show();

            $.post('/moonlight/rubrics/open', {
                rubric: rubric
            });
        } else {
            $.blockUI();

            $.getJSON('/moonlight/rubrics/get', {
                rubric: rubric
            }, function (data) {
                $.unblockUI();

                if (data.html) {
                    block.append(data.html);
                    block.data('display', 'show');
                }
            });
        }
    });

    $('body').on('click', '.sidebar .elements span.open', function () {
        var span = $(this);
        var li = span.parents('li').first();
        var rubric = span.data('rubric');
        var classId = span.data('class-id');
        var display = span.data('display');

        if (display == 'show') {
            $('.sidebar .elements ul[data-node="' + classId + '"]').slideUp(200);
            span.data('display', 'hide').removeClass('rotate');

            $.post('/moonlight/rubrics/node/close', {
                rubric: rubric,
                classId: classId
            });

        } else if (display == 'hide') {
            $('.sidebar .elements ul[data-node="' + classId + '"]').slideDown(200);
            span.data('display', 'show').addClass('rotate');

            $.post('/moonlight/rubrics/node/open', {
                rubric: rubric,
                classId: classId
            });
        } else {
            $.blockUI();

            $.getJSON('/moonlight/rubrics/node/get', {
                rubric: rubric,
                class_id: classId
            }, function (response) {
                $.unblockUI();

                if (response.html) {
                    $(response.html).hide().appendTo(li).slideDown(200);
                    span.data('display', 'show').addClass('rotate');
                }
            });
        }
    });

    $('body').on('contextmenu', '.sidebar .elements a', function (event) {
        event.preventDefault();
        event.stopPropagation();

        var a = $(this);
        var sidebar = $('.sidebar');
        var menu = $('.sidebar .contextmenu');

        var left = a.offset().left;
        var top = a.offset().top - sidebar.offset().top + a.height() + 2;

        menu.find('li.title > span').text(a.text());
        menu.find('li.title > small').text(a.data('item-title'));
        menu.find('li.edit > a').attr('href', a.data('edit-url'));
        menu.find('li.browse > a').attr('href', a.attr('href'));

        if (top + menu.height() > $(window).height()) {
            if (top - menu.height() - a.height() - 4 < 0) {
                top = 0;
            } else {
                top = top - menu.height() - a.height() - 4;
            }
        }

        menu.css({
            left: left + 'px',
            top: top + 'px'
        }).fadeIn(200);
    });

    $('body').on('click', '.sidebar .contextmenu', function (event) {
        event.stopPropagation();
    });

    $('body').on('click', '.sort-toggler', function () {
        var itemContainer = $(this).parents('div.item[data-item]');
        var th = itemContainer.find('th.browse');
        var sort = th.data('sort');

        if (sort) {
            th.data('sort', false);
            itemContainer.find('td.browse a').show();
            itemContainer.find('td.browse .drag').hide();
        } else {
            th.data('sort', true);
            itemContainer.find('td.browse a').hide();
            itemContainer.find('td.browse .drag').show();
        }
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
        var classId = itemContainer.attr('classId');
        var input = $(this);
        var perpage = input.val();

        $.post('/moonlight/perpage', {
            item: item,
            classId: classId,
            perpage: perpage
        });
    }, 500));

    $('body').on('keypress', 'li.column-toggler .dropdown .perpage input', function (event) {
        var itemContainer = $(this).parents('div.item[data-item]');
        var item = itemContainer.data('item');
        var classId = itemContainer.data('class-id');
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
                classId: classId,
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
            getElements(item);
        });
    });
});
