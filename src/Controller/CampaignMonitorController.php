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
    $this->settings = \Drupal::config('campaignmonitor.general')->get();
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
    // If the page option isn't turned on, throw an access denied error.
    if (!isset($this->settings['page']) ||
      ($this->settings['page'] == 0)) {
      throw new AccessDeniedHttpException();
    }

    $cm = $this->campaignMonitor;
    $lists = $cm->getLists();

    $enabled_lists = array();
    foreach($lists as $list_id => $enabled) {
      $enabled_lists[$list_id] = $lists[$list_id]['name'];
    }

    // Prefix text.
    $prefix = $this->settings['page_prefix']['value'];

    $form = \Drupal::formBuilder()->getForm('Drupal\campaignmonitor\Form\SubscribeForm',
      array('enabled_lists' => $enabled_lists));

    return array(
      'prefix' => array('#markup' => $prefix),
      'subscribe_form' => $form,
    );
  }
}
?>
