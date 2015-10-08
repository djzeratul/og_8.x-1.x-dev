<?php
namespace Drupal\og_ui\Tests;

/**
 * Verify that role permissions can be added and removed via the permissions page of the group.
 *
 * @group og_ui
 */
class OgUiUserPermissionsTestCase extends \Drupal\simpletest\WebTestBase {

  protected $profile = 'standard';

  public static function getInfo() {
    return [
      'name' => 'OG UI role permissions',
      'description' => 'Verify that role permissions can be added and removed via the permissions page of the group.',
      'group' => 'Organic groups UI',
    ];
  }

  public function setUp() {
    parent::setUp('og_ui', 'entity_feature');

    // Add OG group fields.
    og_create_field(OG_GROUP_FIELD, 'entity_test', 'main');
  }

  public /**
   * Change user permissions and check og_user_access().
   */
  function testOgUiUserPermissionChanges() {
    $permissions = [
      'bypass node access',
      'administer content types',
      'administer group',
    ];
    $admin_user = $this->drupalCreateUser($permissions);
    $web_user = $this->drupalCreateUser();
    $this->drupalLogin($admin_user);

    // Create a group.
    $entity1 = \Drupal::entityManager()->getStorage('entity_test')->create([
      'name' => 'main',
      'uid' => $admin_user->uid,
    ]);
    $wrapper = entity_metadata_wrapper('entity_test', $entity1);
    $wrapper->{OG_GROUP_FIELD}->set(1);
    $wrapper->save();

    $this->assertTrue(og_user_access('entity_test', $entity1->pid, 'subscribe', $web_user), t('User has "subscribe" permission.'));

    // Remove a permission.
    $this->drupalPost('admin/config/group/permissions/entity_test/main', [
      '1[subscribe]' => FALSE
      ], t('Save permissions'));
    $this->assertText(t('The changes have been saved.'), t('Successful save message displayed.'));

    // FIXME: There is an og_invalidate_cache() on permissions granting
    // and revoking, but somehow, we need to do it manually here.
    og_invalidate_cache();

    $this->assertFalse(og_user_access('entity_test', $entity1->pid, 'subscribe', $web_user), t('User now does not have "subscribe" permission.'));

    // Re-add permission.
    $this->drupalPost('admin/config/group/permissions/entity_test/main', [
      '1[subscribe]' => TRUE
      ], t('Save permissions'));

    // FIXME: There is an og_invalidate_cache() on permissions granting
    // and revoking, but somehow, we need to do it manually here.
    og_invalidate_cache();
    $this->assertTrue(og_user_access('entity_test', $entity1->pid, 'subscribe', $web_user), t('User has "subscribe" permission again.'));
  }

}
