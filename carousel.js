// Carousel implementation with seamless scrolling and consistent height
(function($) {
    'use strict';

    class InfiniteCarousel {
        constructor(element) {
            this.$carousel = $(element);
            this.$items = this.$carousel.find('.carousel-item');
            this.itemCount = this.$items.length;
            this.currentIndex = 0;
            this.isAnimating = false;
            
            // Clone first and last items for seamless scrolling
            this.$firstClone = this.$items.first().clone();
            this.$lastClone = this.$items.last().clone();
            
            // Insert clones
            this.$items.first().before(this.$lastClone);
            this.$items.last().after(this.$firstClone);
            
            // Calculate item width and set container width
            this.itemWidth = this.$items.first().outerWidth();
            this.$carousel.css('width', this.itemWidth * (this.itemCount + 2));
            
            // Initialize CSS
            this.$carousel.css({
                'transform': 'translateX(-' + this.itemWidth + 'px)',
                'transition': 'transform 0.5s ease-in-out'
            });
            
            // Setup event listeners
            this.setupEventListeners();
        }

        setupEventListeners() {
            this.$carousel.on('transitionend', () => {
                if (!this.isAnimating) return;
                
                // Handle seamless scrolling
                if (this.currentIndex === this.itemCount) {
                    this.$carousel.css({
                        'transition': 'none',
                        'transform': 'translateX(-' + this.itemWidth + 'px)'
                    });
                    this.currentIndex = 0;
                } else if (this.currentIndex === -1) {
                    this.$carousel.css({
                        'transition': 'none',
                        'transform': 'translateX(-' + (this.itemCount * this.itemWidth) + 'px)'
                    });
                    this.currentIndex = this.itemCount - 1;
                }
                
                this.$carousel.css('transition', 'transform 0.5s ease-in-out');
                this.isAnimating = false;
            });
        }

        next() {
            if (this.isAnimating) return;
            
            this.isAnimating = true;
            this.currentIndex++;
            const translateX = -this.currentIndex * this.itemWidth;
            this.$carousel.css('transform', 'translateX(' + translateX + 'px)');
        }

        prev() {
            if (this.isAnimating) return;
            
            this.isAnimating = true;
            this.currentIndex--;
            const translateX = -this.currentIndex * this.itemWidth;
            this.$carousel.css('transform', 'translateX(' + translateX + 'px)');
        }
    }

    // Initialize carousels when document is ready
    $(document).ready(function() {
        $('.carousel-container').each(function() {
            new InfiniteCarousel(this);
        });
    });

    // Export for testing
    window.InfiniteCarousel = InfiniteCarousel;
})(jQuery);
