<?php

function bf_add_element($form, $element){
	$form->addElement($element);
}

function bf_get_post_status_array($select_condition = false){

    $status_array = array(
        'publish'       => 'Published',
        'pending'       => 'Pending Review',
        'draft'         => 'Draft',
        'future'        => 'Scheduled',
        'private'       => 'Privately Published',
        'trash'         => 'Trash',
    );

	return $status_array;
}

/**
 * Restricting users to view only media library items they upload.
 *
 * @package BuddyForms
 * @since 0.5 beta
 */
add_action('pre_get_posts','buddyforms_restrict_media_library');
function buddyforms_restrict_media_library( $wp_query_obj ) {
    global $current_user, $pagenow;

	if(is_super_admin( $current_user->ID ))
		return;

    if( !is_a( $current_user, 'WP_User') )
        return;

	if( 'admin-ajax.php' != $pagenow || $_REQUEST['action'] != 'query-attachments' )
        return;

	if( !current_user_can('manage_media_library') )
        $wp_query_obj->set('author', $current_user->ID );

	return;
}

/**
 * Check if a subscriber have the needed rights to upload images and add this capabilities if needed.
 *
 * @package BuddyForms
 * @since 0.5 beta
 */
add_action('init', 'buddyforms_allow_subscriber_uploads');
function buddyforms_allow_subscriber_uploads() {

    if ( current_user_can('subscriber') && !current_user_can('upload_files') ){
        $contributor = get_role('subscriber');

        $contributor->add_cap('upload_files');
    }

}

/**
 * rewrite the url of the edit-this-post link in the frontend
 *
 * @package BuddyForms
 * @since 0.3 beta
 */
add_filter( 'get_edit_post_link', 'my_edit_post_link', 1,3 );
function my_edit_post_link( $url, $post_ID) {
	global $buddyforms, $current_user;

	if(is_admin())
		return $url;

	if(!isset($buddyforms['buddyforms']))
		return $url;

	$the_post	= get_post( $post_ID );
	$post_type	= get_post_type( $the_post );

	if ($the_post->post_author != $current_user->ID) // @todo this needs to be modified for admins and collaborative content creation
		return $url;

	foreach ($buddyforms['buddyforms'] as $key => $buddyform) {

		if(isset($buddyform['post_type']) && $buddyform['post_type'] == $post_type && isset($buddyform['edit_link']) && $buddyform['edit_link'] == 'all'){
			$permalink	= get_permalink( $buddyform['attached_page'] );
			$url = $permalink.'edit/'.$key.'/'.get_the_ID();
			return $url;
		}
	}

	return $url;
}

/**
 * handle custom page
 * do flush if changing rule, then reload the admin page
 *
 * @package BuddyForms
 * @since 0.3 beta
 */
add_action('admin_init', 'buddyforms_attached_page_rewrite_rules');
function buddyforms_attached_page_rewrite_rules(){
	global $buddyforms;

	if(!isset($buddyforms['buddyforms']))
		return;

	foreach ($buddyforms['buddyforms'] as $key => $buddyform) {
		if(isset($buddyform['attached_page'])){
			$post_data = get_post($buddyform['attached_page'], ARRAY_A);
			//add_rewrite_rule($post_data['post_name'].'/([^/]+)/([^/]+)/([^/]+)/?', 'index.php?pagename='.$post_data['post_name'].'&bf_action=$matches[1]&bf_post_type=$matches[2]&bf_form_slug=$matches[3]', 'top');
			add_rewrite_rule($post_data['post_name'].'/create/([^/]+)/?', 'index.php?pagename='.$post_data['post_name'].'&bf_action=create&bf_form_slug=$matches[1]', 'top');
			add_rewrite_rule($post_data['post_name'].'/view/([^/]+)/?', 'index.php?pagename='.$post_data['post_name'].'&bf_action=view&bf_form_slug=$matches[1]', 'top');
			add_rewrite_rule($post_data['post_name'].'/edit/([^/]+)/([^/]+)/?', 'index.php?pagename='.$post_data['post_name'].'&bf_action=edit&bf_form_slug=$matches[1]&bf_post_id=$matches[2]', 'top');
			add_rewrite_rule($post_data['post_name'].'/delete/([^/]+)/([^/]+)/?', 'index.php?pagename='.$post_data['post_name'].'&bf_action=delete&bf_form_slug=$matches[1]&bf_post_id=$matches[2]', 'top');
			add_rewrite_rule($post_data['post_name'].'/revison/([^/]+)/([^/]+)/([^/]+)/?', 'index.php?pagename='.$post_data['post_name'].'&bf_action=revison&bf_form_slug=$matches[1]&bf_post_id=$matches[2]&bf_rev_id=$matches[3]', 'top');
		}

	}
	flush_rewrite_rules();
}

