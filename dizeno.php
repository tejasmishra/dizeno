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


include_once(ABSPATH . 'wp-content/plugins/dizeno/posttypes.php');

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

    if( $wpdb->get_var('SHOW TABLES LIKE ' . $post_type_table_name) != $post_type_table_name ) {
        $sql = 'CREATE TABLE ' . $post_type_table_name . '('
                . 'id BIGINT(20) UNSIGNED AUTO_INCREMENT, '
                . 'title VARCHAR(255) NOT NULL, '
                . 'post_type_slug VARCHAR(20) NOT NULL, '
                . 'dizeno_post_type VARCHAR(10) NOT NULL '
                . 'PRIMARY KEY (id) ) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci';
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        add_option('post_type_database_version','1.0');
    }
    
}
register_activation_hook(__FILE__, 'dizeno_activate');


// Add menu For Dizeno
add_action( 'admin_menu', 'register_dizeno_settings_page' );
function register_dizeno_settings_page() {
        //add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
	add_menu_page( 'Dizeno', 'Dizeno', 'manage_options', 'dizeno-settings', 'dizeno_settings_page', 'dashicons-admin-generic', 4 );
        //add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
        add_submenu_page( 'dizeno-settings', 'Post Type Settings', 'Post Type Settings', 'manage_options', 'post_type_settings', 'post_type_settings_page' );   
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
        $wpdb->insert($post_type_table_name, array(
                                                'title'=>$post_title, 
                                                'post_type_slug'=>  strtolower($post_title),
                                                'dizeno_post_type' => strtolower($dizeno_post_type)
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
            
            <form action='?page=dizeno-settings&tab=post-type' method='post' >
                Post Type:
                <input type="text" name="post_title" id="post-title">
                <select id="dizeno_post_type" name="dizeno_post_type">
                    <option value="product" selected="selected">Product</option>
                    <option value="place">Place</option>
                    <option value="event">Event</option>
		</select>
                <input type="submit" name="submit" value="Add" class="button button-primary">
            </form>
        </div>

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
                 $testListTable->display();                
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
<?php
}






/* Custom List Table */
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
class Dizeno_Post_Type_List_tabel extends WP_List_Table {
    
    /** ************************************************************************
     * Normally we would be querying data from a database and manipulating that
     * for use in your list table. For this example, we're going to simplify it
     * slightly and create a pre-built array. Think of this as the data that might
     * be returned by $wpdb->query().
     * 
     * @var array 
     **************************************************************************/


    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We 
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct(){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'post_type',     //singular name of the listed records
            'plural'    => 'posts_type',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
        
    }


    /** ************************************************************************
     * Recommended. This method is called when the parent class can't find a method
     * specifically build for a given column. Generally, it's recommended to include
     * one method for each column you want to render, keeping your package class
     * neat and organized. For example, if the class needs to process a column
     * named 'title', it would first see if a method named $this->column_title() 
     * exists - if it does, that method will be used. If it doesn't, this one will
     * be used. Generally, you should try to use custom column methods as much as 
     * possible. 
     * 
     * Since we have defined a column_title() method later on, this method doesn't
     * need to concern itself with any column with a name of 'title'. Instead, it
     * needs to handle everything else.
     * 
     * For more detailed insight into how columns are handled, take a look at 
     * WP_List_Table::single_row_columns()
     * 
     * @param array $item A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
    function column_default($item, $column_name){
        switch($column_name){
            case 'id':
            case 'post_type_slug':
            case 'dizeno_post_type':
                return $item[$column_name];
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }


    /** ************************************************************************
     * Recommended. This is a custom column method and is responsible for what
     * is rendered in any column with a name/slug of 'title'. Every time the class
     * needs to render a column, it first looks for a method named 
     * column_{$column_title} - if it exists, that method is run. If it doesn't
     * exist, column_default() is called instead.
     * 
     * This example also illustrates how to implement rollover actions. Actions
     * should be an associative array formatted as 'slug'=>'link html' - and you
     * will need to generate the URLs yourself. You could even ensure the links
     * 
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
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


    /** ************************************************************************
     * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
     * is given special treatment when columns are processed. It ALWAYS needs to
     * have it's own method.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_cb($item){
        return sprintf(
            
            '<input type="checkbox" name="%1$s" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['id']                //The value of the checkbox should be the record's id
        );
    }


    /** ************************************************************************
     * REQUIRED! This method dictates the table's columns and titles. This should
     * return an array where the key is the column slug (and class) and the value 
     * is the column's title text. If you need a checkbox for bulk actions, refer
     * to the $columns array below.
     * 
     * The 'cb' column is treated differently than the rest. If including a checkbox
     * column in your table you must create a column_cb() method. If you don't need
     * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
            'title'     => 'Custom Post Name',
            'id'    => 'Id',
            'post_type_slug' => 'Slug',
            'dizeno_post_type' => 'Post Type'
        );
        return $columns;
    }


    /** ************************************************************************
     * Optional. If you want one or more columns to be sortable (ASC/DESC toggle), 
     * you will need to register it here. This should return an array where the 
     * key is the column that needs to be sortable, and the value is db column to 
     * sort by. Often, the key and value will be the same, but this is not always
     * the case (as the value is a column name from the database, not the list table).
     * 
     * This method merely defines which columns should be sortable and makes them
     * clickable - it does not handle the actual sorting. You still need to detect
     * the ORDERBY and ORDER querystring variables within prepare_items() and sort
     * your data accordingly (usually by modifying your query).
     * 
     * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
     **************************************************************************/
    function get_sortable_columns() {
        $sortable_columns = array(
            'title'     => array('title',false),     //true means it's already sorted
            'id'    => array('id',false),
            'post_type_slug' => array('post_type_slug',false),
            'dizeno_post_type' => array('dizeno_post_type',false)
        );
        return $sortable_columns;
    }


    /** ************************************************************************
     * Optional. If you need to include bulk actions in your list table, this is
     * the place to define them. Bulk actions are an associative array in the format
     * 'slug'=>'Visible Title'
     * 
     * If this method returns an empty value, no bulk action will be rendered. If
     * you specify any bulk actions, the bulk actions box will be rendered with
     * the table automatically on display().
     * 
     * Also note that list tables are not automatically wrapped in <form> elements,
     * so you will need to create those manually in order for bulk actions to function.
     * 
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_bulk_actions() {
        $actions = array(
            'delete'    => 'Delete'
        );
        return $actions;
    }


    /** ************************************************************************
     * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
     * For this example package, we will handle it in the class to keep things
     * clean and organized.
     * 
     * @see $this->prepare_items()
     **************************************************************************/
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


    /** ************************************************************************
     * REQUIRED! This is where you prepare your data for display. This method will
     * usually be used to query the database, sort and filter the data, and generally
     * get it ready to be displayed. At a minimum, we should set $this->items and
     * $this->set_pagination_args(), although the following properties and methods
     * are frequently interacted with here...
     * 
     * @global WPDB $wpdb
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     **************************************************************************/
    function prepare_items() {
        global $wpdb; //This is used only if making any database queries

        $post_type_table_name = $wpdb->prefix . "post_type";
    
        

        /**
         * First, lets decide how many records per page to show
         */
        $per_page = 5;
        
        
        /**
         * REQUIRED. Now we need to define our column headers. This includes a complete
         * array of columns to be displayed (slugs & titles), a list of columns
         * to keep hidden, and a list of columns that are sortable. Each of these
         * can be defined in another method (as we've done here) before being
         * used to build the value for our _column_headers property.
         */
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        
        /**
         * REQUIRED. Finally, we build an array to be used by the class for column 
         * headers. The $this->_column_headers property takes an array which contains
         * 3 other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         */
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        
        /**
         * Optional. You can handle your bulk actions however you see fit. In this
         * case, we'll handle them within our package just to keep things clean.
         */
        $this->process_bulk_action();
        
        
        /**
         * Instead of querying a database, we're going to fetch the example data
         * property we created for use in this plugin. This makes this example 
         * package slightly different than one you might build on your own. In 
         * this example, we'll be using array manipulation to sort and paginate 
         * our data. In a real-world implementation, you will probably want to 
         * use sort and pagination data to build a custom query instead, as you'll
         * be able to use your precisely-queried data immediately.
         */
        // $data = $this->example_data;
        $data = $wpdb->get_results( 'SELECT * FROM '. $post_type_table_name .' ORDER BY title ASC');
        $data = json_decode( json_encode($data), true);

        
        /**
         * REQUIRED for pagination. Let's figure out what page the user is currently 
         * looking at. We'll need this later, so you should always include it in 
         * your own package classes.
         */
        $current_page = $this->get_pagenum();
        
        /**
         * REQUIRED for pagination. Let's check how many items are in our data array. 
         * In real-world use, this would be the total number of items in your database, 
         * without filtering. We'll need this later, so you should always include it 
         * in your own package classes.
         */
        $total_items = count($data);
        
        
        /**
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. We can use
         * array_slice() to 
         */
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        
        
        
        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where 
         * it can be used by the rest of the class.
         */
        $this->items = $data;
        
        
        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }


}


function post_type_settings_page() {
    global $wpdb;
    $post_type_table_name = $wpdb->prefix . "post_type";
    
    if(isset($_GET[id])) {
        $id = $_GET[id];
        $post_type_edit = $wpdb->get_row('SELECT * FROM '. $post_type_table_name .' WHERE id = '. $id );
        $post_type_edit = json_decode( json_encode($post_type_edit), true);
    }
    
    if(isset($_POST[save])) {
        $post_type_name = $_POST[post_type_name];
        $post_type_slug = $_POST[post_type_slug];
        $dizeno_post_type = $_POST[dizeno_post_type];
        $wpdb->update( $post_type_table_name, array('title'=>$post_type_name,  'post_type_slug'=>  strtolower($post_type_slug), 'dizeno_post_type' => strtolower($dizeno_post_type)), array( 'id' => $id ), array('%s','%s','%s'));
        $post_type_edit = $wpdb->get_row('SELECT * FROM '. $post_type_table_name .' WHERE id = '. $id );
        $post_type_edit = json_decode( json_encode($post_type_edit), true);
    }
   
    
    ?>
        <div class="wrap">
            <h2>Post Type Settings</h2>
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
                                <input type="hidden" name="save" value="save"/>
                                <input type="submit" name="submit" value="Save" class="button button-primary">
                            </form>
                            
                        </div>
                    </div>
                </div>
            </div>
            
            
            
           
        </div>
        
        
    <?php
        

}



add_action('init', 'dizeno_create_custom_post_type_init');
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
                'supports' => array('title','editor','comments')
        ); 
        register_post_type($post_title,$args);
    }

}




?>



