<?php
namespace Drupal\og_ui\Tests;

/**
 * Verify the people management functionality.
 *
 * @group og_ui
 */
class OgUiManagePeopleTestCase extends \Drupal\simpletest\WebTestBase {

  protected $profile = 'standard';

  public $user1;

  public $user2;

  public $entity;

  public static function getInfo() {
    return [
      'name' => 'OG UI manage people',
      'description' => 'Verify the people management functionality.',
      'group' => 'Organic groups UI',
    ];
  }

  public function setUp() {
    parent::setUp('og_ui', 'entity_feature');
    // Add OG group field.
    og_create_field(OG_GROUP_FIELD, 'entity_test', 'test');

    // Create users.
    $this->user1 = $this->drupalCreateUser();
    $this->user2 = $this->drupalCreateUser();

    // Create a group.
    $this->entity = \Drupal::entityManager()->getStorage('entity_test')->create([
      'name' => 'test',
      'uid' => $this->user1->uid,
    ]);
    $wrapper = entity_metadata_wrapper('entity_test', $this->entity);
    $wrapper->{OG_GROUP_FIELD}->set(1);
    $wrapper->save();

  }

  public /**
   * Test adding people via group/[entity_type]/[etid]/admin/people/add-user.
   */
  function testOgUiAddPeople() {
    $this->drupalLogin($this->user1);

    // Add user2 to the group.
    $this->assertFalse(og_is_member('entity_test', $this->entity->pid, 'user', $this->user2), 'User is not a group member');
    $edit = [];
    $edit['name'] = $this->user2->name;
    $this->drupalPost('group/entity_test/' . $this->entity->pid . '/admin/people/add-user', $edit, t('Add users'));

    // Reload user.
    og_invalidate_cache();
    $this->assertTrue(og_is_member('entity_test', $this->entity->pid, 'user', $this->user2), 'User was added to the group.');

    // Add the same user twice.
    $this->drupalPost('group/entity_test/' . $this->entity->pid . '/admin/people/add-user', $edit, t('Add users'));
    $this->assertText(t('User @name is already subscribed to group.', [
      '@name' => format_username($this->user2)
      ]), 'User can not be added twice.');

    // Add non-existing user.
    $edit = [];
    $edit['name'] = $this->randomName();
    $this->drupalPost('group/entity_test/' . $this->entity->pid . '/admin/people/add-user', $edit, t('Add users'));
    $this->assertText(t('You have entered an invalid user name.'), t('Invalid user name not added to group.'));
  }

  public /**
   * Change membership status for a single group user.
   */
  function testOgUiEditMembershipStatus() {
    $this->drupalLogin($this->user1);

    // Group the user to the group.
    $membership = og_group('entity_test', $this->entity->pid, [
      'entity' => $this->user2
      ]);

    // Updating the state status.
    $states = og_group_content_states();
    foreach ($states as $state => $title) {
      $this->drupalPost('group/entity_test/' . $this->entity->pid . '/admin/people/edit-membership/' . $membership->id, [
        'state' => $state
        ], t('Update membership'));

      // Reset the static cache for a fresh OG membership object.
      drupal_static_reset();
      $membership = og_membership_load($membership->id);

      // Verify the membership has updates successfully.
      $this->assertEqual($membership->state, $state, format_string('The membership state has updated successfully to @title', [
        '@title' => $title
        ]));
    }
  }

  public /**
   * Delete a single user's membership from group.
   */
  function testOgUiDeleteMembership() {
    $this->drupalLogin($this->user1);

    // Group the user to the group.
    $membership = og_group('entity_test', $this->entity->pid, [
      'entity' => $this->user2
      ]);
    $this->drupalPost('group/entity_test/' . $this->entity->pid . '/admin/people/delete-membership/' . $membership->id, [], t('Remove'));

    // Verify the membership was removed.
    $this->assertText('The membership was removed.');
  }