/**
 * add the query vars
 *
 * @package BuddyForms
 * @since 0.3 beta
 */
add_filter('query_vars', 'buddyforms_attached_page_query_vars');
function buddyforms_attached_page_query_vars($query_vars){

	if(is_admin())
		return $query_vars;

	$query_vars[] = 'bf_action';
	$query_vars[] = 'bf_form_slug';
	$query_vars[] = 'bf_post_id';
	$query_vars[] = 'bf_rev_id';

	return $query_vars;
}

/**
 * make the template redirect
 *
 * @package BuddyForms
 * @since 0.3 beta
 */
add_filter('template_redirect', 'buddyforms_attached_page_content');
function buddyforms_attached_page_content($content){
	global $wp_query, $buddyforms;

	if(is_admin())
		return $content;
		
	if(!isset($buddyforms['buddyforms']))
		return;

	$new_content = $content;
    if (isset($wp_query->query_vars['bf_action'])) {
    	$form_slug = '';
		if(isset($wp_query->query_vars['bf_form_slug']))
    		$form_slug = $wp_query->query_vars['bf_form_slug'];
		
		$post_id = '';
		if(isset($wp_query->query_vars['bf_post_id']))
    		$post_id = $wp_query->query_vars['bf_post_id'];
		
		$post_type = $buddyforms['buddyforms'][$form_slug]['post_type'];
		
		$args = array(
			'form_slug'	=> $form_slug,
			'post_id'	=> $post_id,
			'post_type'	=> $post_type
		);
		
    	if($wp_query->query_vars['bf_action'] == 'create' || $wp_query->query_vars['bf_action'] == 'edit' || $wp_query->query_vars['bf_action'] == 'revison'){
			ob_start();
				buddyforms_create_edit_form($args);
				$bf_form = ob_get_contents();
			ob_clean();
			$new_content .= $bf_form;		
    	}
		if($wp_query->query_vars['bf_action'] == 'view'){
    		ob_start();
				buddyforms_the_loop($args);
				$bf_form = ob_get_contents();
			ob_clean();
			$new_content .= $bf_form;				
		}
		if($wp_query->query_vars['bf_action'] == 'delete'){
			ob_start();
				buddyforms_delete_post($args);
				$bf_form = ob_get_contents();
			ob_clean();
			$new_content .= $bf_form;		
		}
		
	} elseif(isset($wp_query->query_vars['pagename'])){

		foreach ($buddyforms['buddyforms'] as $key => $buddyform) {
				
			if(isset($buddyform['attached_page']) && $wp_query->query_vars['pagename'] == $buddyform['attached_page'])
				$post_data = get_post($buddyform['attached_page'], ARRAY_A);
			
			if(isset($post_data['post_name']) && $post_data['post_name'] == $wp_query->query_vars['pagename']){
				$args = array(
					'form_slug' => $buddyform['slug'],
				);
				ob_start();
					buddyforms_the_loop($args);
					$bf_form = ob_get_contents();
				ob_clean();
				$new_content .= $bf_form;
			}
		}
		
	}
	if(!empty($new_content))
		add_filter( 'the_content', create_function('', 'return "' . addcslashes($new_content, '"') . '";') );

}
												
/**
 * add the forms to the admin bar
 *
 * @package BuddyForms
 * @since 0.3 beta
 */
add_action('wp_before_admin_bar_render', 'buddyforms_wp_before_admin_bar_render',1,2);
function buddyforms_wp_before_admin_bar_render(){
	global $wp_admin_bar, $buddyforms;

	if(!isset($buddyforms['buddyforms'] ))
		return;
	
	foreach ($buddyforms['buddyforms'] as $key => $buddyform) {

		if(isset($buddyform['admin_bar'][0]) && $buddyform['post_type'] != 'none' && !empty($buddyform['attached_page']) ){
			$permalink = get_permalink( $buddyform['attached_page'] );
			$wp_admin_bar->add_menu( array(
				'parent' 	=> 'my-account',
				'id'		=> 'my-account-'.$buddyform['slug'],
				'title'		=> $buddyform['name'],
				'href'		=> $permalink
			));
			$wp_admin_bar->add_menu( array(
				'parent'	=> 'my-account-'.$buddyform['slug'],
				'id'		=> 'my-account-'.$buddyform['slug'].'-view',
				'title'		=> __('View my ', 'buddyforms') . $buddyform['name'],
				'href'		=> $permalink.'/view/'.$buddyform['slug'].'/'
			)); 

			 $wp_admin_bar->add_menu( array(
				'parent'	=> 'my-account-'.$buddyform['slug'],
				'id'		=> 'my-account-'.$buddyform['slug'].'-new',
				'title'		=> __('New ', 'buddyforms') . $buddyform['singular_name'],
				'href'		=> $permalink.'create/'.$buddyform['slug'].'/'
			));  

		}
	}
}

