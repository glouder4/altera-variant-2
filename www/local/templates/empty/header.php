<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
	die();
?>
<!DOCTYPE html>
<html>
	<head>
		<?$APPLICATION->ShowHead();?>
		<title><?$APPLICATION->ShowTitle();?></title>
		<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />

        <script src="https://unpkg.com/imask"></script>
        <?
            use Bitrix\Main\Page\Asset;
            Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/template_script.js",true);
        ?>
	</head>
	<body>
		<div id="panel">
			<?$APPLICATION->ShowPanel();?>
		</div>

    <header class="main-header">
        <div class="header-logo">
            <!-- Замените src на свой логотип или используйте текст -->
            <img src="/local/templates/empty/assets/logo_new.svg" alt="НЕОМЕД" style="height:40px;">
            <!-- или просто <span>НЕОМЕД</span> -->
        </div>
        <div class="header-contacts">
            <div class="header-phone">
                <span class="phone-number">+7 (347) 225-25-27</span>
                <div class="phone-desc">Телефон приёмной</div>
            </div>
            <button class="header-btn" id="openAppointmentModal">Написать</button>
        </div>
    </header>