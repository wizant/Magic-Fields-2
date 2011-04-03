<?php
// initialisation
global $mf_domain;


// class with static properties encapsulating functions for the field type

class image_media_field extends mf_custom_fields {

  public $allow_multiple = TRUE;
  public $has_properties = TRUE;
  
  public function _update_description(){
    global $mf_domain;
    $this->description = __("Simple image_media input",$mf_domain);
  }
  
  public function _options1(){
    global $mf_domain;
    
    $data = array(
      'option'  => array(
        'type'  => array(
          'type'        =>  'text',
          'id'          =>  'uno',
          'label'       =>  'opcion 1',
          'name'        =>  'mf_field[option][uno]',
          'default'     =>  '',
          'description' =>  __( 'aqui una descripcion', $mf_domain ),
          'value'       =>  '',
          'div_class'    => 'clase1',
          'class'       => 'vemos1'
        )
      )
    );
    
    return $data;
  }
  
}