(function ($) {
    "use strict";

    // Initialize Slick slider
    $(".variable-width").slick({
        dots: false,
        infinite: true,
        speed: 600, // Smooth transition speed
        slidesToShow: 3,
        slidesToScroll: 1,
        centerMode: false,
        arrows: true,
        autoplay: true,
        autoplaySpeed: 2000, // 2 seconds for auto slide
        pauseOnHover: false,
        responsive: [
            {
                breakpoint: 1024,
                settings: {
                    slidesToShow: 3,
                },
            },
            {
                breakpoint: 768,
                settings: {
                    slidesToShow: 2,
                },
            },
        ],
    });

    $(".variable-width").on("beforeChange", function() {
        $(".variable-width").slick("slickPause"); // Pause autoplay when manually clicking
        setTimeout(function() {
            $(".variable-width").slick("slickPlay"); // Resume autoplay after a short delay
        }, 100);
    });

    $(".variable-width .slick-slide").hover(
        function() {
            $(".variable-width").slick("slickSetOption", "speed", 600, true);
        },
        function() {
            $(".variable-width").slick("slickSetOption", "speed", 600, true);
        }
    );



    $(".free-course-banner").slick({
        dots: false,
        infinite: true,
        speed: 500,
        slidesToShow: 1,
        adaptiveHeight: true,
        arrows: true,
        autoplay: true,
        autoplaySpeed: 2000,
        prevArrow:
            '<button type="button" class="slick-prev"><i class="fa fa-arrow-left"></i></button>',
        nextArrow:
            '<button type="button" class="slick-next"><i class="fa fa-arrow-right"></i></button>',
    });

    $(".book-banner").slick({
        dots: false,
        infinite: true,
        speed: 500,
        slidesToShow: 1,
        adaptiveHeight: true,
        arrows: true,
        autoplay: true,
        autoplaySpeed: 2000,
        prevArrow:
            '<button type="button" class="slick-prev"><i class="fa fa-arrow-left"></i></button>',
        nextArrow:
            '<button type="button" class="slick-next"><i class="fa fa-arrow-right"></i></button>',
    });

    $(".blog-slide").slick({
        dots: true,
        infinite: true,
        speed: 500,
        slidesToShow: 1,
        adaptiveHeight: true,
        arrows: true,
        autoplay: true,
        autoplaySpeed: 2000,
        prevArrow:
            '<button type="button" class="slick-prev"><i class="fa fa-arrow-left"></i></button>',
        nextArrow:
            '<button type="button" class="slick-next"><i class="fa fa-arrow-right"></i></button>',
        responsive: [
            {
                breakpoint: 600,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    infinite: true,
                    dots: true,
                    arrows: false,
                },
            },
        ],
    });

    $(".home-course-category-slider").slick({
        autoplay: true,
        autoplaySpeed: 2000,
        dots: false,
        infinite: true,
        arrows: true,
        speed: 300,
        slidesToShow: 4,
        slidesToScroll: 4,
        responsive: [
            {
                breakpoint: 1024,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 3,
                    infinite: true,
                },
            },
            {
                breakpoint: 768,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 2,
                    initialSlide: 2,
                    arrows: false,
                    dots: true,
                },
            },

            {
                breakpoint: 425,
                settings: {
                    slidesToShow: 2,
                    arrows: false,
                    dots: true,
                },
            },
        ],
    });

    $(".student-review-slider").slick({
        dots: false,
        speed: 7000, // Set a higher speed for a smooth continuous effect
        slidesToShow: 3, // Number of slides visible at a time
        slidesToScroll: 1, // Number of slides to scroll (irrelevant in continuous scrolling)
        arrows: false, // Enable navigation arrows
        autoplay: true, // Enable auto slide
        autoplaySpeed: 0, // Speed for auto slide (irrelevant in continuous scrolling)
        infinite: true, // Enable infinite scrolling
        cssEase: "linear", // Linear transition for smooth continuous scrolling
        responsive: [
            {
                breakpoint: 1024, // Adjust settings for screens less than 1024px
                settings: {
                    slidesToShow: 3,
                },
            },
            {
                breakpoint: 768, // Adjust settings for screens less than 768px
                settings: {
                    slidesToShow: 2,
                    arrows: false,
                    dots: true,
                },
            },
            {
                breakpoint: 425, // Adjust settings for screens less than 425px
                settings: {
                    slidesToShow: 1,
                },
            },
        ],
    });

    $(".student-review-video-slider").slick({
        rtl: true, // Enable right-to-left scrolling
        dots: false,
        speed: 7000, // Set a higher speed for a smooth continuous effect
        slidesToShow: 3, // Number of slides visible at a time
        slidesToScroll: 1, // Number of slides to scroll (irrelevant in continuous scrolling)
        arrows: false, // Enable navigation arrows
        autoplay: true, // Enable auto slide
        autoplaySpeed: 0, // Speed for auto slide (irrelevant in continuous scrolling)
        infinite: true, // Enable infinite scrolling
        cssEase: "linear", // Linear transition for smooth continuous scrolling

        responsive: [
            {
                breakpoint: 1024, // Adjust settings for screens less than 1024px
                settings: {
                    slidesToShow: 3,
                },
            },
            {
                breakpoint: 768, // Adjust settings for screens less than 768px
                settings: {
                    slidesToShow: 2,
                    arrows: false,
                    dots: true,
                },
            },
            {
                breakpoint: 425, // Adjust settings for screens less than 425px
                settings: {
                    slidesToShow: 1,
                },
            },
        ],
    });
    $(".student-review-video-slider2").slick({
        dots: false,
        speed: 7000, // Set a higher speed for a smooth continuous effect
        slidesToShow: 3, // Number of slides visible at a time
        slidesToScroll: 1, // Number of slides to scroll (irrelevant in continuous scrolling)
        arrows: false, // Enable navigation arrows
        autoplay: true, // Enable auto slide
        autoplaySpeed: 0, // Speed for auto slide (irrelevant in continuous scrolling)
        infinite: true, // Enable infinite scrolling
        cssEase: "linear", // Linear transition for smooth continuous scrolling

        responsive: [
            {
                breakpoint: 1024, // Adjust settings for screens less than 1024px
                settings: {
                    slidesToShow: 3,
                },
            },
            {
                breakpoint: 768, // Adjust settings for screens less than 768px
                settings: {
                    slidesToShow: 2,
                    arrows: false,
                    dots: true,
                },
            },
            {
                breakpoint: 425, // Adjust settings for screens less than 425px
                settings: {
                    slidesToShow: 1,
                },
            },
        ],
    });


})(jQuery);
