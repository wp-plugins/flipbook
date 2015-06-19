<?php 

class WP_FlipBook {

  private $path = null;

  public function __construct( $path ) {
    
    global $post;
    $this->path = $path;
    
    //Create custom post type
    add_action( 'init', array( &$this, 'init_flipbook' ) );
    
    //Create flipbook metaboxes
    add_action( 'add_meta_boxes', array( &$this, 'add_meta_boxes' ) );
    
    //Save data
    add_action ( 'save_post', array( &$this, 'save_data' ) );
  
    //Include admin scripts
    add_action( 'admin_enqueue_scripts', array( &$this, 'include_admin_scripts' ) );
    
    //Include frontend scripts
    add_action( 'wp_enqueue_scripts', array( &$this, 'include_frontend_scripts' ), 100 );
    
    //Add shortcode
    add_shortcode ( 'wp-flipbook', array( &$this, 'process_shortcode') );
    
    //Add messages
    add_filter( 'post_updated_messages', array( &$this, 'modify_messages' ) );
    
    //Add shortcode column to flipbook admin
    add_filter( 'manage_posts_columns', array( &$this, manage_flipbook_columns ) );
    add_filter( 'manage_posts_custom_column', array( &$this, manage_flipbook_custom_columns ), 10, 2);
  }

  function modify_messages($messages) {
    global $post;
    
    if ( get_post_type() != 'wp-flipbook' ) {
      return $messages;
    }
    
    $messages['wp-flipbook'] = array(
      "Shortcode is [wp-flipbook id={$post->ID}]",
      "Flipbook updated. Shortcode is [wp-flipbook id={$post->ID}]",
      "Custom field updated.",
      "Custom field deleted.",
      "Flipbook updated. Shortcode is [wp-flipbook id={$post->ID}]",
      "Shortcode is [wp-flipbook id={$post->ID}]",
      "Flipbook published. Shortcode is [wp-flipbook id={$post->ID}]",
      "Flipbook saved. Shortcode is [wp-flipbook id={$post->ID}]",
      "Flipbook submitted",
      "Flipbook scheduled",
      "Flipbook draft updated"
    );
    
    return $messages;
  }
  
  function process_shortcode($atts) {
    extract( $atts );
    
    $meta = get_post_custom($id);
    $pages = maybe_unserialize( $meta['wp_flipbook_pages'][0] );
    $properties = maybe_unserialize( $meta['wp_flipbook_metas'][0] );
    if ( empty( $pages ) ) {
      echo "Flipbook is empty or it doesn't exist.";
      return;
    }
    
    ?>
    <div class="struct-flipbook" style="<?php echo !$properties['wp-flipbook-margin-top'] || $properties['wp-flipbook-margin-top'] == '' ? 'margin-top:50px;' : 'margin-top:' . $properties['wp-flipbook-margin-top'] . 'px;'; echo !$properties['wp-flipbook-margin-bottom'] || $properties['wp-flipbook-margin-bottom'] == '' ? 'margin-bottom:150px;' : 'margin-bottom:' . $properties['wp-flipbook-margin-bottom'] . 'px;'; ?>">
      <?php if( $properties['wp-flipbook-navigation'] == 'true') : ?>
        <nav>
          <ul>
            <li><a id='first' href="#" title='goto first page'>First page</a></li>
            <li><a id='back' href="#" title='go back one page'>Back</a></li>
            <li><a id='next' href="#" title='go foward one page'>Next</a></li>
            <li><a id='last' href="#" title='goto last page'>last page</a></li>
            <li><a id='zoomin' href="#" title='zoom in'>Zoom In</a></li>
            <li><a id='zoomout' href="#" title='zoom out'>Zoom Out</a></li>
          </ul>
        </nav>
      <?php endif; ?>
      <div id='features'>
        <?php 
          $total = count( $pages );
          foreach ( $pages as $key => $page ) :
            $image = wp_get_attachment_image_src( $page, 'large' ); 
            if( $key == 0 ) : 
        ?>
              <div id='cover'>
                <img src="<?php echo $image[0] ?>" alt=""/>
              </div>
        <?php 
            endif;
            if($key > 0 && ($key+1) < $total) :
        ?>
              <div class="feature">
                <img src="<?php echo $image[0] ?>" alt=""/>
              </div>
        <?php 
            endif;
            if( $total == ( $key + 1 )) : ?>
              <div class='last_cover'>
                <img src="<?php echo $image[0] ?>" alt=""/>
              </div>
        <?php 
            endif; 
          endforeach 
        ?>
      </div> 
    </div>
    <script type="text/javascript">
    jQuery(document).ready(function() {
      jQuery('#features').wowBook({
         height : <?php echo !$properties['wp-flipbook-height'] || $properties['wp-flipbook-height'] == '' ? 625 : $properties['wp-flipbook-height']; ?>
        ,width  : <?php echo !$properties['wp-flipbook-width'] || $properties['wp-flipbook-width'] == '' ? 980 : $properties['wp-flipbook-width']; ?>
        ,centeredWhenClosed : <?php echo !$properties['wp-flipbook-center-close'] ? 'true' : $properties['wp-flipbook-center-close']; ?>
        ,hardcovers : <?php echo !$properties['wp-flipbook-hardcovers'] ? 'true' : $properties['wp-flipbook-hardcovers']; ?>
        ,turnPageDuration : 1000
        ,numberedPages : [1,-2]
        ,controls : {
            zoomIn    : '#zoomin',
            zoomOut   : '#zoomout',
            next      : '#next',
            back      : '#back',
            first     : '#first',
            last      : '#last',
            slideShow : '#slideshow',
            flipSound : '#flipsound'
          }
      }).css({'display':'none', 'margin':'auto'}).fadeIn(1000);

      jQuery("#cover").click(function(){
        jQuery.wowBook("#features").advance();
      });
    });
  </script>
    <?php
  }

