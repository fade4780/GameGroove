document.addEventListener('DOMContentLoaded', function() {
    // Мобильное меню
    const menuToggle = document.querySelector('.menu-toggle');
    const navMenu = document.querySelector('.nav-menu');

    if (menuToggle && navMenu) {
        menuToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            menuToggle.classList.toggle('active');
        });

        // Закрываем меню при клике вне его
        document.addEventListener('click', function(event) {
            if (!navMenu.contains(event.target) && !menuToggle.contains(event.target)) {
                navMenu.classList.remove('active');
                menuToggle.classList.remove('active');
            }
        });
    }

    // Инвестиционные опции
    const investmentOptions = document.querySelectorAll('.investment-option');
    if (investmentOptions.length > 0) {
        investmentOptions.forEach(option => {
            option.addEventListener('click', function() {
                investmentOptions.forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
                
                // Если есть поле для пользовательской суммы
                const customAmount = document.querySelector('.custom-amount');
                if (customAmount) {
                    customAmount.style.display = this.dataset.custom ? 'block' : 'none';
                }
            });
        });
    }
}); 