<?php 
    $usluga_id = $_GET['usluga'];
	
	
    if(!empty($_POST['dataBufferForSavingToBase'])){
	    
	    $data = json_decode($_POST['dataBufferForSavingToBase']);
		

		// удаляем первый ряд содержащий вспомогательную информацию
		unset($data->tbl_data[0]);
		
	     echo '<pre>'; print_r($data);echo '</pre>';
		
		 //exit; //
		if(!empty($_POST['dataBufferForDeleting'])){
		    $toDeleteArr = explode('|',trim($_POST['dataBufferForDeleting'],'|'));
		    //echo '<pre>'; print_r($toDeleteArr);echo '</pre>';////
			if(count($toDeleteArr)>0){
			    for($i=0;$i<count($toDeleteArr);$i++){
				     //echo '-'.(int)$toDeleteArr[$i].'-<br>';
					 if((int)$toDeleteArr[$i]!=0){
					      $query ="DELETE FROM `".BASE__CALCULATORS_PRINT_TYPES_SIZES_PLACES_REL_TBL."` WHERE id = '".$toDeleteArr[$i]."'";
						  $mysqli->query($query)or die($mysqli->error);
					 }
				}
				
			}
		}
		//exit; //
		foreach($data->tbl_data as $val){
		    $query ="SELECT*FROM `".BASE__CALCULATORS_PRINT_TYPES_SIZES_PLACES_REL_TBL."` WHERE id = '".$val[0]."'";
			
			$result = $mysqli->query($query)or die($mysqli->error);
			if($result->num_rows>0){
		       $query2 ="UPDATE `".BASE__CALCULATORS_PRINT_TYPES_SIZES_PLACES_REL_TBL."` SET place_id='".$val[1]."', print_id='".$usluga_id."' , size='".cor_data_for_SQL($val[2])."', val='".(float)$val[3]."', type='".cor_data_for_SQL($val[4])."', target='".cor_data_for_SQL($val[5])."', `default`='".(int)$val[6]."' WHERE id = '".$val[0]."'";
			  echo $query2;
			   $mysqli->query($query2)or die($mysqli->error);
			}
			else{
			  
			   $query2 ="INSERT INTO `".BASE__CALCULATORS_PRINT_TYPES_SIZES_PLACES_REL_TBL."` VALUES('','".(int)$val[1]."','".$usluga_id."','".cor_data_for_SQL($val[2])."','','".(float)$val[3]."','".cor_data_for_SQL($val[4])."','".cor_data_for_SQL($val[5])."','".(int)$val[6]."')";
			   //echo $query2;
			   $mysqli->query($query2)or die($mysqli->error);
			}
		}
		
		header('location:'.$_SERVER['HTTP_REFERER']);//
		exit;
	}

    
	
	$td1  = '<td contenteditable="true">'; 
	$td1_hidden  = '<td style="display:none;">'; 
	$td1_unedit  = '<td>'; 
	$td2  = '</td>';
	$td_td  = $td2.$td1;
	$td_td_unedit  = $td2.$td1_unedit;
	$tr1  = '<tr>'; 
	$tr2  = '</tr>';
	$tr_tr  = $tr2.$tr1;
	$tbl_types = array();
	
	
	
	
	/*
	// выбираем данные из таблицы содержащей данные о типах нанесения
	$query="SELECT * FROM `".OUR_USLUGI_LIST."` WHERE `parent_id` = '6'";
    $result = $mysqli->query($query)or die($mysqli->error);
    if($result->num_rows>0){
	    while($row = $result->fetch_assoc()){
		    $printsOpions[$row['id']] = '<option value="'.$row['id'].'" selected_mark>'.$row['name'].'</option>';
		}
	}
    */
	
	// выбираем данные из таблицы содержащей данные о местах нанесения
	$query="SELECT*FROM `".BASE__PRINT_PLACES_TYPES_TBL."` ORDER by  id";
	//echo $query;
	$result = $mysqli->query($query)or die($mysqli->error);
	
	$placesOpions[0] = '<option value="0" selected_mark></option>';
	
	if($result->num_rows>0){
		while($row = $result->fetch_assoc()){     
			 $placesOpions[$row['id']] = '<option value="'.$row['id'].'" selected_mark>'.$row['name'].' , (товар - '.$row['comment'].')</option>'; 
		}
	}
	$type = 'places';
	
	// выбираем данные из таблицы содержащей данные о размерах и местах нанесения
	
	$query="SELECT*FROM `".BASE__CALCULATORS_PRINT_TYPES_SIZES_PLACES_REL_TBL."` WHERE print_id = '".$usluga_id."' ORDER BY id";
	//echo $query;
	$result = $mysqli->query($query)or die($mysqli->error);
	
	if($result->num_rows>0){
		while($row = $result->fetch_assoc()){ 
		     
			 //print_r($row);
             $placesOpionsClone = $placesOpions;
			 
			 foreach($placesOpionsClone as $id => $option){
			     if($id == $row['place_id']) $placesOpionsClone[$id] = str_replace('selected_mark','selected',$placesOpionsClone[$id]); //
			 }
			 $placesSelect='<select style="width:500px;">'.implode('',$placesOpionsClone).'</select>';
			 
			 
			 $row_tpl = $tr1;
			 $row_tpl .= $td1_hidden.$row['id'].$td2;
			 $row_tpl .= $td1.$placesSelect.$td2;
			 //$row_tpl .= $td1.$row['place_id'].$td2;
			 $row_tpl .= $td1.$row['size'].$td2;
			 //$row_tpl .= $td1.$row['percentage'].$td2;
			 $row_tpl .= $td1.$row['val'].$td2;
			 $row_tpl .= $td1.$row['type'].$td2;
			 $row_tpl .= $td1.$row['target'].$td2;
			 $row_tpl .= $td1.$row['default'].$td2;
			 $row_tpl .= $td1.'<span class="deleteElementBtn" onclick="deleteRowFromTable(this,\''.$type.'\');">&#215;</span>'.$td2;
			 $row_tpl .= $tr2;
			// 
		     $tbl_rows[] = $row_tpl;
			 
			 //print_r($tbl_rows);
		}
	}
	
	if(!isset($tbl_rows)){
	    $placesSelect='<select style="width:500px;">'.implode('',$placesOpions).'</select>';
		
		$row_tpl = array($placesSelect, '','1.00','','','0','<span class="deleteElementBtn" onclick="deleteRowFromTable(this,\''.$type.'\');">&#215;</span>');
		$tbl_rows[] = $tr1.$td1_hidden.''.$td2.$td1.implode($td_td,$row_tpl).$td2.$tr2;	
	}
	
	$row =array('','место нанесения','варианты размеров','величина','тип<br>(coeff/addition)','применение<br>(price/summ)','*по умолчанию<br>(0/1)','');	
    array_unshift($tbl_rows,$tr1.$td1_hidden.implode($td_td_unedit,$row).$td2.$tr2);	
	
	
	//echo 'тип прайса: '.$type;
	echo '<div>
		  <span class="pointer" onclick="addRowsToTbl(\''.$type.'\',{\'clearCell\':1});">добавить</span><input size="1" id="rowsNum'.$type.'" value="1">рядов
		 </div>';
   echo '<form method="POST">';
   echo '<table id="tbl'.$type.'">'.implode('',$tbl_rows).'</table>';	
   echo '<input type="hidden" name="dataBufferForDeleting" id="dataBufferForDeleting'.$type.'" value="">';
   echo '<input type="hidden" name="dataBufferForSavingToBase" id="tblDataBuffer'.$type.'" value="">';
   echo '<input type="button"  class="pointer" onclick="priceManagerSendDataToBase(this.form,{\'type\':\'sizes\',\'bufferId\':\'tblDataBuffer'.$type.'\',\'tblId\':\'tbl'.$type.'\'});" value="сохранить">';/**/
   echo '</form>';
   echo '<br><br><br><span style="font-size:11px;">*если к товару не привязаны конкретные места нанесения (а привязаны только типы нанесения или вообще ничего не привязано), то в разделе "площадь" в калькуляторе будут выводиться только те варианты площади, которые промаркированы значением "1" в данном столбце</span>';
	
	

?>