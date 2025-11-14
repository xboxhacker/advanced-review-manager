/**
 * Advanced Review Manager - Frontend Scripts
 * File: assets/frontend-script.js
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        initFloatingWidget();
        initReviewCarousel();
    });

    /**
     * Initialize floating review notification widget
     */
    function initFloatingWidget() {
        if (typeof armRecentReviews === 'undefined' || !armRecentReviews.length) {
            return;
        }

        var $widget = $('#arm-floating-widget');
        if (!$widget.length) {
            return;
        }

        var currentIndex = 0;
        var isVisible = false;

        function showNextReview() {
            if (isVisible) {
                return;
            }

            var review = armRecentReviews[currentIndex];
            var productTitle = $('h1.product_title').length ? $('h1.product_title').text() : 'a product';
            
            // Get time ago
            var reviewDate = new Date(review.comment_date);
            var now = new Date();
            var diffMinutes = Math.floor((now - reviewDate) / 60000);
            var timeAgo = '';
            
            if (diffMinutes < 60) {
                timeAgo = diffMinutes + ' minutes ago';
            } else if (diffMinutes < 1440) {
                timeAgo = Math.floor(diffMinutes / 60) + ' hours ago';
            } else {
                timeAgo = Math.floor(diffMinutes / 1440) + ' days ago';
            }

            // Update widget content
            $widget.find('.arm-widget-customer').text(review.comment_author);
            $widget.find('.arm-widget-product').text(productTitle);
            $widget.find('.arm-widget-stars').html(getStars(parseInt(review.rating)));
            $widget.find('.arm-widget-time').text(timeAgo);

            // Show widget
            $widget.addClass('arm-widget-show');
            isVisible = true;

            // Hide after 5 seconds
            setTimeout(function() {
                $widget.removeClass('arm-widget-show');
                isVisible = false;
                
                // Move to next review
                currentIndex = (currentIndex + 1) % armRecentReviews.length;
            }, 5000);
        }

        // Show first review after 3 seconds
        setTimeout(showNextReview, 3000);

        // Show review every 30 seconds
        setInterval(function() {
            if (!isVisible) {
                showNextReview();
            }
        }, 30000);

        // Close button
        $widget.find('.arm-widget-close').on('click', function() {
            $widget.removeClass('arm-widget-show');
            isVisible = false;
        });
    }

    /**
     * Get star HTML
     */
    function getStars(rating) {
        var stars = '';
        for (var i = 0; i < 5; i++) {
            if (i < rating) {
                stars += '⭐';
            } else {
                stars += '☆';
            }
        }
        return stars;
    }

    /**
     * Initialize review carousel
     */
    function initReviewCarousel() {
        var $carousel = $('.arm-carousel-container');
        if (!$carousel.length) {
            return;
        }

        var currentSlide = 0;
        var $items = $carousel.find('.arm-carousel-item');
        var totalSlides = $items.length;

        if (totalSlides <= 1) {
            return;
        }

        // Clone first and last slides for infinite loop
        $carousel.append($items.first().clone());
        $carousel.prepend($items.last().clone());

        // Set initial position
        $carousel.css('transform', 'translateX(-100%)');

        function showSlide(index) {
            var offset = -(index + 1) * 100;
            $carousel.css({
                'transform': 'translateX(' + offset + '%)',
                'transition': 'transform 0.5s ease-in-out'
            });
        }

        function nextSlide() {
            currentSlide++;
            showSlide(currentSlide);

            if (currentSlide >= totalSlides) {
                setTimeout(function() {
                    $carousel.css('transition', 'none');
                    currentSlide = 0;
                    $carousel.css('transform', 'translateX(-100%)');
                    setTimeout(function() {
                        $carousel.css('transition', 'transform 0.5s ease-in-out');
                    }, 50);
                }, 500);
            }
        }

        // Auto advance every 5 seconds
        setInterval(nextSlide, 5000);
    }

    /**
     * Handle media upload preview
     */
    $('#review_photos').on('change', function(e) {
        var files = e.target.files;
        var preview = $('#photo-preview');
        
        if (!preview.length) {
            $(this).after('<div id="photo-preview" class="arm-media-preview"></div>');
            preview = $('#photo-preview');
        }
        
        preview.empty();
        
        for (var i = 0; i < files.length && i < 5; i++) {
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.append('<img src="' + e.target.result + '" style="width: 80px; height: 80px; object-fit: cover; margin: 5px;">');
            };
            reader.readAsDataURL(files[i]);
        }
    });

    /**
     * Star rating interactive
     */
    $('.comment-form-rating .stars').on('click', 'a', function(e) {
        e.preventDefault();
        var $star = $(this);
        var rating = $star.text();
        
        $star.siblings('a').removeClass('active');
        $star.prevAll('a').addBack().addClass('active');
        
        // Set rating value
        $('#rating').val(rating);
    });

})(jQuery);
