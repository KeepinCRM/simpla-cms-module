<?php

/**
 * KeepinCRM module for Simpla CMS
 *
 * @copyright   2021 Michael Khiminets
 * @link        https://keepincrm.com/
 * @author      Michael Khiminets
 *
 */

class KeepinCrm extends Simpla {
  private $api_key, $source_id;

  public function __construct() {
    $config = self::config(__DIR__ . '/../config/keepincrm.php');

    $this->api_key   = $config['api_key'];
    $this->source_id = $config['source_id'];
  }
}
