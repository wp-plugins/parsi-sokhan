<?php


class csv_parsi_sokhan {

	// Setup options variables
	protected $option_name = 'csv_parsi_sokhan';  // Name of the options array
	protected $data = array(  // Default options values
		'jq_theme' => 'hot-sneaks'
	);
	
	
	public function __construct() {
	
		// Check if is admin
		// We can later update this to include other user roles
		if (is_admin()) {
			add_action( 'admin_init', array( $this, 'csv_parsi_sokhan_settings' ) ); // Create settings
			register_activation_hook( __FILE__ , array($this, 'csv_parsi_sokhan_activate')); // Add settings on plugin activation
		}
	}
	
	public function csv_parsi_sokhan_activate() {
		update_option($this->option_name, $this->data);
	}
	
	public function csv_parsi_sokhan_settings() {
		register_setting('csv_parsi_sokhan_options', $this->option_name, array($this, 'csv_parsi_sokhan_validate'));
	}
	
	public function csv_parsi_sokhan_validate($input) {
		$valid = array();
		$valid['jq_theme'] = $input['jq_theme'];

    	return $valid;
	}
	
	public function csv_parsi_sokhan_admin_scripts() {
		wp_enqueue_script('media-upload');  // For WP media uploader
		wp_enqueue_script('thickbox');  // For WP media uploader
		wp_enqueue_script('jquery-ui-tabs');  // For admin panel page tabs
		wp_enqueue_script('jquery-ui-dialog');  // For admin panel popup alerts
		
		wp_enqueue_script( 'csv_parsi_sokhan', plugins_url( '/js/admin_page.js', __FILE__ ), array('jquery') );  // Apply admin page scripts
		wp_localize_script( 'csv_parsi_sokhan', 'csv_parsi_sokhan_pass_js_vars', array( 'ajax_image' => plugin_dir_url( __FILE__ ).'images/loading.gif', 'ajaxurl' => admin_url('admin-ajax.php') ) );
	}
	
	public function csv_parsi_sokhan_admin_styles() {
		wp_enqueue_style('thickbox');  // For WP media uploader
		wp_enqueue_style('sdm_admin_styles', plugins_url( '/css/admin_page.css', __FILE__ ));  // Apply admin page styles
		
		// Get option for jQuery theme
		$options = get_option($this->option_name);
		$select_theme = isset($options['jq_theme']) ? $options['jq_theme'] : 'hot-sneaks';
		?><link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/<?php echo $select_theme; ?>/jquery-ui.css"><?php  // For jquery ui styling - Direct from jquery
	}

	// Helper function for .csv file exportation
	public function CSV_parsi_sokhan_GENERATE($getTable) {
		ob_end_clean();
		global $wpdb;
		$field='';
		$getField ='';
	
		if($getTable){
			$result = $wpdb->get_results("SELECT * FROM $getTable");
			$requestedTable = mysql_query("SELECT * FROM ".$getTable);
	
			$fieldsCount = mysql_num_fields($requestedTable);
	
			for($i=0; $i<$fieldsCount; $i++){
				$field = mysql_fetch_field($requestedTable);
				$field = (object) $field;         
				$getField .= $field->name.',';
			}
	
			$sub = substr_replace($getField, '', -1);
			$fields = $sub; // Get fields names
			$each_field = explode(',', $sub);
			$csv_file_name = $getTable.'_'.date('Ymd_His').'.csv'; 
	
			// Get fields values with last comma excluded
			foreach($result as $row){
				for($j = 0; $j < $fieldsCount; $j++){
					if($j == 0) $fields .= "\n"; // Force new line if loop complete
					$value = str_replace(array("\n", "\n\r", "\r\n", "\r"), "\t", $row->$each_field[$j]); // Replace new line with tab
					$value = str_getcsv ( $value , ",", "\"" , "\\"); // SEQUENCING DATA IN CSV FORMAT, REQUIRED PHP >= 5.3.0
					$fields .= $value[0].','; // Separate fields with comma
				}
				$fields = substr_replace($fields, '', -1); // Remove extra space at end of string
			}
	
			//header("Content-type: text/x-csv");
			header("Content-type: text/csv");
			header("Content-Transfer-Encoding: binary");
			header("Content-Disposition: attachment; filename=".$csv_file_name);
			header("Content-type: application/x-msdownload");
			header("Pragma: no-cache");
			header("Expires: 0"); 
	
			echo $fields; 
			exit;
		}
	}
	
