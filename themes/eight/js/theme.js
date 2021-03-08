/**
 * @version 8.0 [rev.8.0.03]
 */

/* header categories dropdown */
$('#category-select-btn').click(function (e) {
    e.preventDefault();

    $('#category-select-content').slideToggle('500');
});

$('#category-select-content').find('a').click(function (e) {
    e.preventDefault();

    var categoryName = $(this).attr('data-category-name');
    var categoryId = $(this).attr('data-id');
    var searchForm = $(this).closest('form');

    searchForm.find('input[name="parent_id"]').val(categoryId);

    $('#category-select-btn').find('span').text(categoryName);
    $('#category-select-content').hide('500');
});
/* /header categories dropdown */

/* mobile navbar */
/* convert to navbar fixed top if scrollTop < 100 and a navbar button is clicked */
$('#mobileHeaderMenu, #mobileSearchForm')
    .on('show.bs.collapse', function () {
        $(this).parent().addClass('navbar-force-fixed-top');
    })
    .on('hide.bs.collapse', function () {
        $(this).parent().removeClass('navbar-force-fixed-top');
    });
/* /convert to navbar fixed top if scrollTop < 100 and a navbar button is clicked */

/* navbar fixed top on scroll down */
$(window).scroll(function () {
    if ($(window).scrollTop() >= 100) {
        $('.mobile-navbar').find('.navbar').addClass('navbar-fixed-top');
    }
    else {
        $('.mobile-navbar').find('.navbar').removeClass('navbar-fixed-top');
    }
});
/* /navbar fixed top on scroll down */
/* /mobile navbar */
