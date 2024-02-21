
    $(document).ready(function () {
        $("label.in-progress").click(function () {
            $(".enrolled_course").show();
            $(".completedc").hide();
        });
        $("label.completed-course").click(function () {
            $(".completedc").show();
            $(".enrolled_course").hide();
        });
    });

    $(document).ready(function () {
        $("label.allcourses").click(function () {
            $(".completedc").show();
            $(".enrolled_course").show();
        });
    });
    $(document).ready(function () {
        // Toggle the active class on click
        $('#list li').click(function () {
            $('#list li').removeClass('active');
            $(this).addClass('active');
        });
    });
    $(document).ready(function () {
        //Toggle the active class on click
        $("span.tree_item.navtextcolor.hasicon:contains('My courses')").hide();
        $("span[title='GTA-Academy']").hide();
        $('span.tree_item.navtextcolor.hasicon:contains("Site home")').hide();
        $('span.tree_item.navtextcolor.branch:has(span:contains("Site pages"))').hide();
        $('span.tree_item.navtextcolor.branch.canexpand:has(a:contains("My courses"))').hide();
        $('span.tree_item.navtextcolor.branch:has(a:contains("My courses"))').hide();
        $('span.tree_item.navtextcolor.hasicon.active_tree_node:has(span:contains("Courses"))').hide();
        $('span.tree_item a:contains("Courses")').parent().hide();
    });
    $(document).ready(function () {
        // Check if we are on the specific page
        if (window.location.href.indexOf("/mod") > -1) {
            // Hide the block with a specific ID
            // $(".block_navigation").hide();
        }
    });
    if ($('.tree_item.navtextcolor.hasicon').text().indexOf('Dashboard') !== -1) {
        $('.mydashboardicon').hide();
    }

    $(document).ready(function () {
        var qno = parseInt($('.qno').text().match(/\d+/));
        if (qno) {
            var qno_second = qno - 1;
            $('.ques-list-block').slice(0, qno_second).addClass('answeredQ');
            $('.qno-' + qno).addClass('currentQ');
        }
    });

    $(document).ready(function () {
        $(".Arrow_btns").click(function () {
            var liElem = $(this).closest("li");
            if ($(this).hasClass("collapsed")) {
                liElem.addClass("added-border");

            } else {
                liElem.removeClass("added-border");
            }
        });
    });

    $(document).ready(function () {
        $('#message').hide(); // Hide the message div initially
        $('#MassageDiv').click(function (event) {
            event.preventDefault(); // Prevent the default link behavior
            $('#message').show().animate({
                bottom: 10,
                opacity: 1
            });
        });
    });

    $(document).ready(function () {
        $('#siq-minimize').click(function () {
            $('#message').animate({
                bottom: -$("#message").height(),
                opacity: 0
            });
        });
    });

    $(document).ready(function () {
        $("#sqico-send").click(function () {
            jQuery.ajax({
                type: "POST",
                url:  M.cfg.wwwroot +"/local/query/ajax.php",
                data: $('#msgForm').serialize(),
                success: function (data) {
                    if (data === "success") {
                        $('#msgForm').find("input[type=text], textarea").val("");
                        $('#message').animate({
                            bottom: -$("#message").height(),
                            opacity: 0
                        });
                    } else {
                        $('#msgerror').html(data);
                    }
                }
            });
        });
    });


    function radial_animate() {
        $("svg.radial-progress").each(function (index, value) {
            $(this).find($("circle.bar--animated")).removeAttr("style");
            // Get element in Veiw port
            var elementTop = $(this).offset().top;
            var elementBottom = elementTop + $(this).outerHeight();
            var viewportTop = $(window).scrollTop();
            var viewportBottom = viewportTop + $(window).height();

            if (elementBottom > viewportTop && elementTop < viewportBottom) {
                var percent = $(value).data("countervalue");
                var radius = $(this).find($("circle.bar--animated")).attr("r");
                var circumference = 2 * Math.PI * radius;
                var strokeDashOffset =
                    circumference - (percent * circumference) / 100;
                $(this).find($("circle.bar--animated")).animate(
                    {
                        "stroke-dashoffset": strokeDashOffset,
                    },
                    4000
                );
            }
        });
    }


// To check If it is in Viewport
    var $window = $(window);

    function check_if_in_view() {
        $(".countervalue").each(function () {
            if ($(this).hasClass("start")) {
                var elementTop = $(this).offset().top;
                var elementBottom = elementTop + $(this).outerHeight();

                var viewportTop = $(window).scrollTop();
                var viewportBottom = viewportTop + $(window).height();

                if (elementBottom > viewportTop && elementTop < viewportBottom) {
                    $(this).removeClass("start");
                    $(".countervalue").text();
                    var myNumbers = $(this).text();
                    if (myNumbers == Math.floor(myNumbers)) {
                        $(this).animate(
                            {
                                Counter: $(this).text(),
                            },
                            {
                                duration: 4000,
                                easing: "swing",
                                step: function (now) {
                                    $(this).text(Math.ceil(now) + "%");
                                },
                            }
                        );
                    } else {
                        $(this).animate(
                            {
                                Counter: $(this).text(),
                            },
                            {
                                duration: 4000,
                                easing: "swing",
                                step: function (now) {
                                    $(this).text(now.toFixed(2) + "$");
                                },
                            }
                        );
                    }

                    radial_animate();
                }
            }
        });
    }


    radial_animate();
    check_if_in_view();
    $window.on("scroll", check_if_in_view);
    $window.on("load", check_if_in_view);
