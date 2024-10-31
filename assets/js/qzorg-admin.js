
/*================================================================================
 * @name: Manage at admin
 * @author: Quiz Organizer Team
 * @demo: https://quizorganizer.com/
 * @requires jQuery 
 ================================================================================*/

var qzorgCategoryEndPoint;
var qzorgModify;
var qzorgSettings;
var qzorgCreate;

(function ($) {

    $('.qzorg-custom').on('click', '.custom-notice-btn', function (e) {
        jQuery(this).parents('.custom-notice-parent').fadeOut();
    });

    $('.qzorg-tab-button').click(function (e) {
        qzorg_pd(e);
        var qzorg_tab = $(this).attr('data-tab');
        $('.qzorg-tab-button').removeClass('active');
        $('.qzorg-each-tab-content').removeClass('active');
        $(this).addClass('active');
        $("#" + qzorg_tab).addClass('active');
    });

    $('.qzorg-modal-top-close span').on('click', function () {
        $('#'+$(this).parents('.qzorg-bpopup-wrapper').attr('id')).bPopup().close();
    });

}(jQuery));

function qzorg_setfield(i, v = "") {
    jQuery('#' + i).val(v);
}

function qzorg_show_note(a, b = 'info', c = true) {
    let button = "";
    let dismis = "";
    if (c) {
        dismis = 'is-dismissible';
        button = '<button type="button" class="notice-dismiss custom-notice-btn"><span class="screen-reader-text">Dismiss this notice.</span></button>';
    }
    var notice = '<div class="notice custom-notice-parent quiz-organizer-notice notice-' + b + ' ' + dismis + '">' + '<p>' + a + '</p>' + button + '</div>';
    setTimeout(function () { jQuery('.custom-notice-parent:not(:first)').fadeOut(); }, 4500);
    return notice;
}

function qzorg_show_small_note(a, b = 'info') {
    return '<div class="notice notice-small notice-' + b + '"><p>' + a + '</p></div>';
}

function qzorg_pd(e) {
    e.preventDefault();
}
