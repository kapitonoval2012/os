<?php	
	// инициализация класса формы
	$post = isset($_POST)?$_POST:array();
	$get = isset($_GET)?$_GET:array();
	$FORM = new Forms($get,$post,$_SESSION);

	// инициализация класса работы с некаталожными позициями
	$POSITION_NO_CAT = new Position_no_catalog($get,$post,$_SESSION);

	/*******************************   AJAX   ***********************************/
	## GET
	if(isset($_GET['AJAX']) && $_GET['AJAX']=="get_uslugi_list_Database_Html"){
		
	}

	## POST
	if(isset($_POST['AJAX'])){	
		// меняем статус для группы вариантов
		if($_POST['AJAX'] == 'change_status_gl'){
			$POSITION_NO_CAT->change_status_gl_Database();
			exit;
		}

		// мен ставит на паузу
		if($_POST['AJAX'] == 'change_status_gl_pause'){
			$POSITION_NO_CAT->change_status_gl_pause_Database();
			exit;
		}

		// редактируем дату подачи макета
		if($_POST['AJAX'] == 'change_maket_date'){
			$POSITION_NO_CAT->change_maket_date_Database();
			exit;
		}

		// редактируем no_cat_json
		if($_POST['AJAX'] == 'change_no_cat_json'){
			$POSITION_NO_CAT->change_no_cat_json_Database();
			exit;
		}

		// редактируем количество рабочих дней на изготовление продукции
		if($_POST['AJAX'] == 'edit_work_days'){
			$POSITION_NO_CAT->edit_work_days_Database();
			exit;
		}

		// редактируем комменты снаба
		if ($_POST['AJAX'] == 'edit_snab_comment') {
			$POSITION_NO_CAT->edit_snab_comment_Database();
			exit;
		}

		// добаление данных, прикрепление новой услуги к расчёту
		if($_POST['AJAX']=='add_new_usluga'){
			$POSITION_NO_CAT->add_uslug_Database_Html($_POST['id_uslugi'],$_POST['dop_row_id'],$_POST['quantity']);
			exit;
		}

		if($_POST['AJAX']=='chose_supplier'){

			// запоминаем id уже выбранных поставщиков
			$already_chosen_arr = explode(',', $_POST['already_chosen']);


			$suppliers_arr = Supplier::get_all_suppliers_Database_Array();
			$html = '<form>';
			$html .='<table id="chose_supplier_tbl">';

			$n=0;
			for ($i=1; $i < count($suppliers_arr); $i++) {
				$html .= '<tr>';
			    for ($j=1; $j<=3; $j++) {
			    	$checked = '';
			    	foreach ($already_chosen_arr as $key => $id) {
			    		if($suppliers_arr[$i]['id']==trim($id)){
			    			$checked = 'class="checked"';
			    		}
			    	}
			    	$html .= (isset($suppliers_arr[$i]['nickName']))?'<td '.$checked.' data-id="'.$suppliers_arr[$i]['id'].'">'.$suppliers_arr[$i]['nickName']."</td>":"<td></td>";
			    	$i++;
			    }

			    $html .= '</tr>';
			}
			$html .= '</table>';
			$html .= '<input type="hidden" name="AJAX" value="change_supliers_info_dop_data">';
			$html .= '<input type="hidden" name="dop_data_id" value="">';
			$html .= '<input type="hidden" name="suppliers_id" value="">';
			$html .= '<input type="hidden" name="suppliers_name" value="">';
			$html .= '</form>';
			echo $html;
			exit;
		}

		if($_POST['AJAX'] == 'change_supliers_info_dop_data'){
			$POSITION_NO_CAT->change_supliers_info_dop_data_Database();
			exit;
		}



		if($_POST['AJAX'] == 'save_new_price_dop_uslugi'){
			// редактирование цены в прикреплённой услуге
			$POSITION_NO_CAT->save_edit_price_dop_uslugi_Database();
			exit;
		}

		if($_POST['AJAX']=='save_new_price_dop_data'){
			$POSITION_NO_CAT->change_dop_data_Database();
			exit;
		}

		if($_POST['AJAX']=='delete_usl_of_variant'){
			Position_no_catalog::del_uslug_Database($_POST['uslugi_id']);
			echo '{"response":"OK"}';
			exit;
		}

		// получение формы выбора услуги
		if($_POST['AJAX']=="get_uslugi_list_Database_Html"){
			$html = '<form>';
			$html .= Position_no_catalog::get_uslugi_list_Database_Html();
			$html .= '<input type="hidden" name="id_uslugi" value="">';
			$html .= '<input type="hidden" name="dop_row_id" value="">';
			$html .= '<input type="hidden" name="quantity" value="">';
			$html .= '<input type="hidden" name="AJAX" value="add_new_usluga">';
			$html .= '</form>';
			echo $html;
			exit;
		}	

		if($_POST['AJAX']=='to_chose_the_type_product_form'){
			// форма выбора типа продукта
			echo $FORM->to_chose_the_type_product_form_Html();
			
			exit;
		}

		if($_POST['AJAX']=='get_form_Html'){
			// запрашиваем из POST массива данные о типе продукта
			$t_p = (isset($_POST['type_product']) && $_POST['type_product']!="")?$_POST['type_product']:'none';
			// если тип уже известен, то мы уже не можем его менять, а значит можем выдать форму только для него
			if(isset($type_product)){
				$t_p = $type_product;
			}

			// запрос формы html
			$FORM->get_product_form_Html($t_p);
			exit;
		}



		if($_POST['AJAX'] == 'general_form_for_create_product'){
			unset($_POST['AJAX']); // уничтожаем переменную, дабы она не попала в массив обработки
			$type_product = $_POST['type_product'];
			// echo '<pre>';
			// print_r($_POST);
			// echo '<pre>';
			echo '<div style="border-top:1px solid red">'.$FORM->restructuring_of_the_entry_form($_POST,$type_product).'</div>';
			exit;
		}
		if($_POST['AJAX'] == 'save_no_cat_variant'){
			unset($_POST['AJAX']); // уничтожаем переменную, дабы она не попала в массив обработки
			

			$FORM->insert_new_options_in_the_Database();
			exit;
		}
	}
	/*******************************  END AJAX  *********************************/
	


	ob_start();		


	echo 'controller_pol.php отвечает за обработку информации по товару полиграфии листовой';
	
	$variants_content = ob_get_contents();
	ob_get_clean();

	
	//echo $variants_content;

	
?>