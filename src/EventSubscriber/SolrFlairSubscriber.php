<?php

namespace Drupal\solr_flair\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Event subscriber for switching Solr indexes.
 */
class SolrFlairSubscriber implements EventSubscriberInterface {
  /**
   * Config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new SolrFlairSubscriber.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      KernelEvents::REQUEST => [['switchSolrIndex', 20]],
    ];
  }

  /**
   * Switches the Solr index based on the environment.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The event to process.
   */
  public function switchSolrIndex(RequestEvent $event) {
    // Check if running on Pantheon or local DDEV.
    if (isset($_ENV['PANTHEON_ENVIRONMENT'])) {
      // Pantheon environment detected, use the Pantheon Solr index.
      $solr_index = 'pantheon_solr8';
    }
    else {
      // Local DDEV environment, use the local Solr index.
      $solr_index = 'ddev_solr';
    }
    dpm($solr_index);

    // Switch the Solr index.
    $search_api_config = $this->configFactory->getEditable('search_api.server.solr_server');
    $search_api_config->set('backend_config.connector_config.core', $solr_index);
    $search_api_config->save();
  }

}
