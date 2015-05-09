<?php
/**
 * Plugin Name: Dizeno
 * Plugin URI: 
 * Description: A brief description of the plugin.
 * Version: 1.0.0
 * Author: Tejas H Mishra
 * Author URI: 
 * Text Domain: Optional. Plugin's text domain for localization. Example: mytextdomain
 * Domain Path: Optional. Plugin's relative directory path to .mo files. Example: /locale/
 * Network: Optional. Whether the plugin can only be activated network wide. Example: true
 * License: A short license name. Example: GPL2
 */

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */




function dizeno_activate() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . "dizeno";
    if( $wpdb->get_var('SHOW TABLES LIKE ' . $table_name) != $table_name ) {
        $sql = 'CREATE TABLE ' . $table_name . '('
                . 'id BIGINT(20) UNSIGNED AUTO_INCREMENT, '
                . 'layout_header VARCHAR(255), '
                . 'PRIMARY KEY (id) ) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci';
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        add_option('dizeno_database_version','1.0');
    }

    $post_type_table_name = $wpdb->prefix . "post_type";
    if( $wpdb->get_var('SHOW TABLES LIKE ' . $post_type_table_name) != $post_type_table_name ) {
        $sql = 'CREATE TABLE ' . $post_type_table_name . '('
                . 'id BIGINT(20) UNSIGNED AUTO_INCREMENT, '
                . 'title VARCHAR(255) NOT NULL, '
                . 'post_type_slug VARCHAR(20) NOT NULL, '
                . 'dizeno_post_type VARCHAR(10) NOT NULL, '
                . 'dizeno_category VARCHAR(10) NOT NULL, '
                . 'dizeno_category_list TEXT(50) NULL, '
                . 'dizeno_tag VARCHAR(10) NOT NULL, '
                . 'dizeno_tag_list TEXT(50) NULL, '
                . 'PRIMARY KEY (id) ) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci';
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        add_option('post_type_database_version','1.0');
    }
    
}
register_activation_hook(__FILE__, 'dizeno_activate');

function dizeno_admin_theme() {
    wp_enqueue_style('dizeno-admin-theme', plugins_url('wp-dizeno-admin.css', __FILE__));
    wp_enqueue_script('dizeno-admin-theme-script', plugins_url('wp-dizeno-admin-script.js', __FILE__));
}
add_action('admin_enqueue_scripts', 'dizeno_admin_theme');
add_action('login_enqueue_scripts', 'dizeno_admin_theme');


// Add menu For Dizeno
add_action( 'admin_menu', 'register_dizeno_settings_page' );
function register_dizeno_settings_page() {
    //add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
    add_menu_page( 'Dizeno', 'Dizeno', 'manage_options', 'dizeno-settings', 'dizeno_settings_page', 'dashicons-admin-generic', 4 );
    //add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
    add_submenu_page( 'dizeno-settings', 'Settings', 'Settings', 'manage_options', 'post_type_settings', 'post_type_settings_page' );   
}







