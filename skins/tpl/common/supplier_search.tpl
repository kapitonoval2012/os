				<div class="search_div">
                    <div class="search_cap">Поиск:</div>
                    <div class="search_field">                    
                        <input id="search_query" placeholder="<?php echo $searchPlaceholder; ?>" type="text" onclick="delete_alert_win();" value="<?php echo (isset($_GET['search']))? $_GET['search'] : ''; ?>"><div class="undo_btn"><a href="#"  onclick="return  clear_search_input();">&#215;</a></div></div>
                    <div class="search_button" onClick="do_search(this/*'filter_by_letter&num_page&filter_by_rating&sotring'*/);">&nbsp;</div>
                    <div class="clear_div"></div>
                </div>