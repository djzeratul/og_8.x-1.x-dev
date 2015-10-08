<?php
namespace Drupal\og_ui\Tests;

/**
 * Verify the subscribe and unsubsribe functionality.
 *
 * @group og_ui
 */
class OgUiSubscribeTestCase extends \Drupal\simpletest\WebTestBase {

  protected $profile = 'standard';

  public static function getInfo() {
    return [
      'name' => 'OG UI subscribe',
      'description' => 'Verify the subscribe and unsubsribe functionality.',
      'group' => 'Organic groups UI',
    ];
  }

  public function setUp() {
    parent::setUp('og_ui', 'entity_feature');
    // Add OG group field.
    og_create_field(OG_GROUP_FIELD, 'node', 'article');
  }

  public /**
   * Test subscribing to group.
   */
  function testOgUiSubscribe() {
    $user1 = $this->drupalCreateUser();
    $user2 = $this->drupalCreateUser();
    $this->drupalLogin($user1);

    // Create a group.
    $settings = [];
    $settings['type'] = 'article';
    $settings['uid'] = $user1->uid;
    $settings[OG_GROUP_FIELD][\Drupal\Core\Language\Language::LANGCODE_NOT_SPECIFIED][0]['value'] = 1;
    $node = $this->drupalCreateNode($settings);

    $this->drupalGet('node/' . $node->nid);
    $this->assertText(t('You are the group manager'), t('Group manager gets correct text.'));


    $this->drupalLogin($user2);
    $this->drupalGet('node/' . $node->nid);
    $this->assertText(t('Request group membership'), t('Non-member without "subscribe without approval" gets correct text.'));

    $this->clickLink(t('Request group membership'));
    // Assert user's request field appears.
    $this->assertText('Request message', t('Request message appears.'));
    $request = $this->randomString();
    $edit = [];
    $edit['og_membership_request[und][0][value]'] = $request;
    $this->drupalPost(NULL, $edit, t('Join'));

    $og_membership = og_get_membership('node', $node->nid, 'user', $user2->uid);
    $wrapper = entity_metadata_wrapper('og_membership', $og_membership);
    $this->assertEqual($request, $wrapper->og_membership_request->value(), t('User request was saved in group membership.'));

    $this->drupalGet('node/' . $node->nid);
    $this->assertText(t('Unsubscribe from group'), t('Member gets correct unsubscribe text.'));
    $this->clickLink(t('Unsubscribe from group'));
    $this->drupalPost(NULL, [], t('Remove'));

    $this->assertFalse(og_is_member('node', $node->nid, 'user', $user2, [
      OG_STATE_ACTIVE,
      OG_STATE_PENDING,
    ]), t('User unsubscribed from group.'));

    // Change global permissions to allow user to subscribe without approval.
    $og_roles = og_roles('node', 'article');
    $rid = array_search(OG_ANONYMOUS_ROLE, $og_roles);
    og_role_change_permissions($rid, ['subscribe without approval' => 1]);

    $this->drupalGet('node/' . $node->nid);
    $this->assertText(t('Subscribe to group'), t('Non-member with "subscribe without approval" gets correct text.'));
    $this->clickLink(t('Subscribe to group'));
    $this->assertNoText('Request message', t('Request message does not appear.'));
    $this->drupalPost(NULL, [], t('Join'));
    $this->assertTrue(og_is_member('node', $node->nid, 'user', $user2), t('User subscribed to group'));
  }

}
