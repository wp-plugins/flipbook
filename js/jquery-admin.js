jQuery(document).ready( function() {
  /* Sortable */
  jQuery(".wp-flipbook-sortable").sortable();
  
  jQuery("body").on('click','.wp-flipbook-sortable .wp-flipbook-portlet-header .wp-flipbook-header-visibility', function(e){
    jQuery(e.currentTarget).parents(".wp-flipbook-portlet").find(".wp-flipbook-portlet-content").toggle();
  });
  
  jQuery(".wp-flipbook-sortable-add-page").on('click', function(e) {
    var newPage = '<div class="wp-flipbook-portlet">' +
            '<div class="wp-flipbook-portlet-header">' +
              'Página' +
              '<span class="wp-flipbook-portlet-header-buttons">' +
                '<span class="wp-flipbook-header-visibility"></span>' +
                '<span class="wp-flipbook-header-remove"></span>' +
              '</span>' +
            '</div>' +
            '<div class="wp-flipbook-portlet-content" style="display:block">' +
              '<input class="wp-flipbook-attachment-id" name="wp-flipbook-attachment[]" type="hidden"/>' +
              '<input class="button-secondary wp-flipbook-image-upload" type="button" value="Upload de Imagem"/>' +
            '</div>' +
          '</div>';
    jQuery(".wp-flipbook-sortable").append(newPage);
  });
  
  jQuery("body").on('click','.wp-flipbook-sortable .wp-flipbook-header-remove', function(e) {
    jQuery(e.currentTarget).parents('.wp-flipbook-portlet').remove();
  });
  
  /* WP Media */
  
  var current_page;
  var current_page_frame;
  
  jQuery("body").on("click",".wp-flipbook-image-upload",function(e) {
    e.preventDefault();
    
    current_page = jQuery(e.currentTarget).parents(".wp-flipbook-portlet-content");
    
    if ( current_page_frame ) {
      current_page_frame.open();
      return;
    };
    
    current_page_frame = wp.media({
      className: 'media-frame cs-frame',
      frame: 'select',
      multiple: false,
      title: 'Select image',
      library: {
        type:'image'
      },
      button: {
        text:'Use image'
      }
    });
    
    current_page_frame.on('select',function() {
      var media_attachment;
      
      media_attachment = current_page_frame.state().get('selection').first().toJSON();
      
      if( current_page.find(".wp-flipbook-img").length > 0 ) {
        current_page.find('.wp-flipbook-img').attr('src',media_attachment.url);
      }
      else {
        current_page.prepend('<img src='+media_attachment.url+' class="wp-flipbook-img"/> ');
      }
      
      current_page.find('.wp-flipbook-attachment-id').val(media_attachment.id);
      current_page.find('.wp-flipbook-image-upload').val('Alterar Imagem');
      
    });
    
    current_page_frame.open();
  });
});