	public function csv_parsi_sokhan_menu_page() {
		
		// Set variables		
		global $wpdb;
		$error_message = '';
		$success_message = '';
		$message_info_style = '';
		
		//
		// If Delete Table button was pressed
		if(!empty($_POST['delete_db_button_hidden'])) {
			
			$del_qry = 'Delete From '.$_POST['table_select'];
			$del_qry_success = $wpdb->query($del_qry);
			
			if($del_qry_success) {
				$success_message .= __('شما جدول انتخابی را پاک کردید.','csv_parsi_sokhan');
			}
			else {
				$error_message .= '* '.__('پاک کردن جدول انتخابی موفقیت آمیز نبود.','csv_parsi_sokhan');
			}
		}
		
		if ((isset($_POST['export_to_csv_button'])) && (empty($_POST['table_select']))) {
			$error_message .= '* '.__('دیتابیسی برای اکسپرت کردن انتخاب نشده. لطفا یک جدول را انتخاب نمایید.','csv_parsi_sokhan').'<br />';
		}
		
		if ((isset($_POST['export_to_csv_button'])) && (!empty($_POST['table_select']))) {
			$this->CSV_parsi_sokhan_GENERATE($_POST['table_select']);
		}
		
		// If button is pressed to "Import to DB"
		if (isset($_POST['execute_button'])) {
			
			// If the "Select Table" input field is empty
			if(empty($_POST['table_select'])) {
				$error_message .= '* '.__('جدولی انتخاب نکرده اید . لطفا جدول پارسی سخن را انتخاب نمایید.','csv_parsi_sokhan').'<br />';
			}
			// If the "Select Input File" input field is empty
			if(empty($_POST['csv_file'])) {
				$error_message .= '* '.__('فیلدی برای انتخاب نیست . لطفا یکی را انتخاب نمایید.','csv_parsi_sokhan').'<br />';
			}
			// Check that "Input File" has proper .csv file extension
			$ext = pathinfo($_POST['csv_file'], PATHINFO_EXTENSION);
			if($ext !== 'csv') {
				$error_message .= '* '.__('فایل انتخابی شما یک فایل csv نمی باشد لطفا یک فایل csv انتخاب نمایید.','csv_parsi_sokhan');
			}
			
			// If all fields are input; and file is correct .csv format; continue
			if(!empty($_POST['table_select']) && !empty($_POST['csv_file']) && ($ext === 'csv')) {
				
				// If "disable auto_inc" is checked.. we need to skip the first column of the returned array (or the column will be duplicated)
				if(isset($_POST['remove_autoinc_column'])) {
					$db_cols = $wpdb->get_col( "DESC " . $_POST['table_select'], 0 );  
					unset($db_cols[0]);  // Remove first element of array (auto increment column)
				} 
				// Else we just grab all columns
				else {
					$db_cols = $wpdb->get_col( "DESC " . $_POST['table_select'], 0 );  // Array of db column names
				}
				// Get the number of columns from the hidden input field (re-auto-populated via jquery)
				$numColumns = $_POST['num_cols'];
				
				// Open the .csv file and get it's contents
				if(( $fh = @fopen($_POST['csv_file'], 'r')) !== false) {
					
					// Set variables
					$values = array();
					$too_many = '';  // Used to alert users if columns do not match
					
					while(( $row = fgetcsv($fh)) !== false) {  // Get file contents and set up row array
						if(count($row) == $numColumns) {  // If .csv column count matches db column count
							$values[] = '("' . implode('", "', $row) . '")';  // Each new line of .csv file becomes an array
						}
					}
					
					// If user elects to input a starting row for the .csv file
					if(isset($_POST['sel_start_row']) && (!empty($_POST['sel_start_row']))) {
						
						// Get row number from user
						$num_var = $_POST['sel_start_row'] - 1;  // Subtract one to make counting easy on the non-techie folk!  (1 is actually 0 in binary)
						
						// If user input number exceeds available .csv rows
						if($num_var > count($values)) {
							$error_message .= '* '.__('Starting Row value exceeds the number of entries being updated to the database from the .csv file.','csv_parsi_sokhan').'<br />';
							$too_many = 'true';  // set alert variable
						}
						// Else splice array and remove number (rows) user selected
						else {
							$values = array_slice($values, $num_var);
						}
					}
					
					// If there are no rows in the .csv file AND the user DID NOT input more rows than available from the .csv file
					if( empty( $values ) && ($too_many !== 'true')) {
						$error_message .= '* '.__('ستون ها یکی نیستند.','csv_parsi_sokhan').'<br />';
						$error_message .= '* '.__('ستون های موجود در دیتابیس با ستون های فایل شما یکی نیستند.','csv_parsi_sokhan').'<br />';
						$error_message .= '* '.__('تعداد ستون های خود را بررسی کنید - ستون های جدول را در پیش نمایش می توانید ببینید.','csv_parsi_sokhan').'<br />';
					}
					else {
						// If the user DID NOT input more rows than are available from the .csv file
						if($too_many !== 'true') {
							
							$db_query_update = '';
							$db_query_insert = '';
								
							// Format $db_cols to a string
							$db_cols_implode = implode(',', $db_cols);
								
							// Format $values to a string
							$values_implode = implode(',', $values);
							
							
							// If "Update DB Rows" was checked
							if (isset($_POST['update_db'])) {
								
								// Setup sql 'on duplicate update' loop
								$updateOnDuplicate = ' ON DUPLICATE KEY UPDATE ';
								foreach ($db_cols as $db_col) {
									$updateOnDuplicate .= "$db_col=VALUES($db_col),";
								}
								$updateOnDuplicate = rtrim($updateOnDuplicate, ',');
								
								
								$sql = 'INSERT INTO '.$_POST['table_select'] . ' (' . $db_cols_implode . ') ' . 'VALUES ' . $values_implode.$updateOnDuplicate;
								$db_query_update = $wpdb->query($sql);
							}
							else {
								$sql = 'INSERT INTO '.$_POST['table_select'] . ' (' . $db_cols_implode . ') ' . 'VALUES ' . $values_implode;
								$db_query_insert = $wpdb->query($sql);
							}
							
							// If db db_query_update is successful
							if ($db_query_update) {
								$success_message = __('تبریک دیتابیس شما با موفقیت بروز رسانی شد.','csv_parsi_sokhan');
							}
							// If db db_query_insert is successful
							elseif ($db_query_insert) {
								$success_message = __('تبریک دیتا های شما با موفقیت وارد دیتابیس شد.','csv_parsi_sokhan');
								$success_message .= '<br /><strong>'.count($values).'</strong> '.__('رکورد اضافه شد به جدول', 'csv_parsi_sokhan').' <strong>'.$_POST['table_select'].'</strong> '.__('با موفقیت.','csv_parsi_sokhan');
							}
							// If db db_query_insert is successful AND there were no rows to udpate
							elseif( ($db_query_update === 0) && ($db_query_insert === '') ) {
								$message_info_style .= '* '.__('هیچ چیزی آپدیت نشد این دیتا ها قبلا وجود داشته است.','csv_parsi_sokhan').'<br />';
							}
							else {
								$error_message .= '* '.__('یک خطا در دیتابیس بوجود آمد.','csv_parsi_sokhan').'<br />';
								$error_message .= '* '.__('یک مقدار تکراری در فایل شما وجود دارد.','csv_parsi_sokhan').'<br />';
								$error_message .= '* '.__('اگر نیاز است از آپشن  "بروز رسانی سطر ها" استفاده کنید.','csv_parsi_sokhan').'<br />';
							}
						}
					}
				}
				else {
					$error_message .= '* '.__('فایل csv معتبری یافت نشد لطفا قسمت ورود فایل را بررسی نمایید.','csv_parsi_sokhan').'<br />';
				}
			}
		}
		
		// If there is a message - info-style
		if(!empty($message_info_style)) {
			echo '<div class="info_message_dismiss">';
			echo $message_info_style;
			echo '<br /><em>('.__('برای رفتن این پیغام کلیک کنید','csv_parsi_sokhan').')</em>';
			echo '</div>';
		}
		
		// If there is an error message	
		if(!empty($error_message)) {
			echo '<div class="error_message">';
			echo $error_message;
			echo '<br /><em>('.__('برای رفتن این پیغام کلیک کنید','csv_parsi_sokhan').')</em>';
			echo '</div>';
		}
		
		// If there is a success message
		if(!empty($success_message)) {
			echo '<div class="success_message">';
			echo $success_message;
			echo '<br /><em>('.__('برای رفتن این پیغام کلیک کنید','csv_parsi_sokhan').')</em>';
			echo '</div>';
		}
		?>
		<div class="wrap">
        
            <h2><?php _e('افزودن داده های شما','csv_parsi_sokhan'); ?></h2>
            
            <p>با استفاده از این قسمت افزونه شما میتوانید جملات خود را وارد دیتابیس افزونه کنید.</p>
			<p><a href="http://alivazirinia.ir/blog" target="_blank">دیدن صفحه افزونه</a> برای اطلاعات بیشتر.</p>
            
            <div id="tabs">
                <ul>
    				<li><a href="#tabs-1"><?php _e('افزودن','csv_parsi_sokhan'); ?></a></li>
    				<li><a href="#tabs-2"><?php _e('راهنما','csv_parsi_sokhan'); ?></a></li>
    				<li><a href="#tabs-4"><?php _e('تنظیمات','csv_parsi_sokhan'); ?></a></li>
                </ul>
                
                <div id="tabs-1">
                
        			<form id="csv_parsi_sokhan_form" method="post" action="">
                    <table class="form-table"> 
                        
                        <tr valign="top"><th scope="row"><?php _e('جدول افزونه را انتخاب کنید:','csv_parsi_sokhan'); ?></th>
                            <td>
                                <select id="table_select" name="table_select" value="">
                                <option name="" value=""></option>
                                
                                <?php  // Get all db table names
                                global $wpdb;
                                $sql = "SHOW TABLES";
                                $results = $wpdb->get_results($sql);
                                $repop_table = isset($_POST['table_select']) ? $_POST['table_select'] : null;
                                
                                foreach($results as $index => $value) {
                                    foreach($value as $tableName) {
                                        ?><option name="wp_parsi_sokhan" value="wp_parsi_sokhan" class="parsisokhantable">wp_parsi_sokhan</option><?php
                                    }
                                }
                                ?>
                            </select>
                            </td> 
                        </tr>
                        <tr valign="top"><th scope="row"><?php _e('فایل شما:','csv_parsi_sokhan'); ?></th>
                            <td>
                                <?php $repop_file = isset($_POST['csv_file']) ? $_POST['csv_file'] : null; ?>
                                <?php $repop_csv_cols = isset($_POST['num_cols_csv_file']) ? $_POST['num_cols_csv_file'] : '0'; ?>
                                <input id="csv_file" name="csv_file"  type="text" size="70" value="<?php echo $repop_file; ?>" />
                                <input id="csv_file_button" type="button" value="آپلود فایل" />
                                <input id="num_cols" name="num_cols" type="hidden" value="" />
                                <input id="num_cols_csv_file" name="num_cols_csv_file" type="hidden" value="" />
                                <br><?php _e('فایل شما باید یک فایل با پسوند .csv باشد','csv_parsi_sokhan'); ?>
                                <br><?php _e('تعداد ستون های فایل csv. شما:','csv_parsi_sokhan'); echo ' '; ?><span id="return_csv_col_count"><?php echo $repop_csv_cols; ?></span>
                            </td>
                        </tr>
                        <tr valign="top"><th scope="row"><?php _e('سطر شروع کننده:','csv_parsi_sokhan'); ?></th>
                            <td>
                            	<?php $repop_row = isset($_POST['sel_start_row']) ? $_POST['sel_start_row'] : null; ?>
                                <input id="sel_start_row" name="sel_start_row" type="text" size="10" value="<?php echo $repop_row; ?>" />
                                <br><?php _e('پیشفرض روی 1 می باشد ','csv_parsi_sokhan'); ?>
                            </td>
                        </tr>
                        <tr valign="top"><th scope="row"><?php _e('غیر فعال کردن ستون آی دی :','csv_parsi_sokhan'); ?></th>
                            <td>
                                <input id="remove_autoinc_column" name="remove_autoinc_column" type="checkbox" />
                                <br><?php _e('Bypasses the "auto_increment" column;','csv_parsi_sokhan'); ?>
                                <br><?php _e('This will reduce (for the purposes of importation) the number of DB columns by "1".','csv_parsi_sokhan'); ?>
                            </td>
                        </tr>
                        <tr valign="top"><th scope="row"><?php _e('بروزرسانی سطر ها:','csv_parsi_sokhan'); ?></th>
                            <td>
                                <input id="update_db" name="update_db" type="checkbox" />
                                <br><?php _e('زمانی که آیدی تکراری وجود داشته باشد از این استفاده کنید.','csv_parsi_sokhan'); ?>
                                <br><?php _e('Defaults to all rows inserted as new rows.','csv_parsi_sokhan'); ?>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <input id="execute_button" name="execute_button" type="submit" class="button-primary" value="<?php _e('اضافه کردن', 'csv_parsi_sokhan') ?>" />
                        <input id="export_to_csv_button" name="export_to_csv_button" type="submit" class="button-secondary" value="<?php _e('اکسپرت گرفتن', 'csv_parsi_sokhan') ?>" />
                        <input id="delete_db_button" name="delete_db_button" type="button" class="button-secondary" value="<?php _e('خالی کردن جدول', 'csv_parsi_sokhan') ?>" />
                        <input type="hidden" id="delete_db_button_hidden" name="delete_db_button_hidden" value="" />
                    </p>
                    </form>
                </div> <!-- End tab 1 -->
                <div id="tabs-2">
                	<?php _e('آموزش آپلود جملات:','csv_parsi_sokhan'); ?>
                    <ul>
                        <li><?php _e('جدول پارسی سخن را از لیست جداول انتخاب کنید.','csv_parsi_sokhan'); ?></li>
                        <li><?php _e('بعد از انتخاب در قسمت پیش نمایش می توانید ستون هایی از جدول را ببینید سپس فایل خود را دقیقا بر این اساس بسازید.','csv_parsi_sokhan'); ?></li>
                        <li><?php _e('فایل سخنان خود را آپلود کنید ( این فایل باید با پسوند csv باشد ).','csv_parsi_sokhan'); ?></li>
                        <li><strong><?php _e('دقت کنید با زدن گزینه خالی کردن جدول تمامی سخنان قدیمی شما پاک می شود !','csv_parsi_sokhan'); ?></strong></li>
                        
                    </ul>
                    <br /><br />
                    
                    <?php _e('مشخصات فایل شما:','csv_parsi_sokhan'); ?>
                    <ul>
                       <li><?php _e('ابتدا یک فایل اکسل بسازید.','csv_parsi_sokhan'); ?></li>
                       <li><?php _e('سپس در ستون 1 و سطر 1 مشخصاتی را که در پیش نمایش جدول مشاهده میکنید را وارد کنید.','csv_parsi_sokhan'); ?></li>
                       <li><?php _e('دقت کنید اگر سطر اول را برای مشخصات قرار میدهید همان آیدی و ... در زمان آپلود فایل در قسمت سطر شروع کننده مقدار 2 را وارد کنید.','csv_parsi_sokhan'); ?></li>
                       
                    </ul>
                    <br /><br />
                    
                   <?php _e('توضیحات ستون ها:','csv_parsi_sokhan'); ?>
                    <ul>
                       <li><?php _e('id : این مشخصه برای درج آیدی سخنان می باشد که میتوان آن را به صورت اعداد پر کرد ( مانند : 1 و 2 و 3 و ... ).','csv_parsi_sokhan'); ?></li>
                       <li><?php _e('taller : این مشخصه برای گوینده سخن می باشد و باید در آن نام گوینده سخن وارد کرد.','csv_parsi_sokhan'); ?></li>
                       <li><?php _e('content : این مشخصه برای متن سخن گوینده می باشد که باید در آن وارد شود.','csv_parsi_sokhan'); ?></li>
                       <li><?php _e('status : نشان دهنده وضعیت فعال و غیر فعال بودن این جمله بعد از وارد شدن می باشد که با مقادیر 0 و 1 باید پر شود مقدار 0 یعنی غیر فعال باشد و مقدار 1 یعنی فعال باشد.','csv_parsi_sokhan'); ?></li>
                       <li><?php _e('date : این مشخصه برای تاریخ درج سخن می باشد که باید به صورت فرمت تاریخ درج شود ( مانند : 2015/07/22 ).','csv_parsi_sokhan'); ?></li>
                       
                    </ul>
                    
                </div> <!-- End tab 2 -->
                
                
                <div id="tabs-4">
                	<?php $options = get_option($this->option_name); ?>
                	<?php _e('Options Settings:','csv_parsi_sokhan'); ?>
                    
                    <form method="post" action="options.php">
						<?php settings_fields('csv_parsi_sokhan_options'); ?>
                        <table class="form-table">
                            <tr valign="top"><th scope="row"><?php _e('jQuery Theme','csv_parsi_sokhan'); ?></th>
                                <td>
                                	<!-- <input type="text" name="<?php //echo $this->option_name?>[jq_theme]" value="<?php //echo $options['jq_theme']; ?>" /> -->
                                	<select name="<?php echo $this->option_name?>[jq_theme]"/>
                                    	<?php
                        				$jquery_themes = array('base','black-tie','blitzer','cupertino','dark-hive','dot-luv','eggplant','excite-bike','flick','hot-sneaks','humanity','le-frog','mint-choc','overcast','pepper-grinder','redmond','smoothness','south-street','start','sunny','swanky-purse','trontastic','ui-darkness','ui-lightness','vader');
										
										foreach($jquery_themes as $jquery_theme) {
											$selected = ($options['jq_theme']==$jquery_theme) ? 'selected="selected"' : '';
											echo "<option value='$jquery_theme' $selected>$jquery_theme</option>";
										}
										?>
                                	</select>
                                </td>
                            </tr>
                        </table>
                        <p class="submit">
                            <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
                        </p>
                    </form>
                </div> <!-- End tab 4 -->
            </div> <!-- End #tabs -->
        </div> <!-- End page wrap -->
        
        <h3><?php _e('پیش نمایش جدول انتخابی:','csv_parsi_sokhan'); ?><input id="repop_table_ajax" name="repop_table_ajax" value="<?php _e('بارگزاری مجدد','csv_parsi_sokhan'); ?>" type="button" style="margin-left:20px;" /></h3>
            
        <div id="table_preview">
        </div>
        
        <p><?php _e('بعد از اینکه شما جدول افزونه را از لیست جدول انتخاب کنید لیست ستون ها نشان داده می شوند.','csv_parsi_sokhan'); ?>
        <br><?php _e('اگر غیر فعال کردن افزودن خودکار ستون ها نیاز باشد به شما اخطار میدهد .','csv_parsi_sokhan'); ?>
        
        <!-- Delete table warning - jquery dialog -->
        <div id="dialog-confirm" title="<?php _e('جدول انتخابی پاک شود ؟','csv_parsi_sokhan'); ?>">
        	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php _e('آیا تمایل دارید کلیه محتویات این جدول را پاک کنید .','csv_parsi_sokhan'); ?></p>
        </div>
        
        <!-- Alert invalid .csv file - jquery dialog -->
        <div id="dialog_csv_file" title="<?php _e('این یک پسوند معتبر نمی باشد','csv_parsi_sokhan'); ?>" style="display:none;">
        	<p><?php _e('این یک فایل csv نیست ! .','csv_parsi_sokhan'); ?></p>
        </div>
        
        <!-- Alert select db table - jquery dialog -->
        <div id="dialog_select_db" title="<?php _e('جدولی انتخاب نشده است','csv_parsi_sokhan'); ?>" style="display:none;">
        	<p><?php _e('ابتدا از لیست جداول جدولی را انتخاب کنید.','csv_parsi_sokhan'); ?></p>
        </div>
        <?php
	}
	
}
$csv_parsi_sokhan = new csv_parsi_sokhan();

