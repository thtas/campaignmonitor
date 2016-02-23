<?php

namespace Drupal\campaignmonitor\Form;

use Drupal\campaignmonitor\CampaignMonitor;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

class SubscribeForm extends FormBase {
  /** 
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'campaignmonitor_admin_settings_form';
  }

  /** 
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $values = $form_state->getBuildInfo();
    $values = $values['args'][0];
    $enabled_lists = array();
    if(isset($values['enabled_lists'])) {
      $enabled_lists = $values['enabled_lists'];
    }

    $form['email'] = array(
      '#title' => 'Email',
      '#type' => 'textfield', 
    );
    
    $form['lists'] = array(
      '#title' => 'Newsletters',
      '#type' => 'checkboxes',
      '#options' => $enabled_lists,
      '#required' => TRUE,
    );
    
    $form['subscribe'] = array(
      '#type' => 'submit',
      '#value' => 'Subscribe'
    );
    
    return $form;
  }

  /** 
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }
  
  /** 
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    foreach($form_state['values']['lists'] as $list_id) {
      
      if(!$list_id) {
        continue;
      }
      
      $email = check_plain($form_state['values']['email']);
      $cm = CampaignMonitor::getConnector();
  
      // Update subscriber information or add new subscriber to the list.
      if (!$cm->subscribe($list_id, $email)) {
        form_set_error('', t('You were not subscribed to the list, please try again.'));
        $form_state['redirect'] = FALSE;
        return FALSE;
      }
    
      // Check if the user should be sent to a subscribe page.
      $lists = $cm->getLists();
      if (isset($lists[$list_id]['details']['ConfirmationSuccessPage']) && !empty($lists[$list_id]['details']['ConfirmationSuccessPage'])) {
        drupal_goto($lists[$list_id]['details']['ConfirmationSuccessPage']);
      }
      else {
        drupal_set_message(t('You are now subscribed to the "@list" list.', array('@list' => $lists[$list_id]['name'])), 'status');
      }    
    
    }    
  }
}
