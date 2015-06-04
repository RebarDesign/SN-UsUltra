<?php
class rdCustom {

	var $messages_process;

	var $profile_order_field;

	var $profile_role;
	var $profile_order;
	var $uultra_args;

	function __construct()
	{

		add_action( 'wp_ajax_rd_pay_tutor',  array( $this, 'rd_pay_tutor' ));
		//array( XooUserLogin::$instance,
		add_action( 'wp_ajax_rd_add_session_payment',  array( $this, 'rd_add_session_payment' ));
		add_action( 'wp_ajax_rd_update_session_payment',  array( $this, 'rd_update_session_payment' ));
		add_action( 'wp_ajax_rd_process_session_status',  array( $this, 'rd_process_session_status' ));

	}

	public function display_user_credits($user_id){

			 global  $xoouserultra;
			 $rd_user_role = implode(', ',get_userdata($user_id)->roles);
			 $fields_list = "<div id='credit-information'>";
			 $getUserCredit = getUserCredit($user_id);

			 if(!$getUserCredit){
				$getUserCredit = 0;
				$fields_list .= "<p id='rd_credits_remaining' class='rd_credits_remaining_none'>No funds</p>";
				return $fields_list;
			}

			$getUserCredit = number_format((float)$getUserCredit, 1);

			if($rd_user_role == 'tutor'){
				$fields_list .= "<p><input disabled id='rd_credits_remaining' class='rd_credits_remaining' value='".$getUserCredit."'>   DKK Earned</p>";
				$fields_list .= "</div>";
				return $fields_list;
			}
			else {
				$fields_list .= "<p><input disabled id='rd_credits_remaining' class='rd_credits_remaining' value='".$getUserCredit."'>   DKK Available</p>";
				$fields_list .= "</div>";
				return $fields_list;
			}

		} // END display_user_credits()

		public function display_student_hours($user_id){

			 global  $xoouserultra;

			 // $l_q_p = $this->$live_quarter_price;
			 // $o_q_p = $this->$online_quarter_price;

			 $l_q_p = get_custom( '15_min_live_tutoring');
			 $o_q_p = get_custom( '15_min_online_tutoring');

			 // $l_q_p = 87.5;
			 // $o_q_p = 75;

			 $rd_user_role = implode(', ',get_userdata($user_id)->roles);
			 $fields_list = "<div id='credit-information'>";
			 $getUserCredit = getUserCredit($user_id);

			 if(!$getUserCredit){
				$getUserCredit = 0;
				$fields_list .= "<p id='rd_credits_remaining' class='rd_credits_remaining_none'>No funds</p>";
				return $fields_list;
			}

			$getUserCreditForm = number_format((float)$getUserCredit, 1);

			$live_durH = intval($getUserCredit/($l_q_p * 4));
			$leftMinutes = $getUserCredit - ($live_durH * $l_q_p * 4);
			$live_durM = 15*intval($leftMinutes/$l_q_p);

			$online_durH = intval($getUserCredit/($o_q_p * 4));
			$leftMinutes = $getUserCredit - ($online_durH * $o_q_p * 4);
			$online_durM = 15*intval($leftMinutes/$o_q_p );


			$fields_list .= "<strong>Tutoring Time Available</strong>
							<input hidden id='rd_credits_remaining' class='rd_credits_remaining' value='".$getUserCreditForm."'></br></br>";


			$fields_list .= "<p><span><i style='margin-right:10px' class='fa fa fa-male fa-lg'></i>  Person</span><input disabled class='rd_credits_remaining' style='margin-left:10px' value='".$live_durH."h:".$live_durM."min'></p>";

			$fields_list .= "<p> Or </p>";

			$fields_list .= "<p><span><i style='margin-right:10px' class='fa fa fa-globe fa-lg'></i>  Online</span><input disabled class='rd_credits_remaining' style='margin-left:10px' value='".$online_durH."h:".$online_durM."min'></p>";

			$fields_list .= "</div>";
			return $fields_list;
		} //END display_student_hours()

