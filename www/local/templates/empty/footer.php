<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
	die();
?>

    <div class="appointment-modal" id="appointmentModal" style="display:none;">
        <div class="appointment-modal-content">
            <span class="appointment-modal-close" id="closeAppointmentModal">&times;</span>
            <form id="appointmentForm">
                <?=bitrix_sessid_post()?>
                <h2>Запись на приём</h2>
                <label>Филиал:</label>
                <select name="clinic" id="clinicSelect" required>
                    <option value="">Выберите филиал</option>
                    <?php
                    // Получаем филиалы из инфоблока 1
                    if (CModule::IncludeModule("iblock")) {
                        $res = CIBlockElement::GetList(
                            ["NAME" => "ASC"],
                            ["IBLOCK_ID" => 1, "ACTIVE" => "Y"],
                            false, false,
                            ["ID", "NAME"]
                        );
                        while ($ob = $res->GetNext()) {
                            echo '<option value="'.$ob["ID"].'">'.htmlspecialchars($ob["NAME"]).'</option>';
                        }
                    }
                    ?>
                </select>

                <label>Отделение:</label>
                <select name="department" id="departmentSelect" required>
                    <option value="">Сначала выберите филиал</option>
                </select>

                <label>Специалист:</label>
                <select name="staff" id="staffSelect" required>
                    <option value="">Сначала выберите отделение</option>
                </select>

                <label>ФИО:</label>
                <input type="text" name="fio" required>

                <label>Телефон:</label>
                <input type="tel" name="phone" id="phoneInput" required placeholder="+7 (___) ___-__-__">

                <label>Email:</label>
                <input type="email" name="email" required>

                <label>Дата рождения:</label>
                <input type="date" name="birthdate" required>

                <label>Адрес:</label>
                <input type="text" name="address">

                <label>Дата и время приёма:</label>
                <input type="text" name="appointment_datetime" placeholder="например: 12.06.2024 15:00">


                <label>Сообщение:</label>
                <textarea name="message" rows="3"></textarea>

                <button type="submit" class="main-appointment-btn">Отправить</button>
            </form>
        </div>
    </div>

	</body>
</html>