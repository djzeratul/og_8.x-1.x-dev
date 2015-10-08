<?php

/**
 * @file
 * Contains \Drupal\og_context\Form\OgContextConfigureForm.
 */

namespace Drupal\og_context\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

class OgContextConfigureForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'og_context_configure_form';
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {

    $form = [
      '#submit' => ['og_context_configure_form_submit'],
      '#theme' => 'og_context_configure_form',
      '#group_context_providers' => og_context_negotiation_info(),
    ];

    _og_context_configure_form_table($form);

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Save settings'),
    ];

    return $form;
  }

  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $type = 'group_context';

    $negotiation = [];
    $enabled_providers = $form_state->getValue([$type, 'enabled']);
    $providers_weight = $form_state->getValue([$type, 'weight']);

    foreach ($providers_weight as $id => $weight) {
      if ($enabled_providers[$id]) {
        $provider = $form[$type]['#group_context_providers'][$id];
        $provider['weight'] = $weight;
        $negotiation[$id] = $provider;
      }
    }

    og_context_negotiation_set($negotiation);
    // @FIXME
    // // @FIXME
    // // The correct configuration object could not be determined. You'll need to
    // // rewrite this call manually.
    // variable_set("og_context_providers_weight_$type", $providers_weight);


    $form_state->set(['redirect'], 'admin/config/group/context');
    drupal_set_message(t('Group context negotiation configuration saved.'));
  }

}
