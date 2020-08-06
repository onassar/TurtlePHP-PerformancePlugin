<?php

    // namespace
    namespace Plugin;

    /**
     * Performance
     * 
     * Performance plugin for TurtlePHP.
     * 
     * Analyzes a response that is ready for flushing and attaches performance
     * metrics to the HTTP headers.
     * 
     * @author  Oliver Nassar <onassar@gmail.com>
     * @abstract
     * @extends Base
     */
    abstract class Performance extends Base
    {
        /**
         * _configPath
         *
         * @access  protected
         * @var     string (default: 'config.default.inc.php')
         * @static
         */
        protected static $_configPath = 'config.default.inc.php';

        /**
         * _request
         * 
         * @access  protected
         * @static
         * @var     null|\Turtle\Request (default: null)
         */
        protected static $_request = null;

        /**
         * _initiated
         *
         * @access  protected
         * @var     bool (default: false)
         * @static
         */
        protected static $_initiated = false;

        /**
         * _addApplicationHook
         * 
         * @access  protected
         * @static
         * @param   callable $callback
         * @return  void
         */
        protected static function _addApplicationHook(callable $callback): void
        {
            $hookKey = 'flush';
            \Turtle\Application::addHook($hookKey, $callback);
        }

        /**
         * _addDurationCallback
         * 
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _addDurationCallback(): void
        {
            static::_addApplicationHook(function(?string $buffer): bool {
                if (headers_sent() === true) {
                    return false;
                }
                $key = 'Duration';
                $duration = static::_getRequestDuration();
                static::_addPerformanceHeader($key, $duration);
                return true;
            });
        }

        /**
         * _addMemcachedCacheCallback
         * 
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _addMemcachedCacheCallback(): void
        {
            static::_addApplicationHook(function(?string $buffer): bool {
                if (headers_sent() === true) {
                    return false;
                }
                if (class_exists('MemcachedCache') === false) {
                    return false;
                }
                $key = 'MemcachedCache-misses';
                $misses = \MemcachedCache::getMisses();
                static::_addPerformanceHeader($key, $misses);
                $key = 'MemcachedCache-reads';
                $reads = \MemcachedCache::getReads();
                static::_addPerformanceHeader($key, $reads);
                $key = 'MemcachedCache-writes';
                $writes = \MemcachedCache::getWrites();
                static::_addPerformanceHeader($key, $writes);
                $key = 'MemcachedCache-duration';
                $duration = \MemcachedCache::getDuration();
                static::_addPerformanceHeader($key, $duration);
                return true;
            });
        }

        /**
         * _addMemoryCallback
         * 
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _addMemoryCallback(): void
        {
            static::_addApplicationHook(function(?string $buffer): bool {
                if (headers_sent() === true) {
                    return false;
                }
                $key = 'Memory';
                $memory = static::_getPeakMemoryUsed();
                static::_addPerformanceHeader($key, $memory);
                return true;
            });
        }

        /**
         * _addMySQLConnectionCallback
         * 
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _addMySQLConnectionCallback(): void
        {
            static::_addApplicationHook(function(?string $buffer): bool {
                if (headers_sent() === true) {
                    return false;
                }
                if (class_exists('MySQLConnection') === false) {
                    return false;
                }
                $key = 'MySQLConnection-selectQueries';
                $selectQueries = \MySQLConnection::getNumberOfSelectQueries();
                static::_addPerformanceHeader($key, $selectQueries);
                $key = 'MySQLConnection-insertQueries';
                $insertQueries = \MySQLConnection::getNumberOfInsertQueries();
                static::_addPerformanceHeader($key, $insertQueries);
                $key = 'MySQLConnection-updateQueries';
                $updateQueries = \MySQLConnection::getNumberOfUpdateQueries();
                static::_addPerformanceHeader($key, $updateQueries);
                $key = 'MySQLConnection-cumulativeQueryDuration';
                $cumulativeQueryDuration = \MySQLConnection::getCumulativeQueryDuration();
                static::_addPerformanceHeader($key, $cumulativeQueryDuration);
                return true;
            });
        }

        /**
         * _addPerformanceHeader
         * 
         * @access  protected
         * @static
         * @param   string $key
         * @param   string $value
         * @return  void
         */
        protected static function _addPerformanceHeader(string $key, string $value): void
        {
            $headerKey = 'TurtlePHP-' . ($key);
            $headerValue = $value;
            $value = ($headerKey) . ': ' . ($headerValue);
            parent::_setHeader($value);
        }

        /**
         * _addRequestCacheCallback
         * 
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _addRequestCacheCallback(): void
        {
            static::_addApplicationHook(function(?string $buffer): bool {
                if (headers_sent() === true) {
                    return false;
                }
                if (class_exists('RequestCache') === false) {
                    return false;
                }
                $key = 'RequestCache-misses';
                $misses = \RequestCache::getMisses();
                static::_addPerformanceHeader($key, $misses);
                $key = 'RequestCache-reads';
                $reads = \RequestCache::getReads();
                static::_addPerformanceHeader($key, $reads);
                $key = 'RequestCache-writes';
                $writes = \RequestCache::getWrites();
                static::_addPerformanceHeader($key, $writes);
                return true;
            });
        }

        /**
         * _addRequestsCallback
         * 
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _addRequestsCallback(): void
        {
            static::_addApplicationHook(function(?string $buffer): bool {
                if (headers_sent() === true) {
                    return false;
                }
                $key = 'NumberOfRequests';
                $requests = static::_getNumberOfRequest();
                static::_addPerformanceHeader($key, $requests);
                return true;
            });
        }

        /**
         * _addRouteHeader
         * 
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _addRouteHeader(): void
        {
            static::_addApplicationHook(function(?string $buffer): bool {
                if (headers_sent() === true) {
                    return false;
                }
                $key = 'Route';
                $path = static::_getRequestRoutePath() ?? ':unknown:';
                static::_addPerformanceHeader($key, $path);
                return true;
            });
        }

        /**
         * _checkDependencies
         * 
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _checkDependencies(): void
        {
            static::_checkConfigPluginDependency();
        }

        /**
         * _getNumberOfRequest
         * 
         * @access  protected
         * @static
         * @return  int
         */
        protected static function _getNumberOfRequest(): int
        {
            $requests = \Turtle\Application::getRequests();
            $numberOfRequests = count($requests);
            return $numberOfRequests;
        }

        /**
         * _getPeakMemoryUsed
         * 
         * @see     https://www.php.net/manual/en/function.memory-get-peak-usage.php
         * @access  protected
         * @static
         * @return  string
         */
        protected static function _getPeakMemoryUsed(): string
        {
            $realUsage = true;
            $memory = memory_get_peak_usage($realUsage);
            $memory = $memory / 1024;
            $memory = round($memory);
            $memory = number_format($memory) . 'kb';
            return $memory;
        }

        /**
         * _getRequestDuration
         * 
         * @access  protected
         * @static
         * @return  float
         */
        protected static function _getRequestDuration(): float
        {
            $currentTimestamp = microtime(true);
            $requestStartTimestamp = $_SERVER['REQUEST_TIME_FLOAT'];
            $duration = $currentTimestamp - $requestStartTimestamp;
            $duration = round($duration, 4);
            return $duration;
        }

        /**
         * _getRequestRoutePath
         * 
         * @access  protected
         * @static
         * @return  null|string
         */
        protected static function _getRequestRoutePath(): ?string
        {
            $request = \Turtle\Application::getRequest();
            $requestRoutePath = $request->getRoutePath();
            return $requestRoutePath;
        }

        /**
         * init
         * 
         * @access  public
         * @static
         * @return  bool
         */
        public static function init(): bool
        {
            if (static::$_initiated === true) {
                return false;
            }
            parent::init();
            static::_addDurationCallback();
            static::_addMemcachedCacheCallback();
            static::_addMemoryCallback();
            static::_addMySQLConnectionCallback();
            static::_addRouteHeader();
            static::_addRequestCacheCallback();
            static::_addRequestsCallback();
            return true;
        }
    }

    // Config path loading
    $info = pathinfo(__DIR__);
    $parent = ($info['dirname']) . '/' . ($info['basename']);
    $configPath = ($parent) . '/config.inc.php';
    \Plugin\Database::setConfigPath($configPath);
