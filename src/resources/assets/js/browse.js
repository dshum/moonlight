$(function() {
    var itemTotal = 0;
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

    var init = function(item) {
        $('.main div[item="' + item + '"] input.one').each(function() {
            var parent = $(this).parents('div.row');
            var relatedItem = $(this).attr('item');
            var name = $(this).attr('property');
    
            $(this).autocomplete({
                serviceUrl: '/moonlight/elements/autocomplete',
                params: {
                    item: relatedItem
                },
                formatResult: function(suggestion, currentValue) {
                    return suggestion.value + ' <small>(' + suggestion.id + ')</small>';
                },
                onSelect: function (suggestion) {
                    parent.find('input:hidden[name="' + name + '"]').val(suggestion.id);
                    parent.find('span[container][name="' + name + '"]').html(suggestion.value);
                },
                minChars: 0
            });
        });

        $('.main div[item="' + item + '"] table.elements tbody').each(function() {
            var tbody = $(this);

            tbody.sortable({
                handle: '.drag',
                chosenClass: 'chosen',
                dragClass: 'dragging',
                onEnd: function (event) {
                    if (event.newIndex === event.oldIndex) return false;

                    var order = [];

                    $(event.to).find('tr').each(function() {
                        var id = $(this).attr('elementId');

                        order.push(id);
                    });

                    $.blockUI();

                    $.post('/moonlight/elements/order', {
                        item: item,
                        elements: order
                    }, function(data) {
                        $.unblockUI();
                    });
                }
            });
        });
    };

    var loadElements = function(item, classId = null) {
        $.getJSON('/moonlight/elements/list', {
            item: item,
            classId: classId
        }, function(data) {
            if (data.html && data.html.length) {
                $('.main div[item="' + item + '"]').hide().html(data.html).fadeIn(200);

                init(item);

                empty = false;
            }

            itemCount++;

            if (itemCount == itemTotal) {
                if (empty) $('div.empty').show();
            } else {
                loadElements(items[itemCount].item, items[itemCount].classId);
            }
        }).fail(function() {
            $.alertDefaultError();
        });
    };

    var getElements = function(item, classId = null, addition = null) {
        var params = {
            item: item,
            classId: classId
        };

        if (addition) {
            for (var index in addition) {
                params[index] = addition[index];
            }
        }

        $.blockUI();

        $.getJSON('/moonlight/elements/list', params, function(data) {
            $.unblockUI();

            if (data.html && data.html.length) {
                $('.main div[item="' + item + '"]').html(data.html);

                init(item);
            } else {
                $('.main div[item="' + item + '"]').fadeOut(200, function() {
                    itemCount--;

                    if (! itemCount) {
                        $('div.empty').show();
                    }
                });
            }
        }).fail(function() {
            $.unblockUI();
            $.alertDefaultError();
        });
    };

    var items = [];

    $('.main div[item]').each(function () {
        var item = $(this).attr('item');
        var classId = $(this).attr('classId');

        items.push({item: item, classId: classId});

        itemTotal++;
    });

    if (itemTotal) {
        loadElements(items[0].item, items[0].classId);
    }

    $('body').on('click', '.main div[item] ul.header > li.h2', function() {
        var h2 = $(this);
        var display = h2.attr('display');
        var div = h2.parents('div[item]');
        var container = div.find('div[list]');
        var item = div.attr('item');
        var classId = div.attr('classId');

        if (display == 'show') {
            h2.attr('display', 'hide');
            container.hide();

            $.post('/moonlight/elements/close', {
                item: item,
                classId: classId
            });
        } else if (display == 'hide') {
            h2.attr('display', 'show');
            container.show();

            $.post('/moonlight/elements/open', {
                item: item,
                classId: classId
            });
        } else {
            $.blockUI();

            $.getJSON('/moonlight/elements/list', {
                item: item,
                classId: classId,
                open: true
            }, function(data) {
                $.unblockUI();

                if (data.html) {
                    $('.main div[item="' + item + '"]').html(data.html);

                    init(item);
                }
            });
        }
    });

    $('body').on('click', 'table.elements th span[resetorder]', function() {
        var itemContainer = $(this).parents('div[item]');
        var item = itemContainer.attr('item');
        var classId = itemContainer.attr('classId');

        getElements(item, classId, {
            resetorder: true
        });
    });

    $('body').on('click', 'table.elements th span[order][direction]', function() {
        var itemContainer = $(this).parents('div[item]');
        var item = itemContainer.attr('item');
        var classId = itemContainer.attr('classId');
        var order = $(this).attr('order');
        var direction = $(this).attr('direction');

        getElements(item, classId, {
            order: order,
            direction: direction
        });
    });

    $('body').on('click', 'table.elements td.editable', function() {
        var td = $(this);
        var tr = td.parent();
        var itemContainer = $(this).parents('div[item]');
        var item = itemContainer.attr('item');
        var mode = td.attr('mode');
        var elementId = tr.attr('elementId');

        if (mode == 'edit') {
            td.attr('mode', 'view');

            td.find('.view-container').show();
            td.find('.edit-container').hide();

            td.find('.edit-container').find('input,textarea')
                .attr('disabled', 'disabled');
        } else {
            td.attr('mode', 'edit');

            td.find('.view-container').hide();
            td.find('.edit-container').show();

            td.find('.edit-container').find('input,textarea')
                .removeAttr('disabled')
                .focus();
        }

        var count = itemContainer.find('td.editable[mode="edit"]').length;

        if (count) {
            itemContainer.find('.button.save:not(.disabled)').addClass('enabled');
        } else {
            itemContainer.find('.button.save:not(.disabled)').removeClass('enabled');
        }
    });

    $('body').on('click', 'table.elements td.editable input', function(e) {
        e.stopPropagation();
    });

    $('body').on('click', 'table.elements td.editable textarea', function(e) {
        e.stopPropagation();
    });

    $('body').on('click', 'table.elements td.editable div.checkbox', function(e) {
        var checkbox = $(this);
        var td = checkbox.parents('td');
        var tr = td.parent();
        var name = checkbox.attr('name');
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

    $('body').on('submit', 'form[name="save"]', function() {
        var itemContainer = $(this).parents('div[item]');
        var item = itemContainer.attr('item');
        var classId = itemContainer.attr('classId');
        var count = itemContainer.find('td.editable[mode="edit"]').length;

        if (! count) return false;

        itemContainer.find('td.editable.invalid').removeClass('invalid');
        
        $(this).ajaxSubmit({
            url: this.action,
            dataType: 'json',
            success: function(data) {
                $.unblockUI();
                
                if (data.error) {
                    $.alert(data.error);
                }

                if (data.errors) {
                    for (var id in data.errors) {
                        for (var name in data.errors[id]) {
                            itemContainer.find('table.elements tr[elementId="' + id + '"] td.editable[name="' + name + '"]')
                                .addClass('invalid');
                        }
                    }
                }
                
                if (data.views) {
                    for (var id in data.views) {
                        for (var name in data.views[id]) {
                            itemContainer.find('table.elements tr[elementId="' + id + '"] td.editable[name="' + name + '"]')
                                .replaceWith(data.views[id][name]);
                        }
                    }

                    var count = itemContainer.find('td.editable[mode="edit"]').length;

                    if (! count) {
                        itemContainer.find('.button.save:not(.disabled)').removeClass('enabled');
                    }
                }
            },
            error: function(data) {
                $.unblockUI();
                $.alert(data.statusText);
            }
        });

        return false;
    });

    $('body').on('click', '.button.save.enabled', function() {
        var itemContainer = $(this).parents('div[item]');
        
        itemContainer.find('form[name="save"]').submit();

        return false;
    });

    $('body').on('mouseover', 'table.elements td.check', function() {
        var tr = $(this).parent();

        tr.addClass('hover');
    });

    $('body').on('mouseout', 'table.elements td.check', function() {
        var tr = $(this).parent();

        tr.removeClass('hover');
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

    $('body').on('click', '.addition.unset[property]', function() {
        var parent = $(this).parents('div.row');
        var name = $(this).attr('property');

        parent.find('input:hidden[name="' + name + '"]').val('');
        parent.find('input:text[name="' + name + '_autocomplete"]').val('');
        parent.find('span[container][name="' + name + '"]').html('Не определено');
    });

    $('body').on('click', '.button.copy.enabled', function() {
        var itemContainer = $(this).parents('div[item]');
        var item = itemContainer.attr('item');

        $.confirm(null, '.confirm[id="' + item + '_copy"]');
    });

    $('body').on('click', '.button.move.enabled', function() {
        var itemContainer = $(this).parents('div[item]');
        var item = itemContainer.attr('item');

        $.confirm(null, '.confirm[id="' + item + '_move"]');
    });

    $('body').on('click', '.button.bind.enabled', function() {
        var itemContainer = $(this).parents('div[item]');
        var item = itemContainer.attr('item');

        $.confirm(null, '.confirm[id="' + item + '_bind"]');
    });

    $('body').on('click', '.button.unbind.enabled', function() {
        var itemContainer = $(this).parents('div[item]');
        var item = itemContainer.attr('item');

        $.confirm(null, '.confirm[id="' + item + '_unbind"]');
    });

    $('body').on('click', '.button.favorite.enabled', function() {
        var itemContainer = $(this).parents('div[item]');
        var item = itemContainer.attr('item');

        itemContainer.find('div[name="add"], .favorite-list.add').hide();
        itemContainer.find('div[name="remove"], .favorite-list.remove').hide();
        itemContainer.find('.favorite-list.add div[rubric]').hide();
        itemContainer.find('.favorite-list.remove div[rubric]').hide();

        for (var i1 in checked[item]) {
            var id = checked[item][i1];
            var tr = itemContainer.find('table.elements tr[elementId="' + id + '"]');
            var rubrics = tr.attr('rubrics');
            var rubricIds = rubrics.split(',');

            itemContainer.find('.favorite-list.add div[rubric]').each(function() {
                var rubricId = $(this).attr('rubric');
                var index = rubricIds.indexOf(rubricId);

                if (index === -1) {
                    $(this).show();
                    itemContainer.find('div[name="add"], .favorite-list.add').show();
                }
            });

            for (var i2 in rubricIds) {
                var rubricId = rubricIds[i2];

                itemContainer.find('.favorite-list.remove div[rubric="' + rubricId + '"]').show();

                if (rubricId) {
                    itemContainer.find('div[name="remove"], .favorite-list.remove').show();
                }
            }
        }

        $.confirm(null, '.confirm[id="' + item + '_favorite"]');
    });

    $('body').on('click', '.button.delete.enabled', function() {
        var itemContainer = $(this).parents('div[item]');
        var item = itemContainer.attr('item');

        $.confirm(null, '.confirm[id="' + item + '_delete"]');
    });

    $('body').on('click', '.confirm .btn.copy', function() {
        var itemContainer = $(this).parents('div[item]');
        var parent = $(this).parents('.confirm');
        var item = itemContainer.attr('item');

        var name, value;
        
        parent.find('input[type="radio"]:checked:not(:disabled), input[type="hidden"]').each(function() {
            name = $(this).attr('property');
            value = $(this).val();
        });

        if (! name) return false;

        $.confirmClose();
        $.blockUI();

        $.post(
            '/moonlight/elements/copy',
            {
                item: item,
                checked: checked[item],
                name: name,
                value: value
            },
            function(data) {
                $.unblockUI(function() {
                    if (data.error) {
                        $.alert(data.error);
                    } else if (data.copied) {
                        if (! value) {
                            location.reload();
                        } else if (data.url) {
                            location.href = data.url;
                        }
                    }
                });
            }
        );
    });

    $('body').on('click', '.confirm .btn.move', function() {
        var itemContainer = $(this).parents('div[item]');
        var parent = $(this).parents('.confirm');
        var item = itemContainer.attr('item');
        var classId = itemContainer.attr('classId');

        var one = null;
        
        parent.find('input[type="radio"]:checked:not(:disabled), input[type="hidden"]').each(function() {
            var name = $(this).attr('property');
            var value = $(this).val();
            
            one = {
                name: name,
                value: value
            };
        });

        if (! one) return false;

        $.confirmClose();
        $.blockUI();

        $.post(
            '/moonlight/elements/move',
            {
                item: item,
                classId: classId,
                checked: checked[item],
                name: one.name,
                value: one.value
            },
            function(data) {
                $.unblockUI(function() {
                    if (data.error) {
                        $.alert(data.error);
                    } else if (data.moved && data.url) {
                        location.href = data.url;
                    }
                });
            }
        );
    });

    $('body').on('click', '.confirm .btn.bind', function() {
        var itemContainer = $(this).parents('div[item]');
        var parent = $(this).parents('.confirm');
        var item = itemContainer.attr('item');
        var classId = itemContainer.attr('classId');

        var ones = {};
        var count = 0;
        
        parent.find('input[type="radio"]:checked:not(:disabled), input[type="hidden"]').each(function() {
            var name = $(this).attr('property');
            var value = $(this).val();
            
            if (value) {
                ones[name] = value;
                count++;
            }
        });

        if (! count) return false;

        $.confirmClose();
        $.blockUI();

        $.post(
            '/moonlight/elements/bind',
            {
                item: item,
                checked: checked[item],
                ones: ones
            },
            function(data) {
                if (data.error) {
                    $.unblockUI(function() {
                        $.alert(data.error);
                    });
                } else if (data.attached) {
                    getElements(item, classId);
                }
            }
        );
    });

    $('body').on('click', '.confirm .btn.unbind', function() {
        var itemContainer = $(this).parents('div[item]');
        var parent = $(this).parents('.confirm');
        var item = itemContainer.attr('item');
        var classId = itemContainer.attr('classId');

        var ones = {};
        var count = 0;
        
        parent.find('input[type="radio"]:checked:not(:disabled), input[type="hidden"]').each(function() {
            var name = $(this).attr('property');
            var value = $(this).val();
            
            if (value) {
                ones[name] = value;
                count++;
            }
        });

        if (! count) return false;

        $.confirmClose();
        $.blockUI();

        $.post(
            '/moonlight/elements/unbind',
            {
                item: item,
                checked: checked[item],
                ones: ones
            },
            function(data) {
                if (data.error) {
                    $.unblockUI(function() {
                        $.alert(data.error);
                    });
                } else if (data.detached) {
                    getElements(item, classId);
                }
            }
        );
    });

    $('body').on('click', '.confirm .favorite-list.add div[rubric]', function() {
        var parent = $(this).parents('.confirm');
        var itemContainer = $(this).parents('div[item]');
        var classId = itemContainer.attr('classId');
        var item = itemContainer.attr('item');
        var url = parent.attr('url');
        var addRubric = $(this).attr('rubric');

        if (! url) return false;
        if (! addRubric) return false;

        $.confirmClose();
        $.blockUI();

        $.post(url, {
            item: item,
            checked: checked[item],
            add_favorite_rubric: addRubric
        }, function(data) {
            $.unblockUI(function() {
                if (data.error) {
                    $.alert(data.error);
                } else if (data.saved) {
                    getElements(item, classId);
                }
            });
        }).fail(function() {
            $.unblockUI(); 
            $.alertDefaultError();
        });
    });

    $('body').on('click', '.confirm .favorite-list.remove div[rubric]', function() {
        var parent = $(this).parents('.confirm');
        var itemContainer = $(this).parents('div[item]');
        var classId = itemContainer.attr('classId');
        var item = itemContainer.attr('item');
        var url = parent.attr('url');
        var removedRubric = $(this).attr('rubric');

        if (! url) return false;
        if (! removedRubric) return false;

        $.confirmClose();
        $.blockUI();

        $.post(url, {
            item: item,
            checked: checked[item],
            remove_favorite_rubric: removedRubric
        }, function(data) {
            $.unblockUI(function() {
                if (data.error) {
                    $.alert(data.error);
                } else if (data.saved) {
                    getElements(item, classId);
                }
            });
        }).fail(function() {
            $.unblockUI(); 
            $.alertDefaultError();
        });
    });

    $('body').on('keypress', '.confirm .favorite-new input[type="text"]', function(event) {
        if (! event) event = window.event;

		if (event.keyCode) {
			var code = event.keyCode;
		} else if (event.which) {
			var code = event.which;
		}

		if (code == 13) {
            var parent = $(this).parents('.confirm');

            parent.find('.btn.favorite').click();
        }
    });

    $('body').on('click', '.confirm .btn.favorite', function() {
        var parent = $(this).parents('.confirm');
        var itemContainer = $(this).parents('div[item]');
        var classId = itemContainer.attr('classId');
        var item = itemContainer.attr('item');
        var url = parent.attr('url');
        var newRubric = parent.find('.favorite-new input[type="text"]').val();

        if (! url) return false;
        if (! newRubric) return false;

        $.confirmClose();
        $.blockUI();

        $.post(url, {
            item: item,
            checked: checked[item],
            new_favorite_rubric: newRubric
        }, function(data) {
            $.unblockUI(function() {
                if (data.error) {
                    $.alert(data.error);
                } else if (data.saved) {
                    getElements(item, classId);
                }
            });
        }).fail(function() {
            $.unblockUI(); 
            $.alertDefaultError();
        });
    });

    $('body').on('click', '.confirm .btn.remove', function() {
        var itemContainer = $(this).parents('div[item]');
        var classId = itemContainer.attr('classId');
        var item = itemContainer.attr('item');

        $.confirmClose();
        $.blockUI();

        $.post(
            '/moonlight/elements/delete',
            {
                item: item,
                checked: checked[item]
            },
            function(data) {
                if (data.error) {
                    $.unblockUI(function() {
                        $.alert(data.error);
                    });
                } else if (data.deleted) {
                    getElements(item, classId);
                }
            }
        );
    });

    $('body').on('click', 'ul.pager > li[prev].active', function () {
        var pager = $(this).parent();
        var classId = pager.attr('classId');
        var item = pager.attr('item');
        var page = parseInt(pager.attr('page')) - 1;

        if (page < 1) page = 1;

        getElements(item, classId, {page: page});
    });

    $('body').on('click', 'ul.pager > li[first].active', function () {
        var pager = $(this).parent();
        var classId = pager.attr('classId');
        var item = pager.attr('item');

        getElements(item, classId, {page: 1});
    });

    $('body').on('keydown', 'ul.pager > li.page > input', function (event) {
        var pager = $(this).parents('ul.pager');
        var classId = pager.attr('classId');
        var item = pager.attr('item');
        var page = parseInt($(this).val());
        var last = parseInt(pager.attr('last'));
        var code = event.keyCode || event.which;
        
        if (code === 13) {
            if (page < 1) page = 1;
            if (page > last) page = last;

            getElements(item, classId, page);
        }
    });

    $('body').on('click', 'ul.pager > li[last].active', function () {
        var pager = $(this).parent();
        var classId = pager.attr('classId');
        var item = pager.attr('item');
        var last = pager.attr('last');

        getElements(item, classId, {page: last});
    });

    $('body').on('click', 'ul.pager > li[next].active', function () {
        var pager = $(this).parent();
        var classId = pager.attr('classId');
        var item = pager.attr('item');
        var page = parseInt(pager.attr('page')) + 1;
        var last = parseInt(pager.attr('last'));

        if (page > last) page = last;

        getElements(item, classId, {page: page});
    });

    $('.sidebar .elements .h2 span').click(function() {
        var block = $(this).parents('.elements');
        var rubric = block.attr('rubric');
        var display = block.attr('display');
        var ul = block.find('ul').first();

        if (display == 'show') {
            block.attr('display', 'hide');
            ul.hide();

            $.post('/moonlight/rubrics/close', {
                rubric: rubric
            });
            
        } else if (display == 'hide') {
            block.attr('display', 'show');
            ul.show();

            $.post('/moonlight/rubrics/open', {
                rubric: rubric
            });
        } else {
            $.blockUI();

            $.getJSON('/moonlight/rubrics/get', {
                rubric: rubric
            }, function(data) {
                $.unblockUI();

                if (data.html) {
                    block.append(data.html);
                    block.attr('display', 'show');
                }
            });
        }
    });

    $('body').on('click', '.sidebar .elements span.open', function() {
        var span = $(this);
        var li = span.parents('li').first();
        var rubric = span.attr('rubric');
        var bind = span.attr('bind');
        var classId = span.attr('classId');
        var display = span.attr('display');

        if (display == 'show') {
            $('.sidebar .elements ul[node="' + classId + '"]').slideUp(200);

            span.attr('display', 'hide');

            $.post('/moonlight/rubrics/node/close', {
                rubric: rubric,
                classId: classId
            });
            
        } else if (display == 'hide') {
            $('.sidebar .elements ul[node="' + classId + '"]').slideDown(200);

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

    $('body').on('contextmenu', '.sidebar .elements a', function(event) {
        event.preventDefault();
        event.stopPropagation();

        var a = $(this);
        var sidebar = $('.sidebar');
        var menu = $('.sidebar .contextmenu');

        var left = a.offset().left;
        var top = a.offset().top - sidebar.offset().top + a.height() + 2;
    
        menu.find('li.title span').html(a.text());
        menu.find('li.title small').html(a.attr('item'));
        menu.find('li.edit a').attr('href', a.attr('href') + '/edit');
        menu.find('li.browse a').attr('href', a.attr('href'));

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

    $('body').on('click', '.sidebar .contextmenu', function(event) {
        event.stopPropagation();
    });

    $('body').on('click', '.sort-toggler', function() {
        var itemContainer = $(this).parents('div[item]');
        var th = itemContainer.find('th.browse');
        var sort = th.attr('sort');

        if (sort == 'true') {
            th.attr('sort', 'false');
            itemContainer.find('td.browse a').show();
            itemContainer.find('td.browse .drag').hide();
        } else {
            th.attr('sort', 'true');
            itemContainer.find('td.browse a').hide();
            itemContainer.find('td.browse .drag').show();
        }
    });

    $('body').on('click', 'li.column-toggler', function() {
        var li = $(this);
        var dropdown = li.find('.dropdown');
        var display = li.attr('display');

        if (display == 'show') {
            li.attr('display', 'hide');
            dropdown.fadeOut(200);
        } else {
            li.attr('display', 'show');
            dropdown.fadeIn(200);
        }
    });

    $('body').on('click', 'li.column-toggler .dropdown', function(e) {
        e.stopPropagation();
    });

    $('body').on('click', 'li.column-toggler .dropdown ul > li[show]', function(e) {
        var li = $(this);
        var name = li.attr('name');
        var show = li.attr('show');
        var itemContainer = li.parents('div[item]');
        var item = itemContainer.attr('item');

        show = show == 'true' ? 'false' : 'true';

        li.attr('show', show);

        $.post('/moonlight/column', {
            item: item,
            name: name,
            show: show
        });
    });

    $('body').on('click', 'li.column-toggler .dropdown .btn', function(e) {
        var itemContainer = $(this).parents('div[item]');
        var li = $(this).parents('li.column-toggler');
        var dropdown = li.find('.dropdown');
        var classId = itemContainer.attr('classId');
        var item = itemContainer.attr('item');

        li.attr('display', 'hide');
        
        dropdown.fadeOut(200, function() {
            getElements(item, classId);
        });
    });
});