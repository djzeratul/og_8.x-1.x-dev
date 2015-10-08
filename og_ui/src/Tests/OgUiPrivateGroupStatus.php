<?php
namespace Drupal\og_ui\Tests;

/**
 * Testing the automatic approval of a membership request for a private group.
 *
 * @group og_ui
 */
class OgUiPrivateGroupStatus extends \Drupal\simpletest\WebTestBase {

  protected $profile = 'standard';

  public $user;

  public $group;

  public static function getInfo() {
    return [
      'name' => 'Subscribe to private groups',
      'description' => 'Testing the automatic approval of a membership request for a private group.',
      'group' => 'Organic groups UI',
    ];
  }

  public function setUp() {
    parent::setUp(['og', 'og_access', 'og_ui']);
    node_access_rebuild();

    // Creating the content type and the necessary fields for this test.
    $content_type = $this->drupalCreateContentType();
    og_create_field(OG_GROUP_FIELD, 'node', $content_type->type);
    og_create_field(OG_ACCESS_FIELD, 'node', $content_type->type);

    // Creating a private group, and a user.
    $this->group = $this->drupalCreateNode([
      'type' => $content_type->type
      ]);

    $wrapper = entity_metadata_wrapper('node', $this->group);
    $wrapper->{OG_GROUP_FIELD}->set(TRUE);
    $wrapper->{OG_ACCESS_FIELD}->set(1);
    $wrapper->save();
    $this->user = $this->drupalCreateUser();
  }

  /**
   * The variable "og_ui_deny_subscribe_without_approval" responsible for
   * determine that when the user ask to join to a private group their
   * membership status will be pending. By default his value is true. The test
   * has two parts:
   *
   * 1. Checking first that a membership request is defined as pending.
   * 2. Change his value to false and verify that the membership status will
   *    approved automatically.
   */
  public function testMemberShipRequestStatus() {
    $this->drupalLogin($this->user);

    // When the user ask to join a private group we need to verify that the
    // membership will be pending.
    $this->drupalPost('group/node/' . $this->group->nid . '/subscribe/og_user_node', [], t('Join'));
    $this->assertTrue(og_is_member('node', $this->group->nid, 'user', $this->user, [
      OG_STATE_PENDING
      ]), 'The user membership request is pending.');

    // Delete the membership.
    $query = new entityFieldQuery();
    $result = $query
      ->entityCondition('entity_type', 'og_membership')
      ->propertyCondition('etid', $this->user->uid)
      ->propertyCondition('entity_type', 'user')
      ->propertyCondition('gid', $this->group->nid)
      ->propertyCondition('group_type', 'node')
      ->execute();
    og_membership_delete_multiple(array_keys($result['og_membership']));

    // Change the approval variable and ask to join the private group.
    $roles = og_roles('node', $this->group->type);
    og_role_grant_permissions(array_search(OG_ANONYMOUS_ROLE, $roles), [
      'subscribe without approval'
      ]);
    \Drupal::configFactory()->getEditable('og_ui.settings')->set('og_ui_deny_subscribe_without_approval', FALSE)->save();

    // Verify the user membership is approved automatically.
    $this->drupalPost('group/node/' . $this->group->nid . '/subscribe/og_user_node', [], t('Join'));
    $this->assertTrue(og_is_member('node', $this->group->nid, 'user', $this->user, [
      OG_STATE_ACTIVE
      ]), 'The user membership request is active.');
  }

}
