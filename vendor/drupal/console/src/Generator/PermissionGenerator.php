<?php

/**
 * @file
 * Contains Drupal\Console\Generator\PermissionGenerator.
 */

namespace Drupal\Console\Generator;

use Drupal\Console\Extension\Manager;

class PermissionGenerator extends Generator
{

    /** @var Manager  */
    protected $extensionManager;


    /**
     * PermissionGenerator constructor.
     * @param Manager $extensionManager
     */
    public function __construct(
        Manager $extensionManager
    ) {
        $this->extensionManager = $extensionManager;
    }

    /**
     * @param  $module
     * @param  $permissions
     */
    public function generate($module, $permissions)
    {
        $parameters = array(
          'module_name' => $module,
          'permissions' => $permissions,
        );

        $this->renderFile(
            'module/permission.yml.twig',
            $this->extensionManager->getModule($module)->getPath().'/'.$module.'.permissions.yml',
            $parameters,
            FILE_APPEND
        );

        $content = $this->getRenderHelper()->render(
            'module/permission-routing.yml.twig',
            $parameters
        );

        if ($this->isLearning()) {
            echo 'You can use this permission in the routing file like this:'.PHP_EOL;
            echo $content;
        }
    }
}
