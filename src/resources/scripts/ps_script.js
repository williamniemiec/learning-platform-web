// Update progress bar with fail class
function pass_strength_fail(pass_type){
    // If it has successful color, remove it
    if ($('#'+pass_type).hasClass('pass_strength_success')){
        $('#'+pass_type).removeClass('pass_strength_success');
    }

    // If it has check icon, remove it
    if ($('#'+pass_type+' .pass_strength_icon').hasClass('fa-check')){
        $('#'+pass_type+' .pass_strength_icon').removeClass('fa-check');
    }

    // Reset color
    if (!($('#'+pass_type).hasClass('pass_strength_default'))){
        $('#'+pass_type).addClass('pass_strength_default');
    }

    // If it has no close icon, put it
    if (!($('#'+pass_type+' .pass_strength_icon').hasClass('fa-close'))){
        $('#'+pass_type+' .pass_strength_icon').addClass('fa-close');
    }    
}

// Update progress bar with successful class
function pass_strength_success(pass_type){
    // If it has default color, remove it
    if ($('#'+pass_type).hasClass('pass_strength_default')){
        $('#'+pass_type).removeClass('pass_strength_default');
    }

    // If it has close icon, remove it
    if ($('#'+pass_type+' .pass_strength_icon').hasClass('fa-close')){
        $('#'+pass_type+' .pass_strength_icon').removeClass('fa-close');
    }

    // If it has not successful color, put it
    if (!($('#'+pass_type).hasClass('pass_strength_success'))){
        $('#'+pass_type).addClass('pass_strength_success');
    }

    // If it has not check icon, put it
    if (!($('#'+pass_type+' .pass_strength_icon').hasClass('fa-check'))){
        $('#'+pass_type+' .pass_strength_icon').addClass('fa-check');
    }
}

// Remove any color from progress bar
function pass_strength_resetBar(){
    // If it has red color, remove it
    if($('#pass_strength_box .progress .progress-bar').hasClass('pass_strength_fail')){
        $('#pass_strength_box .progress .progress-bar').removeClass('pass_strength_fail');
    }

    // If it has yellow color, remove it
    if($('#pass_strength_box .progress .progress-bar').hasClass('pass_strength_warning')){
        $('#pass_strength_box .progress .progress-bar').removeClass('pass_strength_warning');
    }

    // If it has green color, remove it
    if($('#pass_strength_box .progress .progress-bar').hasClass('pass_strength_success')){
        $('#pass_strength_box .progress .progress-bar').removeClass('pass_strength_success');
    }
}

// Initialize plugin
function pass_strength_init(){
    $('.pass_strength li').each(function(){
        $(this).addClass('list-group-item');
        $(this).addClass('pass_strength_default');
    });

    $('.pass_strength_icon').each(function(){
       $(this).addClass('fa fa-close'); 
    });

    $('.pass_strength').addClass('list-group');

    $('.pass_strength_bar').addClass('progress-bar progress-bar-striped progress-bar-animated');
    $('.pass_strength_bar').attr({
        'role': 'progressbar',
        'aria-valuenow': '0',
        'aria-valuemin': '0',
        'aria-valuemax': '100'
    });

    $('.pass_strength_header').addClass('text-center');
    $('.submit').attr('disabled', 'disabled');
}

$(function(){
    pass_strength_init();
    
    $('.pass_input').keyup(function(){
        // Inicialization
        var pass = $(this).val();
        var strength = 0;
        var minLen = $('#pass_length').data('length');

        // Pass length
        if(pass.length >= minLen){
            strength += 25;
            pass_strength_success('pass_length');
        } else {
            pass_strength_fail('pass_length');
        }

        // Numbers and Characters
        var reg = new RegExp(/(([A-Z]+.*[0-9]+)|([0-9]+.*[A-Z]+))+/i);
        if(reg.test(pass)){
            strength += 25;
            pass_strength_success('pass_numCharact');
        } else {
            pass_strength_fail('pass_numCharact');
        }
        
        // Special Characters
        var reg = new RegExp(/[^A-Z0-9]+/i);
        if(reg.test(pass)){
            strength += 25;
            pass_strength_success('pass_specCharact');
        } else {
            pass_strength_fail('pass_specCharact');
        }

        // Uppercase and lowercase letters
        var reg = new RegExp(/([A-Z]+.*[a-z]+|[a-z]+.*[A-Z]+)+/g);
        if (reg.test(pass)){
            strength += 25;
            pass_strength_success('pass_ulCharact');
        } else {
            pass_strength_fail('pass_ulCharact');
        }

        // Remove any color from progress bar
        pass_strength_resetBar();

        // Update progress bar color
        if(strength <= 25){
            $('#pass_strength_box .progress .progress-bar').addClass('pass_strength_fail');
        }
        else if(strength <= 75){
            $('#pass_strength_box .progress .progress-bar').addClass('pass_strength_warning');
        }
        else if(strength > 75){
            $('#pass_strength_box .progress .progress-bar').addClass('pass_strength_success');
        }

        // Update progress bar percent
        $('#pass_strength_box .progress .progress-bar').css('width',strength+'%');
        $('#pass_strength_box .progress .progress-bar').attr('aria-valuenow', strength);
        $('#pass_strength_box .progress .progress-bar').html(strength+'%');

        // Enable / disable submit button
        if(strength <= 75){
            $('.submit').attr('disabled', 'disabled');
        }
        else {
            $('.submit').removeAttr('disabled');
        }
    });
    
});