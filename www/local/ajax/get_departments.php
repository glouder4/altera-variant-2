<?php
    define("NO_KEEP_STATISTIC", true);
    define("NOT_CHECK_PERMISSIONS", true);
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

    header('Content-Type: application/json; charset=UTF-8');

    if (!CModule::IncludeModule("iblock")) {
        echo json_encode([]);
        exit;
    }

    $clinicId = intval($_GET['clinic_id']);
    if ($clinicId <= 0) {
        echo json_encode([]);
        exit;
    }

    $result = [];
    $res = CIBlockElement::GetList(
        ["NAME" => "ASC"],
        [
            "IBLOCK_ID" => 3,
            "ACTIVE" => "Y",
            "PROPERTY_CLINIC_BRANCHES" => $clinicId
        ],
        false,
        false,
        ["ID", "NAME"]
    );

    while ($ob = $res->GetNext()) {
        $result[] = [
            "ID" => $ob["ID"],
            "NAME" => $ob["NAME"]
        ];
    }

    echo json_encode($result);