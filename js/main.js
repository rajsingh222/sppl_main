




(function ($) {
    "use strict";

    // Spinner
    var spinner = function () {
        setTimeout(function () {
            if ($('#spinner').length > 0) {
                $('#spinner').removeClass('show');
            }
        }, 1);
    };
    spinner();
    
    
    // Initiate the wowjs
    new WOW().init();


    // Sticky Navbar
    $(window).scroll(function () {
        if ($(this).scrollTop() > 45) {
            $('.navbar').addClass('sticky-top shadow-sm');
        } else {
            $('.navbar').removeClass('sticky-top shadow-sm');
        }
    });
    
    

    // Facts counter
    $('[data-toggle="counter-up"]').counterUp({
        delay: 10,
        time: 2000
    });
    
    
    // Back to top button
    $(window).scroll(function () {
        if ($(this).scrollTop() > 100) {
            $('.back-to-top').fadeIn('slow');
        } else {
            $('.back-to-top').fadeOut('slow');
        }
    });
    $('.back-to-top').click(function () {
        $('html, body').animate({scrollTop: 0}, 150, 'easeInOutExpo');
        return false;
    });


    // Testimonials carousel
    $(".testimonial-carousel").owlCarousel({
        autoplay: true,
        smartSpeed: 1500,
        dots: true,
        loop: true,
        center: true,
        responsive: {
            0:{
                items:1
            },
            576:{
                items:1
            },
            768:{
                items:2
            },
            992:{
                items:3
            }
        }
    });


   // Vendor carousel
   $('.vendor-carousel').owlCarousel({
    loop: true,
    margin: 45,
    dots: false,
    autoplay: true,
    autoplayTimeout: 1,      // Almost no delay between transitions
    autoplaySpeed: 5000,     // Adjust speed for smooth continuous movement
    smartSpeed: 5000,        // Ensures the same smoothness in animation
    autoplayHoverPause: false,
    slideTransition: 'linear', // Use a linear easing for continuous effect (if supported)
    responsive: {
        0: { items: 2 },
        576: { items: 4 },
        768: { items: 6 },
        992: { items: 8 }
    }
});

    
})(jQuery);

function toggleDiv(circleElem, id) {
    const allDivs = document.querySelectorAll('.toggle-div');

    // Hide all others
    allDivs.forEach(div => {
        if (div.id !== id) {
            div.style.display = 'none';
        }
    });

    const target = document.getElementById(id);
    const isVisible = target.style.display === 'block';

    if (isVisible) {
        target.style.display = 'none';
        return;
    }

    // Show target and position it
    target.style.display = 'block';
    target.style.position = 'absolute';

    const circleRect = circleElem.getBoundingClientRect();
    const containerRect = circleElem.parentElement.getBoundingClientRect();

    // Calculate positions relative to container
    const left = circleRect.left - containerRect.left + (circleRect.width / 2) - (target.offsetWidth / 2);
    const top = circleRect.top - containerRect.top + circleRect.height + 8; // 8px spacing

    target.style.left = `${left}px`;
    target.style.top = `${top}px`;
}