function dizeno_settings_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . "dizeno";
    $post_type_table_name = $wpdb->prefix . "post_type";
    
    isset ( $_GET['tab'] )  ? $current_tab = $_GET['tab'] : $current_tab = 'post-type';
    if(isset($_POST[layout_header])) {
        $layout_header = $_POST[layout_header];
        $wpdb->update($table_name, array('layout_header'=>$layout_header),array('id'=>1));
    }
    if(isset($_POST['post_title'])) {
        $post_title = $_POST['post_title'];
        $dizeno_post_type = $_POST['dizeno_post_type'];
        $dizeno_category_list = $_POST['dizeno_category_list'];
        $dizeno_tag_list = $_POST['dizeno_tag_list'];
        if (isset($_POST['dizeno_category']) && $_POST['dizeno_category'] == 'on') {
            $dizeno_category = 'Yes';
            $dizeno_category_list = $_POST['dizeno_category_list'];
        } else {
            $dizeno_category = 'No';
            $dizeno_category_list = '';
        }

        if (isset($_POST['dizeno_tag']) && $_POST['dizeno_tag'] == 'on') {
            $dizeno_tag = 'Yes';
            $dizeno_tag_ist = $_POST['dizeno_tag_ist'];
        } else {
            $dizeno_tag = 'No';
            $dizeno_tag_ist = '';
        }
        $wpdb->insert($post_type_table_name, array(
                                                'title'=>$post_title, 
                                                'post_type_slug'=>  strtolower($post_title),
                                                'dizeno_post_type' => strtolower($dizeno_post_type),
                                                'dizeno_category' => $dizeno_category,
                                                'dizeno_category_list' => $dizeno_category_list,
                                                'dizeno_tag' => $dizeno_tag,
                                                'dizeno_tag_list' => $dizeno_tag_list
                                            )
                    ); 
    }
    
    
    //Create an instance of our package class...
    $testListTable = new Dizeno_Post_Type_List_tabel();
    //Fetch, prepare, sort, and filter our data...
    $testListTable->prepare_items();
        
    ?>
        <div class="wrap">
            <h2>Dizeno</h2>
            <p>Welcome to the Dizeno Settings Page. Here you can configure settings as your need.</p>
            <div id="col-container">
                <div id="col-right">
                    <div class="col-wrap">
                        <?php $tabs = array( 'post-type' => 'Post Types', 'general' => 'General', 'footer' => 'Footer' ); ?>
                        <div id="icon-themes" class="icon32"><br></div>
                        <h2 class="nav-tab-wrapper">
                        <?php foreach( $tabs as $tab => $name ){
                            $class = ( $tab == $current_tab ) ? ' nav-tab-active' : '';
                            echo "<a class='nav-tab$class' href='?page=dizeno-settings&tab=$tab'>$name</a>";

                        } ?>
                        </h2>
                        
                        <form  method='get'>
                            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
                            <?php $selected_layout = $wpdb->get_var( 'SELECT layout_header FROM '. $table_name . ' WHERE id=1'); ?>
                            <table class="form-table">
                                <?php switch ( $current_tab ){
                                    case 'post-type' :
                                    ?>
                                    <?php $testListTable->display(); ?>
                                       
                                                        
                                    <?php
                                    break;
                                    case 'general' :
                                        ?>
                                        <tr>
                                            <th>Header Position:</th>
                                            <td>
                                                <input type="radio" name="layout_header" value="header-top-full" id="header-top-full" <?php if($selected_layout == 'header-top-full') echo 'checked'; ?> ><label for="header-top-full">Top Full</label>
                                                <br><br>
                                                <input type="radio" name="layout_header" value="header-top-box" id="header-top-box" <?php if($selected_layout == 'header-top-box') echo 'checked'; ?> ><label for="header-top-box">Top Boxed</label>
                                                <br><br>
                                                <input type="radio" name="layout_header" value="header-bottom-full" id="header-bottom-full" <?php if($selected_layout == 'header-bottom-full') echo 'checked'; ?> ><label for="header-bottom-full">Bottom Full</label>
                                           </td>
                                        </tr>
                                        <?php
                                    break;
                                    case 'footer' :
                                        ?>
                                        <tr>
                                            <th><label for="ilc_ga">Insert tracking code:</label></th>
                                            <td>
                                             Enter your Google Analytics tracking code:
                                             <textarea id="ilc_ga" name="ilc_ga" cols="60" rows="5"><?php echo esc_html( stripslashes( $settings["ilc_ga"] ) ); ?></textarea><br />

                                            </td>
                                        </tr>
                                        <?php
                                    break;
                                } ?>
                            </table>
                            <input type="submit" name="submit" value="Save" class="button button-primary">
                        </form> 
                    </div>
                </div>
                <div id="col-left">
                    <div class="col-wrap">
                            <h3><span>Add New Post Type</span></h3>
                            <div class="form-wrap inside">
                                <form action='?page=dizeno-settings&tab=post-type' method='post' >
                                    <div class="form-field term-name-wrap">
                                        <label for="post_type_name">Post Name:</label>
                                        <input type="text" name="post_title" id="post-title">
                                        <p>The name of your product or post type.</p>
                                    </div>
                                    <div class="form-field term-type-wrap">
                                        <label for="dizeno_post_type">Post Type:</label>
                                        <select id="dizeno_post_type" name="dizeno_post_type">
                                            <option value="product" selected="selected">Product</option>
                                            <option value="place">Place</option>
                                            <option value="event">Event</option>
                                        </select>
                                        <p>The name of your product or post type.</p>
                                    </div>
                                    <div class="form-field term-taxonomy-wrap">
                                        <label for="dizeno_post_type_taxonomy">Add Taxonomy:</label>
                                        <ul>
                                            <li id="dizeno_category_li">
                                                <label><input type="checkbox" id="dizeno_category" name="dizeno_category" <?php if($post_type_edit['dizeno_category'] == 'Yes') { echo "checked"; }  ?>> Category</label>
                                                <div>
                                                    <input type="text" name="dizeno_category_list" id="dizeno_category_list">
                                                    <p>It will create default category. If you want to add custom category then type in this text box and separate custom category (non hierarchical list) with commas.</p>
                                                </div>
                                            </li>
                                            <li id="dizeno_tag_li">
                                                <label><input type="checkbox" id="dizeno_tag" name="dizeno_tag" <?php if($post_type_edit['dizeno_tag'] == 'Yes') { echo "checked"; }  ?>> Tag</label>
                                                <div>
                                                    <input type="text" name="dizeno_tag_list" id="dizeno_tag_list">
                                                    <p>It will create default tag. If you want to add custom tag then type in this text box and separate custom tags (hierarchical list) with commas.</p>
                                                </div>                                            
                                            </li>
                                        </ul>
                                        <p>The name of your product or post type.</p>
                                    </div>
                                    <input type="submit" name="submit" value="Add" class="button button-primary">
                                </form>
                            </div>
                    </div>
                </div>
            </div>
        </div>

        
