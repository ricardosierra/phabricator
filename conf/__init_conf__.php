<?php

function phabricator_read_config_file($original_config) {
  $root = dirname(dirname(__FILE__));

  // Accept either "myconfig" (preferred) or "myconfig.conf.php".
  $config = 'production'; //preg_replace('/\.conf\.php$/', '', $original_config);
  $full_config_path = $original_config; //$root.'/conf/'.$config.'.conf.php';

  if (!Filesystem::pathExists($full_config_path)) {
    // These are very old configuration files which we used to ship with
    // by default. File based configuration was de-emphasized once web-based
    // configuration was built. The actual files were removed to reduce
    // user confusion over how to configure Phabricator.

    switch ($config) {
      case 'default':
      case 'production':
        return array();
      case 'development':
        return array(
          'phabricator.developer-mode'      => true,
          'darkconsole.enabled'             => true,
        );
    }

    $files = id(new FileFinder($root.'/conf/'))
      ->withType('f')
      ->withSuffix('conf.php')
      ->withFollowSymlinks(true)
      ->find();

    foreach ($files as $key => $file) {
      $file = trim($file, './');
      $files[$key] = preg_replace('/\.conf\.php$/', '', $file);
    }
    $files = '    '.implode("\n    ", $files);

    throw new Exception(
      pht(
        "CONFIGURATION ERROR\n".
        "Config file '%s' does not exist. Valid config files are:\n\n%s",
        $original_config,
        $files));
  }

  $conf = [
    // 'mysql' => [
        'mysql.host' => config('database.connections.mysql.host'),
        'mysql.port' => config('database.connections.mysql.port'),
        'mysql.user' => config('database.connections.mysql.username'),
        'mysql.pass' => config('database.connections.mysql.password'),
        'mysql.database' => config('database.connections.mysql.database'),

        'cluster.instance' => '',
        'phabricator.serious-business' => '',
        'phabricator.base-uri' => \Validate\Url::toDatabase(config('app.url')),
        'phabricator.production-uri' => \Validate\Url::toDatabase(config('app.url')),
        'phabricator.allowed-uris' => '',
        'debug.time-limit' => '',
        'debug.sample-rate' => '',
        'debug.stop-on-redirect' => '',
        'phabricator.developer-mode' => '',
        'cluster.addresses' => '',
        'security.alternate-file-domain' => '',
        'phurl.short-uri' => '',

    // ]
  ];

  // Use: PhabricatorEnv::getEnvConfig('mysql.database')

  return $conf;
}
