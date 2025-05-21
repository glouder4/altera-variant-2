<?php
function pre($o) {

    $bt = debug_backtrace();
    $bt = $bt[0];
    $dRoot = $_SERVER["DOCUMENT_ROOT"];
    $dRoot = str_replace("/", "\\", $dRoot);
    $bt["file"] = str_replace($dRoot, "", $bt["file"]);
    $dRoot = str_replace("\\", "/", $dRoot);
    $bt["file"] = str_replace($dRoot, "", $bt["file"]);
    ?>
    <div style='font-size:9pt; color:#000; background:#fff; border:1px dashed #000;text-align: left!important;'>
        <div style='padding:3px 5px; background:#99CCFF; font-weight:bold;'>File: <?= $bt["file"] ?> [<?= $bt["line"] ?>]</div>
        <pre style='padding:5px;'><? print_r($o) ?></pre>
    </div>
    <?
}

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

AddEventHandler("main", "OnBeforeUserUpdate", "OnBeforeUserUpdateHandler");

function OnBeforeUserUpdateHandler(&$arFields)
{
    if (!CModule::IncludeModule("main")) {
        return;
    }

    $OMS_GROUP = 6;    // ОМС
    $DMS_GROUP = 7;    // ДМС
    $NONRESIDENT_GROUP = 8; // Нерезиденты

    if (isset($arFields["GROUP_ID"]) && is_array($arFields["GROUP_ID"])) {
        // Получаем все ID групп
        $groupIds = array_column($arFields["GROUP_ID"], "GROUP_ID");

        // Если выбрана группа нерезидентов
        if (in_array($NONRESIDENT_GROUP, $groupIds)) {
            // Оставляем только те группы, которые не ОМС и не ДМС
            $arFields["GROUP_ID"] = array_filter(
                $arFields["GROUP_ID"],
                function($group) use ($OMS_GROUP, $DMS_GROUP) {
                    return $group["GROUP_ID"] != $OMS_GROUP && $group["GROUP_ID"] != $DMS_GROUP;
                }
            );
            // Переиндексация массива (Bitrix иногда чувствителен к этому)
            $arFields["GROUP_ID"] = array_values($arFields["GROUP_ID"]);
        }
    }
}

AddEventHandler("iblock", "OnAfterIBlockElementUpdate", "OnAfterIBlockElementUpdateHandler");

function OnAfterIBlockElementUpdateHandler(&$arFields)
{
    // Проверяем, что это нужный инфоблок
    if ($arFields["IBLOCK_ID"] == 5) {
        // Получаем свойства элемента
        $res = CIBlockElement::GetList(
            [],
            ["ID" => $arFields["ID"], "IBLOCK_ID" => 5],
            false,
            false,
            ["ID", "IBLOCK_ID"]
        );
        if ($ob = $res->GetNextElement()) {
            $props = $ob->GetProperties();
            // Проверяем свойство IS_CONFIRMED
            if (
                isset($props["IS_CONFIRMED"]["VALUE_XML_ID"]) &&
                $props["IS_CONFIRMED"]["VALUE_XML_ID"] == "YES"
            ) {

                // Отправка письма с подтверждением
                CEvent::Send("REQUEST_CONFIRMED", SITE_ID, []);
            }
            elseif(
                isset($props["IS_CONFIRMED"]["VALUE_XML_ID"]) &&
                $props["IS_CONFIRMED"]["VALUE_XML_ID"] == "NO"
            ){
                // Отправка письма с отменой
                CEvent::Send("REQUEST_DECLINED", SITE_ID, []);
            }
        }
    }
}