<?php
}

function post_type_settings_page() {
    global $wpdb;
    $post_type_table_name = $wpdb->prefix . "post_type";
    
    if(isset($_GET['id'])) {
        $id = $_GET['id'];
        $post_type_edit = $wpdb->get_row('SELECT * FROM '. $post_type_table_name .' WHERE id = '. $id );
        $post_type_edit = json_decode( json_encode($post_type_edit), true);
    }
    
    if(isset($_POST['save'])) {
        $post_type_name = $_POST['post_type_name'];
        $post_type_slug = $_POST['post_type_slug'];
        $dizeno_post_type = $_POST['dizeno_post_type'];
        $dizeno_category_list = $_POST['dizeno_category_list'];
        $dizeno_tag_list = $_POST['dizeno_tag_list'];
        if (isset($_POST['dizeno_category']) && $_POST['dizeno_category'] == 'on') {
            $dizeno_category = 'Yes';
            $dizeno_category_list = $_POST['dizeno_category_list'];
        } else {
            $dizeno_category = 'No';
            $dizeno_category_list = '';
        }

        if (isset($_POST['dizeno_tag']) && $_POST['dizeno_tag'] == 'on') {
            $dizeno_tag = 'Yes';
            $dizeno_tag_ist = $_POST['dizeno_tag_ist'];
        } else {
            $dizeno_tag = 'No';
            $dizeno_tag_ist = '';
        }
        
        $wpdb->update( $post_type_table_name, array('title'=>$post_type_name,  
                                                    'post_type_slug'=>  strtolower($post_type_slug), 
                                                    'dizeno_post_type' => strtolower($dizeno_post_type),
                                                    'dizeno_category' => $dizeno_category,
                                                    'dizeno_category_list' => $dizeno_category_list,
                                                    'dizeno_tag' => $dizeno_tag,
                                                    'dizeno_tag_list' => $dizeno_tag_list
                                                    ), 
                                            array( 'id' => $id ),
                                            array('%s','%s','%s','%s','%s','%s','%s')
                                           
                    );
        $post_type_edit = $wpdb->get_row('SELECT * FROM '. $post_type_table_name .' WHERE id = '. $id );
        $post_type_edit = json_decode( json_encode($post_type_edit), true);
    }
   
    
    ?>
        <div class="wrap">
            <h2>Settings</h2>
            <br class="clear">
            <div id="col-container">
                <div id="col-right"></div>
                <div id="col-left">
                    <div class="col-wrap">
                        <div class="form-wrap">
                            <h3>Edit Post Type</h3>
                            <form action="?page=post_type_settings&id=<?php echo $id; ?>" method='post' >
                                <div class="form-field term-name-wrap">
                                    <label for="post_type_name">Name</label>
                                    <input id="post_type_name" type="text" size="40" value="<?php echo $post_type_edit['title']; ?>" name="post_type_name">
                                    <p>The name of your product or post type.</p>
                                </div>
                                
                                <div class="form-field term-slug-wrap">
                                    <label for="post_type_slug">Slug</label>
                                    <input type="text" size="40" value="<?php echo $post_type_edit['post_type_slug']; ?>" id="post_type_slug" name="post_type_slug">
                                    <p>The “slug” is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.</p>
                                </div>
                                
                                <div class="form-field term-type-wrap">
                                    <label for="dizeno_post_type">Post Type</label>
                                    <select id="dizeno_post_type" name="dizeno_post_type">
                                        <?php 
                                            if($post_type_edit['dizeno_post_type'] == '') {
                                                echo '<option value="product" selected>Product</option>';
                                                echo '<option value="place">Place</option>';
                                                echo '<option value="event">Event</option>';
                                            }
                                            else {?>
                                                <option value="<?php echo $post_type_edit['dizeno_post_type']; ?>" selected="selected"><?php echo $post_type_edit['dizeno_post_type']; ?></option>
                                           <?php }
                                        ?>
                                        
                                        <?php 
                                            if($post_type_edit['dizeno_post_type'] == 'product') {
                                                echo '<option value="place">Place</option>';
                                                echo '<option value="event">Event</option>';
                                            } elseif ($post_type_edit['dizeno_post_type'] == 'place') {
                                                echo '<option value="product">Product</option>';
                                                echo '<option value="event">Event</option>';
                                            } elseif ($post_type_edit['dizeno_post_type'] == 'event') {
                                                echo '<option value="product">Product</option>';
                                                echo '<option value="event">Place</option>';
                                            }
                                        ?>
                                    </select>
                                    <p>The “slug” is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.</p>
                                </div>

                                <div class="form-field term-taxonomy-wrap">
                                    <label for="dizeno_post_type_taxonomy">Add Taxonomy:</label>
                                    <ul>
                                        <li id="dizeno_category_li">
                                            <label><input type="checkbox" id="dizeno_category" name="dizeno_category" <?php if($post_type_edit['dizeno_category'] == 'Yes') { echo "checked"; }  ?>> Category</label>
                                            <div>
                                                <input type="text" name="dizeno_category_list" id="dizeno_category_list" value="<?php echo $post_type_edit['dizeno_category_list']; ?>">
                                                <p>It will create default category. If you want to add custom category then type in this text box and separate custom category (non hierarchical list) with commas.</p>
                                            </div>
                                        </li>
                                        <li id="dizeno_tag_li">
                                            <label><input type="checkbox" id="dizeno_tag" name="dizeno_tag" <?php if($post_type_edit['dizeno_tag'] == 'Yes') { echo "checked"; }  ?>> Tag</label>
                                            <div>
                                                <input type="text" name="dizeno_tag_list" id="dizeno_tag_list" value="<?php echo $post_type_edit['dizeno_tag_list']; ?>">
                                                <p>It will create default tag. If you want to add custom tag then type in this text box and separate custom tags (hierarchical list) with commas.</p>
                                            </div>                                            
                                        </li>
                                    </ul>
                                    <p>The name of your product or post type.</p>
                                </div>
                                <input type="hidden" name="save" value="save"/>
                                <input type="submit" name="submit" value="Save" class="button button-primary">
                                <a href="?page=dizeno-settings" class="button button-secondary">Back</a>
                            </form>
                            
                        </div>
                    </div>
                </div>
            </div>
            
            
            
           
        </div>
        
        
    <?php
        

}



