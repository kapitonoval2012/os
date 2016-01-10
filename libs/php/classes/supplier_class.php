<?php
/**
  *	Класс для раздела поставщиков
  * aplStdAJAXMethod расширение стандартного обработчика
  *
  *	@param 		
  *	@return  	
  *	@author  	Алексей Капитонов
  *	@version 	
  */
class Supplier extends aplStdAJAXMethod{
	
	// содержит название и пути к изображениям для каждого типа контактных данных
	static $array_img = array(
		'email'=>'<img src="skins/images/img_design/social_icon1.png" >',
		'skype' => '<img src="skins/images/img_design/social_icon2.png" >',
		'isq' => '<img src="skins/images/img_design/social_icon3.png" >',
		'twitter' => '<img src="skins/images/img_design/social_icon4.png" >',
		'fb' => '<img src="skins/images/img_design/social_icon5.png" >',
		'vk' => '<img src="skins/images/img_design/social_icon6.png" >',
		'other' => '<img src="skins/images/img_design/social_icon7.png" >'
		);


	public function __construct($id) {
		// подключение к базе
		$this->db();

		$this->user_id = isset($_SESSION['access']['user_id'])?$_SESSION['access']['user_id']:0;
		$this->user_access = $this->get_user_access_Database_Int($this->user_id);


		## данные POST
		if(isset($_POST['AJAX'])){
			// получаем данные пользователя
			$User = $this->getUserDatabase($this->user_id);
			
			$this->user_last_name = $User['last_name'];
			$this->user_name = $User['name'];

			$this->_AJAX_($_POST['AJAX']);
		}


		if($id > 0){
			$this->get_object($id);
		}
	}

	/**
	 *	для генерации отвта выделен класс responseClass()
	 *
	 *  AJAX методов и преобразовать их ответы в соответствии с новыми правилами
	 *
	 *	@param name		method name width prefix _AJAX
	 *	@return  		string
	 *	@see 			{"respons","OK"}
	 *	@author  		Алексей Капитонов
	 *	@version 		12:16 17.12.2015
	 */
	protected function _AJAX_(){
		$method_AJAX = $_POST['AJAX'].'_AJAX';
		//echo $method_AJAX;exit;
		
		if(method_exists($this, $method_AJAX)){
			// подключаем файл с набором стандартных утилит 
			// AJAX, stdApl
			include_once __DIR__.'/../../../../libs/php/classes/aplStdClass.php';
			// создаем экземпляр обработчика
			$this->responseClass = new responseClass();
			// обращаемся непосредственно 
			$this->$method_AJAX();				
			// вывод ответа
			echo $this->responseClass->getResponse();					
			exit;
		}					
	}

