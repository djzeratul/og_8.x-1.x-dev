<?php /**
 * @file
 * Contains \Drupal\og_ui\Plugin\Field\FieldFormatter\OgListDefault.
 */

namespace Drupal\og_ui\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;

/**
 * @FieldFormatter(
 *  id = "og_list_default",
 *  label = @Translation("OG audience list"),
 *  field_types = {"entityreference"}
 * )
 */
class OgListDefault extends FormatterBase {

  /**
   * @FIXME
   * Move all logic relating to the og_list_default formatter into this
   * class. For more information, see:
   *
   * https://www.drupal.org/node/1805846
   * https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Field%21FormatterInterface.php/interface/FormatterInterface/8
   * https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Field%21FormatterBase.php/class/FormatterBase/8
   */

}
