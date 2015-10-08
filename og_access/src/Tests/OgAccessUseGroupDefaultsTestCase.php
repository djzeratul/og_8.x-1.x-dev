<?php
namespace Drupal\og_access\Tests;

/**
 * Verify for an exception when OG access field is missing from the group, but exists on the group content.
 *
 * @group og_access
 */
class OgAccessUseGroupDefaultsTestCase extends \Drupal\simpletest\WebTestBase {

  protected $profile = 'standard';

  public static function getInfo() {
    return [
      'name' => 'Use group defaults',
      'description' => "Verify for an exception when OG access field is missing from the group, but exists on the group content.",
      'group' => 'Organic groups access',
    ];
  }

  public function setUp() {
    parent::setUp('og_access');
    node_access_rebuild();

    // Create group and group content node types.
    $this->group_type = $this->drupalCreateContentType()->type;
    og_create_field(OG_GROUP_FIELD, 'node', $this->group_type);

    $this->group_content_type = $this->drupalCreateContentType()->type;
    og_create_field(OG_AUDIENCE_FIELD, 'node', $this->group_content_type);
    og_create_field(OG_CONTENT_ACCESS_FIELD, 'node', $this->group_content_type);

    // Create a group node.
    $settings = [];
    $settings['type'] = $this->group_type;
    $settings[OG_GROUP_FIELD][\Drupal\Core\Language\Language::LANGCODE_NOT_SPECIFIED][0]['value'] = TRUE;
    $settings['uid'] = 1;
    $this->group_node = $this->drupalCreateNode($settings);
  }

  public /**
   * Create a group content with group content access field when the group
   * doesn't have an access field, and the "Use group defaults" is selected.
   */
  function testCatchException() {
    $settings = ['type' => $this->group_content_type];
    $settings[OG_AUDIENCE_FIELD][\Drupal\Core\Language\Language::LANGCODE_NOT_SPECIFIED][0]['target_id'] = $this->group_node->nid;
    $settings[OG_CONTENT_ACCESS_FIELD][\Drupal\Core\Language\Language::LANGCODE_NOT_SPECIFIED][0]['value'] = OG_CONTENT_ACCESS_DEFAULT;

    try {
      $this->drupalCreateNode($settings);
      $this->fail("Can set node visibility  when access field is missing from the group.");
    }
    
      catch (OgException $e) {
      $this->pass("Cannot set node visibility when access field is missing from the group.");
    }

    // Attach the OG access field to the group bundle and try to create a
    // group content.
    og_create_field(OG_ACCESS_FIELD, 'node', $this->group_type);
    $node = $this->drupalCreateNode($settings);

    $this->assertTrue($node, 'A group content has been created successfully.');
  }

}