	// собираем объект поставщика
	private function get_object($id){
		//получаем данные из основной таблицы
		$query = "SELECT * FROM `".SUPPLIERS_TBL."` WHERE `id` = '".(int)$id."'";
		$result = $this->mysqli->query($query) or die($this->mysqli->error);
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$this->info = $row;
				$this->name = $row['nickName'];
			}
		}
		//получаем телефоны, email, vk и т.д.	
		$arr = $this->get_contact_info("SUPPLIERS_TBL",$id);
		$this->cont_company_phone = (isset($arr['phone']))?$arr['phone']:''; 
		$this->cont_company_other = (isset($arr['other']))?$arr['other']:'';
	}

	// получаем полный список поставщиков
	static function get_all_suppliers_Database_Array(){
		//получаем данные из основной таблицы
		$query = "SELECT * FROM `".SUPPLIERS_TBL."` GROUP BY `nickName` ASC";
		$result = $this->mysqli->query($query) or die($this->mysqli->error);
		$arr = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$arr[] = $row;
			}
		}

		return $arr;

	}

	public function get_contact_info($tbl,$parent_id){
		$query = "SELECT * FROM `".CONT_FACES_CONTACT_INFO_TBL."` WHERE `table` = '".$tbl."' AND `parent_id` = '".$parent_id."'";
		$result = $this->mysqli->query($query) or die($this->mysqli->error);
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

	/**
	  *	оповещение разработчиков об удалении поставщика
	  *
	  *	@author  	Алексей Капитонов
	  *	@version 	00:19 11.01.2016
	  */
	static function removal_request($supplier_id,$username){
		$mail = new Mail();
		$mail->add_bcc('kapitonoval2012@gmail.com');
		$to = 'premier22@yandex.ru';
		$from = 'Оналайн Сервис <online_service@apelburg.ru>';
		$subject = 'Заявка на удаление поставщика';
	    $message = 'Прошу удалить поставщика № '.$supplier_id.' '.$username.'';
		$out_data = $mail->send($to,$from,$subject,$message);
		return 1;		
	}


	static function search_name($name){
		$query = "SELECT `id` FROM `".SUPPLIERS_TBL."` WHERE `fullName` = '".$name."' OR `nickName` = '".$name."'";
		$result = $this->mysqli->query($query) or die($this->mysqli->error);

		$row_cnt = $result->num_rows;
		return $row_cnt;
	}

	/**
	  *	заведение нового поставщика
	  *
	  *	@author  	Алексей Капитонов
	  *	@version 	00:20 11.01.2016
	  */
	static function create($name,$fullname,$dop_info){
		$query ="INSERT INTO `".SUPPLIERS_TBL."` SET
			`nickName` = '".$name."',
		    `fullName` = '".$fullname."',
			`dop_info` = '".$dop_info."'";		 
	    $result = $this->mysqli->query($query) or die($this->mysqli->error);	    
		return $this->mysqli->insert_id;
	}

	/**
	  *	выды деятельности
	  *
	  *	@param 		supplier_id
	  *	@return  	array()
	  *	@author  	Алексей Капитонов
	  *	@version 	00:21 11.01.2016
	  */
	static function get_activities($supplier_id){
		$query = "
			SELECT  `".RELATE_SUPPLIERS_ACTIVITIES_TBL."` . * ,  `".SUPPLIERS_ACTIVITIES_TBL."`.`name` AS  `name` 
			FROM  `".SUPPLIERS_ACTIVITIES_TBL."` 
			INNER JOIN  `".RELATE_SUPPLIERS_ACTIVITIES_TBL."` ON  `".SUPPLIERS_ACTIVITIES_TBL."`.`id` =  `".RELATE_SUPPLIERS_ACTIVITIES_TBL."`.`activity_id` 
			WHERE  `".RELATE_SUPPLIERS_ACTIVITIES_TBL."`.`supplier_id` =  '".$supplier_id."'
		";
		$arr = array();
		$result = $this->mysqli->query($query) or die($this->mysqli->error);
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$arr[] = $row;
			}
		}
		return $arr;		
	}

	/**
	  *	адреса
	  *
	  *	@param 		supplier_id
	  *	@return     array()  	
	  *	@author  	Алексей Капитонов
	  *	@version 	00:22 11.01.2016
	  */
	static function get_addres($id){
		$query = "SELECT * FROM  `".CLIENT_ADRES_TBL."` WHERE `parent_id` = '".(int)$id."'";
		$arr = array();
		$result = $this->mysqli->query($query) or die($this->mysqli->error);
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$arr[] = $row;
			}
		}
		return $arr;
	}

	/**
	  *	контакты
	  *
	  *	@return  	html	
	  *	@author  	Алексей Капитонов
	  *	@version 	00:22 11.01.2016
	  */
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

	/**
	  *	рейтинг
	  *
	  *	@param 		supplier_id		
	  *	@return  	html
	  *	@author  	Алексей Капитонов
	  *	@version 	00:23 11.01.2016
	  */
	static function get_reiting($supplier_id){
		$html = '';
		// SUPPLIERS_RATINGS_TBL subject_id
		$query = "SELECT * FROM `".SUPPLIERS_RATINGS_TBL."` WHERE `subject_id` = '".(int)$supplier_id."'";
		$result = $this->mysqli->query($query) or die($this->mysqli->error);
		$rate = 0;
		if($result->num_rows > 0){
			$sum = 0;
			$i = 0;			
			while($row = $result->fetch_assoc()){
				$sum = $row['rate'];
				$i++;
			}
			$rate = floor ($sum/$i);
		}		
		$arr[0] = array('5','0');
		$arr[1] = array('5','5');
		$arr[2] = array('5','10');
		$arr[3] = array('5','15');
		$arr[4] = array('5','20');
		$arr[5] = array('5','25');

		$html = '<div id="rate_1" data-id="'.$supplier_id.'">
			<input type="hidden" name="review_count" value="'.$arr[$rate]['0'].'" />
			<input type="hidden" name="review_rate" value="'.$arr[$rate]['1'].'" />
		</div>';
		return $html;
	}

	/**
	  *	контактные лица
	  *
	  *	@param 		supplier_id
	  *	@return  	array()
	  *	@author  	Алексей Капитонов
	  *	@version 	00:24 11.01.2016
	  */
	static function cont_faces($id){
		$query = "SELECT * FROM `".SUPPLIERS_CONT_FACES_TBL."` WHERE `supplier_id` = '".(int)$id."'";
		$result = $this->mysqli->query($query) or die($this->mysqli->error);
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

	/**
	  *	логирование истории (что, когда, где, чего, кто, зачем)
	  *
	  *	@return  	true - (1) 
	  *	@author  	Алексей Капитонов
	  *	@version 	00:26 11.01.2016
	  */
	static function history($user_id, $notice, $type, $supplier_id){
		$query ="INSERT INTO `".LOG_SUPPLIER."` SET
		             `user_id` = '".$user_id."',
		             `supplier_id` = '".$supplier_id."',
					 `user_nick` = (SELECT `nickname` FROM `".MANAGERS_TBL."` WHERE `id` = '".$user_id."'),
					 `date` = CURRENT_TIMESTAMP,
					 `type` = '".$type."',
					 `notice` = '".$notice."'";
		$result = $this->mysqli->multi_query($query) or die($this->mysqli->error);	
		return 1;
	}

	/**
	  *	запись в лог отредактированных данных
	  * сохранение предыдущих значений и новых для возможности их восстановления
	  *
	  *	@return  	true - (1)
	  *	@author  	Алексей Капитонов
	  *	@version 	00:28 11.01.2016
	  */
	static function history_edit_type($supplier_id, $user_id, $text ,$type,$tbl,$post,$id_row=0){
		$query = "SELECT * FROM " . constant($tbl) . " WHERE `id` = '" . $_POST['id'] . "'";
        $i=0;
        $result = $this->mysqli->query($query) or die($this->mysqli->error);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $arr_adres = $row;
            }
        }
        // пишем в лог предыдущие данные
        foreach ($arr_adres as $key => $value){
            if(isset($post[$key]) && trim($value)!=trim($post[$key])){
                if($i>0 && count($arr_adres)!=($i-1)){
                    $text.=",";
                }
                $i++;
                $text .= "поле ".$key ." изменено с ". $value." на ".$_POST[$key];
            }
        }
        if($i>0){
            self::history($user_id, $text ,$type,$supplier_id);
        }
        return 1;

	}

	/**
	  *	запись в лог удаления каких-либо данных
	  *
	  *	@return  	true - (1)
	  *	@author  	Алексей Капитонов
	  *	@version 	00:30 11.01.2016
	  */
	static function history_delete_type($supplier_id, $user_id, $text ,$type,$tbl,$post,$id_row){
		$query = "SELECT * FROM " . constant($tbl) . " WHERE `id`= '" . $id_row . "'";
        $i=0;
        $result = $this->mysqli->query($query) or die($this->mysqli->error);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $arr_adres = $row;
            }
        }
        // пишем в лог предыдущие данные
        foreach ($arr_adres as $key => $value) {
            if($i!=0 || count($arr_adres)!=($i-1)){
                $text.=",";
            }
            $text .= "поле ".$key ." = ". $value;
            
        }
        self::history($user_id, $text ,$type, $supplier_id);
		return 1;
	}

	/**
	  *	получение имени поставщика
	  *
	  *	@param 		supplier_id
	  *	@return  	(str)Name
	  *	@author  	Алексей Капитонов
	  *	@version 	00:30 11.01.2016
	  */
	static function get_supplier_name($id){
		$name = "";
		//получаем данные из основной таблицы
		$query = "SELECT * FROM `".SUPPLIERS_TBL."` WHERE `id` = '".(int)$id."'";
		$result = $this->mysqli->query($query) or die($this->mysqli->error);
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$name = $row['nickName'];
			}
		}
		return $name;
	}

	/**
	  *	запрашивает из базы допуски пользователя
	  * необходимо до тех пор, пока при входе в чужой аккаунт меняется только id
	  *
	  *	@param 		user_id
	  *	@return  	(int)User_access
	  *	@author  	Алексей Капитонов
	  *	@version 	00:31 11.01.2016
	  */
	private function get_user_access_Database_Int($id){
		$query = "SELECT `access` FROM `".MANAGERS_TBL."` WHERE id = '".$id."'";
		$result = $this->mysqli->query($query) or die($this->mysqli->error);				
		$int = 0;
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$int = (int)$row['access'];
			}
		}
		return $int;
	}


}
