<?php

/**
 * @file
 * Contains \Drupal\campaignmonitor\Plugin\Block\SubscribeBlock.
 */

namespace Drupal\campaignmonitor\Plugin\Block;

use Drupal\block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\campaignmonitor\CampaignMonitor;

/**
 * Provides a 'Subscribe' block.
 *
 * @Block(
 *   id = "subscribe_block",
 *   admin_label = @Translation("Subscribe Block"),
 * )
 */
class SubscribeBlock extends BlockBase implements BlockPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();    
    $cm = CampaignMonitor::getConnector();
    $lists = array();

    foreach($cm->getLists() as $list_id => $info) {
      $lists[$list_id] = $info['name'];
    }

    $form['prefix'] = array(
      '#title' => 'Prefix text for the subscribe block',
      '#type' => 'textarea',
      '#default_value' => isset($config['prefix']) ? $config['prefix'] : array(),
    );
    $form['list_status'] = array(
      '#title' => 'Which lists can people select?',
      '#type' => 'checkboxes',
      '#options' => $lists,
      '#default_value' => isset($config['list_status']) ? $config['list_status'] : array(),
    );    
    return $form;
  }
  
  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    // Save our custom settings when the form is submitted.
    $this->setConfigurationValue('list_status', $form_state['values']['list_status']);
    $this->setConfigurationValue('prefix', $form_state['values']['prefix']);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();   
    $list_status = isset($config['list_status'])?$config['list_status']:array();            
    $prefix = isset($config['prefix'])?$config['prefix']:array();            
    $cm = CampaignMonitor::getConnector();
    $lists = $cm->getLists();

    $enabled_lists = array();
    foreach($list_status as $list_id => $enabled) {
      if($enabled && isset($lists[$list_id])) {
        $enabled_lists[$list_id] = $lists[$list_id]['name'];
      }
    }    

    $form = \Drupal::formBuilder()->getForm('Drupal\campaignmonitor\Form\SubscribeForm',
      array('enabled_lists' => $enabled_lists));

    return array(
      'prefix' => array('#markup' => $prefix),
      'subscribe_form' => $form,
    );
  }
}
?>
