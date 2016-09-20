// on page load
$(document).ready(function () {
    $(window).scroll(
        function () {
            // sticky navigation
            var navbar = $("#navbar");
            // scrollTop Button
            var scrollTop = $("#scrollTop");

            var winPos = $(window).scrollTop();
            if (winPos > 32) {
                navbar.addClass("fixed");
                scrollTop.fadeIn(800, "linear");
            } else {
                navbar.removeClass("fixed");
                scrollTop.fadeOut(800, "linear");
            }


        }
    );

    // scroll to top of window
    $("#scrollTop").click(function () {
        var completeCalled = false;
        $("html, body").animate(
            {scrollTop: "0"},
            {
                complete: function () {
                    if (!completeCalled) {
                        completeCalled = true;
                    }
                }
            }
        );
    });
});

