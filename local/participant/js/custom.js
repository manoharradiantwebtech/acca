
    document.addEventListener("DOMContentLoaded", function () {
        const stars = document.querySelectorAll(".star");

        stars.forEach(function (star) {
            star.addEventListener("mouseover", function () {
                const rating = this.getAttribute("data-rating");
                highlightStars(rating);
            });

            star.addEventListener("mouseout", function () {
                const currentRating = document.getElementById("current-rating").textContent;
                highlightStars(currentRating);
            });

            star.addEventListener("click", function () {
                const rating = this.getAttribute("data-rating");
                document.getElementById("current-rating").textContent = rating;
            });
        });

        function highlightStars(rating) {
            stars.forEach(function (star) {
                const starRating = star.getAttribute("data-rating");
                star.classList.toggle("active", starRating <= rating);
            });
        }
    });



/*
$(document).ready(function() {F
    // Toggle the active class on click
  console.log('hello');
  $('.block_calendar_month').show();
});
 */
/*
$(document).ready(function() {
    // Toggle the active class on click
    console.log('hello');
    $('.block_calendar_month').show();
});
 */
