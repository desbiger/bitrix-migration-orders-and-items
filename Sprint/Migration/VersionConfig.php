<?php

namespace Sprint\Migration;

class VersionConfig
{
    private $configCurrent = array();

    private $configList = array();

    private $availablekeys = array(
        'migration_table',
        'migration_extend_class',
        'stop_on_errors',
        'show_other_solutions',
        'migration_dir',
        'tracker_task_url',
        'version_prefix',
        'version_builders',
        'show_admin_interface',
        'console_user',
    );

    public function __construct($configName) {
        $configName = empty($configName) ? 'cfg' : $configName;

        $this->configList = array();
        $this->configList['cfg'] = $this->prepareConfig('cfg', array());

        $directory = new \DirectoryIterator(Module::getPhpInterfaceDir());
        foreach ($directory as $item) {
            if (!$item->isFile()) {
                continue;
            }

            if (!preg_match('/^migrations\.([a-z0-9_-]*)\.php$/i', $item->getFilename(), $matches)) {
                continue;
            }

            /** @noinspection PhpIncludeInspection */
            $values = include $item->getPathname();
            if (!$this->validConfig($values)) {
                continue;
            }

            $cname = $matches[1];
            $this->configList[$cname] = $this->prepareConfig($cname, $values);
        }

        if (isset($this->configList[$configName])) {
            $this->configCurrent = $this->configList[$configName];
        } else {
            $this->configCurrent = $this->configList['cfg'];
        }
    }

    public function getConfigName() {
        return $this->configCurrent['name'];
    }

    public function getConfigList() {
        return $this->configList;
    }

    public function getConfigCurrent() {
        return $this->configCurrent;
    }

    protected function validConfig($values) {
        foreach ($this->availablekeys as $key) {
            if (isset($values[$key])) {
                return true;
            }
        }
        return false;
    }

    protected function prepareConfig($configName, $configValues = array()) {
        $configValues = $this->prepareConfigValues($configValues);
        if (!empty($configValues['title'])) {
            $title = $configValues['title'];
            unset($configValues['title']);
        } else {
            $title = GetMessage('SPRINT_MIGRATION_CONFIG_TITLE');
        }
        $title = sprintf('%s (%s)', $title, 'migrations.' . $configName . '.php');
        return array(
            'name' => $configName,
            'title' => $title,
            'values' => $configValues
        );
    }

    protected function prepareConfigValues($values = array()) {
        if (empty($values['migration_extend_class'])) {
            $values['migration_extend_class'] = 'Version';
        }

        if (empty($values['migration_table'])) {
            $values['migration_table'] = 'sprint_migration_versions';
        }

        if (empty($values['migration_dir'])) {
            $values['migration_dir'] = Module::getPhpInterfaceDir() . '/migrations';
        } else {
            $values['migration_dir'] = Module::getDocRoot() . $values['migration_dir'];
        }

        if (!is_dir($values['migration_dir'])) {
            mkdir($values['migration_dir'], BX_DIR_PERMISSIONS, true);
            $values['migration_dir'] = realpath($values['migration_dir']);
        } else {
            $values['migration_dir'] = realpath($values['migration_dir']);
        }

        if (empty($values['version_prefix'])) {
            $values['version_prefix'] = 'Version';
        }

        if (isset($values['show_admin_interface']) && !$values['show_admin_interface']) {
            $values['show_admin_interface'] = false;
        } else {
            $values['show_admin_interface'] = true;
        }

        if (isset($values['stop_on_errors']) && $values['stop_on_errors']) {
            $values['stop_on_errors'] = true;
        } else {
            $values['stop_on_errors'] = false;
        }

        if (isset($values['show_other_solutions']) && !$values['show_other_solutions']) {
            $values['show_other_solutions'] = false;
        } else {
            $values['show_other_solutions'] = true;
        }

        if (empty($values['tracker_task_url'])) {
            $values['tracker_task_url'] = '';
        }

        $cond1 = isset($values['console_user']);
        $cond2 = ($cond1 && $values['console_user'] === false);
        $cond3 = ($cond1 && strpos($values['console_user'],'login:') === 0);

        $values['console_user'] = ($cond2 || $cond3) ? $values['console_user'] : 'admin';

        if (!empty($values['version_builders']) && is_array($values['version_builders'])) {
            $values['version_builders'] = array_merge($this->getVersionBuilders(), $values['version_builders']);
        } else {
            $values['version_builders'] = $this->getVersionBuilders();
        }

        ksort($values);
        return $values;
    }

    public function getConfigVal($name, $default = '') {
        if (isset($this->configCurrent['values'][$name])) {
            if (is_bool($this->configCurrent['values'][$name])){
                return $this->configCurrent['values'][$name];
            } elseif (!empty($this->configCurrent['values'][$name])){
                return $this->configCurrent['values'][$name];
            }
        }

        return $default;
    }

    protected function getVersionBuilders() {
        return array(
            'Version' => '\Sprint\Migration\Builders\Version',
            'IblockExport' => '\Sprint\Migration\Builders\IblockExport',
            'HlblockExport' => '\Sprint\Migration\Builders\HlblockExport',
            'UserTypeEntities' => '\Sprint\Migration\Builders\UserTypeEntities',
        );
    }

}



