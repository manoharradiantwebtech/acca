$(document).ready(function() {
    $('a[data-toggle="collapse"]').click(function() {
        var $closestLi = $(this).closest('li');

        // Check if the li is open or closed
        if ($closestLi.hasClass('active')) {
            $closestLi.removeClass('active'); // Remove active class if li is open
        } else {
            $closestLi.addClass('active');    // Add active class if li is closed
        }
    });
});