/**
 * Add a button to the content editor, next to the media button 
 * This button will show a popup that contains inline content 
 * @package BuddyForms
 * @since 0.3 beta
 * 
 */
add_action('media_buttons_context', 'buddyforms_editor_button');
function buddyforms_editor_button($context) {
  	
  if (!is_admin())
  	return $context;
  
  // Path to my icon
  // $img = plugins_url( 'admin/img/icon-buddyformsc-16.png' , __FILE__ );
  
  // The ID of the container I want to show in the popup
  $container_id = 'popup_container';
  
  // Our popup's title
  $title = 'BuddyForms Shortcode Generator!';

  // Append the icon <a href="#" class="button insert-media add_media" data-editor="content" title="Add Media"><span class="wp-media-buttons-icon"></span> Add Media</a>
  $context .= "<a class='button thickbox' data-editor='content'  title='{$title}'
    href='#TB_inline?width=400&inlineId={$container_id}'>
    <span class='tk-icon-buddyforms' style='color: #888; font-size: 24px; margin-left: -2px;'/></span> Add Form</a>";
  
  return $context;
}


/**
 * Add some content to the bottom of the page for the BuddyForms shortcodes
 * This will be shown in the thickbox of the post edit screen
 * 
 * @package BuddyForms
 * @since 0.1 beta
 */
add_action('admin_footer', 'buddyforms_editor_button_inline_content');
function buddyforms_editor_button_inline_content() {
global $buddyforms;
	if (!is_admin())
		return; ?>
		
	<div id="popup_container" style="display:none;">
	<h2></h2>
	<?php 
  
  	// Get all post types
    $args=array(
		'public' => true,
		'show_ui' => true
    ); 
    $output = 'names'; // names or objects, note names is the default
    $operator = 'and'; // 'and' or 'or'
    $post_types = get_post_types($args,$output,$operator); 
   	$post_types_none['none'] = 'none';
	$post_types = array_merge($post_types_none,$post_types);
	
  
  	$form = new Form("buddyforms_add_form");
	$form->configure(array(
		"prevent" => array("bootstrap", "jQuery"),
		"action" => $_SERVER['REQUEST_URI'],
		"view" => new View_Inline
	));
	$the_forms[] = 'Select the form to use';
	
	foreach ($buddyforms['buddyforms'] as $key => $buddyform) {
		$the_forms[] = $buddyform['slug'];
	}
	$form->addElement( new Element_Select("<h3>" . __('Select the form to use', 'buddyforms') . "</h3><br>", "buddyforms_add_form", $the_forms, array('class' => 'buddyforms_add_form')));
	$form->addElement( new Element_Select("<br /><h3>" . __('Select the post type', 'buddyforms') . "</h3><br>", "buddyforms_posttype", $post_types, array('class' => 'buddyforms_posttype')));
	$form->render();
  ?>
  <br /><br />
  <a href="#" class="buddyforms-button-insert button"><?php _e('Add Form Now', 'buddyforms') ?></a>
</div>
<?php
}

add_action('admin_footer',  'buddyforms_editor_button_mce_popup');
function buddyforms_editor_button_mce_popup(){ ?>
   <script>

jQuery(document).ready(function (){
    jQuery('.buddyforms-button-insert').on('click',function(event){  
    	var form = jQuery('.buddyforms_add_form').val();
    	var posttype = jQuery('.buddyforms_posttype').val();
		window.send_to_editor('[buddyforms_form form_slug="'+form +'" post_type="'+posttype+'"]');
        });
});

</script>
<?php
}

/**
 * Get the BuddyForms template directory.
 *
 * @package BuddyForms
 * @since 0.1 beta
 *
 * @uses apply_filters()
 * @return string
 */
function buddyforms_get_template_directory() {
	return apply_filters('buddyforms_get_template_directory', constant('BUDDYFORMS_TEMPLATE_PATH'));
}

/**
 * Locate a template
 *
 * @package BuddyForms
 * @since 0.1 beta
 */
function buddyforms_locate_template($file) {
	if (locate_template(array($file), false)) {
		locate_template(array($file), true);
	} else {
		include (BUDDYFORMS_TEMPLATE_PATH . $file);
	}
}