add_action('init', 'dizeno_create_custom_post_type_init',1);
function dizeno_create_custom_post_type_init() {
    global $wpdb;
    $post_type_table_name = $wpdb->prefix . "post_type";

    $create_post_type_titles = $wpdb->get_col('SELECT title FROM '. $post_type_table_name );

    
    
    foreach ($create_post_type_titles as $post_title) {
        $custom_post_type_labels = array(
            'name' => _x($post_title, 'post type general name'),
            'singular_name' => _x($post_title, 'post type singular name'),
            'all_items' => __('All '. $post_title),
            'add_new' => _x('Add new '. strtolower($post_title), strtolower($post_title)),
            'add_new_item' => __('Add new '.strtolower($post_title)),
            'edit_item' => __('Edit '.strtolower($post_title)),
            'new_item' => __('New '.strtolower($post_title)),
            'view_item' => __('View '.strtolower($post_title)),
            'search_items' => __('Search in '.strtolower($post_title)),
            'not_found' =>  __('No '.strtolower($post_title).' found'),
            'not_found_in_trash' => __('No '.strtolower($post_title).' found in trash'), 
            'parent_item_colon' => ''
        );
        $args = array(
                'labels' => $custom_post_type_labels,
                'public' => true,
                'publicly_queryable' => true,
                'show_ui' => true, 
                'query_var' => true,
                'rewrite' => true,
                'capability_type' => 'post',
                'hierarchical' => false,
                'menu_position' => 5,
                'supports' => array('title','editor','author','thumbnail','excerpt','comments','custom-fields'),
		'has_archive' => 'archive-name'
        ); 
        register_post_type($post_title,$args);
    }

}



