document.addEventListener('DOMContentLoaded', function() {
    const mediaUpload = document.getElementById('mediaUpload');
    const fileInput = document.getElementById('project_images');
    const mediaPreview = document.getElementById('mediaPreview');
    const projectForm = document.getElementById('projectForm');

    // Обработка клика по зоне загрузки
    mediaUpload.addEventListener('click', function() {
        fileInput.click();
    });

    // Предотвращаем открытие файла при перетаскивании
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        mediaUpload.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    // Подсветка при перетаскивании
    mediaUpload.addEventListener('dragover', function() {
        mediaUpload.classList.add('dragover');
    });

    mediaUpload.addEventListener('dragleave', function() {
        mediaUpload.classList.remove('dragover');
    });

    mediaUpload.addEventListener('drop', function(e) {
        mediaUpload.classList.remove('dragover');
        const dt = new DataTransfer();
        
        // Добавляем существующие файлы
        if (fileInput.files.length > 0) {
            Array.from(fileInput.files).forEach(file => dt.items.add(file));
        }
        
        // Добавляем новые файлы
        Array.from(e.dataTransfer.files).forEach(file => {
            if (file.type.startsWith('image/')) {
                dt.items.add(file);
            }
        });
        
        fileInput.files = dt.files;
        updatePreview();
    });

    // Обработка выбора файлов
    fileInput.addEventListener('change', updatePreview);

    function updatePreview() {
        mediaPreview.innerHTML = '';
        const files = fileInput.files;

        if (files.length > 5) {
            alert('Максимальное количество изображений - 5');
            const dt = new DataTransfer();
            for (let i = 0; i < 5; i++) {
                dt.items.add(files[i]);
            }
            fileInput.files = dt.files;
        }

        Array.from(fileInput.files).forEach((file, index) => {
            if (!file.type.startsWith('image/')) return;

            const reader = new FileReader();
            const preview = document.createElement('div');
            preview.className = 'preview-item';

            reader.onload = function(e) {
                preview.innerHTML = `
                    <div class="preview-image-container">
                        <img src="${e.target.result}" alt="Preview">
                        <div class="preview-actions">
                            <button type="button" class="make-main" data-index="${index}" title="Сделать главным">
                                <i class="ri-star-${index === 0 ? 'fill' : 'line'}"></i>
                            </button>
                            <button type="button" class="remove-image" data-index="${index}" title="Удалить">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </div>
                    </div>
                    ${index === 0 ? '<div class="main-badge">Главное</div>' : ''}
                `;
            };

            reader.readAsDataURL(file);
            mediaPreview.appendChild(preview);
        });

        // Добавляем обработчики для кнопок
        setTimeout(() => {
            // Удаление изображения
            document.querySelectorAll('.remove-image').forEach(button => {
                button.addEventListener('click', function() {
                    const index = parseInt(this.dataset.index);
                    const dt = new DataTransfer();
                    const files = fileInput.files;

                    Array.from(files).forEach((file, i) => {
                        if (i !== index) dt.items.add(file);
                    });

                    fileInput.files = dt.files;
                    updatePreview();
                });
            });

            // Сделать главным
            document.querySelectorAll('.make-main').forEach(button => {
                button.addEventListener('click', function() {
                    const index = parseInt(this.dataset.index);
                    const dt = new DataTransfer();
                    const files = Array.from(fileInput.files);
                    
                    // Перемещаем выбранный файл в начало
                    const selectedFile = files.splice(index, 1)[0];
                    files.unshift(selectedFile);
                    
                    // Обновляем файлы
                    files.forEach(file => dt.items.add(file));
                    fileInput.files = dt.files;
                    updatePreview();
                });
            });
        }, 100);
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