document.addEventListener("DOMContentLoaded", function() {
    var phoneMask = IMask(
        document.getElementById('phoneInput'), {
            mask: '+{7} (000) 000-00-00'
        }
    );

// Валидация обязательных полей
    document.getElementById('appointmentForm').onsubmit = function (e) {
        e.preventDefault();

        let form = this;
        let requiredFields = ['fio', 'phone', 'email', 'birthdate'];
        let valid = true;
        requiredFields.forEach(function (name) {
            let el = form.elements[name];
            if (!el || !el.value.trim()) {
                el.style.borderColor = 'red';
                valid = false;
            } else {
                el.style.borderColor = '';
            }
        });
        if (!valid) return;

        let formData = new FormData(form);
        formData.append('sessid', BX.message && BX.message('bitrix_sessid') ? BX.message('bitrix_sessid') : (form.sessid ? form.sessid.value : ''));
        formData.append('submit', 'Y');

        fetch('/local/ajax/appointment_send.php', {
            method: 'POST',
            body: formData
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('Заявка отправлена!');
                    form.reset();
                    document.getElementById('appointmentModal').style.display = 'none';
                } else {
                    alert(data.error || 'Ошибка отправки');
                }
            })
            .catch(() => alert('Ошибка соединения'));
    };

    document.getElementById('openAppointmentModal').onclick = function () {
        document.getElementById('appointmentModal').style.display = 'block';
    };
    document.getElementById('closeAppointmentModal').onclick = function () {
        document.getElementById('appointmentModal').style.display = 'none';
    };
    window.onclick = function (event) {
        if (event.target == document.getElementById('appointmentModal')) {
            document.getElementById('appointmentModal').style.display = 'none';
        }
    };

// Динамическая подгрузка отделений и специалистов
    document.getElementById('clinicSelect').onchange = function () {
        var clinicId = this.value;
        var departmentSelect = document.getElementById('departmentSelect');
        departmentSelect.innerHTML = '<option>Загрузка...</option>';
        fetch('/local/ajax/get_departments.php?clinic_id=' + clinicId)
            .then(response => response.json())
            .then(data => {
                departmentSelect.innerHTML = '<option value=\"\">Выберите отделение</option>';
                data.forEach(function (item) {
                    departmentSelect.innerHTML += '<option value=\"' + item.ID + '\">' + item.NAME + '</option>';
                });
                document.getElementById('staffSelect').innerHTML = '<option value=\"\">Сначала выберите отделение</option>';
            });
    };

    document.getElementById('departmentSelect').onchange = function () {
        var departmentId = this.value;
        var staffSelect = document.getElementById('staffSelect');
        staffSelect.innerHTML = '<option>Загрузка...</option>';
        fetch('/local/ajax/get_staff.php?department_id=' + departmentId)
            .then(response => response.json())
            .then(data => {
                staffSelect.innerHTML = '<option value=\"\">Выберите специалиста</option>';
                data.forEach(function (item) {
                    staffSelect.innerHTML += '<option value=\"' + item.ID + '\">' + item.NAME + '</option>';
                });
            });
    };

    document.getElementById('appointmentForm').onsubmit = function (e) {
        e.preventDefault();

        // Валидация обязательных полей
        let form = this;
        let requiredFields = ['clinic', 'department', 'staff', 'fio', 'phone', 'email'];
        let valid = true;
        requiredFields.forEach(function (name) {
            let el = form.elements[name];
            if (!el || !el.value.trim()) {
                el.style.borderColor = 'red';
                valid = false;
            } else {
                el.style.borderColor = '';
            }
        });
        if (!valid) return;

        // Формируем данные для отправки
        let formData = new FormData(form);
        formData.append('sessid', BX.message('bitrix_sessid')); // если BX.message доступен
        formData.append('submit', 'Y');

        fetch('/local/ajax/appointment_send.php', {
            method: 'POST',
            body: formData
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('Заявка отправлена!');
                    form.reset();
                    document.getElementById('appointmentModal').style.display = 'none';
                } else {
                    alert(data.error || 'Ошибка отправки');
                }
            })
            .catch(() => alert('Ошибка соединения'));
    };
})