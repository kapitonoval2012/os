<?php

    // ** БЕЗОПАСНОСТЬ **
	// проверяем выдан ли доступ на вход на эту страницу
	// если нет $ACCESS['название раздела']['access'] или она равна FALSE прерываем работу скирпта 
	if(!@$ACCESS['admin']['access']) exit($ACCESS_NOTICE);
	// ** БЕЗОПАСНОСТЬ **

    ob_start();	
	
    switch ($section) {
		case 'price_manager':
		include 'price_manager/router.php';
		break;
			
		default:
		include 'controller.php';
		break;
	}
	
	$content = ob_get_contents();
	ob_get_clean();

	include ROOT.'/skins/tpl/admin/show.tpl';
   
?>