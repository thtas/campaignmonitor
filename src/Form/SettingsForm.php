<?php

/**
 * @file
 * Contains \Drupal\campaignmonitor\Form\SettingsForm.
 */

namespace Drupal\campaignmonitor\Form;

use Drupal;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\campaignmonitor\CampaignMonitor;


/**
 * Class SettingsForm.
 *
 * Creates an admin form to configure CampaignMonitor.
 *
 * @package Drupal\campaignmonitor\Form
 */
class SettingsForm extends ConfigFormBase {

  /** 
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'campaignmonitor_admin_settings_form';
  }


  /** 
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'campaignmonitor.account',
      'campaignmonitor.general',
    ];
  }


  /** 
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {       

    // Get config details.
    $account = $this->config('campaignmonitor.account');
    $general = $this->config('campaignmonitor.general');

    // Test if the library has been installed. If it has not been installed an
    // error message will be shown.
    $cm = CampaignMonitor::getConnector();
    $library_path = $cm->getLibraryPath();

    $form['campaignmonitor_account'] = array(
      '#type' => 'fieldset',
      '#title' => t('Account details'),
      '#description' => t('Enter your Campaign Monitor account information.'),
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
        '#default_value' => $general->get('cache_timeout'),      
      );

      $form['campaignmonitor_general']['library_path'] = array(
        '#type' => 'textfield',
        '#title' => t('Library path'),
        '#description' => t('The installation path of the Campaign Monitor library, relative to the Drupal root.'),
        '#default_value' => $library_path ? $library_path : $general->get('library_path'),
      );

      $form['campaignmonitor_general']['archive'] = array(
        '#type' => 'checkbox',
        '#title' => t('Newsletter archive'),
        '#description' => t('Create a block with links to HTML versions of past campaigns.'),
        '#default_value' => $general->get('archive'),
      );

      $form['campaignmonitor_general']['logging'] = array(
        '#type' => 'checkbox',
        '#title' => t('Log errors'),
        '#description' => t('Log communication errors with the Campaign Monitor service, if any.'),
        '#default_value' => $general->get('logging'),
      );

      $form['campaignmonitor_general']['instructions'] = array(
        '#type' => 'textfield',
        '#title' => t('Newsletter instructions'),
        '#description' => t('This message will be displayed to the user when subscribing to newsletters.'),
        '#default_value' => $general->get('instructions'),
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
  public function submitCacheClear(array $form, FormStateInterface $form_state) {    
    CampaignMonitor::getConnector()->clearCache();
    drupal_set_message(t('Caches cleared.'));
  }

  /** 
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('campaignmonitor.account');
    $values = $form_state->getValues();

    $this->config('campaignmonitor.account')
      ->set('api_key', $values['campaignmonitor_account']['api_key'])
      ->set('client_id', $values['campaignmonitor_account']['client_id'])
      ->save();

    $this->config('campaignmonitor.general')
      ->set('cache_timeout', $values['values']['campaignmonitor_general']['cache_timeout'])
      ->set('library_path', $values['values']['campaignmonitor_general']['library_path'])
      ->set('archive', $values['values']['campaignmonitor_general']['archive'])
      ->set('logging', $values['values']['campaignmonitor_general']['logging'])
      ->set('instructions', $values['values']['campaignmonitor_general']['instructions'])
      ->save();

    CampaignMonitor::getConnector()->clearCache();
  }
}