//  Ajax call for showing table column names
add_action( 'wp_ajax_csv_parsi_sokhan_get_columns', 'csv_parsi_sokhan_get_columns_callback' );
function csv_parsi_sokhan_get_columns_callback() {
	
	// Set variables
	global $wpdb;
	$sel_val = isset($_POST['sel_val']) ? $_POST['sel_val'] : null;
	$disable_autoinc = isset($_POST['disable_autoinc']) ? $_POST['disable_autoinc'] : 'false';
	$enable_auto_inc_option = 'false';
	$content = '';
	
	// Ran when the table name is changed from the dropdown
	if ($sel_val) {
		
		// Get table name
		$table_name = $sel_val;
		
		// Setup sql query to get all column names based on table name
		$sql = 'SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = "'.$wpdb->dbname.'" AND TABLE_NAME ="'.$table_name.'" AND EXTRA like "%auto_increment%"';
		
		// Execute Query
		$run_qry = $wpdb->get_results($sql);
		
		//
		// Begin response content
		$content .= '<table id="ajax_table"><tr>';
		
		// If the db query contains an auto_increment column
		if((isset($run_qry[0]->EXTRA)) && (isset($run_qry[0]->COLUMN_NAME))) {
			//$content .= 'auto: '.$run_qry[0]->EXTRA.'<br />';
			//$content .= 'column: '.$run_qry[0]->COLUMN_NAME.'<br />';
			
			// If user DID NOT check 'disable_autoinc'; we need to add that column back with unique formatting 
			if($disable_autoinc === 'false') {
				$content .= '<td class="auto_inc"><strong>'.$run_qry[0]->COLUMN_NAME.'</strong></td>';
			}
			
			// Get all column names from database for selected table
			$column_names = $wpdb->get_col( 'DESC ' . $table_name, 0 );
			$counter = 0;
			
			//
			// IMPORTANT - If the db results contain an auto_increment; we remove the first column below; because we already added it above.
			foreach ( $column_names as $column_name ) {
				if( $counter++ < 1) continue;  // Skip first iteration since 'auto_increment' table data cell will be duplicated
			    $content .= '<td><strong>'.$column_name.'</strong></td>';
			}
		}
		// Else get all column names from database (unfiltered)
		else {
			$column_names = $wpdb->get_col( 'DESC ' . $table_name, 0 );
			foreach ( $column_names as $column_name ) {
			  $content .= '<td><strong>'.$column_name.'</strong></td>';
			}
		}
		$content .= '</tr></table><br />';
		$content .= __('تعداد ستون های جدول:','csv_parsi_sokhan').' <span id="column_count"><strong>'.count($column_names).'</strong></span><br />';
		
		// If there is an auto_increment column in the returned results
		if((isset($run_qry[0]->EXTRA)) && (isset($run_qry[0]->COLUMN_NAME))) {
			// If user DID NOT click the auto_increment checkbox
			if($disable_autoinc === 'false') {
				$content .= '<div class="warning_message">';
				$content .= __('این جدول شامل افزایش خودکار ستون ها می باشد.','csv_parsi_sokhan').'<br />';
				$content .= __('از فایل csv خود اطمینان حاصل فرمایید که مانند پیش نمایش بالا ساخته شده باشد با همان ستون ها و مقدار ها.','csv_parsi_sokhan').'<br />';
				$content .= __('ویژگی وجود دارد برای اینکه شما بتوانید به صورت خودکار آی دی ها را وارد کنید  .','csv_parsi_sokhan').'<br />';
				$content .= '</div>';
				
				// Send additional response
				$enable_auto_inc_option = 'true';
			}
			// If the user clicked the auto_increment checkbox
			if($disable_autoinc === 'true') {
				$content .= '<div class="info_message">';
				$content .= __('شما این قابلیت را میتوانید غیر فعال کنید هم اکنون.','csv_parsi_sokhan').'<br />';
				$content .= __('تمام رکورد های شما به صورت خودکار آیدی میگیرند.','csv_parsi_sokhan').'<br />';
				$content .= __('نام ستونی که حذف : ','csv_parsi_sokhan').' <strong><em>'.$run_qry[0]->COLUMN_NAME.'</em></strong>.<br />';
				$content .= '</div>';
				
				// Send additional response 
				$enable_auto_inc_option = 'true';
			}
		}
	}
	else {
		$content = '';
		$content .= '<table id="ajax_table"><tr><td>';
		$content .= __('دیتابیس انتخاب نشده است.','csv_parsi_sokhan');
		$content .= '<br />';
		$content .= __('لطفا از لیست جداول جدول پارسی سخن را اتخاب نمایید.','csv_parsi_sokhan');
		$content .= '</td></tr></table>';
	}
	
	// Set response variable to be returned to jquery
	$response = json_encode( array( 'content' => $content, 'enable_auto_inc_option' => $enable_auto_inc_option ) );
	header( "Content-Type: application/json" );
	echo $response;
	die();
}

