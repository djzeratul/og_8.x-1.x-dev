<?php
namespace Drupal\og_ui\Tests;

/**
 * Verify that only users with group admin permissions can see the admin tabs.
 *
 * @group og_ui
 */
class OgUiAdminPermissionsTestCase extends \Drupal\simpletest\WebTestBase {

  protected $profile = 'standard';

  public static function getInfo() {
    return [
      'name' => 'OG UI admin section',
      'description' => 'Verify that only users with group admin permissions can see the admin tabs.',
      'group' => 'Organic groups UI',
    ];
  }

  public function setUp() {
    parent::setUp('og_ui');

    // Add OG group fields.
    og_create_field(OG_GROUP_FIELD, 'node', 'article');
  }

  public /**
   * Check access permissions to the group admin tab.
   */
  function testOgUiAdminTabAccess() {
    $user1 = $this->drupalCreateUser();
    $user2 = $this->drupalCreateUser();

    $settings = [];
    $settings['uid'] = $user1->uid;
    $settings['type'] = 'article';
    $settings[OG_GROUP_FIELD][\Drupal\Core\Language\Language::LANGCODE_NOT_SPECIFIED][0]['value'] = 1;
    $node = $this->drupalCreateNode($settings);

    $this->drupalLogin($user2);

    // User does not have any access permissions.
    $this->drupalGet('node/' . $node->nid . '/group');
    $this->assertResponse(403, t('User without privileges can not access group admin tabs.'));

    $perms = [
      'add user',
      'manage members',
      'manage roles',
      'manage permissions',
    ];

    $roles = og_roles('node', 'article');
    $auth_rid = array_search(OG_ANONYMOUS_ROLE, $roles);
    foreach ($perms as $perm) {
      // Add an admin permission to allow the user to access to the admin tabs.
      og_role_grant_permissions($auth_rid, [
        $perm
        ]);
      $this->drupalGet('node/' . $node->nid . '/group');
      $this->assertResponse(200, t('User with "@perm" privilege can access group admin tabs.', [
        '@perm' => $perm
        ]));
      // Remove the admin permission to restrict user access to the admin tabs.
      // User is left without admin permissions for the next loop.
      og_role_revoke_permissions($auth_rid, [
        $perm
        ]);
    }
  }

  public /**
   * Check access to restricted permissions on the permissions edit page.
   */
  function testOgUiAdminPermissionsAccess() {
    $user1 = $this->drupalCreateUser();
    $user2 = $this->drupalCreateUser();

    $settings = [];
    $settings['uid'] = $user1->uid;
    $settings['type'] = 'article';
    $settings[OG_GROUP_FIELD][\Drupal\Core\Language\Language::LANGCODE_NOT_SPECIFIED][0]['value'] = 1;
    $node = $this->drupalCreateNode($settings);

    $this->drupalLogin($user2);
    $roles = og_roles('node', 'article');
    $auth_rid = array_search(OG_ANONYMOUS_ROLE, $roles);
    $text = t('Warning: Give to trusted roles only; this permission has security implications in the group context.');

    // Check that restricted permissions are not displayed to the user with
    // manage permissions but not administer group.
    og_role_grant_permissions($auth_rid, [
      'manage permissions'
      ]);
    $this->drupalGet('group/node/' . $node->nid . '/admin/permissions');
    $this->assertNoText($text, t('Restricted permissions are not displayed to the unprivileged user.'));

    // Check that restricted permissions are displayed to a user with administer
    // group.
    og_role_revoke_permissions($auth_rid, [
      'manage permissions'
      ]);
    og_role_grant_permissions($auth_rid, ['administer group']);
    $this->drupalGet('group/node/' . $node->nid . '/admin/permissions');
    $this->assertText($text, t('Restricted permissions are displayed to the privileged user.'));
  }

}
