<?php
namespace Drupal\og_ui;

/**
 * Groups with different selective state (e.g. open, moderated, etc'.).
 */
class ogGroupSelectiveState implements ogContent {
  public function groupList($user_ids) {
    $list = array();

    foreach (og_selective_map() as $key => $value) {
      $list[] = array(
        'title' => 'group-selective-state-' . $key,
        'uid' => $user_ids[3],
        'body' => 'Group with selective state set to ' . $value,
        'og_description' => 'Group with selective state set.',
        'og_selective' => $key,
      );
    }

    return $list;
  }

  public function postList($user_ids, $groups) {
    return array();
  }

  public function groupActions($user_ids, $groups, $posts) {}
}
