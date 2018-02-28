$(function() {
    var element = {};

    var init = function() {
        $('input[name].date').calendar({
            dateFormat: '%Y-%m-%d'
        });

        $('input.one').each(function() {
            var parent = $(this).parents('div.row');
            var item = $(this).attr('item');
            var name = $(this).attr('property');

            $(this).autocomplete({
                serviceUrl: '/moonlight/elements/autocomplete',
                params: {
                    item: item
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

        $('input.many').each(function() {
            var input = $(this);
            var parent = $(this).parents('div.row');
            var item = $(this).attr('item');
            var name = $(this).attr('property');

            $(this).autocomplete({
                serviceUrl: '/moonlight/elements/autocomplete',
                params: {
                    item: item
                },
                formatResult: function(suggestion, currentValue) {
                    return suggestion.value + ' <small>(' + suggestion.id + ')</small>';
                },
                onSelect: function (suggestion) {
                    element = suggestion;
                    parent.find('span[container][name="' + name + '"]').html(suggestion.value);
                },
                minChars: 0
            });
        });

        $('.addition.unset[property]').click(function() {
            var parent = $(this).parents('div.row');
            var name = $(this).attr('property');
    
            parent.find('input:hidden[name="' + name + '"]').val('');
            parent.find('input:text[name="' + name + '_autocomplete"]').val('');
            parent.find('span[container][name="' + name + '"]').html('Не определено');
        });

        $('.addition.add[property]').click(function() {
            var parent = $(this).parents('div.row');
            var name = $(this).attr('property');
            var elements = $('.many.elements[name="' + name + '"]');
    
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
            parent.find('span[container][name="' + name + '"]').html('');
        });
    };

    $('body').on('change', '.loadfile :file', function(e) {
        var name = $(this).attr('name');
        var path = e.target.files[0] ? e.target.files[0].name : 'Выберите файл';

        $('.file[name="' + name + '"]').html(path);    
        $('[name="' + name + '_drop"]').prop('checked', false);
    });

    $('body').on('click', '.loadfile .file[name]', function() {
        var name = $(this).attr('name');
        var fileInput = $(':file[name="' + name + '"]');

        fileInput.click();
    });

    $('body').on('click', '.loadfile .reset', function() {
        var name = $(this).attr('name');

        $('[name="' + name + 'drop"]').prop('checked', false);
        $('.file[name="' + name + '"]').html('Выберите файл');
        $(':file[name="' + name + '"]').val('');
    });

    tinymce.init({
        selector: 'textarea[tinymce="true"]',
        themes: 'modern',
        skin: 'custom',
        language: 'ru',
        plugins: ['lists', 'link', 'image', 'paste', 'table', 'code', 'preview'],
        width: '50rem',
        height: '20rem',
        convert_urls: false,
        setup: function(editor) {
            editor.on('keypress keydown', function(event) {
                return $.onCtrlS(event);
            });
        }
    });

    $('form').submit(function() {
        var form = $(this);

        form.find('span.error').fadeOut(200);

        $('textarea[tinymce="true"]').each(function() {
            var name = $(this).attr('name');

			$(this).val(tinymce.get(name).getContent());
		});

        $.blockUI();

        $(this).ajaxSubmit({
            url: this.action,
            dataType: 'json',
            success: function(data) {
                $.unblockUI();
                
                if (data.error) {
                    $.alert(data.error);
                } else if (data.errors) {
                    for (var field in data.errors) {
                        form.find('span.error[name="' + field + '"]')
                            .html(data.errors[field])
                            .fadeIn(200);
                    }
                } else if (data.added && data.url) {
                    document.location.href = data.url;
                } else if (data.views) {
                    for (var field in data.views) {
                        $('div.row[name="' + field + '"]')
                            .html(data.views[field]);
                    }

                    init();
                }
            },
            error: function(data) {
                $.unblockUI();
                $.alert(data.statusText);
            }
        });

        return false;
    });

    $('.button.save.enabled').click(function() {
        $('form').submit();
    });

    $('.button.copy.enabled').click(function() {
        $.confirm(null, '#copy');
    });

    $('.button.move.enabled').click(function() {
        $.confirm(null, '#move');
    });

    $('.button.favorite.enabled').click(function() {
        $.confirm(null, '#favorite');
    });

    $('.button.delete.enabled').click(function() {
        $.confirm(null, '#delete');
    });

    $('.confirm .btn.copy').click(function() {
        var parent = $(this).parents('.confirm');
        var url = $(this).attr('url');

        if (! url) return false;

        $.confirmClose();
        $.blockUI();
        
        var one = null;
        
        parent.find('input[type="radio"]:checked:not(disabled), input[type="hidden"]').each(function() {
            var name = $(this).attr('property');
            var value = $(this).val();
            
            one = {
                name: name,
                value: value
            };
        });
        
        $.post(url, one, function(data) {
            $.unblockUI(function() {
                if (data.error) {
                    $.alert(data.error);
                } else if (data.copied && data.url) {
                    location.href = data.url;
                }
            });
        }).fail(function() {
            $.unblockUI();
            $.alertDefaultError();
        });
    });

    $('.confirm .btn.move').click(function() {
        var parent = $(this).parents('.confirm');
        var url = $(this).attr('url');

        if (! url) return false;
        
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
        
        $.post(url, one, function(data) {
            $.unblockUI(function() {
                if (data.error) {
                    $.alert(data.error);
                } else if (data.moved) {
                    $.confirmClose();
                    location.reload();
                }
            });
        }).fail(function() {
            $.unblockUI();
            $.alertDefaultError();
        });
    });

    $('body').on('click', '.confirm .favorite-list.add div[rubric]', function() {
        var parent = $(this).parents('.confirm');
        var url = parent.attr('url');
        var addRubric = $(this).attr('rubric');

        if (! url) return false;
        if (! addRubric) return false;

        $.confirmClose();
        $.blockUI();

        $.post(url, {
            add_favorite_rubric: addRubric
        }, function(data) {
            $.unblockUI(function() {
                if (data.error) {
                    $.alert(data.error);
                } else if (data.added) {
                    parent.find('.favorite-list.add div[rubric="' + data.added + '"]').attr('display', 'hide');
                    parent.find('.favorite-list.remove div[rubric="' + data.added + '"]').attr('display', 'show');

                    if (parent.find('.favorite-list.add div[rubric][display="show"]').length) {
                        parent.find('div[name="add"], .favorite-list.add').show();
                    } else {
                        parent.find('div[name="add"], .favorite-list.add').hide();
                    }

                    parent.find('div[name="remove"], .favorite-list.remove').show();
                }
            });
        }).fail(function() {
            $.unblockUI(); 
            $.alertDefaultError();
        });
    });

    $('body').on('click', '.confirm .favorite-list.remove div[rubric]', function() {
        var parent = $(this).parents('.confirm');
        var url = parent.attr('url');
        var removedRubric = $(this).attr('rubric');

        if (! url) return false;
        if (! removedRubric) return false;

        $.confirmClose();
        $.blockUI();

        $.post(url, {
            remove_favorite_rubric: removedRubric
        }, function(data) {
            $.unblockUI(function() {
                if (data.error) {
                    $.alert(data.error);
                } else if (data.removed) {
                    parent.find('.favorite-list.add div[rubric="' + data.removed + '"]').attr('display', 'show');
                    parent.find('.favorite-list.remove div[rubric="' + data.removed + '"]').attr('display', 'hide');

                    parent.find('div[name="add"], .favorite-list.add').show();

                    if (parent.find('.favorite-list.remove div[rubric][display="show"]').length) {
                        parent.find('div[name="remove"], .favorite-list.remove').show();
                    } else {
                        parent.find('div[name="remove"], .favorite-list.remove').hide();
                    }
                }
            });
        }).fail(function() {
            $.unblockUI(); 
            $.alertDefaultError();
        });
    });

    $('.confirm .favorite-new input[type="text"]').on('keypress', function(event) {
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

    $('.confirm .btn.favorite').click(function() {
        var parent = $(this).parents('.confirm');
        var url = parent.attr('url');
        var newRubric = parent.find('.favorite-new input[type="text"]').val();

        if (! url) return false;
        if (! newRubric) return false;

        $.confirmClose();
        $.blockUI();

        $.post(url, {
            new_favorite_rubric: newRubric
        }, function(data) {
            $.unblockUI(function() {
                if (data.error) {
                    $.alert(data.error);
                } else if (data.new) {
                    parent.find('.favorite-list.remove').append(
                        '<div rubric="' + data.new.id + '">' + data.new.name + '</div>'
                    );

                    parent.find('.favorite-new input[type="text"]').val('');

                    parent.find('div[name="remove"], .favorite-list.remove').show();
                } else if (data.added) {
                    parent.find('.favorite-list.add div[rubric="' + data.added + '"]').attr('display', 'hide');
                    parent.find('.favorite-list.remove div[rubric="' + data.added + '"]').attr('display', 'show');

                    parent.find('.favorite-new input[type="text"]').val('');

                    if (parent.find('.favorite-list.add div[rubric][display="show"]').length) {
                        parent.find('div[name="add"], .favorite-list.add').show();
                    } else {
                        parent.find('div[name="add"], .favorite-list.add').hide();
                    }

                    parent.find('div[name="remove"], .favorite-list.remove').show();
                }
            });
        }).fail(function() {
            $.unblockUI(); 
            $.alertDefaultError();
        });
    });

    $('.confirm .btn.remove').click(function() {
        var url = $(this).attr('url');

        if (! url) return false;

        $.confirmClose();
        $.blockUI();

        $.post(url, {}, function(data) {
            $.unblockUI(function() {
                if (data.error) {
                    $.alert(data.error);
                } else if (data.deleted && data.url) {
                    location.href = data.url;
                }
            });
        }).fail(function() {
            $.unblockUI(); 
            $.alertDefaultError();
        });
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

    init();

    $('div.item').fadeIn(200);
});