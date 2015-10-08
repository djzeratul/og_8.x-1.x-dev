<?php

/**
 * @file
 * Administration functions for OG context module.
 */

/**
 * Setting for language negotiation options
 */
function og_context_configure_form() {

  $form = array(
    '#submit' => array('og_context_configure_form_submit'),
    '#theme' => 'og_context_configure_form',
    '#group_context_providers' => og_context_negotiation_info(),
  );

  _og_context_configure_form_table($form);

  $form['actions'] = array('#type' => 'actions');
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save settings'),
  );

  return $form;
}

/**
 * Helper function to build a group context provider table.
 */
function _og_context_configure_form_table(&$form) {
  $type = 'group_context';

  $table_form = array(
    '#title' => t('Group context detection'),
    '#tree' => TRUE,
    '#description' => t('Order of Group context detection methods.'),
    '#group_context_providers' => array(),
    '#show_operations' => FALSE,
    'weight' => array('#tree' => TRUE),
    'enabled' => array('#tree' => TRUE),
  );

  $group_context_providers = $form['#group_context_providers'];
  // Enable url and node context handlers by default.
  $defaults = array('url' => -5, 'node' => -4);
  // @FIXME
// // @FIXME
// // The correct configuration object could not be determined. You'll need to
// // rewrite this call manually.
// $enabled_providers = variable_get("og_context_negotiation_$type", $defaults);

  // @FIXME
// // @FIXME
// // The correct configuration object could not be determined. You'll need to
// // rewrite this call manually.
// $providers_weight = variable_get("og_context_providers_weight_$type", $defaults);


  // Add missing data to the providers lists.
  foreach ($group_context_providers as $id => $provider) {
    if (!isset($providers_weight[$id])) {
      $providers_weight[$id] = og_context_provider_weight($provider);
    }
  }

  // Order providers list by weight.
  asort($providers_weight);

  foreach ($providers_weight as $id => $weight) {
    $enabled = isset($enabled_providers[$id]);
    $provider = $group_context_providers[$id];


    $table_form['#group_context_providers'][$id] = $provider;

    $table_form['weight'][$id] = array(
      '#type' => 'weight',
      '#default_value' => $weight,
      '#attributes' => array('class' => array("group_context-provider-weight-$type")),
    );

    $table_form['title'][$id] = array('#markup' => \Drupal\Component\Utility\SafeMarkup::checkPlain($provider['name']));
    $table_form['enabled'][$id] = array('#type' => 'checkbox', '#default_value' => $enabled);
    $table_form['description'][$id] = array('#markup' => \Drupal\Component\Utility\Xss::filterAdmin($provider['description']));

    $config_op = array();
    if (isset($provider['config'])) {
      $config_op = array('#type' => 'link', '#title' => t('Configure'), '#href' => $provider['config']);
      // If there is at least one operation enabled show the operation column.
      $table_form['#show_operations'] = TRUE;
    }
    $table_form['operation'][$id] = $config_op;
  }

  $form['group_context'] = $table_form;
}

/**
 * Returns HTML for a group context configuration form.
 *
 * @param $variables
 *   An associative array containing:
 *   - form: A render element representing the form.
 *
 * @ingroup themeable
 */
function theme_og_context_configure_form($variables) {
  $form = $variables['form'];
  $output = '';

  $type = 'group_context';
  $rows = array();
  $title = '<label>' . $form[$type]['#title'] . '</label>';
  $description = '<div class="description">' . $form[$type]['#description'] . '</div>';

  foreach ($form[$type]['title'] as $id => $element) {
    // Do not take form control structures.
    if (is_array($element) && \Drupal\Core\Render\Element::child($id)) {
      $row = array(
        'data' => array(
          '<strong>' . \Drupal::service("renderer")->render($form[$type]['title'][$id]) . '</strong>',
          \Drupal::service("renderer")->render($form[$type]['description'][$id]),
          \Drupal::service("renderer")->render($form[$type]['enabled'][$id]),
          \Drupal::service("renderer")->render($form[$type]['weight'][$id]),
        ),
        'class' => array('draggable'),
      );
      if ($form[$type]['#show_operations']) {
        $row['data'][] = \Drupal::service("renderer")->render($form[$type]['operation'][$id]);
      }
      $rows[] = $row;
    }
  }

  $header = array(
    array('data' => t('Detection method')),
    array('data' => t('Description')),
    array('data' => t('Enabled')),
    array('data' => t('Weight')),
  );

  // If there is at least one operation enabled show the operation column.
  if ($form[$type]['#show_operations']) {
    $header[] = array('data' => t('Operations'));
  }

  $variables = array(
    'header' => $header,
    'rows' => $rows,
    'attributes' => array('id' => "group_context-negotiation-providers-$type"),
  );
  // @FIXME
// theme() has been renamed to _theme() and should NEVER be called directly.
// Calling _theme() directly can alter the expected output and potentially
// introduce security issues (see https://www.drupal.org/node/2195739). You
// should use renderable arrays instead.
// 
// 
// @see https://www.drupal.org/node/2195739
// $table  = theme('table', $variables);

  $table .= drupal_render_children($form[$type]);

  // @FIXME
// TableDrag is now attached with the #tabledrag property of certain render
// arrays. drupal_add_tabledrag() is now internal and should never be called directly.
// 
// 
// @see https://www.drupal.org/node/2160571
// drupal_add_tabledrag("group_context-negotiation-providers-$type", 'order', 'sibling', "group_context-provider-weight-$type");


  $output .= '<div class="form-item">' . $title . $description . $table . '</div>';

  $output .= drupal_render_children($form);
  return $output;
}

/**
 * Submit handler for og_context negotiation settings.
 */
function og_context_configure_form_submit($form, &$form_state) {
  $type = 'group_context';

  $negotiation = array();
  $enabled_providers = $form_state['values'][$type]['enabled'];
  $providers_weight = $form_state['values'][$type]['weight'];

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


  $form_state['redirect'] = 'admin/config/group/context';
  drupal_set_message(t('Group context negotiation configuration saved.'));
}
