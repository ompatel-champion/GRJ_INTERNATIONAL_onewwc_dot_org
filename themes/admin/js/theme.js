/**
 * @version 8.0 [rev.8.0.06]
 */

var sidebarCookie = $.cookie('sidebar-cookie');
var appBody = $('body');

appBody.addClass(sidebarCookie);

/* sidebar menu show / hide */
$('.sidebar-toggler').click(function () {
    appBody.toggleClass('sidebar-hidden-md')
        .toggleClass('sidebar-hidden-lg');

    var bodyClass = '';
    if (appBody.hasClass('sidebar-hidden-lg')) {
        bodyClass = 'sidebar-hidden-lg';
    }

    $.cookie('sidebar-cookie', bodyClass, {
        path: baseUrl,
        expires: 30
    });
});
/* /sidebar menu show / hide */

/* heading icon animation for main menus */
$('.sidebar-heading').click(function () {
    var el = $(this);
    if (el.hasClass('open')) {
        el.removeClass('open').addClass('closed');
    }
    else {
        el.removeClass('closed').addClass('open');
    }
});
/* /heading icon animation for main menus */

/* init category counters async */
$("[name='init_category_counters']").on('click', function (e) {
    e.preventDefault();

    var total = $('#category-total-listings').text(); // need to get this from somewhere.

    var limit = 100;
    var offset = (-1) * limit;
    var button = $(this);
    var buttonValue = button.html();
    var progress = 0;
    button.html('Please wait..').attr('disabled', true);

    function countListingsByCategory() {
        offset += limit;
        if (offset > total) {
            button.attr('disabled', false).html(buttonValue);
            $('#category-counters-progress').html(total + ' listings counted.');
            return;
        }

        $.ajax({
            url: button.data('post-url'),
            dataType: "json",
            data: {
                limit: limit,
                offset: offset
            },
            cache: false,
            success: function (data) {
                progress += data.counter;
                $('#category-counters-progress').html(progress + '/' + total + ' listings counted.');
                countListingsByCategory();
            }
        });
    }

    countListingsByCategory();
});
/* /init category counters async */

/* clear cache async; delete cached images async */
$(".btn-clear-cache").on('click', function (e) {
    e.preventDefault();

    var button = $(this);
    var buttonValue = button.html();
    button.html('Please wait..').attr('disabled', true);

    $.ajax({
        url: button.data('post-url'),
        dataType: "json",
        cache: false,
        success: function (data) {
            button.attr('disabled', false).html(buttonValue);
            $('#' + button.data('progress-bar-id')).html(data.message);
        }
    });
});
/* /clear cache async; delete cached images async */

/* activate search autocomplete plugin for the quick navigation input field */
$('#quick-navigation').searchAutocomplete();