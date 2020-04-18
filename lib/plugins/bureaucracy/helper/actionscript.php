<?php

class helper_plugin_bureaucracy_actionscript extends helper_plugin_bureaucracy_action {

    protected $scriptNamePattern = '/^[_a-zA-Z0-9]+\.php$/';

    /**
     * @inheritDoc
     * @throws \InvalidArgumentException
     */
    public function run($fields, $thanks, $argv) {
        if (count($argv) < 1) {
            throw new InvalidArgumentException('The "script"-action expects exactly 1 argument: the script name.');
        }

        $scriptName = $argv[0];

        if (!$this->validateScriptName($scriptName)) {
            $cleanedScriptName = hsc($scriptName);
            throw new InvalidArgumentException("The supplied scriptname \"<code>$cleanedScriptName</code>\" is invalid! It must conform to <code>{hsc($this->scriptNamePattern)}</code>!");
        }

        $path = DOKU_CONF . 'plugin/bureaucracy/' . $scriptName;

        if (!file_exists($path)) {
            $shortPath = 'conf/plugin/bureaucracy/' . $scriptName;
            throw new InvalidArgumentException("Script <code>$shortPath</code> doesn't exist!");
        }

        require $path;

        $classFragment = substr($scriptName, 0, strpos($scriptName, '.'));
        $className = 'helper_plugin_bureaucracy_handler_' . $classFragment;

        $deprecatedClassName = 'bureaucracy_handler_' . $classFragment;
        if (!class_exists($className) && class_exists($deprecatedClassName)) {
            msg("Please change this script's class-name to <code>$className</code>.
Your current scheme <code>$deprecatedClassName</code> is deprecated and will stop working in the future.", 2);
            $className = $deprecatedClassName;
        }

        /** @var dokuwiki\plugin\bureaucracy\interfaces\bureaucracy_handler_interface $handler */
        $handler = new $className;

        if (!is_a($handler, dokuwiki\plugin\bureaucracy\interfaces\bureaucracy_handler_interface::class)) {
            throw new InvalidArgumentException('The handler must implement the interface <code>dokuwiki\\plugin\\bureaucracy\\interfaces\\bureaucracy_handler_interface</code> !');
        }

        return $handler->handleData($fields, $thanks);
    }

    /**
     * @param $scriptName
     *
     * @return bool
     */
    protected function validateScriptName($scriptName) {
        $valid = preg_match($this->scriptNamePattern, $scriptName);
        return $valid === 1;
    }

}
