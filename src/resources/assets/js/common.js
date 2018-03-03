$(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.onCtrlS = function(event) {
		if ( ! event) event = window.event;

		if (event.keyCode) {
			var code = event.keyCode;
		} else if (event.which) {
			var code = event.which;
		}

		if (code == 83 && event.ctrlKey == true) {
			$('form[save="true"]').submit();
			return false;
		}

		return true;
	};

    
    $.blockUI = function(handle) {
        $('.block-ui').fadeIn(100, handle);
    };
    
    $.unblockUI = function(handle) {
        setTimeout(function() {
            $('.block-ui').fadeOut(100, handle); 
        }, 200);
    };
    
    $.alert = function(content, handle) {
        if (content) {
            $('.alert .content').html(content);
        }
        $('.alert').fadeIn('fast', handle);
    };
    
    $.alertDefaultError = function(handle) {
        $('.alert .content').html('Произошла какая-то ошибка.<br>Обновите страницу.');
        $('.alert').fadeIn('fast', handle);
    };
    
    $.alertClose = function(handle) {
        $('.alert').fadeOut('fast', handle);
    };
    
    $.confirm = function(content, selector, handle) {
        var container = selector ? $(selector) : $('.confirm');
        
        if (content) {
            container.find('.content').html(content);
        }
        
        container.fadeIn('fast', handle);
    };
    
    $.confirmClose = function(selector, handle) {
        var container = selector ? $(selector) : $('.confirm');
        
        container.fadeOut('fast', handle);
    };

    $('body').keypress(function(event) {
		return $.onCtrlS(event);
	}).keydown(function(event) {
		return $.onCtrlS(event);
    }).click(function() {
        $('nav .dropdown').fadeOut(200);
        $('.sidebar .contextmenu').fadeOut(200);
    }).contextmenu(function() {
        $('nav .dropdown').fadeOut(200);
        $('.sidebar .contextmenu').fadeOut(200);
    });

    $('nav').click(function(event) {
        event.stopPropagation();
    });

    $('nav .avatar').click(function() {
        $('nav .dropdown').fadeToggle(200);
    });
    
    $('body').on('click', '.alert .container', function(event) {
        event.stopPropagation();
    });
    
    $('body').on('click', '.alert .hide', function() {
        $('.alert').fadeOut('fast');
    });
    
    $('body').on('click', '.alert', function() {
        $('.alert').fadeOut('fast');
    });

    $('body').on('click', '.confirm .hide', function() {
        $('.confirm').fadeOut('fast');
    });

    $('body').on('click', '.confirm .cancel', function() {
        $('.confirm').fadeOut('fast');
    });

    $('.sidebar-toggler').click(function() {
        var display = $('.sidebar').attr('display');

        if (display == 'show') {
            $('.sidebar').attr('display', 'hide').removeClass('moved');
            $('.main').removeClass('moved');
        } else {
            $('.sidebar').attr('display', 'show').addClass('moved');
            $('.main').addClass('moved');
        }
    });
});