function showDimensionImage(imageNumber) {
    const images = document.querySelectorAll('.img-fluid');

    if(images) {
        images.forEach((image, index) => {
            if(index + 1 === imageNumber) {
                image.classList.add('show');
            } else {
                image.classList.remove('show');
            }
        });
    }
}
function validatePhone(phone) {
    // Удаляем все нецифровые символы
    const cleanPhone = phone.replace(/\D/g, '');

    // Проверяем, что номер состоит из 10 или 11 цифр
    if (cleanPhone.length === 10) {
        return true;
    } else if (cleanPhone.length === 11) {
        // Если 11 цифр, первая должна быть 7 или 8
        return ['7', '8'].includes(cleanPhone[0]);
    }

    return false;
}

function submitOrder() {
    const name = document.getElementById('name').value;
    const phone = document.getElementById('phone').value;

    if (!validatePhone(phone)) {
        showNotification('warning', 'Введите корректный номер телефона.');
        return;
    }

    if (name && phone) {
        const formData = new FormData(document.getElementById('orderForm'));

        fetch('index.php?route=extension/module/sizecalculator/sendMail', {
            method: 'POST',
            body: formData,
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('success', 'Ваш заказ был успешно отправлен!');
                    $('#orderModal').modal('hide');
                    showSuccessMessage('Спасибо за ваш заказ! Мы свяжемся с вами в ближайшее время.');
                } else {
                    showNotification('danger', 'Произошла ошибка при отправке заказа.');
                }
            })
            .catch(error => {
                console.error('Ошибка:', error);
                showNotification('danger', 'Произошла ошибка при отправке заказа.');
            });
    } else {
        showNotification('warning', 'Пожалуйста, заполните все поля.');
    }
}

function showSuccessMessage(message) {
    // Создаем элемент уведомления
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible';
    alertDiv.innerHTML = `
        <i class="fa fa-check-circle"></i> ${message}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    `;

    // Находим контейнер для уведомлений
    const container = document.querySelector('.sizecalculator-module');

    // Вставляем уведомление в начало контейнера
    container.insertBefore(alertDiv, container.firstChild);

    // Автоматически скрываем уведомление через 5 секунд
    setTimeout(() => {
        $(alertDiv).alert('close');
    }, 5000);
}

function showNotification(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible`;
    alertDiv.setAttribute('role', 'alert');
    alertDiv.innerHTML = `
        <i class="fa fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    `;

    const modalBody = document.querySelector('.modal-body');
    modalBody.insertBefore(alertDiv, modalBody.firstChild);

    // Автоматически скрыть уведомление через 5 секунд
    setTimeout(() => {
        $(alertDiv).alert('close');
    }, 5000);
}