$(function() {
    $('form').submit(function() {
        $('div.error').fadeOut(200);
        $('div.ok').fadeOut(200);
        $.blockUI();

        $(this).ajaxSubmit({
            url: this.action,
            dataType: 'json',
            success: function(data) {
                $.unblockUI();
                
                if (data.error) {
                    $('div.error').html(data.error).fadeIn(200);
                } else if (data.ok) {
                    $('div.ok').html(data.ok).fadeIn(200);
                }
            },
            error: function(data) {
                $.unblockUI();
                $.alert(data.statusText);
            }
        });

        return false;
    });
});