function create_custom_post_type_templates() {
    global $wpdb;
    $post_type_table_name = $wpdb->prefix . "post_type";
    $create_single_template = $wpdb->get_col("SELECT title FROM ". $post_type_table_name);
    
    
    foreach ($create_single_template as $key) {
        // echo "<pre>";
        // echo get_template_directory().'<br>';
        $key = strtolower($key);
        $single_template_name = 'single-'.$key.'.php';
        // echo "<br>";
        $archive_template_name = 'archive-'.$key.'.php';
        // echo "<br>";
        $content_template_name = 'content-'.$key.'.php';
        // echo "<br>";

        $template_names = array($single_template_name,$archive_template_name,$content_template_name);
        // echo print_r($template_names);
        // echo "<br>";
            
            $template_dir = dirname(dirname( __FILE__ )) . '/dizeno-posts/'.$key;
            
            foreach ($template_names as $template) {
                if(file_exists($template_dir.'/'.$template)) {
                    $file_permission = 'r';
                } else {
                    $file_permission = 'w';
                }
                if (wp_mkdir_p($template_dir)) {
                    if ($handle = fopen($template_dir.'/'.$template, $file_permission)) {
                        
                        fclose($handle);
                    } else {
                        echo "Could not create/open ".$template." file for writing.";
                    }

                }
            }
        
        // echo "</pre>";
    }
}

add_action('init', 'create_custom_post_type_templates' );

function get_single_custom_post_type_template($single_template) {
    global $post;
    global $wpdb;
    $post_type_table_name = $wpdb->prefix . "post_type";
    $create_single_template = $wpdb->get_col("SELECT title FROM ". $post_type_table_name);
    foreach ($create_single_template as $key) {
        $key = strtolower($key);
        if ($post->post_type == $key) {
            $single_template = dirname(dirname( __FILE__ )) . '/dizeno-posts/'.$key.'/single-'.$key.'.php';
            return $single_template;
        }
    }
}
add_filter( 'single_template', 'get_single_custom_post_type_template' ); 



