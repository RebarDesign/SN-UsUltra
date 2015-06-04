<?php

global $xoouserultra;



?>

<div class="uultra-profile-basic-wrap" style="width:<?php echo $template_width?>">



<div class="row commons-panel xoousersultra-shadow-borers" >



        <div class="text-center col-xs-12 col-md-3">





           <div class="uu-main-pict col-xs-12 col-md-12">



             <h2><?php echo $xoouserultra->userpanel->get_display_name($current_user->ID);?></h2>





               <?php echo $xoouserultra->userpanel->get_user_pic( $user_id, $pic_size, $pic_type, $pic_boder_type,  $pic_size_type)?>







                   <?php if ($optional_fields_to_display!="") { ?>



                   <?php echo $xoouserultra->userpanel->display_optional_fields( $user_id,$display_country_flag, $optional_fields_to_display)?>

                   <?php echo $xoouserultra->rdcustom->get_rd_field( $user_id)?>

                  <?php } ?>



                  <?php if ($profile_fields_to_display=="all") { ?>



                   <?php echo $xoouserultra->userpanel->get_profile_info( $user_id)?>



                  <?php } ?>











           </div>



           <p></p>









        </div>





        <div class="col-xs-12 col-md-9">





         <?php if($display_private_message=="yes"){?>



         <div class="uu-options-bar">




               <?php if($display_private_message=="yes"){?>


                  <a class="col-xs-offset-3 btn col-xs-7 col-md-3 col-md-offset-9" href="#" id="uu-send-private-message" data-id="<?php echo $user_id?>"><span><i class="fa fa-envelope-o"></i></span><?php echo _e("  Send Message", 'xoousers')?></a>


               <?php }?>



             </div>

             <div class="uu-private-messaging rounded col-xs-12 col-md-12" id="uu-pm-box">

             <?php echo $xoouserultra->mymessage->get_send_form( $user_id);?>

             <div id="uu-message-noti-id"></div>

             </div>

         <!-- </div> -->



          <?php }?>











      <?php if(!in_array("photos",$modules)){?>



       <?php if($photos_available){?>



        <?php if($display_gallery){



			 //get selected gallery

		      $current_gal = $xoouserultra->photogallery->get_gallery_public($gal_id, $user_id);







			?>



              <?php if( $current_gal->gallery_name!=""){



				  $xoouserultra->statistc->update_hits($gal_id, 'gallery');



				  ?>



              <h3><a href="<?php echo $xoouserultra->userpanel->get_user_profile_permalink( $user_id);?>"><?php echo _e("Main", 'xoousers')?></a>  / <?php echo $current_gal->gallery_name?></h3>



                <div class="photos">



                       <ul>

                          <?php echo $xoouserultra->photogallery->get_photos_of_gal_public($gal_id, $display_photo_rating, $gallery_type);?>



                       </ul>



                </div>

            <?php }?>



        <?php }?>





        <?php if($display_photo)

		{





			  $current_photo = $xoouserultra->photogallery->get_photo($photo_id, $user_id);



			 //get selected gallery

		      $current_gal = $xoouserultra->photogallery->get_gallery_public( $current_photo->photo_gal_id, $user_id);





			?>



            <?php if( $current_gal->gallery_name!="" && $photo_id > 0){



				  $xoouserultra->statistc->update_hits($photo_id, 'photo');



			 ?>







               <h3><a href="<?php echo $xoouserultra->userpanel->get_user_profile_permalink( $user_id);?>"><?php echo _e("Main", 'xoousers')?></a> /  <a href="<?php echo $xoouserultra->userpanel->public_profile_get_album_link( $current_gal->gallery_id, $user_id);?>"><?php echo $current_gal->gallery_name?></a></h3>



                  <div class="photo_single">





                       <?php echo $xoouserultra->photogallery->get_single_photo($photo_id, $user_id, $display_photo_rating, $display_photo_description);?>





                  </div>





           <?php } //end if photo not empty?>





        <?php }?>







         <?php if(!$display_gallery && !$display_photo){?>



         <div class="photolist">



             <h2><?php echo _e("My Photo Galleries", 'xoousers')?></h2>



           <ul>

              <?php echo $xoouserultra->photogallery->reload_galleries_public($user_id);?>



           </ul>



         </div>





             <?php if(!in_array("videos",$modules)){?>



                 <div class="videolist">



                  <h2><?php echo _e("My Videos", 'xoousers')?></h2>



                   <ul>

                      <?php echo $xoouserultra->photogallery->reload_videos_public($user_id);?>



                   </ul>



                 </div>



             <?php }?>













          <?php }?>





           <?php }else{?>



                 <?php echo _e("Photos available only for registered users", 'xoousers');?>



            <?php }?>





           <?php } //end exclude?>


           <!--   Display number of kr left  -->
           <?php if ($optional_right_col_fields_to_display!="") {

                  $rd_current_user = wp_get_current_user();
                  $rd_user_role = implode(', ',get_userdata($user_id)->roles);

                 if ( in_array( 'administrator', $rd_current_user->roles ) ) {

                  echo $xoouserultra->rdcustom->display_user_credits($user_id);


                  if ( $rd_user_role == 'tutor'){
                    echo $xoouserultra->rdcustom->display_tutor_hours($user_id);
                    }

                  elseif ( $rd_user_role == 'student'){
                    echo $xoouserultra->rdcustom->display_student_hours($user_id);
                    }

                  }
                 elseif (in_array( 'tutor', $rd_current_user->roles ) && $rd_user_role == 'student') {
                  //echo $xoouserultra->rdcustom->display_user_credits($user_id);
                  echo $xoouserultra->rdcustom->display_student_hours($user_id);
                }
           } ?>



        </br>
        </br>
        <div class="opt">
            <?php  if($act=="") {?>
            <?php
            // If you are an administrator watching a tutors profile
            if ( in_array( 'administrator', $rd_current_user->roles ) && $rd_user_role == 'tutor' ) { ?>

              <a class="col-xs-12 col-md-4 uultra-btn-add-session" href="#" id="rd-add-tutor-payment" data-id="<?php echo $user_id?>"><span class="rd-add-session glyphicon glyphicon-usd"></span><?php echo _e("Add Payment", 'xoousers')?></a>

              <div class="uu-private-messaging rounded" id="rd-tutor-pay-box">

                 <?php echo $xoouserultra->rdcustom->get_tutor_pay_form( $user_id);?>

                <div id="rd-tutor-pay-noti-id"></div>

              </div>

              </div>


              </br>
              </br>
              </br>
              <div id="tabs-p" class="col-xs-12 commons-panel-content" >
                  <h3 style="color:#2C2C2C">Sessions</h3>
                  <ul>
                    <li class="col-md-2 col-xs-12"><a href="#tabs-1">Upcoming</a></li>
                    <li class="col-md-2 col-xs-12"><a href="#tabs-2">Past</a></li>
                    <li class="col-md-2 col-xs-12"><a href="#tabs-3">Cancelled</a></li>
                  </ul>

                  <div class="table-responsive" id="tabs-1">
                    <?php echo $xoouserultra->rdcustom->show_my_pending_sessions_no_edit($user_id);?>
                  </div>
                  <div class="table-responsive" id="tabs-2">
                    <?php echo $xoouserultra->rdcustom->show_my_confirmed_sessions_no_edit($user_id);?>
                  </div>
                  <div class="table-responsive" id="tabs-3">
                    <?php echo $xoouserultra->rdcustom->show_my_cancelled_sessions_no_edit($user_id);?>
                  </div>
              </div>
            <?php }

            // Admin looking at student

            elseif ( in_array( 'administrator', $rd_current_user->roles ) && $rd_user_role == 'student' ) { ?>

              </br>
              </br>
              </br>
              <div id="tabs-p" class="col-xs-12 commons-panel-content" >
                  <h3 style="color:#2C2C2C">Sessions</h3>
                  <ul>
                    <li class="col-md-2 col-xs-12"><a href="#tabs-1">Upcoming</a></li>
                    <li class="col-md-2 col-xs-12"><a href="#tabs-2">Past</a></li>
                    <li class="col-md-2 col-xs-12"><a href="#tabs-3">Cancelled</a></li>
                  </ul>

                  <div class="table-responsive" id="tabs-1">
                    <?php echo $xoouserultra->rdcustom->show_student_admin_pending_sessions_no_edit($user_id);?>
                  </div>
                  <div class="table-responsive" id="tabs-2">
                    <?php echo $xoouserultra->rdcustom->show_student_admin_confirmed_sessions_no_edit($user_id);?>
                  </div>
                  <div class="table-responsive" id="tabs-3">
                    <?php echo $xoouserultra->rdcustom->show_student_admin_cancelled_sessions_no_edit($user_id);?>
                  </div>
              </div>
            <?php }


            // if you are a tutor watching a student's profile
            elseif ( in_array( 'tutor', $rd_current_user->roles ) && $rd_user_role == 'student') { ?>
             <a  class="uultra-btn-add-session" href="#" id="rd-add-session" data-id="<?php echo $user_id?>"><span class="rd-add-session glyphicon glyphicon-plus"></span><?php echo _e("Add Session", 'xoousers')?></a>
              <div class="uu-private-messaging rounded" id="rd-add-session-box">
                 <?php echo $xoouserultra->rdcustom->get_add_session_form( $user_id);?>

                <div id="rd-add-session-noti-id"></div>
             </div>

             </div>

             <div id="tabs-p" class="col-xs-12 commons-panel-content" >

                  <ul>
                    <li class="col-md-2 col-xs-12"><a href="#tabs-1">Upcoming</a></li>
                    <li class="col-md-2 col-xs-12"><a href="#tabs-2">Past</a></li>
                    <li class="col-md-2 col-xs-12"><a href="#tabs-3">Cancelled</a></li>
                  </ul>

                  <div class="table-responsive" id="tabs-1">
                    <?php echo $xoouserultra->rdcustom->show_my_students_pending_sessions_edit($user_id);?>
                  </div>
                  <div class="table-responsive" id="tabs-2">
                    <?php echo $xoouserultra->rdcustom->show_my_students_confirmed_sessions_edit($user_id);?>
                  </div>
                  <div class="table-responsive" id="tabs-3">
                    <?php echo $xoouserultra->rdcustom->show_my_students_cancelled_sessions_edit($user_id);?>
                  </div>
              </div>

               <?php
               }

                 if($act=="edit") {?>

                    <?php echo  $xoouserultra->rdcustom->rd_edit_session($post_id);?>


            <?php }} ?>



        </div>

  </div>

</div>
