<?php

    define("NO_KEEP_STATISTIC", true);
    define("NOT_CHECK_PERMISSIONS", true);
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

    header('Content-Type: application/json; charset=UTF-8');

    if (!CModule::IncludeModule("iblock")) {
        echo json_encode([]);
        exit;
    }

    $departmentId = intval($_GET['department_id']);
    if ($departmentId <= 0) {
        echo json_encode([]);
        exit;
    }

    // Получаем отделение и его STAFF_LIST
    $res = CIBlockElement::GetList(
        [],
        [
            "IBLOCK_ID" => 3,
            "ID" => $departmentId,
            "ACTIVE" => "Y"
        ],
        false,
        false,
        ["ID", "NAME", "PROPERTY_STAFF_LIST"]
    );

    $staffIds = [];
    if ($ob = $res->GetNext()) {
        if (!empty($ob["PROPERTY_STAFF_LIST_VALUE"])) {
            if (is_array($ob["PROPERTY_STAFF_LIST_VALUE"])) {
                $staffIds = $ob["PROPERTY_STAFF_LIST_VALUE"];
            } else {
                $staffIds = [$ob["PROPERTY_STAFF_LIST_VALUE"]];
            }
        }
    }

    $result = [];
    if (!empty($staffIds)) {
        $staffRes = CIBlockElement::GetList(
            ["NAME" => "ASC"],
            [
                "IBLOCK_ID" => 4,
                "ID" => $staffIds,
                "ACTIVE" => "Y"
            ],
            false,
            false,
            ["ID", "NAME"]
        );
        while ($staff = $staffRes->GetNext()) {
            $result[] = [
                "ID" => $staff["ID"],
                "NAME" => $staff["NAME"]
            ];
        }
    }

    echo json_encode($result);
