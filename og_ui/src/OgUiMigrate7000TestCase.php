<?php
namespace Drupal\og_ui;

/**
 * Upgrade 7000 test.
 *
 * Load a filled installation of Drupal 6 and run the upgrade process on it.
 */
class OgUiMigrate7000TestCase extends UpgradePathTestCase {
  public static function getInfo() {
    return array(
      'name'  => 'OG UI upgrade path',
      'description'  => 'Tests the upgrade path of Organic groups UI.',
      'group' => 'Organic groups UI',
      'dependencies' => array('migrate'),
    );
  }

  public function setUp() {
    // Path to the database dump.
    $this->databaseDumpFiles = array(
      drupal_get_path('module', 'og_ui') . '/tests/drupal-6.og-ui.database.php',
    );
    parent::setUp();
    $this->assertTrue($this->performUpgrade(), 'The upgrade was completed successfully.');

    // spl_autoload_register() wasn't called, so we do it here, to allow
    // classes to be auto-loaded.
    spl_autoload_register('drupal_autoload_class');
    spl_autoload_register('drupal_autoload_interface');

    module_enable(array('og_ui', 'migrate'));

    foreach (migrate_migrations() as $migration) {
      $machine_name = $migration->getMachineName();
      $result = $migration->processImport();
      $this->assertEqual($result, Migration::RESULT_COMPLETED, format_string('Migration @name executed.', array('@name' => $machine_name)));
    }
  }

  /**
   * Test a successful upgrade.
   */
  public function testUpgrade() {
    // Assert according to the scenario Drupal 6's test table dump was created.
    $nodes_info = array(
      // Open group.
      1 => array(
        'name' => t('open'),
        'anon' => array(
          'subscribe' => FALSE,
          'subscribe without approval' => TRUE,
        ),
      ),
      // Moderated group.
      2 => array(
        'name' => t('moderated'),
        'anon' => array(
          'subscribe' => TRUE,
          'subscribe without approval' => FALSE,
        ),
      ),
      // Invite only group.
      3 => array(
        'name' => t('invite only'),
        'anon' => array(
          'subscribe' => FALSE,
          'subscribe without approval' => FALSE,
        ),
      ),
      // Closed group.
      4 => array(
        'name' => t('closed'),
        'anon' => array(
          'subscribe' => FALSE,
          'subscribe without approval' => FALSE,
        ),
        'auth' => array('unsubscribe' => FALSE),
      ),
    );

    foreach ($nodes_info as $nid => $node_info) {
      // Set default values.
      $node_info += array('auth' => array('unsubscribe' => TRUE));

      $og_roles = og_roles('node', 'test_group', $nid, TRUE);
      $permissions = og_role_permissions($og_roles);

      $anon_rid = array_search(OG_ANONYMOUS_ROLE, $og_roles);
      $auth_rid = array_search(OG_AUTHENTICATED_ROLE, $og_roles);

      // Assert permissions for non-member and member roles.
      $this->assertEqual($permissions[$anon_rid], array_filter($node_info['anon']), t('Correct permissions were set for non-member role in @type group.', array('@type' => $node_info['name'])));
      $this->assertEqual($permissions[$auth_rid], array_filter($node_info['auth']), t('Correct permissions were set for member role in @type group.', array('@type' => $node_info['name'])));
    }
  }
}
