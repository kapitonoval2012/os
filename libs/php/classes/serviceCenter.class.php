<?php

	/*
		ВАЖНО!!!
		1) в услугах сгрупированных по id, id в колонке united_calculations должны храниться по возрастанию

	*/
	class ServiceCenter  extends aplStdAJAXMethod{
		private $Query;
		private $first_default = true;
		private $services_related;
		private $group_list_services = array();
		private $group_list = array();
		private $services_related_dop = array();
		private $services_all = array();

		function __construct(){
			// include_once('printCalculator.php');
			$this->db();

			$this->user_id = isset($_SESSION['access']['user_id'])?$_SESSION['access']['user_id']:0;

			$this->user_access = $this->get_user_access_Database_Int($this->user_id);
		
			if(isset($_POST['AJAX'])){
				$this->_AJAX_($_POST['AJAX']);
			}

				## данные GET --- НА ВРЕМЯ ОТЛАДКИ !!!
			if(isset($_GET['AJAX'])){
				$this->_AJAX_($_GET['AJAX']);		
			}
		}

		/**
		 *	возвращает json услуг по id
		 *
		 *	@param 		$_POST['id_s'] - array()
		 *	@return  	json
		 *	@author  	Alexey Kapitonov
		 *	@version 	16:49 09.03.2016
		 */
		protected function get_new_services_AJAX(){
			$query = "SELECT * FROM `".RT_DOP_USLUGI."` ";
			$query .= " WHERE `id` IN ('".implode("','", $_POST['id_s'])."');";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);

			$arr = array();
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$arr[$row['dop_row_id']][] = $row;
				}
			}
			echo json_encode($arr);
			exit;
		}

		/**
		 *	сохранение общей скидки
		 *
		 *	@author  	Alexey Kapitonov
		 *	@version 	16:06 09.03.2016
		 */
		protected function save_main_discount_AJAX(){
			if(isset($_POST['dop_data_ids']) && count($_POST['dop_data_ids']) > 0){
				$query = "UPDATE `".RT_DOP_DATA."` SET ";
				$query .= " `discount` = '".$_POST['value']."' ";
				$query .= " WHERE `id` IN ('".implode("','", $_POST['dop_data_ids'])."');";
				$result = $this->mysqli->query($query) or die($this->mysqli->error);	
				// $this->responseClass->addMessage('Скидка на варианты изенена.');
			}

			if(isset($_POST['services_ids']) && count($_POST['services_ids']) > 0){
				$query = "UPDATE `".RT_DOP_USLUGI."` SET ";
				$query .= " `discount` = '".$_POST['value']."' ";
				$query .= " WHERE `id` IN ('".implode("','", $_POST['services_ids'])."');";
				$result = $this->mysqli->query($query) or die($this->mysqli->error);	
				// $this->responseClass->addMessage('Скидка на услуги изенена.');
			}
		}

		/**
		 *	сохраняет ТЗ
		 *
		 *	@param 		Alexey Kapitonov
		 *	@version 	12:50 14.03.2016
		 */
		protected function save_coment_tz_AJAX(){
			if(isset($_POST['ids']) && count($_POST['ids']) > 0){
				$query = "UPDATE `".RT_DOP_USLUGI."` SET ";
				$query .= " `tz` = '".$_POST['value']."' ";
				$query .= " WHERE `id` IN ('".implode("','", $_POST['ids'])."');";
				$result = $this->mysqli->query($query) or die($this->mysqli->error);	
				// $this->responseClass->addMessage('Скидка на услуги изенена.');
			}
		}

		/**
		 *	удаление услуги
		 *
		 *	@author  	Alexey Kapitonov
		 *	@version 	11:06 09.03.2016
		 */
		protected function delete_services_AJAX(){

			if(isset($_POST['service_ids']) && count($_POST['service_ids']) > 0){
				$query = "DELETE FROM `".RT_DOP_USLUGI."` WHERE `id` IN ('".implode("','", $_POST['service_ids'])."')";
				$result = $this->mysqli->query($query) or die($this->mysqli->error);
			}
			// $this->responseClass->addMessage($this->printArr($_POST));
		}

		/**
		 *	возвращает окно
		 *
		 *	@author  	Alexey Kapitonov
		 *	@version 	16:10 12.02.2016
		 */
		protected function get_service_center_AJAX(){
			// проверка на наличие номера запроса
			if(!isset($_GET['query_num']) || $_GET['query_num'] ==''){
				$this->responseClass->addMessage('Системе необходимо находиться внутри запроса.');
				return;
			}

			$this->responseClass->options['width'] = '100%';
			$this->responseClass->options['height'] = '100%';
			$this->responseClass->options['title'] = 'Центр услуг';
			$this->responseClass->options['html'] = base64_encode( $this->get_window_content() );
			$this->responseClass->options['myFunc'] = 'show_SC';
			// $this->responseClass->addResponseFunction('show_SC',$options);	  
		}

		/**
		 *	обновление окна 
		 *
		 *	@author  	Alexey Kapitonov
		 *	@version 	16:34 11.03.2016
		 */
		protected function update_service_center_AJAX(){
			$this->responseClass->options['html'] = base64_encode( $this->get_window_content() );
			$this->responseClass->options['myFunc'] = 'update_SC';
		}


		private function group_list(){

			// перебираем все услуги
			foreach ($this->services_all as $key => $row) {
				if(trim($row['united_calculations']) != ''){
					$services_arr = explode(",", $row['united_calculations']);
					$group_name = $this->get_group_name($services_arr);
					if(count($services_arr) > 1){
						$id = "list_".implode('_', explode(",", $group_name));
						$this->group_list[ $id ]['id'] = $id;
						$this->group_list[ $id ]['data-var_id'] = $group_name;
						$this->group_list[ $id ]['data-service_id'] = $row['united_calculations'];
					}
				}
			}

			if(isset($_POST['checked_rows']) && count($_POST['checked_rows'])>1){
				$find_group = false;
				foreach ($this->group_list as $key => $list) {
					if($key == 'list_'.implode('_', $_POST['checked_rows'])){
						$find_group = $key;
					}
				}
				
				echo '<li '.((!$find_group)?'class="checked"':'').'>';
				echo '<div>Артикулы</div>';
				echo '</li>';

				$i = 1;
				foreach ($this->group_list as $key => $list) {
					echo '<li id="'.$key.'" '.(($find_group && $find_group==$key )?'class="checked"':'').' data-var_id="'.$list['data-var_id'].'" data-service_id="'.$list['data-service_id'].'">';
						echo '<div>Тираж № '.($i++).'</div>';
					echo '</li>';
				}
			}else{
				echo '<li class="checked">';
				echo '<div>Артикулы</div>';
				echo '</li>';

				$i = 1;
				foreach ($this->group_list as $key => $list) {
					echo '<li id="'.$key.'" data-var_id="'.$list['data-var_id'].'" data-service_id="'.$list['data-service_id'].'">';
						echo '<div>Тираж № '.($i++).'</div>';
					echo '</li>';
				}	
			}
			
		}



		/**
		 *	редактирование скидки / наценки
		 *
		 *	@author  	Alexey Kapitonov
		 *	@version 	14:19 02.03.2016
		 */
		protected function save_discount_AJAX(){
			switch ($_POST['type']) {
				case 'service':
					$query = "UPDATE `".RT_DOP_USLUGI."` SET ";
					$query .= " `discount` = '".$_POST['value']."' ";
					$query .= " WHERE `id` = '".(int)$_POST['row_id']."';";
					$result = $this->mysqli->query($query) or die($this->mysqli->error);	

					// $this->responseClass->addMessage('редактирование скидки / наценки - Сервис');
					break;
				case 'variant':
					$query = "UPDATE `".RT_DOP_DATA."` SET ";
					$query .= " `discount` = '".$_POST['value']."' ";
					$query .= " WHERE `id` = '".(int)$_POST['row_id']."';";
					$result = $this->mysqli->query($query) or die($this->mysqli->error);	

					// $this->responseClass->addMessage('редактирование скидки / наценки - Вариант');
					break;				
				default:
					$this->responseClass->addMessage('редактирование скидки / наценки');
					break;
			}
		}

		/**
		 *	удвляет услугу
		 *
		 *	@author  	Alexey Kapitonov
		 *	@version 	16:50 01.03.2016
		 */
		protected function services_del_AJAX(){
			$this->responseClass->addMessage('Удаление услуги');
		}


		// возвращает контент для окна
		private function get_window_content(){
			// собираем объект
			$this->get_object_vars();


			$html = '<div id="js-service-center">';
				$variants_rows = $this->variants_print_Html();

				ob_start();
				// echo '<pre>';
				// print_r($_SESSION);

				// echo '<pre>';
				include_once ROOT.'/skins/tpl/client_folder/service_center/show.tpl';
				$html .= ob_get_contents();
				ob_get_clean();
			$html .= '</div>';
			return $html;
		}

		// во
		private function variants_print_Html(){
			// echo '<pre>';
			// print_r($this->Query['positions']);
			// echo '</pre>';
				
			$html = '';
			$position_num = 1;
			$color_arr = array('rgba(79, 154, 48, 0.2)','rgba(79, 142, 13, 0.37)');
			$color = $color_arr[1]; $old_color = '';

			foreach ($this->Query['positions'] as $position) {
				
				if(count($position['variants'])> 1){
					if($old_color == $color){
						foreach ($color_arr as $color_1) {
							if($old_color != $color_1){
								$color = $color_1;		
							}
						}
					}

					$color_style = 'style="background-color:'.$color.'"';	
				}else{
					$color_style = '';
				}
				
				$variant_num = 1;
				foreach ($position['variants'] as $variant) {

					$checked_class = '';
					$checkbox_checked_class = '';
					if(isset($_POST['checked_rows']) && in_array($variant['id'], $_POST['checked_rows'])){
						$checked_class = ' tr_checked';
						if(count($_POST['checked_rows']) > 1){
							$checkbox_checked_class = ' checked';
						}
					}

					if($this->first_default){
						if($variant_num == 1){
							$html .= '<tr data-quantity="'.$variant['quantity'].'" data-dop_row_id="'.$variant['id'].'" data-art_id="'.$position['art_id'].'" id="dop_data_'.$variant['id'].'" class="default_var'.$checked_class.'">';		
						}						
					}else{
						$html .= '<tr data-quantity="'.$variant['quantity'].'" data-dop_row_id="'.$variant['id'].'" data-art_id="'.$position['art_id'].'" id="dop_data_'.$variant['id'].'" class="'.(($variant['id'] == (int)$_POST['row_id'])?'default_var':'').$checked_class.'">';
					}	

						foreach ($variant['services'] as $key => $value) {
							$json = $variant['services'][$key]['print_details'];
							$variant['services'][$key]['print_details'] = json_decode($json,'true');	
							$variant['services'][$key]['desc'] = printCalculator::convert_print_details_for_TotalCom(($json == "")?"{}":$json);
						}
						 
						$html .= '<td class="js-variant_services_json"><div>'.json_encode($variant['services']).'</div></td>';
						$html .= '<td class="'.$checkbox_checked_class.'">';
							$html .= '<div class="js-psevdo_checkbox"></div>';
						$html .= '</td>';
						$html .= '<td>'.$position_num.'.'.$variant_num.'</td>';
						$html .= '<td><span class="marcker_led"  '.$color_style.'>&nbsp;</span></td>';
						$html .= '<td data-no_short="вар '.$variant_num.'">в'.$variant_num.'</td>';
						$html .= '<td>'.$position['art'].'</td>';
						$html .= '<td><span class="service">'.count($variant['services']).'</span></td>';
						$html .= '<td>'.$position['name'].'</td>';
						$html .= '<td><span>'.$variant['quantity'].'</span> шт</td>';
						$html .= '<td><span class="marcker_led"  '.$color_style.'>&nbsp;</span></td>';
						$my_variant = $variant;
						unset($my_variant['services']);
						$html .= '<td class="js-variant_info"><div>'.json_encode($my_variant).'</div></td>';
					$html .= '</tr>';
					$variant_num++;
				}
				$position_num++;
				$old_color = $color;
			}
			return $html;
		}

		// собираем объект
		private function get_object_vars(){
			// получаем строку запроса
			$this->Query = $this->get_query( (int)$_GET['query_num'] );

			// получаем строки позиций
			$this->Query['positions'] = $this->get_positions( (int)$_GET['query_num'] );

			// выбираем id позиций
			$i = 0; $id_s = '';
			foreach ($this->Query['positions'] as $positions) {
				$id_s .= (($i>0)?',':'')."'".$positions['id']."'"; $i++;
			}
			// добавляет варианты в объект
			$this->get_variants_and_services($id_s);

		}
		// возвращает строки вариантов
		private function get_variants_and_services($id_s){
			$query = "SELECT * FROM `".RT_DOP_DATA."` WHERE `row_id` IN (".$id_s.");";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);	
			$arr = array();
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					if($this->first_default == true && $row['id'] == $_POST['row_id']){
						$this->first_default = false;
					}
					$this->Query['positions'][$row['row_id']]['variants'][$row['id']] = $row;
					// получаем услуги к варианту (в целях экономии ресрсов получаем сдесь)
					$this->Query['positions'][$row['row_id']]['variants'][$row['id']]['services'] = $this->get_services($row['id']);
				}
			}	
			return;
		}

		// возвращает прикрепленные услуги
		private function get_services($id){
			$query = "SELECT `".RT_DOP_USLUGI."`.*, IFNULL(`".RT_DOP_USLUGI."`.`other_name`, `".OUR_USLUGI_LIST."`.`name`) AS `service_name` FROM `".RT_DOP_USLUGI."` ";
			$query .= " LEFT JOIN `".OUR_USLUGI_LIST."` ON `".RT_DOP_USLUGI."`.`uslugi_id` = `".OUR_USLUGI_LIST."`.`id`";
			$query .= " WHERE `".RT_DOP_USLUGI."`.`dop_row_id` = '".$id."';";
			
			$result = $this->mysqli->query($query) or die($this->mysqli->error);	
			$arr = array();
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$arr[] = $row;
					
					// зависимости
					$this->services_related[$row['id']] = $row['dop_row_id'];
					$this->services_related_dop[$row['dop_row_id']][] = $row['id'];
					// все услуги с которыми работает тотал
					$this->services_all[] = $row;
				}
			}
			return $arr;
		}


		/**
		 *	возвращает название группы
		 *
		 *	@param 		service_id
		 *	@return  	string
		 *	@author  	Alexey Kapitonov
		 *	@version 	11:57 14.03.2016
		 */
		private function get_group_name($service_arr){
			$dop_row_id = array();
			foreach ($service_arr as $key => $id) {
				if(isset($this->services_related[$id])){
					$dop_row_id[] = $this->services_related[$id];	
				}else{
					return false;
				}				
			}
			return implode(',',$dop_row_id);
		}



		// возвращает строку запроса
		private function get_query($query_num = 0){
			$query = "SELECT * FROM `".RT_LIST."` WHERE `query_num` = '".$query_num."';";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);	
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					return $row;
				}
			}	
			return array();
		}
		// возвращает строки позиций
		private function get_positions($id){
			$query = "SELECT * FROM `".RT_MAIN_ROWS."` WHERE `query_num` = '".$id."' ORDER BY  `sort` ASC ;";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);	
			$arr = array();
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$arr[$row['id']] = $row;
				}
			}	
			return $arr;
		}

		// запрашивает из базы допуски пользователя
		// необходимо до тех пор, пока при входе в чужой аккаунт меняется только id
		private function get_user_access_Database_Int($id){
			$query = "SELECT `access` FROM `".MANAGERS_TBL."` WHERE id = '".$id."'";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);				
			$int = 0;
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$int = (int)$row['access'];
				}
			}
			//echo $query;
			return $int;
		}

		/**
		 * 	 рандомный цвет
		 *
		 *	 @author  	Alexey Kapitonov
		 *	 @version 	 	 
		 */
		public function rand_color() {
		    return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
		}
	}

?>