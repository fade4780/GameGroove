document.addEventListener('DOMContentLoaded', function() {
    const mainImage = document.querySelector('.main-image');
    const thumbnails = document.querySelectorAll('.thumbnail');
    
    // Set initial active state
    if (thumbnails.length > 0) {
        thumbnails[0].classList.add('active');
    }
    
    // Function to update main image
    function updateMainImage(src, thumbnail) {
        mainImage.src = src;
        mainImage.style.opacity = '0';
        
        // Remove active class from all thumbnails
        thumbnails.forEach(thumb => thumb.classList.remove('active'));
        
        // Add active class to clicked thumbnail
        thumbnail.classList.add('active');
        
        // Fade in animation for main image
        setTimeout(() => {
            mainImage.style.opacity = '1';
        }, 50);
    }
    
    // Add click event listeners to thumbnails
    thumbnails.forEach(thumbnail => {
        thumbnail.addEventListener('click', function() {
            // Update main image
            mainImage.style.opacity = '0';
            setTimeout(() => {
                mainImage.src = this.src;
                mainImage.style.opacity = '1';
            }, 300);

            // Update active state
            thumbnails.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        });
        
        // Add hover effect
        thumbnail.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05)';
        });
        
        thumbnail.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
    
    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        const activeThumb = document.querySelector('.thumbnail.active');
        if (!activeThumb) return;
        
        const currentIndex = Array.from(thumbnails).indexOf(activeThumb);
        let nextIndex;
        
        if (e.key === 'ArrowLeft') {
            nextIndex = currentIndex > 0 ? currentIndex - 1 : thumbnails.length - 1;
            updateMainImage(thumbnails[nextIndex].src, thumbnails[nextIndex]);
        } else if (e.key === 'ArrowRight') {
            nextIndex = currentIndex < thumbnails.length - 1 ? currentIndex + 1 : 0;
            updateMainImage(thumbnails[nextIndex].src, thumbnails[nextIndex]);
        }
    });

    // Optional: Preload images for smooth transitions
    thumbnails.forEach(thumbnail => {
        const img = new Image();
        img.src = thumbnail.src;
    });
}); 