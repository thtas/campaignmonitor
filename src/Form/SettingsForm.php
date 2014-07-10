<?php

namespace Drupal\campaignmonitor\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\campaignmonitor\CampaignMonitor;

class SettingsForm extends ConfigFormBase {

  public function getFormID() {
    return 'campaignmonitor_admin_settings_form';
  }
  
  protected function actions(array $form, array &$form_state) {
    $element = parent::actions($form, $form_state);
    drupal_set_message('<pre>'.print_r($element,true).'</pre>');
    return $element;
  }
  
  public function buildForm(array $form, array &$form_state) {       
    
  // Get account details.
  $account = $this->config('campaignmonitor.account');

  // Test if the library has been installed. If it has not been installed an
  // error message will be shown.
  $cm = CampaignMonitor::getConnector();
  $library_path = $cm->getLibraryPath();

  $form['campaignmonitor_account'] = array(
    '#type' => 'fieldset',
    '#title' => t('Account details'),
    '#description' => t('Enter your Campaign Monitor account information. See !link for more information.', array('!link' => l(t('the Campaign Monitor API documentation'), 'http://www.campaignmonitor.com/api/required/'))),
    '#collapsible' => empty($account) ? FALSE : TRUE,
    '#collapsed' => empty($account) ? FALSE : TRUE,
    '#tree' => TRUE,
  );

  $form['campaignmonitor_account']['api_key'] = array(
    '#type' => 'textfield',
    '#title' => t('API Key'),
    '#description' => t('Your Campaign Monitor API Key. See <a href="http://www.campaignmonitor.com/api/required/">documentation</a>.'),
    '#default_value' => $account->get('api_key'),
    '#required' => TRUE,
  );

  $form['campaignmonitor_account']['client_id'] = array(
    '#type' => 'textfield',
    '#title' => t('Client ID'),
    '#description' => t('Your Campaign Monitor Client ID. See <a href="http://www.campaignmonitor.com/api/required/">documentation</a>.'),
    '#default_value' => $account->get('client_id'),
    '#required' => TRUE,
  );

  if (!empty($account)) {
    $defaults = $this->config('campaignmonitor.general');
    $form['campaignmonitor_general'] = array(
      '#type' => 'fieldset',
      '#title' => t('General settings'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#tree' => TRUE,
    );

    $form['campaignmonitor_general']['cache_timeout'] = array(
      '#type' => 'textfield',
      '#title' => t('Cache timeout'),
      '#description' => t('Cache timeout in seconds for stats, subscribers and archive information.'),
      '#size' => 4,
      '#default_value' => $defaults->get('cache_timeout'),      
    );

    $form['campaignmonitor_general']['library_path'] = array(
      '#type' => 'textfield',
      '#title' => t('Library path'),
      '#description' => t('The installation path of the Campaign Monitor library, relative to the Drupal root.'),
      '#default_value' => $library_path ? $library_path : $defaults->get('library_path'),
    );

    $form['campaignmonitor_general']['archive'] = array(
      '#type' => 'checkbox',
      '#title' => t('Newsletter archive'),
      '#description' => t('Create a block with links to HTML versions of past campaigns.'),
      '#default_value' => $defaults->get('archive'),
    );

    $form['campaignmonitor_general']['logging'] = array(
      '#type' => 'checkbox',
      '#title' => t('Log errors'),
      '#description' => t('Log communication errors with the Campaign Monitor service, if any.'),
      '#default_value' => $defaults->get('logging'),
    );

    $form['campaignmonitor_general']['instructions'] = array(
      '#type' => 'textfield',
      '#title' => t('Newsletter instructions'),
      '#description' => t('This message will be displayed to the user when subscribing to newsletters.'),
      '#default_value' => $defaults->get('instructions'),
    );

    // Add cache clear button.
    $form['clear_cache'] = array(
      '#type' => 'fieldset',
      '#title' => t('Clear cached data'),
      '#description' => t('The information downloaded from Campaign Monitor is cached to speed up the website. The lists details, custom fields and other data may become outdated if these are changed at Campaign Monitor. Clear the cache to refresh this information.'),
    );
    
    $form['clear_cache']['clear'] = array(
      '#type' => 'submit',
      '#value' => t('Clear cached data'),
      '#submit' => array(array($this, 'submitCacheClear')),
    );
  }
    
    return parent::buildForm($form, $form_state);
  }
  
  /**
   * Clears the caches.
   */
  public function submitCacheClear(array &$form, array &$form_state) {    
     CampaignMonitor::getConnector()->clearCache();
    drupal_set_message(t('Caches cleared.'));
  }
  
  
  public function submitForm(array &$form, array &$form_state) {
        
    $this->config('campaignmonitor.account')
      ->set('api_key', $form_state['values']['campaignmonitor_account']['api_key'])     
      ->set('client_id', $form_state['values']['campaignmonitor_account']['client_id'])     
      ->save();
    
    $this->config('campaignmonitor.general')
      ->set('cache_timeout', $form_state['values']['campaignmonitor_general']['cache_timeout'])     
      ->set('library_path', $form_state['values']['campaignmonitor_general']['library_path'])     
      ->set('archive', $form_state['values']['campaignmonitor_general']['archive'])     
      ->set('logging', $form_state['values']['campaignmonitor_general']['logging'])     
      ->set('instructions', $form_state['values']['campaignmonitor_general']['instructions'])     
      ->save();
          
    CampaignMonitor::getConnector()->clearCache();
  }

}