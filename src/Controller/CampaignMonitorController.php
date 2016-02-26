<?php
/**
 * @file
 * Contains \Drupal\campaignmonitor\Controller\CampaignMonitorController.
 */

namespace Drupal\campaignmonitor\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\campaignmonitor\CampaignMonitor;

class CampaignMonitorController extends ControllerBase {

  /**
   * The campaign monitor.
   *
   * @var \Drupal\campaignmonitor\CampaignMonitor
   */
  protected $campaignMonitor;

  /**
   * Settings for the module.
   */
  protected $settings;

  /**
   * Constructs a new SubscribeForm.
   */
  public function __construct() {
    $this->campaignMonitor = CampaignMonitor::GetConnector();
    $this->config_factory = \Drupal::service('config.factory');
    $this->formBuilder = \Drupal::service('form_builder');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static();
  }

  /**
   * Constructs a page with a signup form.
  */
  public function content() {
    $settings = $this->config_factory->get('campaignmonitor.general');
    $page = $settings->get('page');

    // If the page option isn't turned on, throw an access denied error.
    if ($page != TRUE) {
      throw new AccessDeniedHttpException();
    }

    $cm = $this->campaignMonitor;
    $lists = $cm->getLists();

    $enabled_lists = array();
    foreach($lists as $list_id => $enabled) {
      $enabled_lists[$list_id] = $lists[$list_id]['name'];
    }

    // Prefix text.
    $prefix = $settings->get('page_prefix');
    $prefix = $prefix['value'];

    $form = $this->formBuilder->getForm('Drupal\campaignmonitor\Form\SubscribeForm',
      array('enabled_lists' => $enabled_lists));

    return array(
      'prefix' => array('#markup' => $prefix),
      'subscribe_form' => $form,
    );
  }
}
?>
