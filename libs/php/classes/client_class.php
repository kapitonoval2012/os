﻿<?php
class Client {	
	static $array_img = array('email'=>'<img src="skins/images/img_design/social_icon1.png" >','skype' => '<img src="skins/images/img_design/social_icon2.png" >','isq' => '<img src="skins/images/img_design/social_icon3.png" >','twitter' => '<img src="skins/images/img_design/social_icon4.png" >','fb' => '<img src="skins/images/img_design/social_icon5.png" >',
'vk' => '<img src="skins/images/img_design/social_icon6.png" >','other' => '<img src="skins/images/img_design/social_icon7.png" >');
	
	public function __construct($id) {
		global $mysqli;
		
		//$this->id = $id;	
		//получаем данные из основной таблицы
		$query = "SELECT * FROM `".CLIENTS_TBL."` WHERE `id` = '".(int)$id."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$this->info = $row;
				$this->name = $row['company'];
			}
		}

		//получаем телефоны, email, vk и т.д.	
		$arr = $this->get_contact_info("CLIENTS_TBL",$id);
		$this->cont_company_phone = (isset($arr['phone']))?$arr['phone']:''; 
		$this->cont_company_other = (isset($arr['other']))?$arr['other']:'';
	}

	static function get_addres($id){
		global $mysqli;
		$query = "SELECT * FROM  `order_manager__client_addres_tbl` WHERE `parent_id` = '".(int)$id."'";
		$arr = array();
		$result = $mysqli->query($query) or die($mysqli->error);
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$arr[] = $row;
			}
		}
		return $arr;
	}

	//вывод доп контактов в табличном виде
	static function get_contact_row($contact_company, $type,$array_dop_contacts_img){
		
		if(isset($type) && $type == "phone"){
			$i=1;
			$str = '<table class="table_phone_contact_information">';
			if(empty($contact_company)){return;}

			foreach($contact_company as $k=>$v){				
				if($v['type'] == $type){
					$str .= "<tr><td class='td_phone'>".$v['telephone_type']." ".$i."</td><td><div  class='del_text' data-adress-id=".$v['id'].">".$v['contact'].((trim($v['dop_phone'])!=0)?" доп.".$v['dop_phone']:'')."</div></td></tr>";	
					$i++;
				}
			}
			$str .= "</table>";	
			// echo $str;
			return $str;
		}else{

			$str = '<table class="table_other_contact_information">';
			foreach($contact_company as $k=>$v){
			if(isset($array_dop_contacts_img[trim($v['type'])])){
				$icon = $array_dop_contacts_img[trim($v['type'])];
			}else{
				@$icon = $array_dop_contacts_img['other'];
			}
				if($v['type'] != 'phone'){
					$str .= "<tr><td class='td_icons'>".$icon."</td><td><div   class='del_text' data-adress-id=".$v['id'].">".$v['contact']."<div></td></tr>";	
				}
			}
			$str .= "</table>";	
			return $str;		
		}			
	}
	static function get__clients_persons_for_requisites($type){
		// string
		// отдает список <option> с названиями занесенных в базу должностей сотрудников для реквизитов
		global $mysqli;
		$query = "SELECT * FROM `".CLIENT_PERSON_REQ_TBL."`";
		$str = "<option value=\"0\">Выберите должность...</option>".PHP_EOL;
		$result = $mysqli->query($query) or die($mysqli->error);				
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$str .= "<option value=\"".$row['id']."\" ".(($type==$row['id'])?'selected':'').">".$row['position']."</option>".PHP_EOL;
			}
		}
		return $str;
	} 

	static function edit_requsits_show_person($person_id){
		// array
		// отдаёт контакты для реквизитов в массиве
		global $mysqli;
		$arr = "";

		// $query = "SELECT * FROM `".CLIENT_REQUISITES_MANAGMENT_FACES_TBL."` INNER JOIN   `".CLIENT_PERSON_REQ_TBL."` ON   `".CLIENT_PERSON_REQ_TBL."`.`id` = `".CLIENT_REQUISITES_MANAGMENT_FACES_TBL."`.`post_id` WHERE `requisites_id` = '".$person_id."'";
		$query = "SELECT * FROM `".CLIENT_REQUISITES_MANAGMENT_FACES_TBL."` WHERE `requisites_id` = '".$person_id."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$arr[] = $row;
			}
		}
		return $arr;
	}

	static function edit_requsits_show_person_all($arr,$client_id){
		// string (html)
		// обрабатывает массив контактных данных из реквизитов и создаёт html
		// ob_start();

		// получаем список должностей для персональных данных контактных лиц из реквизитов
		
		
		// echo "<pre>";
		// print_r($arr);
		// echo "</pre>";
		foreach ($arr as $key => $contact) {
			$get__clients_persons_for_requisites = Client::get__clients_persons_for_requisites($contact['post_id']);
			include('./skins/tpl/clients/client_folder/client_card/edit_requsits_show_person.tpl');
		}
		// $html = ob_get_contents();
		// ob_get_clean();

		// return $html;
	}

	static function get_relate_managers($client_id){
		global $mysqli;
		$query = "SELECT * FROM  `".MANAGERS_TBL."` WHERE `id` IN (SELECT `manager_id` FROM  `".RELATE_CLIENT_MANAGER_TBL."`  WHERE `client_id` IN (SELECT `id` FROM `".CLIENTS_TBL."` WHERE `id` = 1894 ));";
		$result = $mysqli->query($query) or die($mysqli->error);
		$manager_names = "";				
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				if($manager_names!=""){$manager_names .=", ";}
				$manager_names .= $row['name'];
			}
		}
		return $manager_names;
	}

	static function get_requisites($client_id){
		global $mysqli;
		$query = "SELECT * FROM `".CLIENT_REQUISITES_TBL."` WHERE `client_id` = '".$client_id."'";
		$requisites = array();
		$result = $mysqli->query($query) or die($mysqli->error);				
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$requisites[] = $row;
			}
		}
		return $requisites;
	}

	static function get_reiting($id,$rate){
		$arr[0] = array('5','0');
		$arr[1] = array('5','5');
		$arr[2] = array('5','10');
		$arr[3] = array('5','15');
		$arr[4] = array('5','20');
		$arr[5] = array('5','25');

		$r = '<div id="rate_1" data-id="'.$id.'">
			<input type="hidden" name="review_count" value="'.$arr[$rate]['0'].'" />
			<input type="hidden" name="review_rate" value="'.$arr[$rate]['1'].'" />
		</div>';
		return $r;
	}


	
	public function get_contact_info($tbl,$parent_id){
		global $mysqli;
		$query = "SELECT * FROM `".CLIENT_CONT_FACES_CONTACT_INFO_TBL."` WHERE `table` = '".$tbl."' AND `parent_id` = '".$parent_id."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		$contact = array('phone'=>'','other'=>'');//инициализируем массив
		$contacts = array();		
		$contact['phone'] = '<table class="table_phone_contact_information"></table>';
		$contact['other'] = '<table class="table_other_contact_information"></table>';
		
		//  
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$contacts[] = $row;
			}
			$contact['phone'] = self::get_contact_row($contacts, 'phone',self::$array_img);
			$contact['other'] = self::get_contact_row($contacts, 'other',self::$array_img);
		}
		return $contact;

	}
	//получаем реквизиты компании
	static function requisites($id) {
		global $mysqli;
		$query = "SELECT * FROM `".CLIENT_REQUISITES_TBL."` WHERE `client_id` = '".(int)$id."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		$array = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$array[] = $row;
			}
			return $array;
		}
		return $array;
	}
	//получаем данные о контактных дицах компании
	static function cont_faces($id){
		global $mysqli;
		$query = "SELECT * FROM `".CLIENT_CONT_FACES_TBL."` WHERE `client_id` = '".(int)$id."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		$array = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$array[] = $row;
			}
			//получаем телефоны, email, vk и т.д.	
			/*$arr = self::get_contact_info("CLIENT_CONT_FACES_TBL",$id);
			
			$array['phone'] = (isset($arr['phone']))?$arr['phone']:''; 
			$array['other'] = (isset($arr['other']))?$arr['other']:'';*/
			return $array;
		}
		
		return $array;
	}
	//получаем данные о прикреплённых менеджерах
	static function relate_managers($id){
		global $mysqli;
		$query = "SELECT * FROM `".MANAGERS_TBL."` INNER JOIN `".RELATE_CLIENT_MANAGER_TBL."` ON `".RELATE_CLIENT_MANAGER_TBL."`.`manager_id` = `".MANAGERS_TBL."`.`id` WHERE `".RELATE_CLIENT_MANAGER_TBL."`.`client_id` = '".(int)$id."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		$array = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$array[] = $row;
			}
			return $array;
		}
		return $array;
	}	
	//удалить клиента и всё, что с ним связано
	static function delete($id){
		global $mysqli;
		//выполняем все запросы ипишем ОК
						
		//лица имеющих право подписи
		$query = "DELETE FROM `".CLIENT_REQUISITES_MANAGMENT_FACES_TBL."` WHERE `requisites_id` IN (SELECT id FROM `".CLIENT_REQUISITES_TBL."` WHERE `client_id` = '".(int)$id."');\n";	
		//контактные лица компании
		$query .= "DELETE FROM `".CLIENT_CONT_FACES_TBL."` WHERE `client_id` = '".(int)$id."';\n";	
		//прикреплённые менеджеры		
		$query .= "DELETE FROM `".RELATE_CLIENT_MANAGER_TBL."` WHERE `client_id` = '".(int)$id."';\n";
		//реквизиты относящиеся к данному клиенту
		$query .= "DELETE FROM `".CLIENT_REQUISITES_TBL."` WHERE `client_id` = '".(int)$id."';\n";
		//договора
		$query .= "DELETE FROM `".GENERATED_AGREEMENTS_TBL."` WHERE `client_id` = '".(int)$id."';\n";
		//спецификации
		$query .= "DELETE FROM `".GENERATED_SPECIFICATIONS_TBL."` WHERE `client_id` = '".(int)$id."';\n";
		//таблицы РТ
		$query .= "DELETE FROM `".CALCULATE_TBL."` WHERE `client_id` = '".(int)$id."';\n";
		//таблица заказов
		$query .= "DELETE FROM `".CLIENT_ORDERS_TBL."` WHERE `client_id` = '".(int)$id."';\n";
		//КП
		$query .= "DELETE FROM `".COM_PRED_LIST."` WHERE `client_id` = '".(int)$id."';\n";
		//протокол добавления
		$query .= "DELETE FROM `".CALCULATE_TBL_PROTOCOL."` WHERE `client` = '".(int)$id."';\n";			
		//планы
		$query .= "DELETE FROM `".PLANNER."` WHERE `id` = '".(int)$id."';\n";
		//удаляем клинта из основной таблицы		
		$query .= "DELETE FROM `".CLIENTS_TBL."` WHERE `id` = '".(int)$id."';\n";	
		
		//прикреплённые менеджеры		
		$query .= "DELETE FROM `".RELATE_CLIENT_MANAGER_TBL."` WHERE `client_id` = '".(int)$id."';";
		//return $query;
		$result = $mysqli->multi_query($query) or die($mysqli->error);
		return "Клиент id ".$id." успешно удален.";		
	}
	
	//заводим клиента
	public function create($array_in){
		if(empty($array_in))return "не достаточно данных";
		global $mysqli;
		global $user_id;
		//return $user_id;	
		extract($array_in);
		$rate =(empty($rate))? 1 : $rate ;		
		$query ="INSERT INTO `".CLIENTS_TBL."` SET
					 `set_client_date` = CURRENT_DATE(),
		             `company` = '".$this->cor_data_for_SQL($company)."', `delivery_address` = '".$this->cor_data_for_SQL($delivery_address)."',
					 `email` = '".$this->cor_data_for_SQL($email)."', `phone` = '".$this->cor_data_for_SQL($phone)."',
					 `addres` = '".$this->cor_data_for_SQL($addres)."', `web_site` = '".$this->cor_data_for_SQL($web_site)."',
					 `dop_info` = '".$this->cor_data_for_SQL($dop_info)."', `rate` = '".$rate."'";
		 
	    $result = $mysqli->query($query) or die($mysqli->error);
		$client_id = $mysqli->insert_id;
		
		///////////////////////////////////////////////////
		// add new data in CLIENT_CONT_FACES_TBL
		///////////////////////////////////////////////////

		foreach($_POST['cont_faces_data'] as $cont_face){
			   $query = "INSERT INTO `".CLIENT_CONT_FACES_TBL."` 
		              SET 
					 `client_id` = '".$client_id."',
					 `set_main` = '".@$this->cor_data_for_SQL($cont_face['set_main'])."', 
					 `name` = '".$this->cor_data_for_SQL($cont_face['name'])."',
					 `position` = '".$this->cor_data_for_SQL($cont_face['position'])."',
					 `department` = '".$this->cor_data_for_SQL($cont_face['department'])."',
					 `email` = '".$this->cor_data_for_SQL($cont_face['email'])."',
					 `phone` = '".$this->cor_data_for_SQL($cont_face['phone'])."',
					 `isq_skype` = '".$this->cor_data_for_SQL($cont_face['isq_skype'])."'";
					 
		   $result = $mysqli->query($query) or die($mysqli->error);		   
		}
	
	    $query = "INSERT INTO `".RELATE_CLIENT_MANAGER_TBL."` VALUES('','$client_id','$user_id')";
        $result = $mysqli->query($query) or die($mysqli->error);
		global $mail;
		//$headers = 'Cc : andrey@apelburg.ru';
		//$headers = 'Cc : '.implode(',',array('runman@mail.ru','slava@apelburg.ru'));
		$headers = ''; 
		$manager_info = $this->get_manager_info_by_id($user_id);		
		$message = 'В базу добавлен новый клиент:<br><b>'.$company.'</b><br>
				   поставщика добавил пользователь: '.$manager_info['nickname'].' / '.$manager_info['name'].' '.$manager_info['last_name'].'<br><br>
				   <a href="http://apelburg.ru/admin/order_manager/?page=clients&client_id='.$client_id.'&razdel=show_client_data">ссылка на карточку клиента</a>';
		/**/
		//$mail->sendMail(2,'apelburg.m7@gmail.com','Новый клиент',$message,$headers);		
		$mail->sendMail(2,'kapitonoval2012@gmail.com','Новый клиент',$message,$headers);		
		return $client_id;
	}
	
	//защита от sql инъекций
	private function cor_data_for_SQL($data){
	    if(is_int($data) || is_double($data)) return($data);
	    //return strtr($data,"1","2");
		$data = strip_tags($data,'<b><br><a>');
		return mysql_real_escape_string($data);
	}
	//Получение данных о Менеджере по id
	//возвращает одномерный массив
	private function get_manager_info_by_id($manager_id){
		global $mysqli;
		$query = "SELECT * FROM `".MANAGERS_TBL."` WHERE `id` = '".$manager_id."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		$array = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				return $row;
			}
			//return $array;
		}
		return $array;
	}
	
}