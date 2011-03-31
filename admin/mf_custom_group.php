<?php 

class mf_custom_group extends mf_admin {

  /** 
   *  Page for add a new group
   */
  function add_group() {
    global $mf_domain;

    $data = $this->group_data();
    $this->form_custom_group($data);
  }

  /**
   * Edit group
   */
  function edit_group(){
    global $mf_domian;

    //check param custom_group_id

    $data = $this->group_data();
    $group = $this->get_group($_GET['custom_group_id']);
    
    //check exist group
    if(!$group){
       $this->mf_flash('error',null,null);
    }else{
      //set the values
      foreach($group as $k => $v){
        $data['core'][$k]['value'] = $v;
      }
      $this->form_custom_group($data);
    }
  }
  
  /**
   * Delete a custom group
   */
  public function delete_custom_group(){
    global $wpdb;
    
    //checking the nonce
    check_admin_referer('delete_custom_group');
    
    if( isset($_GET['custom_group_id']) ){
      $id = (int)$_GET['custom_group_id'];
      if( is_int($id) ){
        $group = $this->get_group($id);
        $sql = sprintf("DELETE FROM %s WHERE id = %s",MF_TABLE_CUSTOM_GROUPS,$id);
        $wpdb->query($sql);
        //ToDo: deberiamos borrar tambien todos los custom fields del campo y los datos que ya fueron guardados

        //ToDo: poner mensaje de que se borro correctamente
        $this->mf_redirect('mf_custom_fields','fields_list',array('message' => 'success','post_type' => $group['post_type']));
        
      }
    }
  }

  function save_custom_group(){

    //save custom group
    $mf = $_POST['mf_group'];
    if($mf['core']['id']){
      //update
      $this->update_custom_group($mf);
    }else{
      //insert
      $this->new_custom_group($mf);
    }
    
    //redirect to dashboard fields
    $this->mf_redirect('mf_custom_fields','fields_list',array('message' => 'success','post_type' => $mf['core']['post_type']));
  }

  /**
   * Add a new custom group
   */
  public function new_custom_group($data){
    global $wpdb;
   
    $sql = sprintf(
      "INSERT INTO %s ".
      "(name,label,post_type,duplicated,expanded) ".
      "VALUES ('%s','%s','%s',%s,%s)",
      MF_TABLE_CUSTOM_GROUPS,
      $data['core']['name'],
      $data['core']['label'],
      $data['core']['post_type'],
      $data['core']['duplicate'],
      $data['core']['expanded']
    );
    $wpdb->query($sql);
  }

  /**
   * Update a custom group
   */
  public function update_custom_group($data){
    global $wpdb;

    //ToDo: falta sanitizar variables
    // podriamos crear un mettodo para hacerlo
    // la funcion podria pasarle como primer parametro los datos y como segundo un array con los campos que se va a sanitizar o si se quiere remplazar espacios por _ o quitar caracteres extraños

    $sql = sprintf(
      "UPDATE %s ".
      "SET name = '%s', label ='%s',duplicated = %s, expanded = %s ".
      "WHERE id = %s",
      MF_TABLE_CUSTOM_GROUPS,
      $data['core']['name'],
      $data['core']['label'],
      $data['core']['duplicate'],
      $data['core']['expanded'],
      $data['core']['id']
    );
    
    $wpdb->query($sql);
  }

  public function get_custom_fields_post_type($post_type){
    GLOBAL $wpdb;
    $query = sprintf("SELECT * FROM %s WHERE post_type = '%s'", MF_TABLE_CUSTOM_FIELDS,$post_type);
    $fields = $wpdb->get_results($query, ARRAY_A);
    return $fields;
    
  }