function get_archive_custom_post_type_template( $archive_template ) {
    global $post;
    global $wpdb;
    $post_type_table_name = $wpdb->prefix . "post_type";
    $create_archive_template = $wpdb->get_col("SELECT title FROM ". $post_type_table_name);
    foreach ($create_archive_template as $key) {
        $key = strtolower($key);
        if ( $post->post_type == $key) {
            $archive_template = dirname(dirname( __FILE__ )) . '/dizeno-posts/'.$key.'/archive-'.$key.'.php';
            return $archive_template;
        }
    }
    
}

add_filter( 'archive_template', 'get_archive_custom_post_type_template' ) ;


// Add custom taxonomies
add_action( 'init', 'dizeno_create_taxonomies',2);

function dizeno_create_taxonomies() {
    global $wpdb;
    $post_type_table_name = $wpdb->prefix . "post_type";
    // default category
    $create_taxonomy_titles = $wpdb->get_col("SELECT title FROM ". $post_type_table_name ." WHERE dizeno_category='Yes'");
    for ($i=0; $i < count($create_taxonomy_titles); $i++) { 
        foreach ($create_taxonomy_titles as $taxonomy_title_default) {
            $taxonomy_title_lower = strtolower($taxonomy_title_default);
            $category_labels = array(
                'name' => _x( $taxonomy_title_default.' Category', 'taxonomy general name' ),
                'singular_name' => _x( $taxonomy_title_default.' Category', 'taxonomy singular name' ),
                'search_items' =>  __( 'Search in '.$taxonomy_title_lower.' category' ),
                'all_items' => __( 'All '.$taxonomy_title_lower.' category' ),
                'most_used_items' => null,
                'parent_item' => null,
                'parent_item_colon' => null,
                'edit_item' => __( 'Edit '.$taxonomy_title_lower.' category' ), 
                'update_item' => __( 'Update '.$taxonomy_title_lower.' category' ),
                'add_new_item' => __( 'Add new '.$taxonomy_title_lower.' category' ),
                'new_item_name' => __( 'New '.$taxonomy_title_lower.' category' ),
                'menu_name' => __( $taxonomy_title_default.' Category' ),
            );  
            register_taxonomy($taxonomy_title_lower.'-category',$taxonomy_title_lower,array(
                'hierarchical' => true,
                'labels' => $category_labels,
                'show_ui' => true,
                'query_var' => true,
                'rewrite' => array('slug' => $taxonomy_title_lower.'-category' )
            ));
        }
    }

    // Custom category
    $cat_titles = $wpdb->get_results("SELECT title, dizeno_category_list FROM ". $post_type_table_name ." WHERE   dizeno_category='Yes'");
    $cat_titles = json_decode(json_encode($cat_titles),true);

    for ($i=0; $i < count($cat_titles); $i++) { 
            $taxonomy_title = $cat_titles[$i]['title'];

            $cat_array = explode(",", $cat_titles[$i]['dizeno_category_list']);
            for ($j=0; $j < count($cat_array); $j++) {
                $cat = $cat_array[$j];
                if ($cat!='') {
                    $taxonomy_title_lower = strtolower($taxonomy_title);
                    $taxonomy_cat_lower = strtolower($cat);

                    $category_labels = array(
                        'name' => _x( $cat.' Category', 'taxonomy general name' ),
                        'singular_name' => _x( $cat.' Category', 'taxonomy singular name' ),
                        'search_items' =>  __( 'Search in '.$taxonomy_cat_lower.' category' ),
                        'all_items' => __( 'All '.$taxonomy_cat_lower.' category' ),
                        'most_used_items' => null,
                        'parent_item' => null,
                        'parent_item_colon' => null,
                        'edit_item' => __( 'Edit '.$taxonomy_cat_lower.' category' ), 
                        'update_item' => __( 'Update '.$taxonomy_cat_lower.' category' ),
                        'add_new_item' => __( 'Add new '.$taxonomy_cat_lower.' category' ),
                        'new_item_name' => __( 'New '.$taxonomy_cat_lower.' category' ),
                        'menu_name' => __( $cat.' Category' ),
                    );  
                    register_taxonomy($taxonomy_cat_lower.'-category',$taxonomy_title_lower,array(
                        'hierarchical' => true,
                        'labels' => $category_labels,
                        'show_ui' => true,
                        'query_var' => true,
                        'rewrite' => array('slug' => $taxonomy_cat_lower.'-category' )
                    ));
                }
            }

    }

    


    $create_tag_titles = $wpdb->get_col("SELECT title FROM ". $post_type_table_name . " WHERE dizeno_tag='Yes'");
    // Default Tag
    for ($i=0; $i < count($create_tag_titles); $i++) { 
        foreach ($create_tag_titles as $taxonomy_title) {
            $taxonomy_title_lower = strtolower($taxonomy_title);
            $tag_labels = array(
                'name' => _x( $taxonomy_title.' Tag', 'taxonomy general name' ),
                'singular_name' => _x( $taxonomy_title.' Tag', 'taxonomy singular name' ),
                'search_items' =>  __( 'Search in '.$taxonomy_title_lower.' tags' ),
                'popular_items' => __( 'Popular '.$taxonomy_title_lower.' tags' ),
                'all_items' => __( 'All '.$taxonomy_title_lower.' tags' ),
                'most_used_items' => null,
                'parent_item' => null,
                'parent_item_colon' => null,
                'edit_item' => __( 'Edit '.$taxonomy_title_lower.' tag' ), 
                'update_item' => __( 'Update '.$taxonomy_title_lower.' tag' ),
                'add_new_item' => __( 'Add new '.$taxonomy_title_lower.' tag' ),
                'new_item_name' => __( 'New '.$taxonomy_title_lower.' tag name' ),
                'separate_items_with_commas' => __( 'Separate '.$taxonomy_title_lower.' tags with commas' ),
                'add_or_remove_items' => __( 'Add or remove '.$taxonomy_title_lower.' tags' ),
                'choose_from_most_used' => __( 'Choose from the most used '.$taxonomy_title_lower.' tags' ),
                'menu_name' => __( $taxonomy_title.' Tag' ),
            );
            register_taxonomy($taxonomy_title_lower.' tag',$taxonomy_title_lower,array(
                'hierarchical' => false,
                'labels' => $tag_labels,
                'show_ui' => true,
                'update_count_callback' => '_update_post_term_count',
                'query_var' => true,
                'rewrite' => array('slug' => $taxonomy_title_lower.'-tag' )
            ));
        }
    }

    // Custom tags
    $tag_titles = $wpdb->get_results("SELECT title, dizeno_tag_list FROM ". $post_type_table_name ." WHERE   dizeno_tag='Yes'");

    $tag_titles = json_decode(json_encode($tag_titles),true);
    
    for ($i=0; $i < count($tag_titles); $i++) { 
            $taxonomy_tag_title = $tag_titles[$i]['title'];

            $tag_array = explode(",", $tag_titles[$i]['dizeno_tag_list']);
            for ($j=0; $j < count($tag_array); $j++) {
                $tag = $tag_array[$j];
                if ($tag!='') {
                    $taxonomy_tag_title_lower = strtolower($taxonomy_tag_title);
                    $taxonomy_tag_lower = strtolower($tag);

                    $tag_labels = array(
                        'name' => _x( $tag.' Tag', 'taxonomy general name' ),
                        'singular_name' => _x( $tag.' Tag', 'taxonomy singular name' ),
                        'search_items' =>  __( 'Search in '.$taxonomy_tag_lower.' tags' ),
                        'popular_items' => __( 'Popular '.$taxonomy_tag_lower.' tags' ),
                        'all_items' => __( 'All '.$taxonomy_tag_lower.' tags' ),
                        'most_used_items' => null,
                        'parent_item' => null,
                        'parent_item_colon' => null,
                        'edit_item' => __( 'Edit '.$taxonomy_tag_lower.' tag' ), 
                        'update_item' => __( 'Update '.$taxonomy_tag_lower.' tag' ),
                        'add_new_item' => __( 'Add new '.$taxonomy_tag_lower.' tag' ),
                        'new_item_name' => __( 'New '.$taxonomy_tag_lower.' tag name' ),
                        'separate_items_with_commas' => __( 'Separate '.$taxonomy_tag_lower.' tags with commas' ),
                        'add_or_remove_items' => __( 'Add or remove '.$taxonomy_tag_lower.' tags' ),
                        'choose_from_most_used' => __( 'Choose from the most used '.$taxonomy_tag_lower.' tags' ),
                        'menu_name' => __( $tag.' Tag' ),
                    );
                    register_taxonomy($taxonomy_tag_lower.' tag',$taxonomy_tag_title_lower,array(
                        'hierarchical' => false,
                        'labels' => $tag_labels,
                        'show_ui' => true,
                        'update_count_callback' => '_update_post_term_count',
                        'query_var' => true,
                        'rewrite' => array('slug' => $taxonomy_tag_lower.'-tag' )
                    ));
                }
            }

    }


}