  public /**
   * Test that only the correct group audience fields are shown.
   */
  function testOgUiAddPeopleMultipleAudienceFields() {
    $user1 = $this->drupalCreateUser();
    $this->drupalLogin($user1);

    // Delete the default group audience field
    // @FIXME
    // Fields and field instances are now exportable configuration entities, and
    // the Field Info API has been removed.
    // 
    // 
    // @see https://www.drupal.org/node/2012896
// field_delete_field('og_user_entity_test');


    // Create three group audience fields and corresponding instances on users:
    // - Two for the two bundles on the 'entity_test' entity type.
    // - One for the 'entity_test2' entity type.
    // @FIXME
    // Fields and field instances are now exportable configuration entities, and
    // the Field Info API has been removed.
    // 
    // 
    // @see https://www.drupal.org/node/2012896
// $fields['group_audience_entity_test_test'] = field_create_field(array(
    //       'field_name' => 'group_audience_entity_test_test',
    //       'type' => 'entityreference',
    //       'settings' => array(
    //         'target_type' => 'entity_test',
    //         'handler' => 'og',
    //         'handler_settings' => array(
    //           'target_bundles' => array('test'),
    //           'membership_type' => 'og_membership_type_default',
    //         ),
    //       ),
    //     ));

    // @FIXME
    // Fields and field instances are now exportable configuration entities, and
    // the Field Info API has been removed.
    // 
    // 
    // @see https://www.drupal.org/node/2012896
// field_create_instance(array(
    //       'field_name' => 'group_audience_entity_test_test',
    //       'entity_type' => 'user',
    //       'bundle' => 'user',
    //     ));


    // @FIXME
    // Fields and field instances are now exportable configuration entities, and
    // the Field Info API has been removed.
    // 
    // 
    // @see https://www.drupal.org/node/2012896
// $field['group_audience_entity_test_test2'] = field_create_field(array(
    //       'field_name' => 'group_audience_entity_test_test2',
    //       'type' => 'entityreference',
    //       'settings' => array(
    //         'target_type' => 'entity_test',
    //         'handler' => 'og',
    //         'handler_settings' => array(
    //           'target_bundles' => array('test2'),
    //           'membership_type' => 'og_membership_type_default',
    //         ),
    //       ),
    //     ));

    // @FIXME
    // Fields and field instances are now exportable configuration entities, and
    // the Field Info API has been removed.
    // 
    // 
    // @see https://www.drupal.org/node/2012896
// field_create_instance(array(
    //       'field_name' => 'group_audience_entity_test_test2',
    //       'entity_type' => 'user',
    //       'bundle' => 'user',
    //     ));


    // @FIXME
    // Fields and field instances are now exportable configuration entities, and
    // the Field Info API has been removed.
    // 
    // 
    // @see https://www.drupal.org/node/2012896
// $field['group_audience_entity_test2'] = field_create_field(array(
    //       'field_name' => 'group_audience_entity_test2',
    //       'type' => 'entityreference',
    //       'settings' => array(
    //         'target_type' => 'entity_test2',
    //         'handler' => 'og',
    //         'handler_settings' => array(
    //           'membership_type' => 'og_membership_type_default',
    //         ),
    //       ),
    //     ));

    // @FIXME
    // Fields and field instances are now exportable configuration entities, and
    // the Field Info API has been removed.
    // 
    // 
    // @see https://www.drupal.org/node/2012896
// field_create_instance(array(
    //       'field_name' => 'group_audience_entity_test2',
    //       'entity_type' => 'user',
    //       'bundle' => 'user',
    //     ));


    // Create a group belonging to the 'test' bundle of the 'entity_test' entity
    // type.
    $entity = \Drupal::entityManager()->getStorage('entity_test')->create([
      'name' => 'test',
      'uid' => $user1->uid,
    ]);
    $wrapper = entity_metadata_wrapper('entity_test', $entity);
    $wrapper->{OG_GROUP_FIELD}->set(1);
    $wrapper->save();

    // Because only one of the three fields applies to this entity type and
    // bundle, no select box should be shown.
    $this->drupalGet('group/entity_test/' . $entity->pid . '/admin/people/add-user');
    $this->assertNoField('edit-field-name');

    // Temporarily change the second field to apply to this bundle. Now the
    // select box should be shown.
    $field['group_audience_entity_test_test2']['settings']['handler_settings']['target_bundles'] = [
      'test'
      ];
    $field['group_audience_entity_test_test2']->save();
    $this->drupalGet('group/entity_test/' . $entity->pid . '/admin/people/add-user');
    $this->assertField('edit-field-name');
    $elements = $this->xpath('//select[@id="edit-field-name"]//option');
    $this->assertEqual(count($elements), 2, '2 options available for selection');
    $elements = $this->xpath('//select[@id="edit-field-name"]//option[@value="group_audience_entity_test_test"]');
    $this->assertTrue(isset($elements[0]), '<em>group_audience_entity_test_test</em> field available for selection');
    $elements = $this->xpath('//select[@id="edit-field-name"]//option[@value="group_audience_entity_test_test2"]');
    $this->assertTrue(isset($elements[0]), '<em>group_audience_entity_test_test2</em> field available for selection');

    // Revert the field settings to the previous state.
    $field['group_audience_entity_test_test2']['settings']['handler_settings']['target_bundles'] = [
      'test2'
      ];
    $field['group_audience_entity_test_test2']->save();
    $this->drupalGet('group/entity_test/' . $entity->pid . '/admin/people/add-user');
    $this->assertNoField('edit-field-name');

    // Change the third field to apply to this entity type. In this case the
    // select box should be shown, as well.
    $field['group_audience_entity_test2']['settings']['target_type'] = 'entity_test';
    $field['group_audience_entity_test2']->save();
    $this->drupalGet('group/entity_test/' . $entity->pid . '/admin/people/add-user');
    $this->assertField('edit-field-name');
    $elements = $this->xpath('//select[@id="edit-field-name"]//option');
    $this->assertEqual(count($elements), 2, '2 options available for selection');
    $elements = $this->xpath('//select[@id="edit-field-name"]//option[@value="group_audience_entity_test_test"]');
    $this->assertTrue(isset($elements[0]), '<em>group_audience_entity_test_test</em> field available for selection');
    $elements = $this->xpath('//select[@id="edit-field-name"]//option[@value="group_audience_entity_test2"]');
    $this->assertTrue(isset($elements[0]), '<em>group_audience_entity_test2</em> field available for selection');
  }

  /**
   * Tests that invalid group IDs in the menu path do not cause exceptions.
   */
  public function testOgUiPath() {
    $this->drupalGet('entity_test/' . $this->entity->pid . 'invalid/group');
    $this->assertResponse(403);
    // Numeric values that are not integers are forbidden, too.
    $this->drupalGet('entity_test/' . $this->entity->pid . '.333/group');
    $this->assertResponse(403);
  }

}