  function include_admin_scripts() {
    if ( get_post_type() != 'wp-flipbook' ) {
      return;
    }
  
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'jquery-ui-sortable' );
    
    wp_enqueue_media();
    wp_dequeue_script( 'autosave' );
  }

  function include_frontend_scripts() {
  
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'jquery-ui-core' );
    wp_enqueue_script( 'jquery-ui-widget' );
    wp_enqueue_script( 'jquery-ui-mouse' );
    wp_enqueue_script( 'jquery-ui-draggable' );
    
    wp_enqueue_script( 'wow_book', $this->path . 'js/wow_book.min.js');
    wp_enqueue_style( 'preview', $this->path . 'css/preview.css');
  }

  function manage_flipbook_custom_columns( $column, $id ) {
    if ( get_post_type() != 'wp-flipbook' ) {
      return;
    }
    
    switch( $column ) {
      case 'shortcode' : 
        echo "[wp-flipbook id={$id}]";
        break;
    }
  }

  function manage_flipbook_columns( $columns ) {
    if ( get_post_type() != 'wp-flipbook' ) {
      return $columns;
    }
  
    $columns = array_merge( $columns, array( 'shortcode' => 'Shortcode' ) );
    unset( $columns['date'] );
    
    return $columns;
  }

  function save_data($post_id) {
    
    if ( empty( $_POST ) ) {
      return;
    } 
    
    //Salvando os dados do formulário de Páginas - Imagens do Flipbook
    if ( !empty( $_POST['wp-flipbook-attachment'] ) ) {
      foreach ( $_POST['wp-flipbook-attachment'] as $key => $attachment ) {
        if ( !empty($attachment) ) {
          $pages[] = sanitize_text_field( $attachment );
        }
      }
      delete_post_meta( $post_id, 'wp_flipbook_pages' );
      update_post_meta( $post_id, 'wp_flipbook_pages', $pages );
    }
    
    //Salvando os dados do formulário de configuração
    if ( !empty( $_POST['wp-flipbook-metas'] ) ) {
      
      $properties['wp-flipbook-width'] = sanitize_text_field( $_POST['wp-flipbook-metas']['wp-flipbook-width'] );
      $properties['wp-flipbook-height'] = sanitize_text_field( $_POST['wp-flipbook-metas']['wp-flipbook-height'] );
      $properties['wp-flipbook-navigation'] = sanitize_text_field( $_POST['wp-flipbook-metas']['wp-flipbook-navigation'] );
      $properties['wp-flipbook-margin-top'] = sanitize_text_field( $_POST['wp-flipbook-metas']['wp-flipbook-margin-top'] );
      $properties['wp-flipbook-margin-bottom'] = sanitize_text_field( $_POST['wp-flipbook-metas']['wp-flipbook-margin-bottom'] );
      $properties['wp-flipbook-hardcovers'] = sanitize_text_field( $_POST['wp-flipbook-metas']['wp-flipbook-hardcovers'] );
      $properties['wp-flipbook-center-close'] = sanitize_text_field( $_POST['wp-flipbook-metas']['wp-flipbook-center-close'] );
      
        
      delete_post_meta( $post_id, 'wp_flipbook_metas' );
      update_post_meta( $post_id, 'wp_flipbook_metas', $properties );
    }
  }

  // *************************************************
  // *************************************************
  // **                                             **  
  // ** Functions de Inicialização                  **
  // **                                             **
  // **  * Adiciona Menu Principal como post_type   **
  // **  * Adiciona os Meta Box                     **
  // **    - Páginas (Imagens do Flip Book)         **
  // **    - Configuração do Plugin.                **
  // **  * Construi os formulários de Cadastros     **
  // **                                             **
  // *************************************************
  // *************************************************

  function add_meta_boxes() {
    
    if ( get_post_type() != 'wp-flipbook' ) {
      return;
    }
    
    //Criando Metabox para adição de páginas - Imagens do flipbook
    add_meta_box(
      'flipbook-pages-metabox',
      'FlipBook Pages',
      array( &$this, 'create_pages_metabox' ),
      'wp-flipbook',
      'normal',
      'high'
    );
    
    //Criando Metabox para Configurações
    add_meta_box(
      'flipbook-properties-metabox',
      'FlipBook Properties',
      array( &$this, 'create_properties_metabox' ),
      'wp-flipbook',
      'side',
      'low'
    );

  }

  function create_pages_metabox( $post ) {
    
    if ( get_post_type() != 'wp-flipbook' ) {
      return;
    }
    
    $meta = get_post_custom( $post->ID );
    $pages = maybe_unserialize( $meta['wp_flipbook_pages'][0] );
  
  ?>
    <link rel="stylesheet" href="<?php echo $this->path ?>css/style.css">
    <script type="text/javascript" src="<?php echo $this->path ?>js/jquery-admin.js"></script>
    <div class="wp-flipbook-sortable">
      <?php if ( $pages ) : ?>
        <?php foreach( $pages as $page ) : ?>
          <div class="wp-flipbook-portlet">
            <div class="wp-flipbook-portlet-header">
              <span>Página</span>
              <span class="wp-flipbook-portlet-header-buttons">
                <span class="wp-flipbook-header-visibility"></span>
                <span class="wp-flipbook-header-remove"></span>
              </span>
            </div>
            <div class="wp-flipbook-portlet-content" style="display:block">
              <?php $image = wp_get_attachment_image_src( $page, $size, $icon ); ?> 
              <img src="<?php echo $image[0] ?>" class="wp-flipbook-img"/>
              <input class="wp-flipbook-attachment-id" value="<?php echo $page ?>" name="wp-flipbook-attachment[]" type="hidden" />
              <input class="button-secondary wp-flipbook-image-upload" type="button" value="Replace image" />
            </div>
          </div>
        <?php endforeach ?>
      <?php endif ?>
    </div>
    <a class="button wp-flipbook-sortable-add-page">Adicionar Página</a>
    <?php
  }

  function create_properties_metabox( $post ) {
    
    $meta = get_post_custom($post->ID);
    $properties = maybe_unserialize( $meta['wp_flipbook_metas'][0] );
    
    ?>
    <p>
      <label>Exibir Navegação</label>
      <select class="widefat" name="wp-flipbook-metas[wp-flipbook-navigation]">
        <option <?php if ( $properties["wp-flipbook-navigation"] == "true" ) { echo "selected='selected'"; } ?> value="true">Sim</option>
        <option <?php if ( $properties["wp-flipbook-navigation"] == "false" || !$properties["wp-flipbook-navigation"] ) { echo  "selected='selected'"; } ?> value="false">Não</option>
      </select>
    </p>
    <p>
      <label>Margin Superior</label><br/>
      <input size="18" type="text" name="wp-flipbook-metas[wp-flipbook-margin-top]" value="<?php echo $properties['wp-flipbook-margin-top'] ? $properties['wp-flipbook-margin-top'] : 50  ?>"/> pixels
    </p>
    <p>
      <label>Margin Inferior</label><br/>
      <input size="18" type="text" name="wp-flipbook-metas[wp-flipbook-margin-bottom]" value="<?php echo $properties['wp-flipbook-margin-bottom'] ? $properties['wp-flipbook-margin-bottom'] : 150  ?>"/> pixels
    </p>
    <p>
      <label>Page width</label><br/>
      <input size="18" type="text" name="wp-flipbook-metas[wp-flipbook-width]" value="<?php echo $properties['wp-flipbook-width'] ? $properties['wp-flipbook-width'] : 980  ?>"/> pixels
    </p>
    <p>
      <label>Page height</label><br/>
      <input size="18" type="text" name="wp-flipbook-metas[wp-flipbook-height]" value="<?php echo $properties['wp-flipbook-height'] ? $properties['wp-flipbook-height'] : 625 ?>"/> pixels
    </p>
    <p>
      <label>Capa Dura</label>
      <select class="widefat" name="wp-flipbook-metas[wp-flipbook-hardcovers]">
        <option <?php if ( $properties["wp-flipbook-hardcovers"] == "true" ) { echo "selected='selected'"; } ?> value="true">Sim</option>
        <option <?php if ( $properties["wp-flipbook-hardcovers"] == "false" || !$properties["wp-flipbook-hardcovers"] ) { echo  "selected='selected'"; } ?> value="false">Não</option>
      </select>
    </p>
    <p>
      <label>Centralizar livro ao fechar</label>
      <select class="widefat" name="wp-flipbook-metas[wp-flipbook-center-close]">
        <option <?php if ( $properties["wp-flipbook-center-close"] == "true" ) { echo "selected='selected'"; } ?> value="true">Sim</option>
        <option <?php if ( $properties["wp-flipbook-center-close"] == "false" || !$properties["wp-flipbook-center-close"] ) { echo  "selected='selected'"; } ?> value="false">Não</option>
      </select>
    </p>

    <?php  
  }

  function init_flipbook() {
    
    $labels = array(
      'name' => _x( 'FlipBook', 'post type general name' ),
      'singular_name' => _x( 'FlipBook', 'post type singular name' ),
      'add_new' => _x( 'Adicionar FlipBook', 'FlipBook' ),
      'add_new_item' => __( 'Adicionar FlipBook' ),
      'edit_item' => __( 'Editar FlipBook' ),
      'new_item' => __( 'Novo FlipBook' ),
      'all_items' => __( 'Gerenciar FlipBooks' ),
      'view_item' => __( 'Visualizar' ),
      'search_items' => __( 'Buscar' ),
      'not_found' => __( 'Nenhum FlipBook localizado' ),
      'not_found_in_trash' => __( 'Nenhum FlipBook na lixeira' ),
      'parent_item_colon' => '',
      'menu_name' => __( 'FlipBook' )
    );
     
    $args = array(
      'labels' => $labels,
      'public' => false,
      'publicly_queryable' => true,
      'show_ui' => true,
      'show_in_menu' => true,
      'query_var' => true,
      'rewrite' => true,
      'capability_type' => 'post',
      'has_archive' => true,
      'hierarchical' => false,
      'menu_position' => null,
      'supports' => array( 'title' ),
      'menu_icon' => $this->path . 'images/pageflip.gif',
    );

    register_post_type( 'wp-flipbook', $args );
  
  }
} 
?>