   public function group_data() {
    global $mf_domain;

    $post_type = isset($_GET['post_type'])? $_GET['post_type'] : '';
    $id = isset($_GET['custom_group_id'])? $_GET['custom_group_id']: '';
    $data = array(
      'core'  => array(
        'id' => array(
          'type' => 'hidden',
          'id'   => 'id',
          'name'  => 'mf_group[core][id]',
          'value' => $id
        ),
        'post_type' => array(
          'type' => 'hidden',
          'id'   => 'custom_group_post_type',
          'name' => 'mf_group[core][post_type]',
          'value' => $post_type
        ),
        'name'  => array(
          'type'        =>  'text',
          'id'          =>  'custom_group_name',
          'label'       =>  __('Name',$mf_domain),
          'name'        =>  'mf_group[core][name]',
          'description' =>  __( 'The name only accept letters and numbers (lowercar)', $mf_domain),
          'class'       => '',
          'div_class'   =>  'form-required',
          'value'       =>  ''
        ),
        'label'  => array(
          'type'        =>  'text',
          'id'          =>  'custom_group_label',
          'label'       =>  __('Label',$mf_domain),
          'name'        =>  'mf_group[core][label]',
          'description' =>  __( 'The label of the group', $mf_domain),
          'div_class'   =>  'form-required',
          'value'       =>  ''
        ),
         'duplicated'  =>  array(
          'type'        =>  'checkbox',
          'id'          => 'custom_group_duplicated',
          'label'       =>  __('Can be duplicated',$mf_domain),
          'name'        =>  'mf_group[core][duplicate]',
          'description' =>  __('this group is duplicable?',$mf_domain),
          'value'       =>  0
         ),
        'expanded'    =>  array(
          'type'        =>  'checkbox',
          'id'          =>  'custom_group_expanded',
          'label'       =>  __('Show as Expanded:',$mf_domain),
          'name'        =>  'mf_group[core][expanded]',
          'description' =>  __( 'Display the full expanded group editing interface instead of the group summary',$mf_domain),
          'extra'       => __('Note: the group can still be collapsed by the user, this just determines the default state on load', $mf_domain ),
          'value'       =>  0
        )   
      )
    );

    return $data;
  }

  function form_custom_group( $data ) {
    global $mf_domain;
    ?>
    <div class="wrap">
      <h2><?php _e('Create Custom Group', $mf_domain);?></h2>


     <form id="addCustomField" method="post" action="admin.php?page=mf_dispatcher&init=true&mf_section=mf_custom_group&mf_action=save_custom_group" class="validate">
      <div class="alignleft fixed" id="mf_add_custom_group">
        <?php foreach( $data['core'] as $core ):?>
          <?php if( $core['type'] == 'hidden' ): ?>
	          <?php mf_form_hidden($core); ?>
          <?php elseif( $core['type'] == 'text' ):?>
	          <div class="form-field mf_form <?php echo $core['div_class']; ?>">
              <?php mf_form_text($core); ?>
            </div>
          <?php elseif( $core['type'] == "select" ):?>
            <div class="form-field mf_form <?php echo $core['div_class']; ?>">
              <?php mf_form_select($core); ?>
            </div>
          <?php elseif( $core['type'] == "checkbox" ):?>
            <fieldset>
              <div class="form-field mf_form <?php echo $core['div_class']; ?>">
              <?php mf_form_checkbox($core);?>
              </div>
            </fieldset>
          <?php endif;?> 
        <?php endforeach;?>
      	<p class="submit">
      	  <a style="color:black" href="admin.php?page=mf_dispatcher&mf_section=mf_custom_fields&mf_action=fields_list&post_type=<?php echo $data['core']['post_type']['value'];?>" class="button">Cancel</a>
      	  <input type="submit" class="button" name="submit" id="submit" value="Save Custom Group">
      	</p>
      </div>
      <div class="widefat mf_form_right">
        <h4>Aqui un texto esplicando tal vez que es un grupo</h4>
        <div  id="options_field">
          <p>texto texto</p>
        </div>
      </div>
    </div>
</form>
  <?php
  }
}