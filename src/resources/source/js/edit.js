$(function () {
    var element = {};

    var init = function () {
        $('input.datetime[data-property]').each(function () {
            var dateInput = $(this);
            var name = dateInput.data('property');
            var timeInput = $('input.time[data-property="' + name + '"]');
            var datepicker = $('.datepicker[data-property="' + name + '"]');
            var popup = $('.timepicker-popup[data-property="' + name + '"]');

            dateInput.calendar({
                triggerElement: '.datepicker[data-property="' + name + '"]',
                dateFormat: '%Y-%m-%d',
                selectHandler: function () {
                    datepicker.html(this.date.print('%d.%m.%Y'));
                    dateInput.val(this.date.print(this.dateFormat));

                    if (! timeInput.val()) {
                        timeInput.val('00:00:00');

                        popup.find('table.hours td[data-value="00"]').addClass('active');
                        popup.find('table.minutes td[data-value="00"]').addClass('active');
                        popup.find('table.seconds td[data-value="00"]').addClass('active');

                        datepicker.after(', <span class="timepicker" data-property="' + name + '">' + timeInput.val() + '</span>');
                    }
                }
            });
        });

        $('input.date[data-property]').each(function () {
            var dateInput = $(this);
            var name = dateInput.data('property');
            var datepicker = $('.datepicker[data-property="' + name + '"]');

            dateInput.calendar({
                triggerElement: '.datepicker[data-property="' + name + '"]',
                dateFormat: '%Y-%m-%d',
                selectHandler: function () {
                    datepicker.html(this.date.print('%d.%m.%Y'));
                    dateInput.val(this.date.print(this.dateFormat));
                }
            });
        });

        $('input.one').each(function () {
            var parent = $(this).parents('div.field.row');
            var relatedItem = $(this).data('item');
            var name = $(this).data('property');
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
                    parent.find('input:hidden[name="' + name + '"]').val(suggestion.id);
                    parent.find('span.element-container[data-name="' + name + '"]').html(suggestion.value);
                },
                width: width,
                minChars: 0
            });
        });

        $('input.many').each(function () {
            var input = $(this);
            var parent = $(this).parents('div.field.row');
            var relatedItem = $(this).data('item');
            var name = $(this).data('property');
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
                    element = suggestion;
                    parent.find('span.element-container[name="' + name + '"]').html(suggestion.value);
                },
                width: width,
                minChars: 0
            });
        });
    };

    $('body').on('click', '.timepicker[data-property]', function (event) {
        event.stopPropagation();

        var timepicker = $(this);
        var name = timepicker.data('property');
        var popup = $('.timepicker-popup[data-property="' + name + '"]');
        var timeInput = $('input.time[data-property="' + name + '"]');
        var main = $('.main');
        var left = timepicker.offset().left - main.offset().left;
        var top = timepicker.offset().top - main.offset().top;

        popup.css({
            left: left + 'px',
            top: top + 'px'
        }).fadeToggle(200);
    });

    $('body').on('click', '.timepicker-popup', function (event) {
        event.stopPropagation();
    });

    $('body').on('click', '.timepicker-popup .title.minutes', function (event) {
        $('.timepicker-popup table.minutes td.add').toggleClass('hide');
    });

    $('body').on('click', '.timepicker-popup .title.seconds', function (event) {
        $('.timepicker-popup table.seconds td.add').toggleClass('hide');
    });

    $('body').on('click', '.timepicker-popup table.hours td, .timepicker-popup table.minutes td, .timepicker-popup table.seconds td', function (event) {
        var td = $(this);
        var table = td.parents('table');
        var popup = td.parents('.timepicker-popup');
        var name = popup.data('property');
        var value = td.text();
        var timeInput = $('input.time[data-property="' + name + '"]');
        var timepicker = $('.timepicker[data-property="' + name + '"]');

        table.find('td.active').removeClass('active');
        td.addClass('active');

        var hours = popup.find('table.hours td.active').text();
        var minutes = popup.find('table.minutes td.active').text();
        var seconds = popup.find('table.seconds td.active').text();
        var time = hours + ':' + minutes + ':' + seconds;

        timepicker.html(time);
        timeInput.val(time);
    });

    $('body').on('click', '.addition.unset[data-property]', function (event) {
        var parent = $(this).parents('div.field.row');
        var name = $(this).data('property');

        parent.find('input:hidden[name="' + name + '"]').val('');
        parent.find('input:text[name="' + name + '_autocomplete"]').val('');
        parent.find('span.element-container[data-name="' + name + '"]').html('Не определено');
    });

    $('body').on('click', '.addition.add[data-property]', function (event) {
        var parent = $(this).parents('div.field.row');
        var name = $(this).data('property');
        var elements = $('.many.elements[data-name="' + name + '"]');

        if (element.id) {
            var checkbox = $('input:checkbox[name="' + name + '[]"][id="' + element.classId + '"]');

            if (checkbox.length) {
                checkbox.prop('checked', true);
            } else {
                elements.append('<p><input type="checkbox" name="' + name + '[]" id="' + element.classId + '" checked value="' + element.id + '"><label for="' + element.classId + '">' + element.value + '</label></p>');
            }

            element = {};
        }

        parent.find('input:text[name="' + name + '_autocomplete"]').val('');
        parent.find('span.element-container[data-name="' + name + '"]').html('');
    });

    $('body').on('change', '.loadfile :file', function (e) {
        var name = $(this).attr('name');
        var path = e.target.files[0] ? e.target.files[0].name : 'Выберите файл';

        $('.file[data-name="' + name + '"]').html(path);
        $('[name="' + name + '_drop"]').prop('checked', false);
    });

    $('body').on('click', '.loadfile .file[data-name]', function () {
        var name = $(this).data('name');
        var fileInput = $(':file[name="' + name + '"]');

        fileInput.click();
    });

    $('body').on('click', '.loadfile .reset', function () {
        var name = $(this).data('name');

        $('input[name="' + name + 'drop"]').prop('checked', false);
        $(':file[name="' + name + '"]').val('');
        $('.file[data-name="' + name + '"]').html('Выберите файл');
    });

    $('textarea[data-tinymce="true"]').each(function () {
        var name = $(this).data('name');
        var toolbar = $(this).data('toolbar')
            || 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | subscript superscript code';

        tinymce.init({
            selector: 'textarea[data-tinymce="true"][data-name="' + name + '"]',
            themes: 'modern',
            skin: 'custom',
            language: 'ru',
            plugins: ['lists', 'link', 'image', 'paste', 'table', 'code', 'preview'],
            menubar: 'file edit view insert format tools table',
            toolbar: toolbar,
            width: '50rem',
            height: '20rem',
            convert_urls: false,
            verify_html: false,
            forced_root_block: false,
            entity_encoding: 'raw',
            invalid_elements: 'script,style',
            setup: function (editor) {
                editor.on('keypress keydown', function (event) {
                    return $.onCtrlS(event);
                });
            }
        });
    });

    $('textarea[data-codemirror="true"]').each(function () {
        let editor = CodeMirror.fromTextArea(this, {
            lineNumbers: true,
            mode: "htmlmixed",
            theme: "eclipse",
            indentUnit: 4,
            indentWithTabs: true,
            autoRefresh: true,
            extraKeys: {
                "Ctrl-Space": "autocomplete",
                "Ctrl-S": function (cm) {
                    editor.save();
                },
                "F11": function (cm) {
                    cm.setOption("fullScreen", ! cm.getOption("fullScreen"));
                },
                "Esc": function (cm) {
                    if (cm.getOption("fullScreen")) cm.setOption("fullScreen", false);
                }
            }
        });
    });

    $('form').submit(function () {
        var form = $(this);

        form.find('span.error').fadeOut(200);

        $('textarea[data-tinymce="true"]').each(function () {
            var name = $(this).attr('name');

            $(this).val(tinymce.get(name).getContent());
        });

        $.blockUI();

        $.ajax({
            url: this.action,
            method: "POST",
            data: new FormData(form[0]),
            contentType: false,
            processData: false
        }).done(function (response) {
            $.unblockUI();

            if (response.error) {
                $.alert(response.error);
            } else if (response.errors) {
                for (var field in response.errors) {
                    form.find('span.error[data-name="' + field + '"]')
                        .html(response.errors[field])
                        .fadeIn(200);
                }
            } else if (response.added && response.url) {
                location.href = response.url;
            } else if (response.views) {
                for (var field in response.views) {
                    $('div.field.row[data-name="' + field + '"]')
                        .html(response.views[field]);
                }

                init();
            }
        }).fail(function (response) {
            $.unblockUI();
            $.alert(response.statusText);
        });

        return false;
    });

    $('.button.save.enabled').click(function (e) {
        $('form[data-save="true"]').submit();
    });

    $('.button.copy.enabled').click(function () {
        $.confirm(null, '.confirm[data-confirm-type="copy"]');
    });

    $('.button.move.enabled').click(function () {
        $.confirm(null, '.confirm[data-confirm-type="move"]');
    });

    $('.button.favorite.enabled').click(function () {
        $.confirm(null, '.confirm[data-confirm-type="favorite"]');
    });

    $('.button.delete.enabled').click(function () {
        $.confirm(null, '.confirm[data-confirm-type="delete"]');
    });

    $('.confirm .btn.copy').click(function () {
        var confirmContainer = $(this).parents('.confirm');
        var url = confirmContainer.data('url');
        var one = null;

        $.confirmClose();
        $.blockUI();

        confirmContainer.find('input[type="radio"]:checked:not(disabled), input[type="hidden"]').each(function () {
            one = {
                name: $(this).data('property'),
                value: $(this).val()
            };
        });

        $.post(url, one, function (data) {
            $.unblockUI(function () {
                if (data.error) {
                    $.alert(data.error);
                } else if (data.copied && data.url) {
                    location.href = data.url;
                }
            });
        }).fail(function () {
            $.unblockUI();
            $.alertDefaultError();
        });
    });

    $('.confirm .btn.move').click(function () {
        var confirmContainer = $(this).parents('.confirm');
        var url = confirmContainer.data('url');
        var one = null;

        confirmContainer.find('input[type="radio"]:checked:not(:disabled), input[type="hidden"]').each(function () {
            one = {
                name: $(this).data('property'),
                value: $(this).val()
            };
        });

        if (! one) return false;

        $.confirmClose();
        $.blockUI();

        $.post(url, one, function (data) {
            $.unblockUI(function () {
                if (data.error) {
                    $.alert(data.error);
                } else if (data.moved) {
                    $.confirmClose();
                    location.reload();
                }
            });
        }).fail(function () {
            $.unblockUI();
            $.alertDefaultError();
        });
    });

    $('body').on('click', '.confirm .favorite-list.add div[data-rubric]', function () {
        var confirmContainer = $(this).parents('.confirm');
        var url = confirmContainer.data('url');
        var addRubric = $(this).data('rubric');

        $.confirmClose();
        $.blockUI();

        $.post(url, {
            add_favorite_rubric: addRubric
        }, function (response) {
            $.unblockUI(function () {
                if (response.error) {
                    $.alert(response.error);
                } else if (response.added) {
                    confirmContainer.find('.favorite-list.add div[data-rubric="' + response.added + '"]').addClass('hidden');
                    confirmContainer.find('.favorite-list.remove div[data-rubric="' + response.added + '"]').removeClass('hidden');
                    confirmContainer.find('.favorite-title.remove, .favorite-list.remove').removeClass('hidden');

                    var count = confirmContainer.find('.favorite-list.add div.rubric').filter(function () {
                        return $(this).hasClass('hidden') === false;
                    }).length;

                    if (count > 0) {
                        confirmContainer.find('.favorite-title.add, .favorite-list.add').removeClass('hidden');
                    } else {
                        confirmContainer.find('.favorite-title.add, .favorite-list.add').addClass('hidden');
                    }
                }
            });
        }).fail(function () {
            $.unblockUI();
            $.alertDefaultError();
        });
    });

    $('body').on('click', '.confirm .favorite-list.remove div[data-rubric]', function () {
        var confirmContainer = $(this).parents('.confirm');
        var url = confirmContainer.data('url');
        var removedRubric = $(this).data('rubric');

        $.confirmClose();
        $.blockUI();

        $.post(url, {
            remove_favorite_rubric: removedRubric
        }, function (response) {
            $.unblockUI(function () {
                if (response.error) {
                    $.alert(response.error);
                } else if (response.removed) {
                    confirmContainer.find('.favorite-list.add div[data-rubric="' + response.removed + '"]').removeClass('hidden');
                    confirmContainer.find('.favorite-list.remove div[data-rubric="' + response.removed + '"]').addClass('hidden');
                    confirmContainer.find('.favorite-title.add, .favorite-list.add').removeClass('hidden');

                    var count = confirmContainer.find('.favorite-list.remove div.rubric').filter(function () {
                        return $(this).hasClass('hidden') === false;
                    }).length;

                    if (count > 0) {
                        confirmContainer.find('.favorite-title.remove, .favorite-list.remove').removeClass('hidden');
                    } else {
                        confirmContainer.find('.favorite-title.remove, .favorite-list.remove').addClass('hidden');
                    }
                }
            });
        }).fail(function () {
            $.unblockUI();
            $.alertDefaultError();
        });
    });

    $('.confirm .favorite-new input[type="text"]').on('keypress', function (event) {
        if (! event) event = window.event;

        var code;

        if (event.keyCode) {
            code = event.keyCode;
        } else if (event.which) {
            code = event.which;
        }

        if (code == 13) {
            $(this).parents('.confirm').find('.btn.favorite').click();
        }
    });

    $('.confirm .btn.favorite').click(function () {
        var confirmContainer = $(this).parents('.confirm');
        var url = confirmContainer.data('url');
        var newRubric = confirmContainer.find('.favorite-new input[type="text"]').val();

        if (! newRubric) return false;

        $.confirmClose();
        $.blockUI();

        $.post(url, {
            new_favorite_rubric: newRubric
        }, function (response) {
            $.unblockUI(function () {
                if (response.error) {
                    $.alert(response.error);
                } else if (response.new) {
                    confirmContainer.find('.favorite-list.add').append(
                        '<div data-rubric="' + response.new.id + '" class="rubric hidden">' + response.new.name + '</div>'
                    );
                    confirmContainer.find('.favorite-list.remove').append(
                        '<div data-rubric="' + response.new.id + '" class="rubric">' + response.new.name + '</div>'
                    );

                    confirmContainer.find('.favorite-new input[type="text"]').val('');
                    confirmContainer.find('.favorite-title.remove, .favorite-list.remove').removeClass('hidden');
                } else if (data.added) {
                    confirmContainer.find('.favorite-list.add div[data-rubric="' + response.added + '"]').addClass('hidden');
                    confirmContainer.find('.favorite-list.remove div[data-rubric="' + response.added + '"]').removeClass('hidden');
                    confirmContainer.find('.favorite-title.remove, .favorite-list.remove').removeClass('hidden');
                    confirmContainer.find('.favorite-new input[type="text"]').val('');

                    var count = confirmContainer.find('.favorite-list.add div.rubric').filter(function () {
                        return $(this).hasClass('hidden') === false;
                    }).length;

                    if (count > 0) {
                        confirmContainer.find('.favorite-title.add, .favorite-list.add').removeClass('hidden');
                    } else {
                        confirmContainer.find('.favorite-title.add, .favorite-list.add').addClass('hidden');
                    }
                }
            });
        }).fail(function () {
            $.unblockUI();
            $.alertDefaultError();
        });
    });

    $('.confirm .btn.remove').click(function () {
        var confirmContainer = $(this).parents('.confirm');
        var url = confirmContainer.data('url');

        $.confirmClose();
        $.blockUI();

        $.post(url, {}, function (data) {
            $.unblockUI(function () {
                if (data.error) {
                    $.alert(data.error);
                } else if (data.deleted && data.url) {
                    location.href = data.url;
                }
            });
        }).fail(function () {
            $.unblockUI();
            $.alertDefaultError();
        });
    });

    $('.sidebar .elements .h2 span').click(function () {
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
            }, function (data) {
                $.unblockUI();

                if (data.html) {
                    block.append(data.html);
                    block.attr('display', 'show');
                }
            });
        }
    });

    $('body').on('click', '.sidebar .elements span.open', function () {
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
            }, function (data) {
                $.unblockUI();

                if (data.html) {
                    $(data.html).hide().appendTo(li).slideDown(200);

                    span.attr('display', 'show');
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

    $('body').on('click', '.sidebar .contextmenu', function (event) {
        event.stopPropagation();
    });

    init();

    $('div.item').fadeIn(200);
});
