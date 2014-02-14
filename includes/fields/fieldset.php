<?php
function ninja_forms_register_fieldset(){
  $args = array(
    'name'              => __( 'Fieldset', 'ninja-forms' ),
    'sidebar'           => 'layout_fields',
    'edit_function'     => '',
    'display_function'  => 'ninja_forms_fieldset_display',
    'group'             => 'layout_elements',
    'display_label'     => false,
    'display_wrap'      => false,
    'edit_label'        => true,
    'edit_label_pos'    => false,
    'edit_req'          => false,
    'edit_custom_class' => false,
    'edit_help'         => false,
    'edit_meta'         => false,
    'edit_conditional'  => false,
    'process_field'     => false,
    'nesting' => true
  );

  ninja_forms_register_field('_fieldset', $args);
}
add_action('init', 'ninja_forms_register_fieldset');


function ninja_forms_fieldset_display($field_id, $data){
  if( isset( $data['display_style'] ) ){
    $display_style = $data['display_style'];
  }else{
    $display_style = '';
  }
  $form = ninja_forms_get_form_by_field_id( $field_id );
  $field_class = ninja_forms_get_field_class($field_id);
  ?>
  <div class="<?php echo $field_class;?>" style="<?php echo $display_style;?>" id="ninja_forms_field_<?php echo $field_id;?>_div_wrap" rel="<?php echo $field_id;?>">
    <?php
      if( !empty( $data['label'] ) ){
        printf( '<span class="legend">%s</span>', $data['label'] );
      }
      ninja_forms_display_fields( $form['id'], $field_id );
    ?>
  </div>
  <?php
}