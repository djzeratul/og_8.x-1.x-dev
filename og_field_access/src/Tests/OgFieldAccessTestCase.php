<?php
namespace Drupal\og_field_access\Tests;

/**
 * Test the access control on fields.
 *
 * @group og_field_access
 */
class OgFieldAccessTestCase extends \Drupal\simpletest\WebTestBase {

  protected $profile = 'standard';

  public static function getInfo() {
    return [
      'name' => 'Organic groups field access',
      'description' => 'Test the access control on fields.',
      'group' => 'Organic groups field access',
    ];
  }

  public function setUp() {
    parent::setUp('og_field_access');
  }

  public /**
   * Group with access field.
   */
  function testOgFieldAccess() {
    $user1 = $this->drupalCreateUser();
    $user2 = $this->drupalCreateUser();
    $this->drupalLogin($user1);

    // Create group and group content node types.
    $group_type = $this->drupalCreateContentType();
    og_create_field(OG_GROUP_FIELD, 'node', $group_type->type);

    $group_content_type = $this->drupalCreateContentType();
    og_create_field(OG_AUDIENCE_FIELD, 'node', $group_content_type->type);

    $og_roles = og_roles('node', $group_type->type);

    // Set global permissions.
    $anon_rid = array_search(OG_ANONYMOUS_ROLE, $og_roles);
    $permissions = [
      'view body field' => 0,
      'update body field' => 0,
      // Allow non-members to edit the group, so we can test the node-edit page.
      'update group' => 1,
    ];
    og_role_change_permissions($anon_rid, $permissions);

    $this->drupalLogin($user1);
    // Create a group node.
    $settings = [];
    $settings['type'] = $group_type->type;
    $settings[OG_GROUP_FIELD][\Drupal\Core\Language\Language::LANGCODE_NOT_SPECIFIED][0]['value'] = 1;
    $settings['body'][\Drupal\Core\Language\Language::LANGCODE_NOT_SPECIFIED][0]['value'] = $this->randomName();

    $node = $this->drupalCreateNode($settings);

    // Assert another user is not a group member.
    $this->drupalLogin($user2);
    $this->assertFalse(og_is_member('node', $node->nid, 'user', $user2), t('User is not a group member.'));

    // Assert user can't view the field.
    $this->drupalGet('node/' . $node->nid);
    $this->assertResponse('200', t('Non group member can view node.'));
    $this->assertNoText($node->body[\Drupal\Core\Language\Language::LANGCODE_NOT_SPECIFIED][0]['value'], t('Non group member can not view field.'));

    // Change permissions and assert user can view the field.
    $permissions['view body field'] = 1;
    og_role_change_permissions($anon_rid, $permissions);
    $this->drupalGet('node/' . $node->nid);
    $this->assertText($node->body[\Drupal\Core\Language\Language::LANGCODE_NOT_SPECIFIED][0]['value'], t('Non group member can now view field.'));

    // Assert user can't edit the field.
    $this->drupalGet('node/' . $node->nid . '/edit');
    $this->assertResponse('200', t('Non group member can edit node.'));
    $this->assertNoText($node->body[\Drupal\Core\Language\Language::LANGCODE_NOT_SPECIFIED][0]['value'], t('Non group member can not edit field.'));

    // Change permissions and assert user can view the field.
    $permissions['update body field'] = 1;
    og_role_change_permissions($anon_rid, $permissions);
    $this->drupalGet('node/' . $node->nid . '/edit');
    $langcode = \Drupal\Core\Language\Language::LANGCODE_NOT_SPECIFIED;
    $this->assertFieldByName("body[$langcode][0][value]", $node->body[\Drupal\Core\Language\Language::LANGCODE_NOT_SPECIFIED][0]['value'], t('Non group member can now edit field.'));

    // Assert field permissions on group content.
    $permissions['view body field'] = 0;
    og_role_change_permissions($anon_rid, $permissions);

    $settings = [];
    $settings['uid'] = $user1->uid;
    $settings['type'] = $group_content_type->type;
    $settings[OG_AUDIENCE_FIELD][\Drupal\Core\Language\Language::LANGCODE_NOT_SPECIFIED][0]['target_id'] = $node->nid;
    $settings['body'][\Drupal\Core\Language\Language::LANGCODE_NOT_SPECIFIED][0]['value'] = $this->randomName();
    $node = $this->drupalCreateNode($settings);

    $this->drupalLogin($user1);
    $this->drupalGet('node/' . $node->nid);
    $this->assertText($node->body[\Drupal\Core\Language\Language::LANGCODE_NOT_SPECIFIED][0]['value'], t('Group member can view field of a group content.'));

    $this->drupalLogin($user2);
    $this->drupalGet('node/' . $node->nid);
    $this->assertNoText($node->body[\Drupal\Core\Language\Language::LANGCODE_NOT_SPECIFIED][0]['value'], t('Non member can not view field of a group content.'));

    // Assert field permissions on orphan group content.
    $settings = [];
    $settings['type'] = $group_content_type->type;
    $settings['uid'] = $user1->uid;
    $node = $this->drupalCreateNode($settings);

    $this->drupalGet('node/' . $node->nid);
    $this->assertText($node->body[\Drupal\Core\Language\Language::LANGCODE_NOT_SPECIFIED][0]['value'], t('Non member can view field of an orphan group content.'));

    // Assert fields of nodes not related to OG are not being restricted.
    $user3 = $this->drupalCreateUser([
      'access content',
      'create page content',
      'edit any page content',
    ]);
    $this->drupalLogin($user3);

    $node = $this->drupalCreateNode();
    $this->drupalGet('node/' . $node->nid);
    $this->assertText($node->body[\Drupal\Core\Language\Language::LANGCODE_NOT_SPECIFIED][0]['value'], t('User can view field of content not related to Organic groups.'));

    $this->drupalGet('node/' . $node->nid . '/edit');
    $this->assertText($node->body[\Drupal\Core\Language\Language::LANGCODE_NOT_SPECIFIED][0]['value'], t('User can edit field of content not related to Organic groups.'));
  }

}
