<?php
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

header('Content-Type: application/json; charset=UTF-8');

if (check_bitrix_sessid() && !empty($_REQUEST["submit"])) {
    // Получаем поля
    $fields = [
        'clinic'     => trim($_REQUEST['clinic']),
        'department' => trim($_REQUEST['department']),
        'staff'      => trim($_REQUEST['staff']),
        'fio'        => trim($_REQUEST['fio']),
        'phone'      => trim($_REQUEST['phone']),
        'email'      => trim($_REQUEST['email']),
        'message'    => trim($_REQUEST['message']),
        'birthdate'  => trim($_REQUEST['birthdate']),
        'address'  => trim($_REQUEST['address']),
        'appointment_datetime'  => trim($_REQUEST['appointment_datetime']),
    ];
    $clinicName = getElementNameById($fields['clinic'], 1);
    $departmentName = getElementNameById($fields['department'], 3);
    $staffName = getElementNameById($fields['staff'], 4);

    // Валидация
    foreach (['fio','phone','email','birthdate'] as $f) {
        if (empty($fields[$f])) {
            echo json_encode(['success' => false, 'error' => 'Заполните все обязательные поля']);
            exit;
        }
    }

    global $USER;
    if (!CModule::IncludeModule("iblock") || !CModule::IncludeModule("main")) {
        echo json_encode(['success' => false, 'error' => 'Не удалось подключить модули']);
        exit;
    }

// 1. Поиск/создание пользователя
    $user = false;
    $rsUser = CUser::GetList($by="id", $order="asc", ["EMAIL" => $fields['email']]);
    if ($arUser = $rsUser->Fetch()) {
        $userId = $arUser['ID'];
    } else {
        $password = Main\Security\Random(8);
        $user = new CUser;
        $arFields = [
            "NAME" => $fields['fio'],
            "EMAIL" => $fields['email'],
            "LOGIN" => $fields['email'],
            "LID" => SITE_ID,
            "ACTIVE" => "Y",
            "GROUP_ID" => [5],
            "PASSWORD" => $password,
            "CONFIRM_PASSWORD" => $password,
        ];
        $userId = $user->Add($arFields);
        if (intval($userId) <= 0) {
            echo json_encode(['success' => false, 'error' => 'Ошибка создания пользователя: '.$user->LAST_ERROR]);
            exit;
        }
        else{
            // Формируем массив для письма
            $arEventFields = [
                "FIO" => $fields['fio'],
                "EMAIL" => $fields['email'],
                "PHONE" => $fields['phone'],
                "BIRTHDATE" => $fields['birthdate'],
                "ADDRESS" => $fields['address'],
            ];

            // Отправка письма
            CEvent::Send("NEW_USER", SITE_ID, $arEventFields);
        }
    }

// 2. Добавление в инфоблок
    $el = new CIBlockElement;
    $PROP = [
        "PATIENT" => $userId,
        "CLINIC" => $fields['clinic'],
        "CLINIC_BRANCH" => $fields['department'],
        "CLINIC_SPECIALIST" => $fields['staff'],
        "PATIENT_PHONE" => $fields['phone'],
        "PATIENT_ADRESS" => $fields['address'],
        "DATE_TI_REQUEST" => $fields['appointment_datetime'],
        "PATENT_MESSAGE" => $fields['message'],
        "PATIEN_BIRTH_DATE" => $fields['birthdate'],
    ];
    $arLoadProductArray = [
        "IBLOCK_ID" => 5,
        "PROPERTY_VALUES" => $PROP,
        "NAME" => $fields['fio'],
        "ACTIVE" => "Y",
    ];
    if (!$el->Add($arLoadProductArray)) {
        echo json_encode(['success' => false, 'error' => 'Ошибка добавления в инфоблок: '.$el->LAST_ERROR]);
        exit;
    }

// 3. Создание веб-формы

    if (CModule::IncludeModule("form")) {
        $formId = 1; // ID вашей веб-формы
        $arValues = [
            "form_text_1" => $clinicName,                // Филиал
            "form_text_2" => $departmentName,            // Отделение
            "form_text_3" => $staffName,                 // Специалист
            "form_text_4" => $fields['fio'],                   // ФИО
            "form_text_5" => $fields['phone'],                 // Телефон
            "form_text_6" => $fields['email'],                 // Email
            "form_text_7" => $fields['birthdate'],             // Дата рождения
            "form_text_8" => $fields['address'],               // Адрес
            "form_text_9" => $fields['appointment_datetime'],  // Дата и время приёма
            "form_textarea_10" => $fields['message'],          // Сообщение
        ];
        $RESULT_ID = 0;
        if (CFormResult::Add($formId, $arValues, "N", $RESULT_ID)) {
            // Успех, результат добавлен
            echo json_encode(['success' => true, 'fields' => $fields]);
            exit;
        } else {
            echo json_encode(['success' => false, 'error' => 'Ошибка добавления результата веб-формы']);
            exit;
        }
    }
}

echo json_encode(['success' => false, 'error' => 'Ошибка сессии или submit']);