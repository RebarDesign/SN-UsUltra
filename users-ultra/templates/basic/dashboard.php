<?php

global $xoouserultra;



$module = "";

$act= "";

$gal_id= "";

$page_id= "";

$view= "";

$reply= "";

$post_id ="";





if(isset($_GET["module"])){	$module = $_GET["module"];	}

if(isset($_GET["act"])){$act = $_GET["act"];	}

if(isset($_GET["gal_id"])){	$gal_id = $_GET["gal_id"];}

if(isset($_GET["page_id"])){	$page_id = $_GET["page_id"];}

if(isset($_GET["view"])){	$view = $_GET["view"];}

if(isset($_GET["reply"])){	$reply = $_GET["reply"];}

if(isset($_GET["post_id"])){	$post_id = $_GET["post_id"];}

$current_user = $xoouserultra->userpanel->get_user_info();

$user_id = $current_user->ID;

$user_email = $current_user->user_email;





$howmany = 5;





?>

<div class="usersultra-dahsboard-cont">



	<div style="width: 170px;" class="usersultra-dahsboard-left"> 

   

    

      <div class="myavatar rounded">

        

      <div class="pic" id="uu-backend-avatar-section">

        

            <?php echo $xoouserultra->rdcustom->get_user_pic( $user_id, "", 'avatar', 'rounded', 'dynamic')?>

            

           

            </div>

            

             <div class="btnupload">

             <a class="uultra-btn-upload-avatar" href="#" id="uu-send-private-message" data-id="<?php echo $user_id?>"><span><i class="fa fa-camera fa-2x"></i></span><?php echo _e("Update Image", 'xoousers')?></a>

             </div>

             

            

           <div class="uu-upload-avatar-sect" id="uu-upload-avatar-box">           

            

            <?php echo $xoouserultra->userpanel->avatar_uploader()?>         

            

            </div>

            

                      

            

      </div>

         

           <ul class="main_menu_rd_prof">

           

              <li><?php echo $xoouserultra->userpanel->get_user_backend_menu('dashboard');?></li>

              

               <?php if(!in_array("account",$modules)){?>  

               <li><?php echo $xoouserultra->userpanel->get_user_backend_menu('account');?></li>

               <?php }?>

               
               <?php if(!in_array("messages",$modules)){?>           

                

                <li><?php echo $xoouserultra->userpanel->get_user_backend_menu('messages');?></li>

                

                <?php }?>

                <?php if(!in_array("myorders",$modules)){?>  

               <li><?php echo $xoouserultra->userpanel->get_user_backend_menu('myorders');?></li>

               <?php }?>

               

                 <?php if(!in_array("wootracker",$modules)){?>  

               <li><?php echo $xoouserultra->userpanel->get_user_backend_menu('wootracker');?></li>

               <?php }?>

               

               <li><?php echo $xoouserultra->userpanel->get_user_backend_menu('profile');?></li>             

                              


                

				<?php if(!in_array("friends",$modules)){?>                 

                <li><?php echo $xoouserultra->userpanel->get_user_backend_menu('friends');?></li>

                <?php }?>

                

                <?php if(!in_array("posts",$modules)){?>                 

                <li><?php echo $xoouserultra->userpanel->get_user_backend_menu('posts');?></li>

                <?php }?>

                

                <?php if(!in_array("photos",$modules)){?>                 

                <li><?php echo $xoouserultra->userpanel->get_user_backend_menu('photos');?></li>           

                <?php }?>

                

                <?php if(!in_array("videos",$modules)){?> 

               <li><?php echo $xoouserultra->userpanel->get_user_backend_menu('videos');?></li>

               

               

               <?php }?>

               <li><?php echo $xoouserultra->userpanel->get_user_backend_menu('settings');?></li>

               <li><?php echo $xoouserultra->userpanel->get_user_backend_menu('logout');?></li>

           

           </ul>

    </div>

    

    

	<div class="usersultra-dahsboard-center"> 

    

        <?php

        //cutom message

		

		$message_custom = $xoouserultra->get_option('messaging_private_all_users');

		

		if($message_custom!="")

		{

			echo "<p><div class='uupublic-ultra-info'><p>".$message_custom."</p></div></p>";

		

		}

		

		?>

    

    

            <?php 

			

			//dashboard

			

			   if($module=="dashboard" ||$module==""  ) 

			   {

			?> 

            

                <h3> <?php  _e('Hello','xoousers');?></h3>
                <br>
                <h1> <?php echo $current_user->display_name?> </h1>     

               

                 <?php if(!in_array("messages",$modules)){?>  

             

              <div class="expandable-panel xoousersultra-shadow-borers" id="cp-1">

                                

                      <div class="expandable-panel-heading">

                              <h2><?php  _e('My Latest Messages','xoousers');?><span class="icon-close-open"></span></h2>

                     </div>

                     

                      <div class="expandable-panel-content" >

                     

                       	<?php  $xoouserultra->mymessage->show_usersultra_latest_messages($howmany);?>

                     

                     

                     </div>                    

                     

                     

               </div>

               

               <?php }?>



   		 

         

          <?php if(!in_array("photos",$modules)){?>  

              <div class="expandable-panel xoousersultra-shadow-borers" id="cp-2">

                                

                      <div class="expandable-panel-heading">

                              <h2><?php  _e('My Latest Photos:','xoousers');?><span class="icon-close-open"></span></h2>

                     </div>

                     

                      <div class="expandable-panel-content">

                     

                      <?php  echo $xoouserultra->photogallery->show_latest_photos_private(10);?>

                     

                     

                     </div>                    

                     

                     

               </div>

               

             <?php }?>   

              

              

        <?php }?>

        

         <?php

	   

	   //my friends

	   if($module=="friends" && !in_array("friends",$modules)) 

	   {

		   

		   

	   

	   ?>

       

	<div class="commons-panel xoousersultra-shadow-borers" >

                                

                      <div class="commons-panel-heading">
                            <?php $rd_current_user = wp_get_current_user(); 
                            if ( in_array( 'student', $rd_current_user->roles )){       
                                echo '<h2>My Tutors</h2>';                            
                            }
                            else {
                                echo '<h2>My Students</h2>'; 
                              }?>
                        </div>
                       

                       
                      <?php

                          if ( in_array( 'student', $rd_current_user->roles )){   ?>    
                                  
                          <p class="paneldesc"><?php echo _e('Here you can see your tutors. Click on the profile for more info','xoousers')?></p>
                                <?php } 

                                
                                else { ?>
                      
                          <p class="paneldesc"><?php echo _e('Here you can see your students. Click on the profile for more info','xoousers')?></p>


                      <?php }?>       

                     

                      <div class="commons-panel-content" id="uultra-my-friends-list"> 
                      <?php

                          if ( in_array( 'student', $rd_current_user->roles )){       
                                    echo '<h2>My Tutors</h2>';                            

                                    echo $xoouserultra->rdcustom->rd_get_my_tutors_students();
                                }

                                
                                else {
                                    echo '<h2>My Students</h2>'; 
                         
                                    echo $xoouserultra->rdcustom->rd_get_my_students_tutors();

                      }?>                       
                                    
                        
                       </div>

                       

                       

                       <script type="text/javascript">

                						jQuery(document).ready(function($){


                						function show_all_friends()

                						{

                							$.post(ajaxurl, {


                								   action: 'show_all_my_friends'

                											

                									}, function (response){									

                																		

                										$("#uultra-my-friends-list").html(response);										
                																
                								});

                						}
                        </script>

                        

                        

                  

                             

                     

                     

               </div>

               

              

       <?php }?>

       

        <?php

	   

	   //my posts

	   if($module=="posts" && !in_array("posts",$modules)) 

	   {

		   

		   

	   

	   ?>

       

             <?php  if($act=="") {?> 

       

                    <div class="commons-panel xoousersultra-shadow-borers" >

                              <div class="commons-panel-heading">

                                      <h2> <?php  _e('My Tutoring','xoousers');?> </h2>

                               </div>

                               

                               

                               <p class="paneldesc"><?php echo _e('Here you can manage your tutoring sessions. ','xoousers')?></p>

                             

                              <div class="commons-panel-content" >  
                              
                                  <?php 
                                  $rd_current_user = wp_get_current_user(); 

                                  if ( in_array( 'student', $rd_current_user->roles )) {
                                    echo $xoouserultra->rdcustom->show_my_latest_topups(10);
                                    //echo $xoouserultra->userpanel->display_user_credits($current_user->ID); 
                                    echo $xoouserultra->rdcustom->display_student_hours($current_user->ID);   
                                  }?>
                              
                               </div>


                                <!-- Check for user credits -->
                                <?php // echo $xoouserultra->userpanel->display_user_credits($current_user->ID)?>
                                </br>

                                <?php
                                  if ( in_array( 'student', $rd_current_user->roles )) {
                                    //echo '<h2>Buy more credits</h2>';
                                    //echo $xoouserultra->userpanel->display_user_credits($current_user->ID); 
                                    //////////////////////////////
                                    // Tutoring Top Up Buttons //
                                    /////////////////////////////
                                    // echo do_shortcode('[add_to_cart id="1146"]');
                                    // echo do_shortcode('[add_to_cart id="6783"]');
                                    // Instructions //
                                    ?>
                                    <div class="col-md-4">
	                                    <?php
	                                    echo do_shortcode('[xyz-ihs snippet="Tutoring-DKK-Buying"]');
	                                    ?>
                                    </div>
                                    <div class="rd-tutoring-products"> 
                                      <div class="visible-sm visible-xs">
                                      <?php 
                                      // echo do_shortcode('[product  id="6783"]' );
                                      // echo do_shortcode('[product  id="6784"]' );
                                      // echo do_shortcode('[product  id="1146"]' );
                                      echo do_shortcode('[product_category category="credit" columns="1"]' );
                                      ?>
                                      </div>
                                      <div class="visible-lg visible-md">
                                      <?php 
                                      echo do_shortcode('[product_category category="credit" columns="3"]' );
                                    ?>
                                      </div>
                                    </div>
                                    <?php
                                    
                                    };?>

                              <div id="rd_tabs" class="commons-panel-content" >

                                <ul>
                                  <li><a href="#tabs-1">Upcoming</a></li>
                                  <li><a href="#tabs-2">Past</a></li>
                                  <li><a href="#tabs-3">Cancelled</a></li>
                                </ul>
                                <div id="tabs-1">
                                  <?php 
                                  if ( in_array( 'student', $rd_current_user->roles )) {
                                    echo $xoouserultra->rdcustom->show_my_pending_sessions_student();}
                                  else {
                                    echo $xoouserultra->rdcustom->show_my_pending_sessions_edit();
                                  };
                                  ?>
                                </div>
                                <div id="tabs-2">
                                  <?php 
                                  if ( in_array( 'student', $rd_current_user->roles )) {
                                    echo $xoouserultra->rdcustom->show_my_confirmed_sessions_student();}
                                  else {
                                    echo $xoouserultra->rdcustom->show_my_confirmed_sessions_edit();
                                    };
                                    ?>
                                </div>
                                <div id="tabs-3">
                                  <?php 
                                  if ( in_array( 'student', $rd_current_user->roles )) {
                                    echo $xoouserultra->rdcustom->show_my_cancelled_sessions_student();}
                                  else {
                                    echo $xoouserultra->rdcustom->show_my_cancelled_sessions_edit();
                                    };
                                    ?>
                                </div>
                              </div>
                              

                                              

                      

                           

                       </div>

               

                <?php }?>

               

                 <?php  if($act=="add") {?>                  

                       

                    <?php echo do_shortcode('[usersultra_front_publisher]');?>                   

                        

                 <?php }?>

                 

                  <?php  if($act=="edit") {?>                  

                       

                    <?php echo  $xoouserultra->rdcustom->rd_edit_session($post_id);?>                   

                        

                 <?php }?>

                   

               

              

       <?php }?>

       

        <?php    

	    if($module=="myorders" && !in_array("myorders",$modules)) 

	   {

		   

	   ?>

       

             <?php  if($act=="") {?> 

       

                    <div class="commons-panel xoousersultra-shadow-borers" >

                              <div class="commons-panel-heading">

                                      <h2> <?php  _e('My courses','xoousers');?> </h2>

                               </div>

                               

                               

                               <p class="paneldesc"><?php echo _e('Here you can see your courses. ','xoousers')?></p>

                             

                              <div class="commons-panel-content" >  

                              

                              <?php echo $xoouserultra->rdcustom->show_my_latest_courses2(5);?>

                              

                              

                               </div>


                           

                       </div >

                               <br>
                               <br>

                       <div class="col-xs-7 col-md-6 rd-course-dashboard-list">
                              

                                   <?php 

                                   $args = array(
                                    'child_of'     => 13,
                                    'post_type'    => 'page',
                                    'post_status'  => 'publish',
                                    'sort_column'  => 'menu_order, post_title',
                                          'sort_order'   => '',
                                    'title_li'     => __(''), 
                                  ); 


                                   ?>
                                   <h4>Upcoming courses</h4>
                                   <?php wp_list_pages( $args ); ?> 
                                    
                                    <br>
                                    <br>
                                    <a class="btnuultra-small" href="/courses"><span><i class="fa fa-list"></i></span>  All Courses</a>
                              
                        </div>
               

                <?php }?>

               

                                 

               

              

       <?php }?>

       

       <?php    

	    if($module=="wootracker" && !in_array("wootracker",$modules)) 

	   {

		   

		   

	   

	   ?>

       

             <?php  if($act=="") {?> 

       

                    <div class="commons-panel xoousersultra-shadow-borers" >

                              <div class="commons-panel-heading">

                                      <h2> <?php  _e('My Top Ups','xoousers');?> </h2>

                               </div>

                               

                               

                               <p class="paneldesc"><?php echo _e('Here you can track your charges. ','xoousers')?></p>

                             

                              <div class="commons-panel-content" >  

                              

                              <!-- <?php //echo $xoouserultra->woocommerce->show_my_latest_orders(10);?> -->

                              <?php echo $xoouserultra->rdcustom->show_my_latest_topups(10);?>
                              

                              

                               </div>

                                              

                      

                           

                       </div>

               

                <?php }?>

               

                                 

               

              

       <?php }?>

       

       

       

       <?php

	   

	   //my photos

	   if($module=="photos" && !in_array("photos",$modules)) 

	   {

		   

		   

	   

	   ?>

       

<div class="commons-panel xoousersultra-shadow-borers" >

                                

                      <div class="commons-panel-heading">

                              <h2> <?php  _e('My Galleries','xoousers');?> / </h2>

                     </div>

                     

                      <div class="commons-panel-content">

                      

                      <p><?php  _e('Here you can manage your galleries and photos.','xoousers');?></p>

                      

                         <a  id="add_gallery"  href="#"> <?php  _e('Add Gallery','xoousers');?></a>

                        <div class="gallery-list">

                        

                         <div class="add-new-gallery" id="new_gallery_div">

                         

                            <p><?php  _e('Name','xoousers');?>

                            <br />

                            

                            <input type="hidden" name="xoouserultra_current_gal"  id="xoouserultra_current_gal" />

                           <input type="text" class="xoouserultra-input" name="new_gallery_name" id="new_gallery_name" value=""> 

                           <?php  _e('Description','xoousers');?>

                           <br />

                            <textarea class="xoouserultra-input'" name="new_gallery_desc" id="new_gallery_desc" ></textarea>

                            </p>

                            

                            <div class="usersultra-btn-options-bar">

                            <a class="buttonize" href="#" id="close_add_gallery"><?php  _e('Cancel','xoousers');?></a>

                            <a class="buttonize green"  href="#" id="new_gallery_add"><?php  _e('Submit','xoousers');?></a>

                            

                            </div>

                        

                        

                         </div>

                        

                        

                                                  

                        <ul id="usersultra-gallerylist">

                                loading ..

                              

                             

                         </ul>

                          

                          </div>

                     

                     

                     </div>                    

                     

                     

               </div>

               

               <script type="text/javascript">

				jQuery(document).ready(function($){

					

					

					var page_id_val =   $('#page_id').val(); 

               

					   $.post(ajaxurl, {

									action: 'reload_galleries', 'page_id': page_id_val

									

									}, function (response){									

																

									$("#usersultra-gallerylist").html(response);

									

														

							});

							

					

				});

				

				 

				   var gallery_delete_confirmation_message = '<?php echo _e( 'Delete this gallery?', 'xoousers' ) ?>';			

                                   

                    

                 </script>

       

       <?php }?>

       

        

               <input type="hidden" value="<?php echo $page_id?>" name="page_id" id="page_id" />

       

        <?php

	   

	   //my photos

	   if($module=="photos-files" && !in_array("photos",$modules)) 

	   {

		   

		   //get selected gallery

		   $current_gal = $xoouserultra->photogallery->get_gallery($gal_id)

	   

	   ?>

       

       <div class="commons-panel xoousersultra-shadow-borers" >

                                

                      <div class="commons-panel-heading">

                              <h2> <?php  _e('My Photos','xoousers');?> / <?php echo $current_gal->gallery_name?></h2>

                       </div>

                     

                      <div class="commons-panel-content">

                      

                      <p><?php  _e('Here you can manage your photos.','xoousers');?></p>

                      

                         <a  id="add_new_files"  href="#"> <?php  _e('Upload Files','xoousers');?></a>

                        <div class="photo-list">                         

                        

                         <div class="res_sortable_container" id="resp_t_image_list">

						 

						 <?php $xoouserultra->photogallery->post_media_display($gal_id);?>                       

                         

                         </div>

                                                                                  

                                <ul id="usersultra-photolist" class="usersultra-photolist-private">

                                       <?php  _e('loading photos ...','xoousers');?>

                                      

                                     

                                 </ul>

                          

                          </div>

                     

                     

                     </div>                    

                     

                     

               </div>

               

              

               

                <script type="text/javascript">

				jQuery(document).ready(function($){

					

					

               

					   $.post(ajaxurl, {

									action: 'reload_photos', 'gal_id': '<?php echo $gal_id?>'

									

									}, function (response){									

																

									$("#usersultra-photolist").html(response);

									

														

							});

							

							

							

					

				});

                    

                 </script>

               

                     

      <?php }?>

      

       <?php

	   

	   

	   if($module=="profile" && !in_array("profile",$modules)) 

	   {

		   

		  

	   

	   ?>

       

       <div class="commons-panel xoousersultra-shadow-borers" >

                                

                      <div class="commons-panel-heading">

                          <h2> <?php  _e('My Profile','xoousers');?> </h2>

                       </div>

                     

                      <div class="commons-panel-content">

                      

                       <?php echo $xoouserultra->userpanel->edit_profile_form();?>

                                         

                      </div>

                     

                     

          </div>

               

                

               

                     

      <?php }?>

      

      

     

      

            <?php if($module=="messages" && !in_array("messages",$modules)) 

	   {	  

	   

			   ?>

               

               

			  

			   <div class="commons-panel xoousersultra-shadow-borers" >

										

							  <div class="commons-panel-heading">

								  <h2> <?php  _e('Received Messages','xoousers');?> </h2>

							   </div>

							 

							  <div class="commons-panel-content">

							  

								   <div class="uultra-myprivate-messages">       

							  

									<?php  

									

									if(!$view && !$reply) 

									{

										$xoouserultra->mymessage->show_usersultra_my_messages();

									

									}

									

									if(isset($view) && $view>0) 

									{

										//display view box

										$xoouserultra->mymessage->show_view_my_message_form($view);

										

									

									}

									

									?>

							  

								   </div>

												 

							  </div>

							 

							 

				  </div>

                  

                  

                              

                

               

                     

      <?php }?>

      

      <?php if($module=="messages_sent" && !in_array("messages",$modules)) 

	   {	  

	   

			   ?>

               

               

			  

			   <div class="commons-panel xoousersultra-shadow-borers" >

										

							  <div class="commons-panel-heading">

								  <h2> <?php  _e('Sent Messages','xoousers');?> </h2>

							   </div>

							 

							  <div class="commons-panel-content">

							  

								   <div class="uultra-myprivate-messages">       

							  

									<?php  

									

									

										$xoouserultra->mymessage->show_usersultra_my_messages_sent();

									

																		

									?>

							  

								   </div>

												 

							  </div>

							 

							 

				  </div>

                  

                  

                              

                

               

                     

      <?php }?>

      

      <?php

     

       //my account

	   if($module=="account" && !in_array("account",$modules)) 

	   {

		   

		   

	   

	   ?>

       

		<div class="commons-panel xoousersultra-shadow-borers" >

        

        

        

                        <div class="commons-panel-heading">

                              <h2> <?php  _e('My Account','xoousers');?>  </h2>

                     </div>  

                     

                     <div class="commons-panel-content">

                     

                     <?php 

					 

					 $user_package_info = 	$xoouserultra->userpanel->get_user_account_type_info($user_id);

					 

					 $current_user = wp_get_current_user();					 

					 $acc_creation_date = date("m/d/Y", strtotime($current_user->user_registered));

					 

					 $current_package_id = $user_package_info["id"];

					 $current_package_name = $user_package_info["name"];

					 $current_package_amount = $user_package_info["price"];	

					 

					 $html= '<div class="uultra-account-type"><p><span>'.__('Account ID: ').'</span> '.$user_id.' <span>'.__('Registered Date: ').'</span> '.$acc_creation_date.' <span>'.__('Account Type: ').'</span> '.$current_package_name.'</p></div>';

					 

					 echo $html;

					 

					 ?>

                     

                    

                     

                     

                     </div> 

                     

        

        <div class="commons-panel-heading">

                              <h2> <?php  _e('Close Account','xoousers');?>  </h2>

                     </div>

        

                    

                      <div class="commons-panel-content">

                       <h2> <?php  _e('Remove Account','xoousers');?>  </h2>

                      

                      <div class="uupublic-ultra-warning">WARNING! This action cannot be reverted.</div>

                      

                      <p><?php  _e('Here you can remove your account.','xoousers');?></p>

                       <form method="post" name="uultra-close-account" id="uultra-close-account">

                 			<input type="hidden" name="uultra-conf-close-account-post" value="ok" />

                             <p><input type="button" name="xoouserultra-register" id="xoouserultra-close-acc-btn" class="xoouserultra-button" value="<?php  _e('YES, CLOSE MY ACCOUNT','xoousers');?>" /></p>

               		  </form>

                      

                                           

                     </div>

                     

                     

                                          

               </div>

               

               <script type="text/javascript">

		

				 

				   var delete_account_confirmation_mesage = '<?php echo _e( 'Are you totally sure that you want to close your account. This action cannot be reverted?', 'xoousers' ) ?>';			

                                   

                    

                 </script>

       

       <?php }?>

       

       

        

	  <?php

	  //my settings

	   if($module=="settings") 

	   {

	   

	   ?>

       

		<div class="commons-panel xoousersultra-shadow-borers" >

                                

                      <div class="commons-panel-heading">

                              <h2> <?php  _e('Settings','xoousers');?>  </h2>

                     </div>

                     

                     

                      <div class="commons-panel-content">

                       <h2> <?php  _e('Update Password','xoousers');?>  </h2>                     

                                           

                     

                       <form method="post" name="uultra-close-account" >

                       <p><?php  _e('Type your New Password','xoousers');?></p>

                 			 <p><input type="password" name="p1" id="p1" /></p>

                            

                             <p><?php  _e('Re-type your New Password','xoousers');?></p>

                 			 <p><input type="password"  name="p2" id="p2" /></p>

                            

                         <p><input type="button" name="xoouserultra-backenedb-eset-password" id="xoouserultra-backenedb-eset-password" class="xoouserultra-button" value="<?php  _e('CLICK HERE TO RESET PASSWORD','xoousers');?>" /></p>

                         

                         <p id="uultra-p-reset-msg">Message</p>

               		  </form>

                      

                       <h2> <?php  _e('Update Email','xoousers');?>  </h2> 

                                           

                     

                       <form method="post" name="uultra-change-email" >

                       <p><?php  _e('Type your New Email','xoousers');?></p>

                 			 <p><input type="text" name="email" id="email" value="<?php echo $user_email?>" /></p>

                                                        

                         <p><input type="button" name="xoouserultra-backenedb-update-email" id="xoouserultra-backenedb-update-email" class="xoouserultra-button" value="<?php  _e('CLICK HERE TO UPDATE YOUR EMAIL','xoousers');?>" /></p>

                         

                         <p id="uultra-p-changeemail-msg"></p>

               		  </form>

                      

                                           

                     </div>

                     

                                          

               </div>

               

               <script type="text/javascript">

		

				 

				   var delete_account_confirmation_mesage = '<?php echo _e( 'Are you totally sure that you want to close your account. This action cannot be reverted?', 'xoousers' ) ?>';			

                                   

                    

                 </script>

       

       <?php }?>

      

      

      <?php

	   

	   

	   if($module=="videos" && !in_array("videos",$modules)) 

	   {

		   

		  

	   

	   ?>

       

       <div class="commons-panel xoousersultra-shadow-borers" >

                                

                      <div class="commons-panel-heading">

                          <h2> <?php  _e('My Videos','xoousers');?> </h2>

                       </div>

                     

                      <div class="commons-panel-content">

                      

                      

                      

                      <p><?php  _e('Here you can manage your videos.','xoousers');?></p>

                      

                         <a  id="add_new_video"  href="#"> <?php  _e('Add Video','xoousers');?></a>

                         

                          <div class="add-new-video" id="new_video_div">

                         

                            <p><?php  _e('Name','xoousers');?>

                            <br />

                            

                           

                           <input type="text" class="xoouserultra-input" name="new_video_name" id="new_video_name" value=""> 

                           

                           </p>

                           <p>

                           

                           <?php  _e('Video ID','xoousers');?>

                           <br />

                            <input type="text" class="xoouserultra-input" name="new_video_unique_vid" id="new_video_unique_vid" value=""> 

                            </p>

                            

                           

                             <p>

                           

                           <?php  _e('Video Type','xoousers');?>

                           <br />

                            <select  name="new_video_type" id="new_video_type" class="xoouserultra-input" >

                              <option value="youtube">Youtube</option>

                              <option value="vimeo">Vimeo</option>

                              dd</select>

                            </p>

                            

                            <div class="usersultra-btn-options-bar">

                            <a class="buttonize" href="#" id="close_add_video"><?php  _e('Cancel','xoousers');?></a>

                            <a class="buttonize green"  href="#" id="new_video_add_confirm"><?php  _e('Submit','xoousers');?></a>

                            

                            </div>  

                            

                         </div>       

                        <div class="video-list">     

                        

                        

                                    

                                                                       

                                <ul id="usersultra-videolist" class="usersultra-video-private">

                                       <?php  _e('please wait, loading videos ...','xoousers');?>

                                 </ul>

                          

                          </div>

                     

                     

                           

                                         

                      </div>                           

     

               

               <script type="text/javascript">

				jQuery(document).ready(function($){			

				

               

					   $.post(ajaxurl, {

									action: 'reload_videos'

									

									}, function (response){																

																

									$("#usersultra-videolist").html(response);

									

														

							});

							

					

				});

				

				 

				   var video_delete_confirmation_message = '<?php echo _e( 'Delete this video?', 'xoousers' ) ?>';			

				  var video_empy_field_name= '<?php echo _e( 'Please input a name', 'xoousers' ) ?>';

				  var video_empy_field_id= '<?php echo _e( 'Please input video ID', 'xoousers' ) ?>';			

                                   

                    

                 </script>  

               

                     

      <?php }?>





    </div>

    

   





</div>