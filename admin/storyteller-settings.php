<?php
function storyteller_settings_init() {
  add_settings_section(
    'storyteller_settings',
    'Storyteller',
    'storyteller_settings_section_setup',
    'general'
  );

  add_settings_field(
    'storyteller_project',
    'Project',
    'storyteller_project_setup',
    'general',
    'storyteller_settings'
  );
  register_setting( 'general', 'storyteller_project' );

  add_settings_field(
    'storyteller_apikey',
    'API Key',
    'storyteller_apikey_setup',
    'general',
    'storyteller_settings'
  );
  register_setting( 'general', 'storyteller_apikey' );
} 
add_action( 'admin_init', 'storyteller_settings_init' );

function storyteller_project_setup() {
  $storyteller_project = get_option('storyteller_project');
  $storyteller_project_name = isset($storyteller_project) ? $storyteller_project : '';
  echo '<input name="storyteller_project" id="storytellerProject" type="text" value="' . $storyteller_project_name . '" />';
}

function storyteller_apikey_setup() {
  $storyteller_apikey = get_option('storyteller_apikey');
  $storyteller_key = isset($storyteller_apikey) ? $storyteller_apikey : '';
  echo '<input name="storyteller_apikey" id="storytellerApiKey" type="text" value="' . $storyteller_key . '" />';
}

function storyteller_settings_section_setup() {

// Render settings html
  echo '<p>Enter Storyteller.io credentials to authenticate WordPress with Storyteller.</p>';
}