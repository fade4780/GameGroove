document.addEventListener('DOMContentLoaded', function() {
    const mediaUpload = document.getElementById('mediaUpload');
    const mediaInput = document.getElementById('images');
    const mediaPreview = document.getElementById('mediaPreview');
    const projectForm = document.getElementById('projectForm');
    let files = [];

    // Обработка клика по зоне загрузки
    mediaUpload.addEventListener('click', () => mediaInput.click());

    // Предотвращаем открытие файла при перетаскивании
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        mediaUpload.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    // Подсветка при перетаскивании
    ['dragenter', 'dragover'].forEach(eventName => {
        mediaUpload.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        mediaUpload.addEventListener(eventName, unhighlight, false);
    });

    function highlight() {
        mediaUpload.classList.add('dragover');
    }

    function unhighlight() {
        mediaUpload.classList.remove('dragover');
    }

    // Обработка загрузки файлов
    mediaUpload.addEventListener('drop', handleDrop, false);
    mediaInput.addEventListener('change', handleFiles, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const droppedFiles = dt.files;
        handleFiles({ target: { files: droppedFiles } });
    }

    function handleFiles(e) {
        const newFiles = Array.from(e.target.files);
        
        // Проверяем общее количество файлов
        if (files.length + newFiles.length > 5) {
            alert('Максимальное количество изображений - 5');
            return;
        }

        // Проверяем каждый файл
        newFiles.forEach(file => {
            if (!file.type.startsWith('image/')) {
                alert('Пожалуйста, загружайте только изображения');
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                alert('Размер каждого файла не должен превышать 5 МБ');
                return;
            }

            files.push(file);
            previewFile(file);
        });
    }

    function previewFile(file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewItem = document.createElement('div');
            previewItem.className = 'preview-item';
            
            const img = document.createElement('img');
            img.src = e.target.result;
            
            const actions = document.createElement('div');
            actions.className = 'actions';
            
            const setMainBtn = document.createElement('button');
            setMainBtn.innerHTML = '<i class="ri-star-line"></i>';
            setMainBtn.title = 'Сделать главным';
            setMainBtn.onclick = function() {
                setMainImage(previewItem);
            };
            
            const removeBtn = document.createElement('button');
            removeBtn.innerHTML = '<i class="ri-delete-bin-line"></i>';
            removeBtn.title = 'Удалить';
            removeBtn.onclick = function() {
                removeImage(previewItem, file);
            };
            
            actions.appendChild(setMainBtn);
            actions.appendChild(removeBtn);
            
            previewItem.appendChild(img);
            previewItem.appendChild(actions);
            mediaPreview.appendChild(previewItem);
        };
        reader.readAsDataURL(file);
    }

    function setMainImage(previewItem) {
        // Удаляем метку главного изображения у всех
        document.querySelectorAll('.main-badge').forEach(badge => badge.remove());
        
        // Добавляем метку главного изображения
        const mainBadge = document.createElement('div');
        mainBadge.className = 'main-badge';
        mainBadge.textContent = 'Главное';
        previewItem.appendChild(mainBadge);
        
        // Перемещаем выбранное изображение в начало
        mediaPreview.insertBefore(previewItem, mediaPreview.firstChild);
    }

    function removeImage(previewItem, file) {
        previewItem.remove();
        files = files.filter(f => f !== file);
    }

    // Валидация формы
    projectForm.addEventListener('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
        }
    });

    function validateForm() {
        const title = document.getElementById('title').value.trim();
        const description = document.getElementById('description').value.trim();
        const category = document.getElementById('category_id').value;
        const goalAmount = document.getElementById('goal_amount').value;
        const duration = document.getElementById('duration').value;

        if (!title) {
            showError('Введите название проекта');
            return false;
        }

        if (!description) {
            showError('Введите описание проекта');
            return false;
        }

        if (!category) {
            showError('Выберите категорию');
            return false;
        }

        if (!goalAmount || goalAmount < 10000) {
            showError('Минимальная сумма финансирования - 10 000 ₽');
            return false;
        }

        if (!duration || duration < 1 || duration > 90) {
            showError('Длительность кампании должна быть от 1 до 90 дней');
            return false;
        }

        if (files.length === 0) {
            showError('Загрузите хотя бы одно изображение');
            return false;
        }

        return true;
    }

    function showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-error';
        errorDiv.textContent = message;
        
        const existingError = document.querySelector('.alert-error');
        if (existingError) {
            existingError.remove();
        }
        
        projectForm.insertBefore(errorDiv, projectForm.firstChild);
    }
});

// Функция предпросмотра проекта
function previewProject() {
    const title = document.getElementById('title').value;
    const description = document.getElementById('description').value;
    const category = document.getElementById('category_id').options[document.getElementById('category_id').selectedIndex].text;
    const goalAmount = document.getElementById('goal_amount').value;
    const duration = document.getElementById('duration').value;
    const images = Array.from(document.querySelectorAll('.preview-item img'));
    
    // Сортируем изображения: главное изображение должно быть первым
    const mainImage = document.querySelector('.main-badge')?.closest('.preview-item')?.querySelector('img');
    if (mainImage) {
        const mainIndex = images.indexOf(mainImage);
        if (mainIndex > -1) {
            images.splice(mainIndex, 1);
            images.unshift(mainImage);
        }
    }

    // Создаем модальное окно для предпросмотра
    const modal = document.createElement('div');
    modal.className = 'preview-modal';
    modal.innerHTML = `
        <div class="preview-content">
            <h2>Предпросмотр проекта</h2>
            
            <div class="preview-section">
                <h3><i class="ri-game-line"></i>${title || 'Название проекта'}</h3>
                <span class="preview-category"><i class="ri-folder-line"></i>${category || 'Не выбрана'}</span>
            </div>

            <div class="preview-section">
                <h4><i class="ri-file-text-line"></i>Описание</h4>
                <p>${description || 'Описание проекта'}</p>
            </div>

            <div class="preview-section">
                <h4><i class="ri-image-line"></i>Изображения</h4>
                <div class="preview-gallery">
                    ${images.map(img => `
                        <div class="preview-gallery-item">
                            <img src="${img.src}" alt="Preview">
                        </div>
                    `).join('')}
                </div>
            </div>

            <div class="preview-section">
                <h4><i class="ri-funds-line"></i>Параметры кампании</h4>
                <div class="preview-stats">
                    <div class="preview-stat">
                        <h5>Цель финансирования</h5>
                        <p>${goalAmount ? goalAmount + ' ₽' : 'Не указана'}</p>
                    </div>
                    <div class="preview-stat">
                        <h5>Длительность</h5>
                        <p>${duration ? duration + ' дней' : 'Не указана'}</p>
                    </div>
                </div>
            </div>

            <div class="preview-actions">
                <button onclick="this.closest('.preview-modal').remove()">
                    <i class="ri-close-line"></i>
                    Закрыть
                </button>
                <button onclick="document.getElementById('projectForm').submit()">
                    <i class="ri-rocket-line"></i>
                    Создать проект
                </button>
            </div>
        </div>
    `;

    document.body.appendChild(modal);
} 