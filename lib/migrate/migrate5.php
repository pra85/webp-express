<?php

namespace WebPExpress;


include_once __DIR__ . '/../classes/Config.php';
use \WebPExpress\Config;

include_once __DIR__ . '/../classes/Messenger.php';
use \WebPExpress\Messenger;


function webpexpress_migrate5() {

    // Regenerate configuration file and wod-options.json.

    // By regenerating the config, we ensure that Config::updateAutoloadedOptions() is called,
    // By regenerating wod-options.json, we ensure that the new "paths" option is there, which is required in "mingled" mode for
    // determining if an image resides in the uploads folder or not.

    $config = Config::loadConfigAndFix();
    if ($config['operation-mode'] == 'just-convert') {
        $config['operation-mode'] = 'no-varied-responses';
    }
    if ($config['operation-mode'] == 'standard') {
        $config['operation-mode'] = 'varied-responses';
    }

    if (Config::saveConfigurationFileAndWodOptions($config)) {
        Messenger::addMessage(
            'info',
            'Successfully migrated webp express options for 0.11+'
        );

        // PSST: When creating new migration files, remember to update WEBPEXPRESS_MIGRATION_VERSION in admin.php
        update_option('webp-express-migration-version', '5');

    } else {
        Messenger::addMessage(
            'error',
            'Failed migrating webp express options to 0.11+. Probably you need to grant write permissions in your wp-content folder.'
        );
    }

}

webpexpress_migrate5();