		public function display_tutor_hours($user_id)
		{
			 global $wpdb, $xoouserultra;
			 $rd_user_role = implode(', ',get_userdata($user_id)->roles);
			 $fields_list = "";
			 $getUserCredit = getUserCredit($user_id);

			 //get last paid trasnfer date
			 $payInfo = $wpdb->get_var(
				"SELECT created
				 FROM `wp_rd_tutor_paid_credits`
				 WHERE id = (SELECT Max(id) FROM `wp_rd_tutor_paid_credits` WHERE uid = ".$user_id." )");

			 if($payInfo!="")
			 {
			 $lastPaid = $payInfo;
			 $lastPaidSec = strtotime($lastPaid);
			 }
			 else{
			 $lastPaid = "Not paid yet";
			 }

			$querystr = "SELECT $wpdb->posts.*
				    FROM $wpdb->posts, $wpdb->postmeta
				    WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id
				    AND $wpdb->postmeta.meta_key = 'session_status'
				    AND $wpdb->postmeta.meta_value = 'ok'
				    AND $wpdb->posts.post_type = 'session'
				    AND $wpdb->posts.post_status = 'publish'
				    AND $wpdb->posts.post_author = $user_id
				    ORDER BY $wpdb->posts.post_modified DESC";

			$authorPosts = $wpdb->get_results($querystr, OBJECT);

			$onlineDuration = "none";
			$liveDuration = "none";

			$online_durH = 0;
			$online_durM = 0;

			$live_durH = 0;
			$live_durM = 0;

			if (!$authorPosts){
			$onlineDuration = "no results";
			$liveDuration = "no results";
			}

			$latestSession = 0;
			$maxSession = 0;

			 foreach ( $authorPosts as $unpaidPost )
			 {

			 		if (strtotime($unpaidPost->post_modified) > $lastPaidSec){
						if (get_post_meta( $unpaidPost->ID, 'session_type', true ) == "online") {
							$sessionDur = get_post_meta( $unpaidPost->ID, 'session_duration', true );
							$durArray = explode(':', $sessionDur);
							$online_durH += (int)$durArray[0];
							$online_durM += (int)$durArray[1];
						}

						if (get_post_meta( $unpaidPost->ID, 'session_type', true ) == "person") {
							$sessionDur = get_post_meta( $unpaidPost->ID, 'session_duration', true );
							$durArray = explode(':', $sessionDur);
							$live_durH += (int)$durArray[0];
							$live_durM += (int)$durArray[1];
						}

					}
			  }

			if ($online_durM >= 60) {
				$online_durH  += floor($online_durM/60); //round down to nearest minute.
				$online_durM = $online_durM % 60;
			}

		    if ($live_durM >= 60) {
				$live_durH  += floor($live_durM/60); //round down to nearest minute.
				$live_durM = $live_durM % 60;
			}


			$onlineDuration = $online_durH."h ".$online_durM."min";
			$liveDuration = $live_durH."h ".$live_durM."min";


			 if(!$getUserCredit){
				$getUserCredit = 0;
				$fields_list .= "<p id='rd_credits_remaining' class='rd_credits_remaining_none'>No hours</p>";
				return $fields_list;
			 }
			 if($onlineCredits = 0){
				$fields_list .= "<p id='rd_credits_remaining' class='rd_credits_remaining_none'>No online sessions</p>";
				return $fields_list;
			 }
			if($rd_user_role == 'tutor'){
				// if ($getUserCredit == 0) {
				// $liveDuration = 0;
				// $liveDuration = 0;
				// }
				$fields_list .= "<p><input disabled id='rd_hours_remaining' class='rd_credits_remaining' value='".$liveDuration."'>   in person</p><p><input disabled id='rd_hours_remaining' class='rd_credits_remaining' value='".$onlineDuration."'>   online</p>";
				$fields_list .= "<p><input disabled id='rd_hours_remaining' class='rd_credits_remaining' value='".$lastPaid."'>   last payment date</p>";
				return $fields_list;
			}
			else {
				$fields_list .= "<p><input disabled id='rd_credits_remaining' class='rd_credits_remaining' value='".$getUserCredit."'>   DKK Available</p>";
				return $fields_list;
			}

	    } // END display_tutor_hours()

	    public function rd_display_user_role($user_id)
		{
			global  $xoouserultra;
			$fields_list = "";
			$fields_list .= "<p><input disabled value='Tutor'></p>";
			return $fields_list;
		} // END rd_display_user_role()

		public function getUserCreditForm($user_id)
		{
		global $wpdb;
		$sql = $wpdb->get_row("SELECT credit FROM `".$wpdb->prefix."woocredit_users` where user_id=".$user_id);
		$res = "credit";
		if($sql->$res!=""):
			return $sql->$res;
		else:
			return null;
		endif;
		} //END getUserCredit()


		function rd_edit_session($id)
		{
		global $wpdb, $current_user, $xoouserultra;


		require_once(ABSPATH . 'wp-includes/general-template.php');

		$user_id = get_current_user_id();

		$res = $wpdb->get_results( 'SELECT `ID`, `post_content` FROM ' . $wpdb->prefix . 'posts WHERE `post_author` = "' . $user_id. '" AND  `ID` = "'.$id.'" AND (`post_status` = "publish" ) ORDER BY `post_date` DESC' );


		if ( !empty( $res ) )
		{
			foreach($res as $rc)
			{
				$post = $rc;
			}


		}else{

			//not valid post

			$xoouserultra->publisher->errors = __('Invalid Post', 'xoousers');

		}


		//TODO Add modified date

		 ?>


         <div class="commons-panel xoousersultra-shadow-borers" >


                  <div class="commons-panel-heading">
                             <h2> <?php echo  __('Edit Session','xoousers');?> </h2>

                   </div>


            <div class="commons-panel-content" >


                 <div class="uultra-post-publish">

                 <?php

				 if($xoouserultra->publisher->errors!="")
				 {

					  echo "<div class='uupublic-ultra-error'>".$xoouserultra->publisher->errors."</div>";

				 }else{

					 if($xoouserultra->publisher->act_message!="")
					 {
						 echo "<div class='uupublic-ultra-success'>".$xoouserultra->publisher->act_message."</div>";

					 }
				 ?>

                 <form method="post" name="uultra-front-publisher-post">

                 <input type="hidden" name="post_id" value="<?php echo $id?>" />

                 <div class="tablenav_post">

                <p><a class="uultra-btn-commm" href="?module=posts" title="<?php echo __('My sessions','xoousers')?>" ><span><i class="fa fa-angle-double-left  fa-lg"></i></span> <?php echo __('My sessions:','xoousers')?> </a></p>

				</div>

				<?php

				$student_id = get_post_meta( $post->ID, 'session_student', true );
				$session_student_name = $wpdb->get_var( "SELECT display_name FROM $wpdb->users WHERE ID = '$student_id'" );
				$session_student_link = $wpdb->get_var( "SELECT user_nicename FROM $wpdb->users WHERE ID = '$student_id'" );


				 echo '<h3><a href="'.get_site_url().'/profile/'.$session_student_link.'">'.$session_student_name.'</a></h3>';

                 $session_credit = getUserCredit($student_id) + get_post_meta( $post->ID, 'session_credits', true );

                 echo '
                 </br>
                 <div class="field_row">
                     <input name="rd_credits_remaining" type="hidden" id="rd_credits_remaining" value="'.$session_credit.'" />
                 </div>
                 ';

                 //echo $xoouserultra->userpanel->display_student_hours($student_id);

                 ?>

                 <div class="field_row">
                     <p><?php echo __('ID:','xoousers')?></p><p><input name="rd_session_id" id="rd_session_id" type="text" class="xoouserultra-input" disabled value="<?php echo $post->ID?>" /></p>
                 </div>

                 <p>Session Type:</p>
                 <?php $value = get_post_meta( $post->ID, 'session_type', true );

				  echo '
				        <select id="rd_session_type" name="rd_session_type" >
				            <option value="person" '.(($value == 'person')?'selected':'').'> In person </option>
				            <option value="online" '.(($value == 'online')?'selected':'').'> Online </option>
				        </select>';
				  ?>


				<p>Session Duration:</p>
				<?php
				$value = get_post_meta( $post->ID, 'session_duration', true );
				echo '<input type="text" value="'.$value.'" id="rd_basicTimePicker" />';
				?>

				 <!-- <p>Session Price:</p>      -->
				  <?php
				  $value = get_post_meta( $post->ID, 'session_credits', true );
  				  echo '<input hidden id="rd_session_price" name="rd_session_price" value="'.$value.'" />';
				  ?>

				 <p>Session Date and Place:</p>
				 <?php
				 $value = get_post_meta( $post->ID, 'session_date', true );
  				 echo '<input type="text" id="rd_session_date" name="rd_session_date" value="'.$value.'" >';
				 ?>

				 <p>Notes:</p>

				 <?php echo '
				 <textarea   class="form-control" rows="3" name="rd_session_notes" id="rd_session_notes" />'.
				 $post->post_content.
				 '</textarea>'
				 ;?>


                <div class="field_row">

                 <?php echo '<a class="uultra-btn-email" href="#" id="rd-update-session-confirm" data-id="'.$student_id.'"><span><i class="fa fa-check"></i></span>'. __("Update", 'xoousers').'</a></p>'; ?>
                 </div>

                 </form>

                 <div id="rd-update-session-noti-id"></div>

                <?php } //end error?>

                </div>


            </div>


         </div> <!--  End post wrapper-->

        <?php

		//return $html;

	}

	// End RD Custom function

	/**
	 * My Sessions
	 */

	// SEEN BY TUTOR IN HIS DASHBOARD
	function show_my_pending_sessions_edit()
	{
		global $wpdb, $current_user, $xoouserultra;

		$user_id = get_current_user_id();



		//$sesns = $wpdb->get_results( 'SELECT `ID`, `post_author`, `post_date`, `post_title`, `post_content` FROM ' . $wpdb->prefix . 'posts WHERE `post_author` = "' . $user_id. '" ORDER BY `post_date` DESC' );

		$querystr = "SELECT $wpdb->posts.*
			    FROM $wpdb->posts, $wpdb->postmeta
			    WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id
			    AND $wpdb->postmeta.meta_key = 'session_status'
			    AND $wpdb->postmeta.meta_value = 'pending'
			    AND $wpdb->posts.post_type = 'session'
			    AND $wpdb->posts.post_status = 'publish'
			    AND $wpdb->posts.post_author = $user_id
			    ORDER BY $wpdb->posts.post_modified DESC";

		$sesns = $wpdb->get_results($querystr, OBJECT);


		// echo '<div class="tablenav_post">

  //               <p><a class="uultra-btn-commm" href="?module=posts&act=add" title="'. __('Add New Session','xoousers').'" ><span><i class="fa fa-plus  fa-lg"></i></span> '.__('Add New Session','xoousers').' </a></p>

		// 		</div>';

		if ( !empty( $status ) )
		{
			echo '<div id="message" class="updated fade"><p>', $status, '</p></div>';
		}
		if ( empty( $sesns ) )
		{
			echo '<p>', __( 'You have no sessions.', 'xoousers' ), '</p>';
		}
		else
		{
			$n = count( $sesns );


			?>

			<form action="" method="get">
				<?php wp_nonce_field( 'usersultra-bulk-action_inbox' ); ?>
				<input type="hidden" name="page" value="usersultra_inbox" />


				<div class="table-responsive">
				<table class="table widefat fixed" id="table-3" cellspacing="0">
					<thead>
					<tr>
						<th class="manage-column" ><?php _e( 'ID', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Type', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Duration', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Date & Info', 'xoousers' ); ?></th>
						<th class="manage-column" style="width: 30%;" ><?php _e( 'Notes', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Student', 'xoousers' ); ?></th>
                        <th class="manage-column" ><?php _e( '', 'xoousers' ); ?></th>
					</tr>
					</thead>
					<tbody>
						<?php
						foreach ( $sesns as $sesn )
						{
								$session_student_id = get_post_meta( $sesn->ID, 'session_student', true );
        						$session_student_name = $wpdb->get_var( "SELECT display_name FROM $wpdb->users WHERE ID = '$session_student_id'" );
       							$session_student_link = $wpdb->get_var( "SELECT user_nicename FROM $wpdb->users WHERE ID = '$session_student_id'" );

							?>
						<tr>

                            <td><?php echo $sesn->ID; ?></td>
							<td><?php echo get_post_meta( $sesn->ID, 'session_type', true ); ?></td>
							<td><?php echo get_post_meta( $sesn->ID, 'session_duration', true ); ?></td>
                            <td><?php echo get_post_meta( $sesn->ID, 'session_date', true ); ?></td>
							<td><?php echo $sesn->post_content; ?></td>
							<td><?php echo '<a href="'.get_site_url().'/profile/'.$session_student_link.'">'.$session_student_name.'</a>' ?></td>

                            <td id="rd_session_actions" class='rd_session_actions'>
	                            <a class="rd-process-session" id="rd-process-session" href="" title="Confirm" item-id="<?php echo $sesn->ID; ?>" action-id="ok"><span><i style="color:green;" class="fa fa-check-circle fa-lg"></i></span></a>
	                            <a class="rd-process-session" id="rd-process-session" href="" title="Cancel" item-id="<?php echo $sesn->ID; ?>" action-id="cancelled"><span><i style="color:red;" class="fa fa-minus-circle fa-lg"></i></span></a>
	                            <a href="?module=posts&act=edit&post_id=<?php echo $sesn->ID; ?>" title="<?php echo __('Edit','xoousers')?>" ><span><i class="fa fa-pencil-square-o fa-lg"></i></span> </a>
                            </td>
						</tr>
							<?php

						}
						?>
					</tbody>

				</table>
				</div>
			</form>
			<?php

		}
		?>

	<?php
	}


	function show_my_confirmed_sessions_edit()
	{
		global $wpdb, $current_user, $xoouserultra;

		$user_id = get_current_user_id();



		//$sesns = $wpdb->get_results( 'SELECT `ID`, `post_author`, `post_date`, `post_title`, `post_content` FROM ' . $wpdb->prefix . 'posts WHERE `post_author` = "' . $user_id. '" ORDER BY `post_date` DESC' );

		$querystr = "SELECT $wpdb->posts.*
			    FROM $wpdb->posts, $wpdb->postmeta
			    WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id
			    AND $wpdb->postmeta.meta_key = 'session_status'
			    AND $wpdb->postmeta.meta_value = 'ok'
			    AND $wpdb->posts.post_type = 'session'
			    AND $wpdb->posts.post_status = 'publish'
			    AND $wpdb->posts.post_author = $user_id
			    ORDER BY $wpdb->posts.post_modified DESC";

		$sesns = $wpdb->get_results($querystr, OBJECT);


		// echo '<div class="tablenav_post">

  //               <p><a class="uultra-btn-commm" href="?module=posts&act=add" title="'. __('Add New Session','xoousers').'" ><span><i class="fa fa-plus  fa-lg"></i></span> '.__('Add New Session','xoousers').' </a></p>

		// 		</div>';

		if ( !empty( $status ) )
		{
			echo '<div id="message" class="updated fade"><p>', $status, '</p></div>';
		}
		if ( empty( $sesns ) )
		{
			echo '<p>', __( 'You have no sessions.', 'xoousers' ), '</p>';
		}
		else
		{
			$n = count( $sesns );


			?>

			<form action="" method="get">
				<?php wp_nonce_field( 'usersultra-bulk-action_inbox' ); ?>
				<input type="hidden" name="page" value="usersultra_inbox" />
				<div class="table-responsive">
				<table class="table widefat fixed" id="table-3" cellspacing="0">
					<thead>
					<tr>
						<th class="manage-column" ><?php _e( 'ID', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Type', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Duration', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Date & Info', 'xoousers' ); ?></th>
						<th class="manage-column" style="width: 30%;" ><?php _e( 'Notes', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Student', 'xoousers' ); ?></th>
                        <th class="manage-column" ><?php _e( 'Confirmed on', 'xoousers' ); ?></th>
					</tr>
					</thead>
					<tbody>
						<?php
						foreach ( $sesns as $sesn )
						{
								$session_student_id = get_post_meta( $sesn->ID, 'session_student', true );
        						$session_student_name = $wpdb->get_var( "SELECT display_name FROM $wpdb->users WHERE ID = '$session_student_id'" );
       							$session_student_link = $wpdb->get_var( "SELECT user_nicename FROM $wpdb->users WHERE ID = '$session_student_id'" );

							?>
						<tr class="success">

                            <td><?php echo $sesn->ID; ?></td>
							<td><?php echo get_post_meta( $sesn->ID, 'session_type', true ); ?></td>
							<td><?php echo get_post_meta( $sesn->ID, 'session_duration', true ); ?></td>
                            <td><?php echo get_post_meta( $sesn->ID, 'session_date', true ); ?></td>
							<td><?php echo $sesn->post_content; ?></td>
							<td><?php echo '<a href="'.get_site_url().'/profile/'.$session_student_link.'">'.$session_student_name.'</a>' ?></td>
                            <td><?php echo $sesn->post_modified; ?></td>
						</tr>
							<?php

						}
						?>
					</tbody>

				</table>
				</div>
			</form>
			<?php

		}
		?>

	<?php
	}

	function show_my_cancelled_sessions_edit()
	{
		global $wpdb, $current_user, $xoouserultra;

		$user_id = get_current_user_id();



		//$sesns = $wpdb->get_results( 'SELECT `ID`, `post_author`, `post_date`, `post_title`, `post_content` FROM ' . $wpdb->prefix . 'posts WHERE `post_author` = "' . $user_id. '" ORDER BY `post_date` DESC' );

		$querystr = "SELECT $wpdb->posts.*
			    FROM $wpdb->posts, $wpdb->postmeta
			    WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id
			    AND $wpdb->postmeta.meta_key = 'session_status'
			    AND $wpdb->postmeta.meta_value = 'cancelled'
			    AND $wpdb->posts.post_type = 'session'
			    AND $wpdb->posts.post_status = 'publish'
			    AND $wpdb->posts.post_author = $user_id
			    ORDER BY $wpdb->posts.post_modified DESC";

		$sesns = $wpdb->get_results($querystr, OBJECT);


		// echo '<div class="tablenav_post">

  //               <p><a class="uultra-btn-commm" href="?module=posts&act=add" title="'. __('Add New Session','xoousers').'" ><span><i class="fa fa-plus  fa-lg"></i></span> '.__('Add New Session','xoousers').' </a></p>

		// 		</div>';

		if ( !empty( $status ) )
		{
			echo '<div id="message" class="updated fade"><p>', $status, '</p></div>';
		}
		if ( empty( $sesns ) )
		{
			echo '<p>', __( 'You have no sessions.', 'xoousers' ), '</p>';
		}
		else
		{
			$n = count( $sesns );


			?>

			<form action="" method="get">
				<?php wp_nonce_field( 'usersultra-bulk-action_inbox' ); ?>
				<input type="hidden" name="page" value="usersultra_inbox" />

				<div class="table-responsive">
				<table class="table widefat fixed" id="table-3" cellspacing="0">
					<thead>
					<tr>
						<th class="manage-column" ><?php _e( 'ID', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Type', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Duration', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Date & Info', 'xoousers' ); ?></th>
						<th class="manage-column" style="width: 30%;" ><?php _e( 'Notes', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Student', 'xoousers' ); ?></th>
                        <th class="manage-column" ><?php _e( 'Cancelled on', 'xoousers' ); ?></th>
					</tr>
					</thead>
					<tbody>
						<?php
						foreach ( $sesns as $sesn )
						{
								$session_student_id = get_post_meta( $sesn->ID, 'session_student', true );
        						$session_student_name = $wpdb->get_var( "SELECT display_name FROM $wpdb->users WHERE ID = '$session_student_id'" );
        						$session_student_link = $wpdb->get_var( "SELECT user_nicename FROM $wpdb->users WHERE ID = '$session_student_id'" );

							?>
						<tr class="danger">

                            <td><?php echo $sesn->ID; ?></td>
							<td><?php echo get_post_meta( $sesn->ID, 'session_type', true ); ?></td>
							<td><?php echo get_post_meta( $sesn->ID, 'session_duration', true ); ?></td>
                            <td><?php echo get_post_meta( $sesn->ID, 'session_date', true ); ?></td>
							<td><?php echo $sesn->post_content; ?></td>
							<td><?php echo '<a href="'.get_site_url().'/profile/'.$session_student_link.'">'.$session_student_name.'</a>' ?></td>
                            <td><?php echo $sesn->post_modified; ?></td>
						</tr>
							<?php

						}
						?>
					</tbody>

				</table>
				</div>
			</form>
			<?php

		}
		?>

	<?php
	}

	function show_my_pending_sessions_no_edit($user_id)
	{
		global $wpdb, $current_user, $xoouserultra;


		//$sesns = $wpdb->get_results( 'SELECT `ID`, `post_author`, `post_date`, `post_title`, `post_content` FROM ' . $wpdb->prefix . 'posts WHERE `post_author` = "' . $user_id. '" ORDER BY `post_date` DESC' );


	    $querystr = "SELECT $wpdb->posts.*
			    FROM $wpdb->posts, $wpdb->postmeta
			    WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id
			    AND $wpdb->postmeta.meta_key = 'session_status'
			    AND $wpdb->postmeta.meta_value = 'pending'
			    AND $wpdb->posts.post_type = 'session'
			    AND $wpdb->posts.post_status = 'publish'
			    AND $wpdb->posts.post_author = $user_id
			    ORDER BY $wpdb->posts.post_modified DESC";

		$sesns = $wpdb->get_results($querystr, OBJECT);






		// echo '<div class="tablenav_post">

  //               <p><a class="uultra-btn-commm" href="?module=posts&act=add" title="'. __('Add New Session','xoousers').'" ><span><i class="fa fa-plus  fa-lg"></i></span> '.__('Add New Session','xoousers').' </a></p>

		// 		</div>';

		if ( !empty( $status ) )
		{
			echo '<div id="message" class="updated fade"><p>', $status, '</p></div>';
		}
		if ( empty( $sesns ) )
		{
			echo '<p>', __( 'You have no sessions.', 'xoousers' ), '</p>';
		}
		else
		{
			$n = count( $sesns );


			?>

			<form action="" method="get">
				<?php wp_nonce_field( 'usersultra-bulk-action_inbox' ); ?>
				<input type="hidden" name="page" value="usersultra_inbox" />


				<div class="table-responsive">
				<table class="table widefat fixed" id="table-3" cellspacing="0">
					<thead>
					<tr>
						<th class="manage-column" ><?php _e( 'ID', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Type', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Duration', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Date & Info', 'xoousers' ); ?></th>
						<th class="manage-column" style="width: 30%;" ><?php _e( 'Notes', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Student', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Created', 'xoousers' ); ?></th>
					</tr>
					</thead>
					<tbody>
						<?php

						foreach ( $sesns as $sesn )
						{

        						$session_student_id = get_post_meta( $sesn->ID, 'session_student', true );
       							$session_student_name = $wpdb->get_var( "SELECT display_name FROM $wpdb->users WHERE ID = '$session_student_id'" );
       							$session_student_link = $wpdb->get_var( "SELECT user_nicename FROM $wpdb->users WHERE ID = '$session_student_id'" );
							?>
						<tr>

                            <td><?php echo $sesn->ID; ?></td>
							<td><?php echo get_post_meta( $sesn->ID, 'session_type', true ); ?></td>
							<td><?php echo get_post_meta( $sesn->ID, 'session_duration', true ); ?></td>
                            <td><?php echo get_post_meta( $sesn->ID, 'session_date', true ); ?></td>
							<td><?php echo $sesn->post_content; ?></td>
							<td><?php echo '<a href="'.get_site_url().'/profile/'.$session_student_link.'">'.$session_student_name.'</a>' ?></td>
							<td><?php echo $sesn->post_modified; ?></td>
						</tr>
							<?php
						}?>
					</tbody>

				</table>
				</div>
			</form>
			<?php

		}
		?>

	<?php
	}

	function show_my_confirmed_sessions_no_edit($user_id)
	{
		global $wpdb, $current_user, $xoouserultra;

		//$sesns = $wpdb->get_results( 'SELECT `ID`, `post_author`, `post_date`, `post_title`, `post_content` FROM ' . $wpdb->prefix . 'posts WHERE `post_author` = "' . $user_id. '" ORDER BY `post_date` DESC' );


	    $querystr = "SELECT $wpdb->posts.*
			    FROM $wpdb->posts, $wpdb->postmeta
			    WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id
			    AND $wpdb->postmeta.meta_key = 'session_status'
			    AND $wpdb->postmeta.meta_value = 'ok'
			    AND $wpdb->posts.post_type = 'session'
			    AND $wpdb->posts.post_status = 'publish'
			    AND $wpdb->posts.post_author = $user_id
			    ORDER BY $wpdb->posts.post_modified DESC";

		$sesns = $wpdb->get_results($querystr, OBJECT);






		// echo '<div class="tablenav_post">

  //               <p><a class="uultra-btn-commm" href="?module=posts&act=add" title="'. __('Add New Session','xoousers').'" ><span><i class="fa fa-plus  fa-lg"></i></span> '.__('Add New Session','xoousers').' </a></p>

		// 		</div>';

		if ( !empty( $status ) )
		{
			echo '<div id="message" class="updated fade"><p>', $status, '</p></div>';
		}
		if ( empty( $sesns ) )
		{
			echo '<p>', __( 'You have no sessions.', 'xoousers' ), '</p>';
		}
		else
		{
			$n = count( $sesns );


			?>

			<form action="" method="get">
				<?php wp_nonce_field( 'usersultra-bulk-action_inbox' ); ?>
				<input type="hidden" name="page" value="usersultra_inbox" />


				<div class="table-responsive">
				<table class="table widefat fixed" id="table-3" cellspacing="0">
					<thead>
					<tr>
						<th class="manage-column" ><?php _e( 'ID', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Type', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Duration', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Date & Info', 'xoousers' ); ?></th>
						<th class="manage-column" style="width: 30%;" ><?php _e( 'Notes', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Student', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Confirmed', 'xoousers' ); ?></th>
					</tr>
					</thead>
					<tbody>
						<?php

						foreach ( $sesns as $sesn )
						{

        						$session_student_id = get_post_meta( $sesn->ID, 'session_student', true );
       							$session_student_name = $wpdb->get_var( "SELECT display_name FROM $wpdb->users WHERE ID = '$session_student_id'" );
       							$session_student_link = $wpdb->get_var( "SELECT user_nicename FROM $wpdb->users WHERE ID = '$session_student_id'" );
							?>
						<tr class="success">

                            <td><?php echo $sesn->ID; ?></td>
							<td><?php echo get_post_meta( $sesn->ID, 'session_type', true ); ?></td>
							<td><?php echo get_post_meta( $sesn->ID, 'session_duration', true ); ?></td>
                            <td><?php echo get_post_meta( $sesn->ID, 'session_date', true ); ?></td>
							<td><?php echo $sesn->post_content; ?></td>
							<td><?php echo '<a href="'.get_site_url().'/profile/'.$session_student_link.'">'.$session_student_name.'</a>' ?></td>
							<td><?php echo $sesn->post_modified; ?></td>

						</tr>
							<?php
						}?>
					</tbody>

				</table>
				</div>
			</form>
			<?php

		}
		?>

	<?php
	}

	function show_my_cancelled_sessions_no_edit($user_id)
	{
		global $wpdb, $current_user, $xoouserultra;


		//$sesns = $wpdb->get_results( 'SELECT `ID`, `post_author`, `post_date`, `post_title`, `post_content` FROM ' . $wpdb->prefix . 'posts WHERE `post_author` = "' . $user_id. '" ORDER BY `post_date` DESC' );


	    $querystr = "SELECT $wpdb->posts.*
			    FROM $wpdb->posts, $wpdb->postmeta
			    WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id
			    AND $wpdb->postmeta.meta_key = 'session_status'
			    AND $wpdb->postmeta.meta_value = 'cancelled'
			    AND $wpdb->posts.post_type = 'session'
			    AND $wpdb->posts.post_status = 'publish'
			    AND $wpdb->posts.post_author = $user_id
			    ORDER BY $wpdb->posts.post_modified DESC";

		$sesns = $wpdb->get_results($querystr, OBJECT);






		// echo '<div class="tablenav_post">

  //               <p><a class="uultra-btn-commm" href="?module=posts&act=add" title="'. __('Add New Session','xoousers').'" ><span><i class="fa fa-plus  fa-lg"></i></span> '.__('Add New Session','xoousers').' </a></p>

		// 		</div>';

		if ( !empty( $status ) )
		{
			echo '<div id="message" class="updated fade"><p>', $status, '</p></div>';
		}
		if ( empty( $sesns ) )
		{
			echo '<p>', __( 'You have no sessions.', 'xoousers' ), '</p>';
		}
		else
		{
			$n = count( $sesns );


			?>

			<form action="" method="get">
				<?php wp_nonce_field( 'usersultra-bulk-action_inbox' ); ?>
				<input type="hidden" name="page" value="usersultra_inbox" />


				<div class="table-responsive">
				<table class="table widefat fixed" id="table-3" cellspacing="0">
					<thead>
					<tr>
						<th class="manage-column" ><?php _e( 'ID', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Type', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Duration', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Date & Info', 'xoousers' ); ?></th>
						<th class="manage-column" style="width: 30%;" ><?php _e( 'Notes', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Student', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Cancelled', 'xoousers' ); ?></th>
					</tr>
					</thead>
					<tbody>
						<?php

						foreach ( $sesns as $sesn )
						{

        						$session_student_id = get_post_meta( $sesn->ID, 'session_student', true );
       							$session_student_name = $wpdb->get_var( "SELECT display_name FROM $wpdb->users WHERE ID = '$session_student_id'" );
       							$session_student_link = $wpdb->get_var( "SELECT user_nicename FROM $wpdb->users WHERE ID = '$session_student_id'" );
							?>
						<tr class="danger">

                            <td><?php echo $sesn->ID; ?></td>
							<td><?php echo get_post_meta( $sesn->ID, 'session_type', true ); ?></td>
							<td><?php echo get_post_meta( $sesn->ID, 'session_duration', true ); ?></td>
                            <td><?php echo get_post_meta( $sesn->ID, 'session_date', true ); ?></td>
							<td><?php echo $sesn->post_content; ?></td>
							<td><?php echo '<a href="'.get_site_url().'/profile/'.$session_student_link.'">'.$session_student_name.'</a>' ?></td>
							<td><?php echo $sesn->post_modified; ?></td>

						</tr>
							<?php
						}?>
					</tbody>

				</table>
				</div>
			</form>
			<?php

		}
		?>

	<?php
	}


	// Sessions the stundent sees in the dashboard
	function show_my_pending_sessions_student()
	{
		global $wpdb, $current_user, $xoouserultra;

		$user_id = get_current_user_id();



		//$sesns = $wpdb->get_results( 'SELECT `ID`, `post_author`, `post_date`, `post_title`, `post_content` FROM ' . $wpdb->prefix . 'posts WHERE `post_author` = "' . $user_id. '" ORDER BY `post_date` DESC' );


	    $query = " SELECT $wpdb->posts.*  FROM $wpdb->posts
				INNER JOIN $wpdb->postmeta m1
				  ON ( $wpdb->posts.ID = m1.post_id )
				INNER JOIN $wpdb->postmeta m2
				  ON ( $wpdb->posts.ID = m2.post_id )
				WHERE
				$wpdb->posts.post_type = 'session'
				AND $wpdb->posts.post_status = 'publish'
				AND ( m1.meta_key = 'session_student' AND m1.meta_value = $user_id ) AND ( m2.meta_key = 'session_status' AND m2.meta_value = 'pending' ) GROUP BY $wpdb->posts.ID
				ORDER BY $wpdb->posts.post_modified	DESC;
				";

		$sesns = $wpdb->get_results($query, OBJECT);






		// echo '<div class="tablenav_post">

  //               <p><a class="uultra-btn-commm" href="?module=posts&act=add" title="'. __('Add New Session','xoousers').'" ><span><i class="fa fa-plus  fa-lg"></i></span> '.__('Add New Session','xoousers').' </a></p>

		// 		</div>';

		if ( !empty( $status ) )
		{
			echo '<div id="message" class="updated fade"><p>', $status, '</p></div>';
		}
		if ( empty( $sesns ) )
		{
			echo '<p>', __( 'You have no sessions.', 'xoousers' ), '</p>';
		}
		else
		{
			$n = count( $sesns );


			?>

			<form action="" method="get">
				<?php wp_nonce_field( 'usersultra-bulk-action_inbox' ); ?>
				<input type="hidden" name="page" value="usersultra_inbox" />


				<div class="table-responsive">
				<table class="table widefat fixed" id="table-3" cellspacing="0">
					<thead>
					<tr>
						<th class="manage-column" ><?php _e( 'ID', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Type', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Duration', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Date & Info', 'xoousers' ); ?></th>
						<th class="manage-column" style="width: 30%;" ><?php _e( 'Notes', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Tutor', 'xoousers' ); ?></th>
					</tr>
					</thead>
					<tbody>
						<?php

						foreach ( $sesns as $sesn )
						{

        						$session_tutor_id = $wpdb->get_var( "SELECT post_author FROM $wpdb->posts WHERE ID = '$sesn->ID'" );
       							$session_tutor_name = $wpdb->get_var( "SELECT display_name FROM $wpdb->users WHERE ID = '$session_tutor_id'" );
       							$session_tutor_link = $wpdb->get_var( "SELECT user_nicename FROM $wpdb->users WHERE ID = '$session_tutor_id'" );
							?>
						<tr>

                            <td><?php echo $sesn->ID; ?></td>
							<td><?php echo get_post_meta( $sesn->ID, 'session_type', true ); ?></td>
							<td><?php echo get_post_meta( $sesn->ID, 'session_duration', true ); ?></td>
                            <td><?php echo get_post_meta( $sesn->ID, 'session_date', true ); ?></td>
							<td><?php echo $sesn->post_content; ?></td>
							<td><?php echo '<a href="'.get_site_url().'/profile/'.$session_tutor_link.'">'.$session_tutor_name.'</a>' ?></td>

						</tr>
							<?php
						}?>
					</tbody>

				</table>
				</div>
			</form>
			<?php

		}
		?>

	<?php
	}

	function show_my_confirmed_sessions_student()
	{
		global $wpdb, $current_user, $xoouserultra;

		$user_id = get_current_user_id();


	    $query = " SELECT $wpdb->posts.*  FROM $wpdb->posts
				INNER JOIN $wpdb->postmeta m1
				  ON ( $wpdb->posts.ID = m1.post_id )
				INNER JOIN $wpdb->postmeta m2
				  ON ( $wpdb->posts.ID = m2.post_id )
				WHERE
				$wpdb->posts.post_type = 'session'
				AND $wpdb->posts.post_status = 'publish'
				AND ( m1.meta_key = 'session_student' AND m1.meta_value = $user_id ) AND ( m2.meta_key = 'session_status' AND m2.meta_value = 'ok' ) GROUP BY $wpdb->posts.ID
				ORDER BY $wpdb->posts.post_modified
				DESC;
				";

		$sesns = $wpdb->get_results($query, OBJECT);






		// echo '<div class="tablenav_post">

  //               <p><a class="uultra-btn-commm" href="?module=posts&act=add" title="'. __('Add New Session','xoousers').'" ><span><i class="fa fa-plus  fa-lg"></i></span> '.__('Add New Session','xoousers').' </a></p>

		// 		</div>';

		if ( !empty( $status ) )
		{
			echo '<div id="message" class="updated fade"><p>', $status, '</p></div>';
		}
		if ( empty( $sesns ) )
		{
			echo '<p>', __( 'You have no sessions.', 'xoousers' ), '</p>';
		}
		else
		{
			$n = count( $sesns );


			?>

			<form action="" method="get">
				<?php wp_nonce_field( 'usersultra-bulk-action_inbox' ); ?>
				<input type="hidden" name="page" value="usersultra_inbox" />


				<div class="table-responsive">
				<table class="table widefat fixed" id="table-3" cellspacing="0">
					<thead>
					<tr>
						<th class="manage-column" ><?php _e( 'ID', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Type', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Duration', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Date & Info', 'xoousers' ); ?></th>
						<th class="manage-column" style="width: 30%;" ><?php _e( 'Notes', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Tutor', 'xoousers' ); ?></th>
					</tr>
					</thead>
					<tbody>
						<?php

						foreach ( $sesns as $sesn )
						{

        						$session_tutor_id = $wpdb->get_var( "SELECT post_author FROM $wpdb->posts WHERE ID = '$sesn->ID'" );
       							$session_tutor_name = $wpdb->get_var( "SELECT display_name FROM $wpdb->users WHERE ID = '$session_tutor_id'" );
       							$session_tutor_link = $wpdb->get_var( "SELECT user_nicename FROM $wpdb->users WHERE ID = '$session_tutor_id'" );
							?>
						<tr class="success">

                            <td><?php echo $sesn->ID; ?></td>
							<td><?php echo get_post_meta( $sesn->ID, 'session_type', true ); ?></td>
							<td><?php echo get_post_meta( $sesn->ID, 'session_duration', true ); ?></td>
                            <td><?php echo get_post_meta( $sesn->ID, 'session_date', true ); ?></td>
							<td><?php echo $sesn->post_content; ?></td>
							<td><?php echo '<a href="'.get_site_url().'/profile/'.$session_tutor_link.'">'.$session_tutor_name.'</a>' ?></td>

						</tr>
							<?php
						}?>
					</tbody>

				</table>
				</div>
			</form>
			<?php

		}
		?>

	<?php
	}

	function show_my_cancelled_sessions_student()
	{
		global $wpdb, $current_user, $xoouserultra;

		$user_id = get_current_user_id();


	    $query = " SELECT $wpdb->posts.*  FROM $wpdb->posts
				INNER JOIN $wpdb->postmeta m1
				  ON ( $wpdb->posts.ID = m1.post_id )
				INNER JOIN $wpdb->postmeta m2
				  ON ( $wpdb->posts.ID = m2.post_id )
				WHERE
				$wpdb->posts.post_type = 'session'
				AND $wpdb->posts.post_status = 'publish'
				AND ( m1.meta_key = 'session_student' AND m1.meta_value = $user_id ) AND ( m2.meta_key = 'session_status' AND m2.meta_value = 'cancelled' ) GROUP BY $wpdb->posts.ID
				ORDER BY $wpdb->posts.post_modified
				DESC;
				";

		$sesns = $wpdb->get_results($query, OBJECT);






		// echo '<div class="tablenav_post">

  //               <p><a class="uultra-btn-commm" href="?module=posts&act=add" title="'. __('Add New Session','xoousers').'" ><span><i class="fa fa-plus  fa-lg"></i></span> '.__('Add New Session','xoousers').' </a></p>

		// 		</div>';

		if ( !empty( $status ) )
		{
			echo '<div id="message" class="updated fade"><p>', $status, '</p></div>';
		}
		if ( empty( $sesns ) )
		{
			echo '<p>', __( 'You have no sessions.', 'xoousers' ), '</p>';
		}
		else
		{
			$n = count( $sesns );


			?>

			<form action="" method="get">
				<?php wp_nonce_field( 'usersultra-bulk-action_inbox' ); ?>
				<input type="hidden" name="page" value="usersultra_inbox" />


				<div class="table-responsive">
				<table class="table widefat fixed" id="table-3" cellspacing="0">
					<thead>
					<tr>
						<th class="manage-column" ><?php _e( 'ID', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Type', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Duration', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Date & Info', 'xoousers' ); ?></th>
						<th class="manage-column" style="width: 30%;" ><?php _e( 'Notes', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Tutor', 'xoousers' ); ?></th>
					</tr>
					</thead>
					<tbody>
						<?php

						foreach ( $sesns as $sesn )
						{

        						$session_tutor_id = $wpdb->get_var( "SELECT post_author FROM $wpdb->posts WHERE ID = '$sesn->ID'" );
       							$session_tutor_name = $wpdb->get_var( "SELECT display_name FROM $wpdb->users WHERE ID = '$session_tutor_id'" );
       							$session_tutor_link = $wpdb->get_var( "SELECT user_nicename FROM $wpdb->users WHERE ID = '$session_tutor_id'" );
							?>
						<tr class="danger">

                            <td><?php echo $sesn->ID; ?></td>
							<td><?php echo get_post_meta( $sesn->ID, 'session_type', true ); ?></td>
							<td><?php echo get_post_meta( $sesn->ID, 'session_duration', true ); ?></td>
                            <td><?php echo get_post_meta( $sesn->ID, 'session_date', true ); ?></td>
							<td><?php echo $sesn->post_content; ?></td>
							<td><?php echo '<a href="'.get_site_url().'/profile/'.$session_tutor_link.'">'.$session_tutor_name.'</a>' ?></td>

						</tr>
							<?php
						}?>
					</tbody>

				</table>
				</div>
			</form>
			<?php

		}
		?>

	<?php
	}

	// ADMIN ON STUDENT PROFILE

	function show_student_admin_pending_sessions_no_edit($user_id)
	{
		global $wpdb, $current_user, $xoouserultra;


		//$sesns = $wpdb->get_results( 'SELECT `ID`, `post_author`, `post_date`, `post_title`, `post_content` FROM ' . $wpdb->prefix . 'posts WHERE `post_author` = "' . $user_id. '" ORDER BY `post_date` DESC' );


	    $query = " SELECT $wpdb->posts.*  FROM $wpdb->posts
				INNER JOIN $wpdb->postmeta m1
				  ON ( $wpdb->posts.ID = m1.post_id )
				INNER JOIN $wpdb->postmeta m2
				  ON ( $wpdb->posts.ID = m2.post_id )
				WHERE
				$wpdb->posts.post_type = 'session'
				AND $wpdb->posts.post_status = 'publish'
				AND ( m1.meta_key = 'session_student' AND m1.meta_value = $user_id ) AND ( m2.meta_key = 'session_status' AND m2.meta_value = 'pending' ) GROUP BY $wpdb->posts.ID
				ORDER BY $wpdb->posts.post_modified	DESC;
				";

		$sesns = $wpdb->get_results($query, OBJECT);


		if ( !empty( $status ) )
		{
			echo '<div id="message" class="updated fade"><p>', $status, '</p></div>';
		}
		if ( empty( $sesns ) )
		{
			echo '<p>', __( 'You have no sessions.', 'xoousers' ), '</p>';
		}
		else
		{
			$n = count( $sesns );


			?>

			<form action="" method="get">
				<?php wp_nonce_field( 'usersultra-bulk-action_inbox' ); ?>
				<input type="hidden" name="page" value="usersultra_inbox" />


				<div class="table-responsive">
				<table class="table widefat fixed" id="table-3" cellspacing="0">
					<thead>
					<tr>
						<th class="manage-column" ><?php _e( 'ID', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Type', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Duration', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Date & Info', 'xoousers' ); ?></th>
						<th class="manage-column" style="width: 30%;" ><?php _e( 'Notes', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Tutor', 'xoousers' ); ?></th>
					</tr>
					</thead>
					<tbody>
						<?php

						foreach ( $sesns as $sesn )
						{

        						$session_tutor_id = $wpdb->get_var( "SELECT post_author FROM $wpdb->posts WHERE ID = '$sesn->ID'" );
       							$session_tutor_name = $wpdb->get_var( "SELECT display_name FROM $wpdb->users WHERE ID = '$session_tutor_id'" );
       							$session_tutor_link = $wpdb->get_var( "SELECT user_nicename FROM $wpdb->users WHERE ID = '$session_tutor_id'" );
							?>
						<tr>

                            <td><?php echo $sesn->ID; ?></td>
							<td><?php echo get_post_meta( $sesn->ID, 'session_type', true ); ?></td>
							<td><?php echo get_post_meta( $sesn->ID, 'session_duration', true ); ?></td>
                            <td><?php echo get_post_meta( $sesn->ID, 'session_date', true ); ?></td>
							<td><?php echo $sesn->post_content; ?></td>
							<td><?php echo '<a href="'.get_site_url().'/profile/'.$session_tutor_link.'">'.$session_tutor_name.'</a>' ?></td>

						</tr>
							<?php
						}?>
					</tbody>

				</table>
				</div>
			</form>
			<?php

		}
		?>

	<?php
	}

	function show_student_admin_confirmed_sessions_no_edit($user_id)
	{
		global $wpdb, $current_user, $xoouserultra;


		//$sesns = $wpdb->get_results( 'SELECT `ID`, `post_author`, `post_date`, `post_title`, `post_content` FROM ' . $wpdb->prefix . 'posts WHERE `post_author` = "' . $user_id. '" ORDER BY `post_date` DESC' );


	    $query = " SELECT $wpdb->posts.*  FROM $wpdb->posts
				INNER JOIN $wpdb->postmeta m1
				  ON ( $wpdb->posts.ID = m1.post_id )
				INNER JOIN $wpdb->postmeta m2
				  ON ( $wpdb->posts.ID = m2.post_id )
				WHERE
				$wpdb->posts.post_type = 'session'
				AND $wpdb->posts.post_status = 'publish'
				AND ( m1.meta_key = 'session_student' AND m1.meta_value = $user_id ) AND ( m2.meta_key = 'session_status' AND m2.meta_value = 'ok' ) GROUP BY $wpdb->posts.ID
				ORDER BY $wpdb->posts.post_modified	DESC;
				";

		$sesns = $wpdb->get_results($query, OBJECT);


		if ( !empty( $status ) )
		{
			echo '<div id="message" class="updated fade"><p>', $status, '</p></div>';
		}
		if ( empty( $sesns ) )
		{
			echo '<p>', __( 'You have no sessions.', 'xoousers' ), '</p>';
		}
		else
		{
			$n = count( $sesns );


			?>

			<form action="" method="get">
				<?php wp_nonce_field( 'usersultra-bulk-action_inbox' ); ?>
				<input type="hidden" name="page" value="usersultra_inbox" />


				<div class="table-responsive">
				<table class="table widefat fixed" id="table-3" cellspacing="0">
					<thead>
					<tr>
						<th class="manage-column" ><?php _e( 'ID', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Type', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Duration', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Date & Info', 'xoousers' ); ?></th>
						<th class="manage-column" style="width: 30%;" ><?php _e( 'Notes', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Tutor', 'xoousers' ); ?></th>
					</tr>
					</thead>
					<tbody>
						<?php

						foreach ( $sesns as $sesn )
						{

        						$session_tutor_id = $wpdb->get_var( "SELECT post_author FROM $wpdb->posts WHERE ID = '$sesn->ID'" );
       							$session_tutor_name = $wpdb->get_var( "SELECT display_name FROM $wpdb->users WHERE ID = '$session_tutor_id'" );
       							$session_tutor_link = $wpdb->get_var( "SELECT user_nicename FROM $wpdb->users WHERE ID = '$session_tutor_id'" );
							?>
						<tr>

                            <td><?php echo $sesn->ID; ?></td>
							<td><?php echo get_post_meta( $sesn->ID, 'session_type', true ); ?></td>
							<td><?php echo get_post_meta( $sesn->ID, 'session_duration', true ); ?></td>
                            <td><?php echo get_post_meta( $sesn->ID, 'session_date', true ); ?></td>
							<td><?php echo $sesn->post_content; ?></td>
							<td><?php echo '<a href="'.get_site_url().'/profile/'.$session_tutor_link.'">'.$session_tutor_name.'</a>' ?></td>

						</tr>
							<?php
						}?>
					</tbody>

				</table>
				</div>
			</form>
			<?php

		}
		?>

	<?php
	}

	function show_student_admin_cancelled_sessions_no_edit($user_id)
	{
		global $wpdb, $current_user, $xoouserultra;


		//$sesns = $wpdb->get_results( 'SELECT `ID`, `post_author`, `post_date`, `post_title`, `post_content` FROM ' . $wpdb->prefix . 'posts WHERE `post_author` = "' . $user_id. '" ORDER BY `post_date` DESC' );


	    $query = " SELECT $wpdb->posts.*  FROM $wpdb->posts
				INNER JOIN $wpdb->postmeta m1
				  ON ( $wpdb->posts.ID = m1.post_id )
				INNER JOIN $wpdb->postmeta m2
				  ON ( $wpdb->posts.ID = m2.post_id )
				WHERE
				$wpdb->posts.post_type = 'session'
				AND $wpdb->posts.post_status = 'publish'
				AND ( m1.meta_key = 'session_student' AND m1.meta_value = $user_id ) AND ( m2.meta_key = 'session_status' AND m2.meta_value = 'cancelled' ) GROUP BY $wpdb->posts.ID
				ORDER BY $wpdb->posts.post_modified	DESC;
				";

		$sesns = $wpdb->get_results($query, OBJECT);


		if ( !empty( $status ) )
		{
			echo '<div id="message" class="updated fade"><p>', $status, '</p></div>';
		}
		if ( empty( $sesns ) )
		{
			echo '<p>', __( 'You have no sessions.', 'xoousers' ), '</p>';
		}
		else
		{
			$n = count( $sesns );


			?>

			<form action="" method="get">
				<?php wp_nonce_field( 'usersultra-bulk-action_inbox' ); ?>
				<input type="hidden" name="page" value="usersultra_inbox" />


				<div class="table-responsive">
				<table class="table widefat fixed" id="table-3" cellspacing="0">
					<thead>
					<tr>
						<th class="manage-column" ><?php _e( 'ID', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Type', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Duration', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Date & Info', 'xoousers' ); ?></th>
						<th class="manage-column" style="width: 30%;" ><?php _e( 'Notes', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Tutor', 'xoousers' ); ?></th>
					</tr>
					</thead>
					<tbody>
						<?php

						foreach ( $sesns as $sesn )
						{

        						$session_tutor_id = $wpdb->get_var( "SELECT post_author FROM $wpdb->posts WHERE ID = '$sesn->ID'" );
       							$session_tutor_name = $wpdb->get_var( "SELECT display_name FROM $wpdb->users WHERE ID = '$session_tutor_id'" );
       							$session_tutor_link = $wpdb->get_var( "SELECT user_nicename FROM $wpdb->users WHERE ID = '$session_tutor_id'" );
							?>
						<tr>

                            <td><?php echo $sesn->ID; ?></td>
							<td><?php echo get_post_meta( $sesn->ID, 'session_type', true ); ?></td>
							<td><?php echo get_post_meta( $sesn->ID, 'session_duration', true ); ?></td>
                            <td><?php echo get_post_meta( $sesn->ID, 'session_date', true ); ?></td>
							<td><?php echo $sesn->post_content; ?></td>
							<td><?php echo '<a href="'.get_site_url().'/profile/'.$session_tutor_link.'">'.$session_tutor_name.'</a>' ?></td>

						</tr>
							<?php
						}?>
					</tbody>

				</table>
				</div>
			</form>
			<?php

		}
		?>

	<?php
	}


	// END ADMIN ON STUDENT PROFILE

	function show_my_students_pending_sessions_edit($user_id)
	{
		global $wpdb, $current_user, $xoouserultra;

		$tutor_id = get_current_user_id();


	    $query = " SELECT $wpdb->posts.*  FROM $wpdb->posts
				INNER JOIN $wpdb->postmeta m1
				  ON ( $wpdb->posts.ID = m1.post_id )
				INNER JOIN $wpdb->postmeta m2
				  ON ( $wpdb->posts.ID = m2.post_id )
				WHERE
				$wpdb->posts.post_type = 'session'
				AND $wpdb->posts.post_author = $tutor_id
				AND $wpdb->posts.post_status = 'publish'
				AND ( m1.meta_key = 'session_student' AND m1.meta_value = $user_id ) AND ( m2.meta_key = 'session_status' AND m2.meta_value = 'pending' ) GROUP BY $wpdb->posts.ID
				ORDER BY $wpdb->posts.post_modified
				DESC;
				";

		$sesns = $wpdb->get_results($query, OBJECT);


		// echo '<div class="tablenav_post">

  //               <p><a class="uultra-btn-commm" href="?module=posts&act=add" title="'. __('Add New Session','xoousers').'" ><span><i class="fa fa-plus  fa-lg"></i></span> '.__('Add New Session','xoousers').' </a></p>

		// 		</div>';

		if ( !empty( $status ) )
		{
			echo '<div id="message" class="updated fade"><p>', $status, '</p></div>';
		}
		if ( empty( $sesns ) )
		{
			echo '<p>', __( 'You have no sessions.', 'xoousers' ), '</p>';
		}
		else
		{
			$n = count( $sesns );


			?>

			<form action="" method="get">
				<?php wp_nonce_field( 'usersultra-bulk-action_inbox' ); ?>
				<input type="hidden" name="page" value="usersultra_inbox" />


				<div class="table-responsive">
				<table class="table widefat fixed" id="table-3" cellspacing="0">
					<thead>
					<tr>
						<th class="manage-column" ><?php _e( 'ID', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Type', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Duration', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Date & Info', 'xoousers' ); ?></th>
						<th class="manage-column" style="width: 30%;" ><?php _e( 'Notes', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( '', 'xoousers' ); ?></th>
					</tr>
					</thead>
					<tbody>
						<?php

						foreach ( $sesns as $sesn )
						{
							$session_tutor_link = $wpdb->get_var( "SELECT user_nicename FROM $wpdb->users WHERE ID = '$tutor_id'" );
							?>
						<tr>

                            <td><?php echo $sesn->ID; ?></td>
							<td><?php echo get_post_meta( $sesn->ID, 'session_type', true ); ?></td>
							<td><?php echo get_post_meta( $sesn->ID, 'session_duration', true ); ?></td>
                            <td><?php echo get_post_meta( $sesn->ID, 'session_date', true ); ?></td>
							<td><?php echo $sesn->post_content; ?></td>
							<td id="rd_session_actions"  class='rd_session_actions'>
	                            <a class="rd-process-session" id="rd-process-session" href="" title="Confirm" item-id="<?php echo $sesn->ID; ?>" action-id="ok"><span><i style="color:green;" class="fa fa-check-circle fa-lg"></i></span></a>
	                            <a class="rd-process-session" id="rd-process-session" href="" title="Cancel" item-id="<?php echo $sesn->ID; ?>" action-id="cancelled"><span><i style="color:red;" class="fa fa-minus-circle fa-lg"></i></span></a>
	                            <a class="rd-process-session" href="<?php echo get_site_url(); ?>/tutor-account/?module=posts&act=edit&post_id=<?php echo $sesn->ID; ?>" title="<?php echo __('Edit','xoousers')?>" ><span><i class="fa fa-pencil-square-o fa-lg"></i></span> </a>
                            </td>

						</tr>
							<?php
						}?>
					</tbody>

				</table>
				</div>
			</form>
			<?php

		}
		?>

	<?php
	}

	function show_my_students_confirmed_sessions_edit($user_id)
	{
		global $wpdb, $current_user, $xoouserultra;

		$tutor_id = get_current_user_id();


	    $query = " SELECT $wpdb->posts.*  FROM $wpdb->posts
				INNER JOIN $wpdb->postmeta m1
				  ON ( $wpdb->posts.ID = m1.post_id )
				INNER JOIN $wpdb->postmeta m2
				  ON ( $wpdb->posts.ID = m2.post_id )
				WHERE
				$wpdb->posts.post_type = 'session'
				AND $wpdb->posts.post_author = $tutor_id
				AND $wpdb->posts.post_status = 'publish'
				AND ( m1.meta_key = 'session_student' AND m1.meta_value = $user_id ) AND ( m2.meta_key = 'session_status' AND m2.meta_value = 'ok' ) GROUP BY $wpdb->posts.ID
				ORDER BY $wpdb->posts.post_modified
				DESC;
				";

		$sesns = $wpdb->get_results($query, OBJECT);


		// echo '<div class="tablenav_post">

  //               <p><a class="uultra-btn-commm" href="?module=posts&act=add" title="'. __('Add New Session','xoousers').'" ><span><i class="fa fa-plus  fa-lg"></i></span> '.__('Add New Session','xoousers').' </a></p>

		// 		</div>';

		if ( !empty( $status ) )
		{
			echo '<div id="message" class="updated fade"><p>', $status, '</p></div>';
		}
		if ( empty( $sesns ) )
		{
			echo '<p>', __( 'You have no sessions.', 'xoousers' ), '</p>';
		}
		else
		{
			$n = count( $sesns );


			?>

			<form action="" method="get">
				<?php wp_nonce_field( 'usersultra-bulk-action_inbox' ); ?>
				<input type="hidden" name="page" value="usersultra_inbox" />


				<div class="table-responsive">
				<table class="table widefat fixed" id="table-3" cellspacing="0">
					<thead>
					<tr>
						<th class="manage-column" ><?php _e( 'ID', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Type', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Duration', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Date & Info', 'xoousers' ); ?></th>
						<th class="manage-column" style="width: 30%;" ><?php _e( 'Notes', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Confirmed', 'xoousers' ); ?></th>
					</tr>
					</thead>
					<tbody>
						<?php

						foreach ( $sesns as $sesn )
						{
							?>
						<tr class="success">

                            <td><?php echo $sesn->ID; ?></td>
							<td><?php echo get_post_meta( $sesn->ID, 'session_type', true ); ?></td>
							<td><?php echo get_post_meta( $sesn->ID, 'session_duration', true ); ?></td>
                            <td><?php echo get_post_meta( $sesn->ID, 'session_date', true ); ?></td>
							<td><?php echo $sesn->post_content; ?></td>
							<td><?php echo $sesn->post_modified; ?></td>

						</tr>
							<?php
						}?>
					</tbody>

				</table>
				</div>
			</form>
			<?php

		}
		?>

	<?php
	}

	function show_my_students_cancelled_sessions_edit($user_id)
	{
		global $wpdb, $current_user, $xoouserultra;

		$tutor_id = get_current_user_id();


	    $query = " SELECT $wpdb->posts.*  FROM $wpdb->posts
				INNER JOIN $wpdb->postmeta m1
				  ON ( $wpdb->posts.ID = m1.post_id )
				INNER JOIN $wpdb->postmeta m2
				  ON ( $wpdb->posts.ID = m2.post_id )
				WHERE
				$wpdb->posts.post_type = 'session'
				AND $wpdb->posts.post_author = $tutor_id
				AND $wpdb->posts.post_status = 'publish'
				AND ( m1.meta_key = 'session_student' AND m1.meta_value = $user_id ) AND ( m2.meta_key = 'session_status' AND m2.meta_value = 'cancelled' ) GROUP BY $wpdb->posts.ID
				ORDER BY $wpdb->posts.post_modified
				DESC;
				";

		$sesns = $wpdb->get_results($query, OBJECT);


		// echo '<div class="tablenav_post">

  //               <p><a class="uultra-btn-commm" href="?module=posts&act=add" title="'. __('Add New Session','xoousers').'" ><span><i class="fa fa-plus  fa-lg"></i></span> '.__('Add New Session','xoousers').' </a></p>

		// 		</div>';

		if ( !empty( $status ) )
		{
			echo '<div id="message" class="updated fade"><p>', $status, '</p></div>';
		}
		if ( empty( $sesns ) )
		{
			echo '<p>', __( 'You have no sessions.', 'xoousers' ), '</p>';
		}
		else
		{
			$n = count( $sesns );


			?>

			<form action="" method="get">
				<?php wp_nonce_field( 'usersultra-bulk-action_inbox' ); ?>
				<input type="hidden" name="page" value="usersultra_inbox" />


				<div class="table-responsive">
				<table class="table widefat fixed" id="table-3" cellspacing="0">
					<thead>
					<tr>
						<th class="manage-column" ><?php _e( 'ID', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Type', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Duration', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Date & Info', 'xoousers' ); ?></th>
						<th class="manage-column" style="width: 30%;" ><?php _e( 'Notes', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Cancelled', 'xoousers' ); ?></th>
					</tr>
					</thead>
					<tbody>
						<?php

						foreach ( $sesns as $sesn )
						{
							?>
						<tr class="danger">

                            <td><?php echo $sesn->ID; ?></td>
							<td><?php echo get_post_meta( $sesn->ID, 'session_type', true ); ?></td>
							<td><?php echo get_post_meta( $sesn->ID, 'session_duration', true ); ?></td>
                            <td><?php echo get_post_meta( $sesn->ID, 'session_date', true ); ?></td>
							<td><?php echo $sesn->post_content; ?></td>
							<td><?php echo $sesn->post_modified; ?></td>

						</tr>
							<?php
						}?>
					</tbody>

				</table>
				</div>
			</form>
			<?php

		}
		?>

	<?php
	}


	function rd_get_my_students_tutors()
	{
		global $wpdb, $current_user, $xoouserultra;

		$user_id = get_current_user_id();

		$sql = ' SELECT * FROM '. $wpdb->prefix . 'usersultra_friends WHERE friend_sender_user_id =  "'.$user_id.'" ORDER BY friend_date DESC ';
		//echo $sql;



		$rows = $wpdb->get_results($sql);

		$html = " ";
		$html .='<div class="tablenav">	';


		$html.='		</div>

				<table class="widefat fixed" id="table-3" cellspacing="0">
					<thead>
					<tr>
                        <th class="manage-column" >'.__( 'Pic', 'xoousers' ).'</th>
						<th class="manage-column">'. __( 'Name', 'xoousers' ).'</th>
						<th class="manage-column" >'. __( 'Date', 'xoousers' ).'</th>
						<th class="manage-column" >'. __( 'Action', 'xoousers' ).'</th>
					</tr>
					</thead>
					<tbody>';



					foreach ( $rows as $msg )
					{
						$friend_id = $msg->friend_receiver_id;
						$add_friend_id = $msg->friend_id;
						$friend_name = $wpdb->get_var( "SELECT display_name FROM $wpdb->users WHERE ID = '$friend_id'" );


					$html .= '<tr>

                             <td>'. $xoouserultra->userpanel->get_user_pic( $friend_id, 75, 'avatar', 'rounded').'</td>

							<td>'.$friend_name.'</td>

							<td>'.$msg->friend_date.'</td>

							<td><a class="uultra-btn-denyred" id="uu-approvedeny-friend" href="" title="'.__('Remove','xoousers').'" item-id="'.$add_friend_id.'" action-id="deny"><span><i class="fa fa fa-times fa-lg"></i></span> Remove </a></td>
						</tr>';


						}



					$html .='</tbody>

				</table>';

				echo  $html;

	}

	function rd_get_my_tutors_students()
	{
		global $wpdb, $current_user, $xoouserultra;

		$user_id = get_current_user_id();

		$sql = ' SELECT * FROM '. $wpdb->prefix . 'usersultra_friends WHERE friend_receiver_id =  "'.$user_id.'" ORDER BY friend_date DESC ';
		//echo $sql;



		$rows = $wpdb->get_results($sql);

		$html = " ";
		$html .='<div class="tablenav">	';


		$html.='		</div>

				<table class="widefat fixed" id="table-3" cellspacing="0">
					<thead>
					<tr>
                        <th class="manage-column" >'.__( 'Pic', 'xoousers' ).'</th>
						<th class="manage-column">'. __( 'Name', 'xoousers' ).'</th>
					</tr>
					</thead>
					<tbody>';



					foreach ( $rows as $msg )
					{
						$friend_id = $msg->friend_sender_user_id;
						$add_friend_id = $msg->friend_id;
						$friend_name = $wpdb->get_var( "SELECT display_name FROM $wpdb->users WHERE ID = '$friend_id'" );


					$html .= '<tr>

                             <td>'. $xoouserultra->userpanel->get_user_pic( $friend_id, 75, 'avatar', 'rounded').'</td>

							<td>'.$friend_name.'</td>


						</tr>';


						}



					$html .='</tbody>

				</table>';

				echo  $html;

	}

	public function rd_pay_tutor()
	{

		global $wpdb,  $xoouserultra;
		require_once(ABSPATH . 'wp-includes/formatting.php');

		$logged_user_id = get_current_user_id();

		$receiver_id =  sanitize_text_field($_POST["receiver_id"]);
		$rd_paid_credits =   sanitize_text_field($_POST["rd_paid_credits"]);

		//get receiver

		$receiver = get_user_by('id',$receiver_id);
		$sender = get_user_by('id',$logged_user_id);

		//store in the db

		if($receiver->ID >0)
		{
			date_default_timezone_set('Europe/Copenhagen');
			$new_tutor_pay = array(
						'id'        => NULL,
						'uid'   => $receiver_id,
						'credits'   => $rd_paid_credits,
						'created'=> date('Y-m-d H:i:s'),
					);

					// insert into database
					$wpdb->insert( $wpdb->prefix . 'rd_tutor_paid_credits', $new_tutor_pay, array( '%d', '%d', '%d', '%s'));
					$res = $wpdb->get_var( 'SELECT credit FROM '.$wpdb->prefix . 'woocredit_users WHERE user_id = "'.$receiver_id.'"' );
					if( $rd_paid_credits <= $res)
					{
						$wpdb->query('UPDATE '.$wpdb->prefix . 'woocredit_users set credit = "'.( $res - $rd_paid_credits ). '" WHERE user_id = "'.$receiver_id.'"' );
					}

		}


		echo "<div class='uupublic-ultra-success'>".__(" Payment Registered ", 'xoousers')."</div>";
		die();



	}

	//Add session

	public function rd_add_session_payment()
	{

		global $wpdb,  $xoouserultra;
		require_once(ABSPATH . 'wp-includes/formatting.php');

		$logged_user_id = get_current_user_id();

		$student_id =  sanitize_text_field($_POST["student_id"]);
		$session_type =  sanitize_text_field($_POST["session_type"]);
		$session_date =  sanitize_text_field($_POST["session_date"]);
		$session_notes =  sanitize_text_field($_POST["session_notes"]);
		$session_duration =  sanitize_text_field($_POST["session_duration"]);
		$session_status =  sanitize_text_field($_POST["session_status"]);
		$rd_session_price =   sanitize_text_field($_POST["rd_session_price"]);


		//get receiver

		$receiver = get_user_by('id',$student_id);
		$sender = get_user_by('id',$logged_user_id);


		//store in the db

		if($receiver->ID > 0)
		{


			$my_session = array(
			  'post_title'    => 'Session '.date("Y-m-d h:i:s"),
			  'post_type'     => 'session',
			  'post_content'  => $session_notes,
			  'post_status'   => 'publish',
			  'post_author'   => $sender->ID
			);

			// Insert the post into the database
			$post_id = wp_insert_post( $my_session, $wp_error );

			add_post_meta($post_id, 'session_student', $student_id);
			add_post_meta($post_id, 'session_type', $session_type);
			add_post_meta($post_id, 'session_date', $session_date);
			add_post_meta($post_id, 'session_duration', $session_duration);
			add_post_meta($post_id, 'session_status', $session_status);
			add_post_meta($post_id, 'session_credits', $rd_session_price);


			$res = $wpdb->get_var( 'SELECT credit FROM '.$wpdb->prefix . 'woocredit_users WHERE user_id = "'.$student_id.'"' );
			if( $rd_session_price <= $res)
			{
			    /* RD Custom */
    			$rd_credited_user = get_user_by( 'id', $student_id );

    			date_default_timezone_set('Europe/Copenhagen');
    			$new_credit_deduct = array(
    					'id'        => NULL,
    					'uid'   => $rd_credited_user->ID,
    					'name'   => $rd_credited_user->display_name,
    					'credits'   => $rd_session_price,
    					'action'   => 'session taken',
    					'date'=> date('Y-m-d H:i:s'),
    				);

    			$wpdb->insert( $wpdb->prefix . 'rd_purchased_credits', $new_credit_deduct, array( '%d', '%d', '%s', '%d' , '%s', '%s'));
    			/* End RD Custom */

				$wpdb->query('UPDATE '.$wpdb->prefix . 'woocredit_users set credit = "'.( $res - $rd_session_price ). '" WHERE user_id = "'.$student_id.'"' );
			}

		}


		echo "<div class='uupublic-ultra-success'>".__(" Session Added ", 'xoousers')."</div>";
		die();



	}

	public function rd_update_session_payment()
	{

		global $wpdb,  $xoouserultra;
		require_once(ABSPATH . 'wp-includes/formatting.php');

		$session_id = sanitize_text_field($_POST["session_id"]);
		$student_id = sanitize_text_field($_POST["student_id"]);
		$session_type =  sanitize_text_field($_POST["session_type"]);
		$session_date =  sanitize_text_field($_POST["session_date"]);
		$session_notes =  sanitize_text_field($_POST["session_notes"]);
		$session_duration =  sanitize_text_field($_POST["session_duration"]);
		$rd_session_price =   sanitize_text_field($_POST["rd_session_price"]);


		//store in the db

		$my_session = array(
			  'ID' => $session_id,
			  'post_content'  => $session_notes,
			  'post_modified' => date('m/d/Y h:i:s a', time())
			);

			// Insert the post into the database
			wp_update_post($my_session, $wp_error );
			$prev_credits = get_post_meta( $session_id, 'session_credits', true );

			update_post_meta($session_id, 'session_type', $session_type);
			update_post_meta($session_id, 'session_date', $session_date);
			update_post_meta($session_id, 'session_duration', $session_duration);
			update_post_meta($session_id, 'session_credits', $rd_session_price);

			$res = $wpdb->get_var( 'SELECT credit FROM '.$wpdb->prefix . 'woocredit_users WHERE user_id = "'.$student_id.'"' );
			// restore previous credits to student
			$previous_student_credits = $res + $prev_credits;
			$wpdb->query('UPDATE '.$wpdb->prefix . 'woocredit_users set credit = "'.$previous_student_credits. '" WHERE user_id = "'.$student_id.'"' );

			if( $rd_session_price <= $previous_student_credits)
			{
				$wpdb->query('UPDATE '.$wpdb->prefix . 'woocredit_users set credit = "'.( $previous_student_credits - $rd_session_price ). '" WHERE user_id = "'.$student_id.'"' );
				echo "<div class='uupublic-ultra-success'>".__(" Session Updated ", 'xoousers')."</div>";
			}
			else {
				echo "<div class='uupublic-ultra-warning'>".__(" Warning. Student does not have enough credits ", 'xoousers')."</div>";
			}
		die();




	}

	public function rd_process_session_status()
	{
		global $wpdb,  $xoouserultra;
		require_once(ABSPATH . 'wp-includes/formatting.php');



		if(isset($_POST["item_id"]))
		{
			$item_id = $_POST["item_id"];
		}


		if(isset($_POST["item_action"]))
		{
			$item_action = $_POST["item_action"];
		}

		$session_author_id = $wpdb->get_var( "SELECT post_author FROM $wpdb->posts WHERE ID = '$item_id'" );
		$session_author_cur_credits = $wpdb->get_var( "SELECT credit FROM $wpdb->prefix".'woocredit_users'." WHERE user_id = '$session_author_id'" );

		$session_student_id = get_post_meta( $item_id, 'session_student', true );
		$session_student_cur_credits = $wpdb->get_var( "SELECT credit FROM $wpdb->prefix".'woocredit_users'." WHERE user_id = '$session_student_id'" );

		$session_price = get_post_meta( $item_id, 'session_credits', true );

		$session_tutor_session_earnings = $session_price / 2;
		$session_tutor_total_earnings = $session_author_cur_credits + $session_tutor_session_earnings;

		$session_student_return = $session_student_cur_credits + $session_price;

		if($item_action=='ok')
		{

			// $sql = "UPDATE ".$wpdb->prefix . "woocredit_users SET `credit` = ".$session_tutor_total_earnings." WHERE `user_id` = ".$session_author_id;

			$sql = "INSERT INTO ".$wpdb->prefix . "woocredit_users (user_id, credit) VALUES(".$session_author_id.", ".$session_tutor_total_earnings.") ON DUPLICATE KEY UPDATE user_id=VALUES(user_id), credit=VALUES(credit)";

			$wpdb->query($sql);

			update_post_meta($item_id, 'session_status', $item_action , 'pending');

			$message = __('Session confirmed','xoousers');
		}

		if($item_action=='cancelled')
		{
			$sql = "UPDATE ".$wpdb->prefix . "woocredit_users SET `credit` = ".$session_student_return." WHERE `user_id` = ".$session_student_id;

			$wpdb->query($sql);


			update_post_meta($item_id, 'session_status', $item_action , 'pending');

			$message = __('Session cancelled','xoousers');
		}


		date_default_timezone_set('Europe/Copenhagen');
		$update_session_date = array(
		      'ID'           => $item_id,
		      'post_modified' => date('m/d/Y h:i:s a', time())
		  );

		wp_update_post($update_session_date);

		echo $message;
		die();

	}

	public function rd_process_session_status_q()
	{
		global $wpdb,  $xoouserultra , $rd_tutor_pay_ratio;
		require_once(ABSPATH . 'wp-includes/formatting.php');



		if(isset($_POST["item_id"]))
		{
			$item_id = $_POST["item_id"];
		}


		if(isset($_POST["item_action"]))
		{
			$item_action = $_POST["item_action"];
		}

		$session_author_id = $wpdb->get_var( "SELECT post_author FROM $wpdb->users WHERE ID = '$item_id'" );
		$session_author_cur_credits = $wpdb->get_var( "SELECT credit FROM $wpdb->prefix".'woocredit_users'." WHERE user_id = '$session_author_id'" );

		$session_student_id = get_post_meta( $item_id, 'session_student', true );
		$session_student_cur_credits = $wpdb->get_var( "SELECT credit FROM $wpdb->prefix".'woocredit_users'." WHERE user_id = '$session_student_id'" );

		$session_price = get_post_meta( $item_id, 'session_credits', true );

		$session_tutor_session_earnings = $session_price * $rd_tutor_pay_ratio;
		$session_tutor_total_earnings = $session_author_cur_credits + $session_tutor_session_earnings;

		$session_student_return = $session_student_cur_credits + $session_price;


		if($item_action=='ok')
		{

			$wpdb->query('UPDATE '.$wpdb->prefix . 'woocredit_users SET credit = "'.$session_tutor_total_earnings. '" WHERE user_id = "'.$item_id.'"' );

			add_post_meta($item_id, 'session_status', $item_action);

			$message = __('Session confirmed','xoousers');
		}

		if($item_action=='cancelled')
		{
			$wpdb->query('UPDATE '.$wpdb->prefix . 'woocredit_users SET credit = "'.$session_student_return. '" WHERE user_id = "'.$item_id.'"' );

			add_post_meta($item_id, 'session_status', $item_action);

			$message = __('Session cancelled','xoousers');
		}


	}

	function get_user_pic( $id, $size, $pic_type=NULL, $pic_boder_type= NULL, $size_type=NULL )
	{

		 global  $xoouserultra;

		 require_once(ABSPATH . 'wp-includes/link-template.php');
		$site_url = site_url()."/";

		$avatar = "";
		$pic_size = "";

		$upload_folder = $xoouserultra->get_option('media_uploading_folder');
		$path = $site_url.$upload_folder."/".$id."/";
		$author_pic = get_the_author_meta('user_pic', $id);

		//get user url
		$user_url= $xoouserultra->userpanel->get_user_profile_permalink($id);

		if($size_type=="fixed" || $size_type=="")
		{
			$dimension = "width:";
			$dimension_2 = "height:";
		}

		if($size_type=="dynamic" )
		{
			$dimension = "max-width:";

		}

		if($size!="")
		{
			$pic_size = $dimension.$size."px".";".$dimension_2.$size."px";

		}



		if($pic_type=='avatar')
		{

			if ($author_pic  != '')
			{
				$avatar_pic = $path.$author_pic;
				$avatar= '<a href="'.$user_url.'">'. '<img src="'.$avatar_pic.'" class="'.$pic_boder_type.'" style="'.$pic_size.' "   id="uultra-avatar-img-'.$id.'" /></a>';

			} else {

				$avatar= '<a href="'.$user_url.'">'. get_avatar($id,$size) .'</a>';



			}

		}elseif($pic_type=='mainpicture'){

				//get user's main picture - medium size will be used to be displayed

			    $avatar_pic = $path.$author_pic;
				$avatar= '<a href="'.$user_url.'">'. '<img src="'.$avatar_pic.'" class="'.$pic_boder_type.'" style="'.$pic_size.' "   id="uultra-avatar-img-'.$id.'"/></a>';


		}

		return $avatar;
	}

		function show_my_latest_courses($howmany, $status=null)
		{
		global $wpdb, $current_user, $xoouserultra, $woocommerce;

		require_once(ABSPATH . 'wp-includes/pluggable.php');
		require_once(ABSPATH. 'wp-admin/includes/user.php' );
		require_once(ABSPATH.  'wp-includes/query.php' );


		$user_id = get_current_user_id();

		$args = array(
         'numberposts' => -1,
         'meta_key' => '_customer_user',
         'meta_value' => $user_id,
         'post_type' => 'shop_order',
         'post_status' => 'publish',
         'tax_query'=>array(
                     array(
                     'taxonomy' =>'shop_order_status',
                     'field' => 'slug',
					 'terms' => array('processing','pending','completed','cancelled')

                     )
         )
        );


        $customer_orders = new WP_Query( $args );




		//print_r($customer_orders );

		if ( !empty( $status ) )
		{
			echo '<div id="message" class="updated fade"><p>', $status, '</p></div>';
		}
		if (  !$customer_orders->have_posts() )
		{
			echo '<p>', __( 'You have no courses.', 'xoousers' ), '</p>';
		}
		else
		{
			$n = count( $msgs );


			?>
			<form action="" method="get">
				<?php wp_nonce_field( 'usersultra-bulk-action_inbox' ); ?>
				<input type="hidden" name="page" value="usersultra_inbox" />


				<div class="table-responsive">
				<table class="table " id="table-3" cellspacing="0">
					<thead>
					<tr>


						<th class="manage-column" ><?php _e( 'Order', 'xoousers' ); ?></th>
                        <th class="manage-column"><?php  _e( 'Total', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Subject', 'xoousers' ); ?></th>
						<th class="manage-column"><?php _e( 'Info', 'xoousers' ); ?></th>
                        <th class="manage-column" ><?php _e( 'Payment', 'xoousers' ); ?></th>
					</tr>
					</thead>
					<tbody>
						<?php

							while ( $customer_orders->have_posts() ) : $customer_orders->the_post();
							$order_id = $customer_orders->post->ID;
							$order = new WC_Order($order_id);


							//$order_name = $wpdb->get_var( 'SELECT order_item_name FROM '.$wpdb->prefix . 'woocommerce_order_items WHERE order_id = "'.$order_id.'"' );
							// GET ALL RESULTS - then name and ID

							//$order_names = $wpdb->get_results( 'SELECT order_item_id, order_item_name FROM '.$wpdb->prefix . 'woocommerce_order_items WHERE order_id = "'.$order_id.'"' );


							$items = $order->get_items();


    						$tutoringTopCat = [];
    						// $tutoringTopCat = [1146 , 6784 , 6783];


						     $args = array(
								    'post_type'             => 'product',
								    'post_status'           => 'publish',
								    'tax_query'             => array(
								        array(
								            'taxonomy'      => 'product_cat',
								            'terms'         =>  110,
								        )
								    )
								);
							$query  = new WP_Query($args);
							$products = $query->get_posts();

							// var_dump($products);

							foreach ( $products as $product ) {
								// print_r($product->ID);
							    $tutoringID = $product->ID;
							    array_push($tutoringTopCat, $tutoringID);
    						}

    						// var_dump($tutoringTopCat);


							foreach($wpdb->get_results ( 'SELECT order_item_type, order_item_id, order_item_name FROM '.$wpdb->prefix . 'woocommerce_order_items WHERE order_id = "'.$order_id.'"' ) as $order_names => $row)
							{

								$order_name = $row->order_item_name;
								$order_item_id = $row->order_item_id;
								$order_type = $row->order_item_type;



								// echo '<pre>';
								// echo $order_name;
								// echo '</br>';
								// echo $order_item_id;
								// echo '</br>';
								// echo $order_type;
								// echo '</pre>';

								// echo '<pre>';
								// print_r($order_names);
								// echo '</pre>';





								if (!in_array($order_item_id, $tutoringTopCat) &&( $order_type != "coupon")) {


								// foreach ( $items as $item ) {
								//     $product_id = $item['product_id'];

								// 	if () {

								// if (($order_name != "Top Up") && ( $order_type != "coupon")) {


									 $result = $wpdb->get_results('
									 select t2.*
									 FROM wp_woocommerce_order_items as t1 JOIN wp_woocommerce_order_itemmeta as t2 ON t1.order_item_id = t2.order_item_id
		            				 where t1.order_item_id='.$order_item_id);

									 // echo '<pre>';
		        // 					print_r($result);
									 // echo '</pre>';

									 $order_post_id = $result[2]->meta_value;

									 $order_link = $wpdb->get_var( 'SELECT post_name FROM '.$wpdb->prefix . 'posts WHERE ID = "'.$order_post_id.'"' );

									 $order_info = get_post_meta( $order_post_id, '_purchase_note' , 'true');

									 // $order_info = get_post_meta( 76, '_purchase_note' );

									 // echo '<pre>';
		        // 					print_r($order_info);
									 // echo '<pre>';
									?>

									<tr>
										<td>#<?php echo $order_id; ?></td>
			                            <td><?php echo woocommerce_price($order->order_total);?></td>
										<td><a href =<?php echo '"'.get_home_url().'?product='.$order_link.'">'.$order_name; ?></a></td>

										<td>


											<!-- <strong><?php  // echo $result[11]->meta_key; ?> :</strong> <?php  // echo $result[11]->meta_value; ?> <br/> -->
											<!-- <strong><?php  // echo $result[12]->meta_key; ?> :</strong> <?php  // echo $result[12]->meta_value; ?> <br/> -->
											<!-- <strong><?php  // echo $result[13]->meta_key; ?> :</strong> <?php  // echo $result[13]->meta_value; ?> <br/> -->
											<!-- <strong><?php  // echo $order_info[0]['name']; ?> : </strong> <?php  // echo $order_info[0]['value']; ?><br/> -->
											<!-- <strong><?php  // echo $order_info[1]['name']; ?> : </strong> <?php  // echo $order_info[1]['value']; ?><br/> -->
											<!-- <strong><?php  // echo $result[14]->meta_key; ?> :</strong> <?php  // echo $result[14]->meta_value; ?> <br/> -->

										<?php
/*
											if ($result[9]->meta_key == '_bundled_items'){

												$order_info = unserialize($result[14]->meta_value);

												echo '<strong>';
												echo $order_info[0]['name'];
												echo '</strong>';
												echo ' : ';
												echo $order_info[0]['value'];
												echo '<br/>';

												echo '<strong>';
												echo $order_info[2]['name'];
												echo '</strong>';
												echo ' : ';
												echo $order_info[2]['value'];
												echo '<br/>';

												echo '<strong>';
												echo $order_info[1]['name'];
												echo '</strong>';
												echo ' : ';
												echo $order_info[1]['value'];
												echo '<br/>';



											} elseif ($result[11]->meta_key == "Included with") {
												echo '<strong>';
												echo $result[11]->meta_key;
												echo '</strong>';
												echo ' : ';
												echo $result[11]->meta_value;
												echo '<br/>';

												$order_class = $wpdb->get_var( 'SELECT name FROM '.$wpdb->prefix . 'terms WHERE slug = "'.$result[9]->meta_value.'"' );
												echo '<strong>';
												echo 'Class';
												echo '</strong>';
												echo ' : ';
												echo $order_class;
												echo '<br/>';
											}
*/
											echo $order_info;
										?>
										</td>

			                            <td><?php echo $order->status; ?></td>
									</tr>


									<?php
								}// end if
							//}// end for items
						}// end for orders
						endwhile;
						?>
					</tbody>

				</table>
				</div>
			</form>
			<?php

		}
	}

	function show_my_latest_topups($howmany, $status=null)

	{

		global $wpdb, $current_user, $xoouserultra, $woocommerce;



		require_once(ABSPATH . 'wp-includes/pluggable.php');

		require_once(ABSPATH. 'wp-admin/includes/user.php' );

		require_once(ABSPATH.  'wp-includes/query.php' );





		$user_id = get_current_user_id();



		$args = array(

         'numberposts' => -1,

         'meta_key' => '_customer_user',

         'meta_value' => $user_id,

         'post_type' => 'shop_order',

         'post_status' => 'publish',

         'tax_query'=>array(

                     array(

                     'taxonomy' =>'shop_order_status',

                     'field' => 'slug',

					 'terms' => array('processing','pending','completed','cancelled')



                     )

         )

        );





        $loop = new WP_Query( $args );



		//print_r($loop );



		if ( !empty( $status ) )

		{

			echo '<div id="message" class="updated fade"><p>', $status, '</p></div>';

		}


		else

		{

			$n = count( $msgs );





			?>

			<form action="" method="get">

				<?php wp_nonce_field( 'usersultra-bulk-action_inbox' ); ?>

				<input type="hidden" name="page" value="usersultra_inbox" />







				<table class="widefat fixed" id="table-3" cellspacing="0">

					<thead>

					<tr>





						<th class="manage-column" ><?php _e( 'Tutoring Purchase', 'xoousers' ); ?></th>

                        <th class="manage-column"><?php _e( 'Total', 'xoousers' ); ?></th>

						<th class="manage-column" ><?php _e( 'Date', 'xoousers' ); ?></th>

                        <th class="manage-column" ><?php _e( 'Status', 'xoousers' ); ?></th>

					</tr>

					</thead>

					<tbody>
						<?php

							while ( $loop->have_posts() ) : $loop->the_post();
							$order_id = $loop->post->ID;
							$order = new WC_Order($order_id);

							$items = $order->get_items();


    						$tutoringTopCat = [];
    						// $tutoringTopCat = [1146 , 6784 , 6783];


						     $args = array(
						    'post_type'             => 'product',
						    'post_status'           => 'publish',
						    'tax_query'             => array(
						        array(
						            'taxonomy'      => 'product_cat',
						            'field' => 'term_id', //This is optional, as it defaults to 'term_id'
						            'terms'         => 110,
						        )
						    )
						);
						$query  = new WP_Query($args);
						$products = $query->get_posts();

						// var_dump($products);

							foreach ( $products as $product ) {
								// print_r($product->ID);
							    $tutoringID = $product->ID;
							    array_push($tutoringTopCat, $tutoringID);
    						}

    						// print_r($tutoringTopCat);

							foreach ( $items as $item ) {
							    $product_id = $item['product_id'];
    							$product_name = $item['name'];

    						// Topup Categories

							// $order_info = $wpdb->get_var( 'SELECT order_item_name FROM '.$wpdb->prefix . 'woocommerce_order_items WHERE order_id = "'.$order_id.'"' );

							if (in_array($product_id, $tutoringTopCat)) {
							?>
							<tr>
								<td>#<?php echo $order_id; ?></td>
	                            <td><?php echo woocommerce_price($order->order_total);?></td>
								<td> <?php echo $order->order_date; ?></td>
	                            <td><?php echo $order->status; ?></td>
                        	</tr>
							<?php
							} //end if
						} //end for
						endwhile;
						?>
					</tbody>



				</table>

			</form>

			<?php



		}

		?>



	<?php

	}

	function show_my_latest_courses2($howmany, $status=null)

	{

		global $wpdb, $current_user, $xoouserultra, $woocommerce;



		require_once(ABSPATH . 'wp-includes/pluggable.php');

		require_once(ABSPATH. 'wp-admin/includes/user.php' );

		require_once(ABSPATH.  'wp-includes/query.php' );





		$user_id = get_current_user_id();



		$args = array(

         'numberposts' => -1,

         'meta_key' => '_customer_user',

         'meta_value' => $user_id,

         'post_type' => 'shop_order',

         'post_status' => 'publish',

         'tax_query'=>array(

                     array(

                     'taxonomy' =>'shop_order_status',

                     'field' => 'slug',

					 'terms' => array('processing','pending','completed','cancelled')



                     )

         )

        );





        $loop = new WP_Query( $args );



		//print_r($loop );



		if ( !empty( $status ) )

		{

			echo '<div id="message" class="updated fade"><p>', $status, '</p></div>';

		}


		else

		{

			$n = count( $msgs );





			?>

			<form action="" method="get">

				<?php wp_nonce_field( 'usersultra-bulk-action_inbox' ); ?>

				<input type="hidden" name="page" value="usersultra_inbox" />







				<table class="widefat fixed" id="table-3" cellspacing="0">

					<thead>

					<tr>





						<th class="manage-column" ><?php _e( 'Order', 'xoousers' ); ?></th>
                        <th class="manage-column"><?php  _e( 'Total', 'xoousers' ); ?></th>
						<th class="manage-column" ><?php _e( 'Subject', 'xoousers' ); ?></th>
                        <th class="manage-column" ><?php _e( 'Payment', 'xoousers' ); ?></th>

					</tr>

					</thead>

					<tbody>
						<?php

							while ( $loop->have_posts() ) : $loop->the_post();
							$order_id = $loop->post->ID;
							$order = new WC_Order($order_id);

							$items = $order->get_items();


    						$tutoringTopCat = [];
    						// $tutoringTopCat = [1146 , 6784 , 6783];


						     $args = array(
						    'post_type'             => 'product',
						    'post_status'           => 'publish',
						    'tax_query'             => array(
						        array(
						            'taxonomy'      => 'product_cat',
						            'field' => 'term_id', //This is optional, as it defaults to 'term_id'
						            'terms'         => 110,
						        )
						    )
						);
						$query  = new WP_Query($args);
						$products = $query->get_posts();

						// var_dump($products);

							foreach ( $products as $product ) {
								// print_r($product->ID);
							    $tutoringID = $product->ID;
							    array_push($tutoringTopCat, $tutoringID);
    						}

    						// print_r($tutoringTopCat);

							foreach ( $items as $item ) {
								// print_r($item);
							    $product_id = $item['product_id'];
    							$product_name = $item['name'];

    						// Topup Categories

							// $order_info = $wpdb->get_var( 'SELECT order_item_name FROM '.$wpdb->prefix . 'woocommerce_order_items WHERE order_id = "'.$order_id.'"' );

							if (!in_array($product_id, $tutoringTopCat) ) {
							?>
							<tr>
								<td>#<?php echo $order_id; ?></td>
	                            <td><?php echo woocommerce_price($order->order_total);?></td>
								<td> <?php echo $product_name; ?></td>
	                            <td><?php echo $order->status; ?></td>
                        	</tr>
							<?php
							} //end if
						} //end for
						endwhile;
						?>
					</tbody>



				</table>

			</form>

			<?php



		}

		?>



	<?php

	}


	public function get_tutor_pay_form($receiver_user_id)
	{

		global $wpdb,  $xoouserultra;
		require_once(ABSPATH . 'wp-includes/formatting.php');

		$logged_user_id = get_current_user_id();

		$current_credits = 0;

		//query  database
			$res = $wpdb->get_var( 'SELECT credit FROM '.$wpdb->prefix . 'woocredit_users WHERE user_id = "'.$receiver_user_id.'"' );

		if ($res != null && $res != '') {
			//$current_credits = number_format((float)$res, 1);;
			$current_credits = $res;
		}

		$html = "";


		if($logged_user_id>0 && $logged_user_id != "")
		{
			//is logged in.
			if($logged_user_id==$receiver_user_id)
			{
				$html .= "<p>".__("You cannot send a payment confirmation to yourself", 'xoousers')."</p>";


			}else{

				$html .= ' <p>Credit Amount:</p>
				  <p><input name="rd_paid_credits" value="'.$current_credits.'" id="rd_paid_credits" type="text" /></p>
				  <p><a class="uultra-btn-email" href="#" id="rd-close-add-tutor-payment" data-id="'.$receiver_user_id.'"><span><i class="fa fa-times"></i></span>'. __("Close", 'xoousers').'</a> <a class="uultra-btn-email" href="#" id="rd-add-tutor-payment-confirm" data-id="'.$receiver_user_id.'"><span><i class="fa fa-check"></i></span>'. __("Send", 'xoousers').'</a></p>
				  ';

				  $html .='';

				  $html .='<script type="text/javascript">

				  var rd_paid_credits_empty = "'.__("Please specify credits", 'xoousers').'";



                 </script>';


			}


		}else{

			$html .= "<p>".__("You have to be logged in to send a payment confirmation", 'xoousers'. "</p>");


		}


		  echo $html;




	}

	public function get_add_session_form($receiver_user_id)
	{

		global $wpdb,  $xoouserultra;
		require_once(ABSPATH . 'wp-includes/formatting.php');

		$logged_user_id = get_current_user_id();

		$current_credits = 0;

		//query  database
			$res = $wpdb->get_var( 'SELECT credit FROM '.$wpdb->prefix . 'woocredit_users WHERE user_id = "'.$receiver_user_id.'"' );

		if ($res != null && $res != '') {
			$current_credits = number_format((float)$res, 1);;
		}

		$html = "";


		if($logged_user_id>0 && $logged_user_id != "")
		{

				$html .= '<p>Session Type:</p>

				  <select id="rd_session_type" class="form-control">
					  <option selected value="person">In Person</option>
					  <option value="online">Online</option>
				  </select>
				  ';

				$html .= '<p>Session Duration:</p>
				   <input type="text" value="0:00" id="rd_basicTimePicker" />
				  ';

				  //disabled the price

				$html .= ' <!-- <p>Session Price:</p> -->
				  <input hidden name="rd_session_price" value="0" id="rd_session_price"/>
				  ';


				$html .= ' <p>Session Date and Time:</p>
				  <p><input name="rd_session_date" id="rd_session_date" value="08:00 PM"/></p></br></br>
				  ';

				$html .= ' <p>Notes:</p>
				  <textarea   class="form-control" rows="3" name="rd_session_notes" id="rd_session_notes" /></textarea><br>
				  ';

				$html .= '<input type="hidden" id="rd_session_status" name="rd_session_status" value="pending" >
				  ';

				$html .= ' <p><a class="uultra-btn-email " href="#" id="rd-close-add-session-form" data-id="'.$receiver_user_id.'"><span><i class="fa fa-times"></i></span>'. __("Close", 'xoousers').'</a> <a class="uultra-btn-email" href="#" id="rd-add-session-confirm" data-id="'.$receiver_user_id.'"><span><i class="fa fa-check"></i></span>'. __("Add", 'xoousers').'</a></p>
				  ';


				  $html .='';

				  $html .='<script type="text/javascript">

				  var rd_paid_credits_empty = "'.__("Please specify duration", 'xoousers').'";

                 </script>';


		}else{

			$html .= "<p>".__("You have to be logged in to add a session", 'xoousers'. "</p>");


		}


		  echo $html;




	}

	public  function get_rd_field ($user_id)
	{
		 global  $xoouserultra;

		$rd_user_role = implode(', ',get_userdata($user_id)->roles);

		$fields_list = "";
		$fields_to_display = "user_school, billing_email,billing_phone,skype";
		$fields  = explode(',', $fields_to_display);

		if($fields_to_display != "")
		/*
		{

			foreach ($fields as $field)
			{
				//get meta

				$u_meta = get_user_meta($user_id, $field, true);

				if($field =='billing_email'){

					if($u_meta=="")
					{
						$u_meta = __("This user hasn't updated his mail", 'xoousers');


					}

					$fields_list .= "Email: <p class='desc'>".$u_meta."</p>";


				}elseif($field =='skype'){

					if($u_meta=="")
					{
						$u_meta = __("This user hasn't updated his skype", 'xoousers');


					}

					$fields_list .= "Skype: <p class='desc'>".$u_meta."</p>";


				}elseif($field =='billing_phone'){

					if($u_meta=="")
					{
						$u_meta = __("This user hasn't updated his phone", 'xoousers');


					}

					$fields_list .= "Phone: <p class='desc'>".$u_meta."</p>";


				}elseif($field =='user_school'){

					if($u_meta=="")
					{
						$u_meta = __("This user hasn't updated his school", 'xoousers');


					}

					$fields_list .= "School: <p class='desc'>".$u_meta."</p>";


				}else{

					$fields_list .= "<p>".$u_meta."</p>";

				}



			}

		}*/

		if($rd_user_role != 'tutor'){

			$u_meta = get_user_meta($user_id, 'billing_city', true);

			if($u_meta=="")
			{
				$u_meta = __("This user hasn't updated his school", 'xoousers');
			}

			$fields_list .= "School: <p class='desc'>".$u_meta."</p>";

		}

		$u_meta = get_user_meta($user_id, 'billing_email', true);

		if($u_meta=="")
		{
			$u_meta = __("This user hasn't updated his email", 'xoousers');
		}

		$fields_list .= "Email: <p class='desc'>".$u_meta."</p>";

		$u_meta = get_user_meta($user_id, 'billing_phone', true);

		if($u_meta=="")
		{
			$u_meta = __("This user hasn't updated his phone", 'xoousers');
		}

		$fields_list .= "Phone: <p class='desc'>".$u_meta."</p>";

		$u_meta = get_user_meta($user_id, 'skype', true);

		if($u_meta=="")
		{
			$u_meta = __("This user hasn't updated his skype", 'xoousers');
		}

		$fields_list .= "Skype: <p class='desc'>".$u_meta."</p>";

		return $fields_list;




	}
}

$key = "rdcustom";
$this->{$key} = new rdCustom();
