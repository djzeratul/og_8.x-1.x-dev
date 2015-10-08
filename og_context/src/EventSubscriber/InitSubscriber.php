<?php /**
 * @file
 * Contains \Drupal\og_context\EventSubscriber\InitSubscriber.
 */

namespace Drupal\og_context\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InitSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [KernelEvents::REQUEST => ['onEvent', 0]];
  }

  public function onEvent() {
    og_context();
  }

}
