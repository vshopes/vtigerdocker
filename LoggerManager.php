<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/*
$_ENV['VT_DEBUG'] = 'true, caio';
$key = '';
for ($i = 0; $i < strlen(trim$_ENV['VT_DEBUG']); $i++) {
    $char = $_ENV['VT_DEBUG'][$i];
    if ()
        var_dump($char);
}
*/

/** Classes to avoid logging */
class LoggerManager
{
	static function getlogger($name = 'ROOT') {
		$configinfo = LoggerPropertyConfigurator::getInstance()->getConfigInfo($name);
		return new Logger($name, $configinfo);
	}
}

/**
 * Core logging class.
 */
class Logger
{
	private $name = false;
	private $appender = false;
	private $configinfo = false;
	
	/**
	 * Writing log file information could cost in-terms of performance.
	 * Enable logging based on the levels here explicitly
	 */
	private $enableLogLevel = array(
        'FATAL' => false,
		'ERROR' => false,
        'WARN'  => false,
		'INFO'  => false,
		'DEBUG' => false,
	);

    /**
     * @var array
     */
    private $logLevelWeight = [
        'FATAL' => 0,
        'ERROR' => 1,
        'WARN'  => 2,
        'INFO'  => 3,
        'DEBUG' => 4
    ];

    /**
     * Logger constructor.
     *
     * @param $name
     * @param bool $configinfo
     */
	function __construct($name, $configinfo = false)
    {
        if ($configinfo) {
            echo '<pre>';
            var_dump($name, $configinfo, $this->enableLogLevel);
            echo '</pre>';
        }

        $this->name = $name;
		$this->configinfo = $configinfo;
		
		/** For migration log-level we need debug turned-on */
		if(strtoupper($name) == 'MIGRATION') {
			$this->enableLogLevel['DEBUG'] = true;
		}
	}

    /**
     * @param $level
     * @param $message
     */
	function emit($level, $message)
    {
		if (!$this->appender) {
			$filename = 'logs/vtigercrm.log';			
			if ($this->configinfo && isset($this->configinfo['appender']['File'])) {
				$filename = $this->configinfo['appender']['File'];
			}
			$this->appender = new LoggerAppenderFile($filename, 0777); 
		}

		$mypid = @getmypid();
		
		$this->appender->emit("$level [$mypid] $this->name - ", $message);
	}

    /**
     * @param $message
     */
	function info($message)
    {
	    if ($this->isLevelEnabled('INFO')) {
            $this->emit('INFO', $message);
		}
	}
	
	function debug($message) {
		if($this->isDebugEnabled()) {
			$this->emit('DEBUG', $message);
		}
	}
	
	function warn($message) {
		if($this->isLevelEnabled('WARN')) {
			$this->emit('WARN', $message);
		}
	}
	
	function fatal($message) {
		if($this->isLevelEnabled('FATAL')) {
			$this->emit('FATAL', $message);
		}		
	}

    /**
     * @param $message
     */
	function error($message)
    {
		if($this->isLevelEnabled('ERROR')) {
			$this->emit('ERROR', $message);
		}
	}

    /**
     * @param $level
     * @return bool
     */
	function isLevelEnabled($level)
    {
		if ($this->enableLogLevel[$level] && $this->configinfo) {
			return $this->isLevelRelevantThan($level, $this->configinfo['level']);
		}

		return false;
	}

    /**
     * @param $level1
     * @param $level2
     */
	function isLevelRelevantThen($level1, $level2)
    {
        return $this->logLevelWeight[$level1] >= $this->logLevelWeight[$level2];
    }

    /**
     * @return bool
     */
	function isDebugEnabled()
    {
		return $this->isLevelEnabled('DEBUG');
	}
}

/**
 * Log message appender to file.
 */
class LoggerAppenderFile
{
    /**
     * @var
     */
	private $filename;

    /**
     * @var int
     */
	private $chmod;

    /**
     * LoggerAppenderFile constructor.
     * @param $filename
     * @param int $chmod
     */
	function __construct($filename, $chmod = 0222)
    {
		$this->filename = $filename;
		$this->chmod    = $chmod;
	}

    /**
     * @param $prefix
     * @param $message
     */
	function emit($prefix, $message)
    {
		if ($this->chmod != 0777 && file_exists($this->filename)) {
			if (is_readable($this->filename)) {
				chmod($this->filename, $this->chmod);
			}
		}
		$fh = fopen($this->filename, 'a');
		if ($fh) {
			fwrite($fh, date('m/d/Y H:i:s') . " $prefix $message\n");
			fclose($fh);
		}
	}
}
