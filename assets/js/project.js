document.addEventListener('DOMContentLoaded', function() {
    const mainImage = document.querySelector('.project-main-image img');
    const thumbnails = document.querySelectorAll('.project-thumbnails img');
    
    // Add click handlers to thumbnails
    thumbnails.forEach(thumbnail => {
        thumbnail.addEventListener('click', function() {
            // Update main image
            mainImage.src = this.src;
            
            // Remove active class from all thumbnails
            thumbnails.forEach(thumb => thumb.classList.remove('active'));
            
            // Add active class to clicked thumbnail
            this.classList.add('active');
        });
    });

    // Initialize first thumbnail as active
    if (thumbnails.length > 0) {
        thumbnails[0].classList.add('active');
    }
}); 