/* Custom List Table */
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
class Dizeno_Post_Type_List_tabel extends WP_List_Table {
    
    function __construct(){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'post_type',     //singular name of the listed records
            'plural'    => 'posts_type',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
        
    }

    function column_default($item, $column_name){
        switch($column_name){
            case 'id':
            case 'post_type_slug':
            case 'dizeno_post_type':
            case 'dizeno_category':
            case 'dizeno_tag':
                return $item[$column_name];
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }
    
    function column_title($item){
        
        //Build row actions
        $actions = array(
            'edit'      => sprintf('<a href="?page=post_type_settings&action=%s&id=%s">Edit</a>','edit',$item['id']),
            'delete'    => sprintf('<a href="?page=%s&action=%s&id=%s">Delete</a>',$_REQUEST['page'],'delete',$item['id']),
        );
        
        //Return the title contents
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            /*$1%s*/ $item['title'],
            /*$2%s*/ $item['id'],
            /*$3%s*/ $this->row_actions($actions)
        );
    }

    function column_cb($item){
        return sprintf(
            
            '<input type="checkbox" name="%1$s" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['id']                //The value of the checkbox should be the record's id
        );
    }

    function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
            'title'     => 'Custom Post Name',
            'id'    => 'Id',
            'post_type_slug' => 'Slug',
            'dizeno_post_type' => 'Post Type',
            'dizeno_category' => 'Category',
            'dizeno_tag' => 'Tag'
        );
        return $columns;
    }

    function get_sortable_columns() {
        $sortable_columns = array(
            'title'     => array('title',false),     //true means it's already sorted
            'id'    => array('id',false),
            'post_type_slug' => array('post_type_slug',false),
            'dizeno_post_type' => array('dizeno_post_type',false),
            'dizeno_category' => array('dizeno_category',false),
            'dizeno_tag' => array('dizeno_tag',false)
        );
        return $sortable_columns;
    }

    function get_bulk_actions() {
        $actions = array(
            'delete'    => 'Delete'
        );
        return $actions;
    }

    function process_bulk_action() {
        global $wpdb; //This is used only if making any database queries

        $post_type_table_name = $wpdb->prefix . "post_type";
        
        //Detect when a bulk action is being triggered...
        if( 'delete'===$this->current_action() ) {
            $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
            if (is_array($ids)) $ids = implode(',', $ids);

            if (!empty($ids)) {
                $wpdb->query("DELETE FROM $post_type_table_name WHERE id IN($ids)");
            }
        }
        
    }

    function prepare_items() {
        global $wpdb; //This is used only if making any database queries

        $post_type_table_name = $wpdb->prefix . "post_type";
    
        $per_page = 5;
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
    
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        
        $this->process_bulk_action();
        
        
        $data = $wpdb->get_results( 'SELECT * FROM '. $post_type_table_name .' ORDER BY title ASC');
        $data = json_decode( json_encode($data), true);

        
        $current_page = $this->get_pagenum();
        
        $total_items = count($data);
        
        
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        
        
        
        $this->items = $data;
        
        
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }
}


?>