// Ajax call to process .csv file for column count
add_action('wp_ajax_csv_parsi_sokhan_get_csv_cols','csv_parsi_sokhan_get_csv_cols_callback');
function csv_parsi_sokhan_get_csv_cols_callback() {
	
	// Get file upload url
	$file_upload_url = $_POST['file_upload_url'];
	
	// Open the .csv file and get it's contents
	if(( $fh = @fopen($_POST['file_upload_url'], 'r')) !== false) {
		
		// Set variables
		$values = array();
		
		// Assign .csv rows to array
		while(( $row = fgetcsv($fh)) !== false) {  // Get file contents and set up row array
			//$values[] = '("' . implode('", "', $row) . '")';  // Each new line of .csv file becomes an array
			$rows[] = array(implode('", "', $row));
		}
		
		// Get a single array from the multi-array... and process it to count the individual columns
		$first_array_elm = reset($rows);
		$xplode_string = explode(", ", $first_array_elm[0]);
		
		// Count array entries
		$column_count = count($xplode_string);
	}
	else {
		$column_count = 'There was an error extracting data from the.csv file. Please ensure the file is a proper .csv format.';
	}
	
	// Set response variable to be returned to jquery
	$response = json_encode( array( 'column_count' => $column_count ) );
	header( "Content-Type: application/json" );
	echo $response;
	die();
}




