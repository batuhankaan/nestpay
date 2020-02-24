<?php

namespace NestPay\Model;

/**
 * Class Log
 *
 * @package NestPay\Model
 */
class Log {

    /**
     * Log message
     *
     * @param $text
     */
    public function logMsg($text) {
        $this->log('nestpay_messages', $text);
    }

    /**
     * Log error
     *
     * @param $text
     */
    public function logError($text) {
        $this->log('nestpay_errors', $text);
    }

    /**
     * Serialize array for log
     *
     * @param  array $arr
     * @return string
     */
    public function serializeForLog($arr=array()) {
        $chunks = array();
        foreach ($arr as $k => $v)
            $chunks[] = sprintf("%s=%s", $k, preg_replace('/\r?\n/', '', $v));
        return implode(', ', $chunks);
    }

    /**
     * Log to file
     *
     * @param string $file
     * @param string $txt
     */
    private function log($file, $txt) {
        file_put_contents(
            Settings::getInstance()->getLogDir() . '/' . $file,
            sprintf("%s (%s) - %s\n", date('Y-m-d H:i:s'), $this->getIp(), $txt),
            FILE_APPEND | LOCK_EX
        );
    }

    /**
     * Get current IP
     *
     * @return string
     */
    private function getIp() {
        return trim(
            array_pop( // might be proxied so take the last IP
                explode(
                    ',',
                    isset($_SERVER['HTTP_X_FORWARDED_FOR']) // might be forwarded
                        ? $_SERVER['HTTP_X_FORWARDED_FOR']
                        : $_SERVER['REMOTE_ADDR']
                )
            )
        );
    }

    /**
     * Get singleton instance
     *
     * @return Log
     */
    public static function getInstance() {
        if (static::$instance == null)
            static::$instance = new Log();
        return static::$instance;
    }

    /** @var Log */
    private static $instance = null;

    /**
     * Log constructor.
     */
    private function __construct() { }

}