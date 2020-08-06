<?php

    // namespace
    namespace Plugin;

    /**
     * Performance
     * 
     * Performance plugin for TurtlePHP.
     * 
     * Analyzes a response that is ready for flushing and attached performance
     * metrics to headers.
     * 
     * @author  Oliver Nassar <onassar@gmail.com>
     * @extends Base
     */
    class Performance extends Base
    {
        /**
         * _request
         * 
         * @access  protected
         * @var     null|\Turtle\Request (default: null)
         */
        protected $_request = null;

        /**
         * __construct
         * 
         * @access  public
         * @param   \Turtle\Request $request
         * @return  void
         */
        public function __construct(\Turtle\Request $request)
        {
            $this->_request = $request;
            $this->_addDurationCallback();
            $this->_addMemcachedCacheCallback();
            $this->_addMemoryCallback();
            $this->_addMySQLConnectionCallback();
            $this->_addPathHeader();
            $this->_addRequestCacheCallback();
            $this->_addRequestsCallback();
        }

        /**
         * _addDurationCallback
         * 
         * @access  protected
         * @return  void
         */
        protected function _addDurationCallback()
        {
            $request = $this->_request;
            $request->addCallback(function(string $buffer) {
                $duration = $this->_getRequestDuration();
                $this->_addPerformanceHeader('Duration', $duration);
                return $buffer;
            });
        }

        /**
         * _addMemcachedCacheCallback
         * 
         * @access  protected
         * @return  void
         */
        protected function _addMemcachedCacheCallback()
        {
            $request = $this->_request;
            $request->addCallback(function(string $buffer) {
                if (class_exists('MemcachedCache') === true) {
                    $key = 'MemcachedCache-misses';
                    $misses = \MemcachedCache::getMisses();
                    $this->_addPerformanceHeader($key, $misses);
                    $key = 'MemcachedCache-reads';
                    $reads = \MemcachedCache::getReads();
                    $this->_addPerformanceHeader($key, $reads);
                    $key = 'MemcachedCache-writes';
                    $writes = \MemcachedCache::getWrites();
                    $this->_addPerformanceHeader($key, $writes);
                    $key = 'MemcachedCache-duration';
                    $duration = \MemcachedCache::getDuration();
                    $this->_addPerformanceHeader($key, $duration);
                }
                return $buffer;
            });
        }

        /**
         * _addMemoryCallback
         * 
         * @access  protected
         * @return  void
         */
        protected function _addMemoryCallback()
        {
            $request = $this->_request;
            $request->addCallback(function(string $buffer) {
                $memory = $this->_getPeakMemoryUsed();
                $this->_addPerformanceHeader('Memory', $memory);
                return $buffer;
            });
        }

        /**
         * _addMySQLConnectionCallback
         * 
         * @access  protected
         * @return  void
         */
        protected function _addMySQLConnectionCallback()
        {
            $request = $this->_request;
            $request->addCallback(function(string $buffer) {
                if (class_exists('MySQLConnection') === true) {
                    $key = 'MySQLConnection-selectQueries';
                    $selectQueries = \MySQLConnection::getNumberOfSelectQueries();
                    $this->_addPerformanceHeader($key, $selectQueries);
                    $key = 'MySQLConnection-insertQueries';
                    $insertQueries = \MySQLConnection::getNumberOfInsertQueries();
                    $this->_addPerformanceHeader($key, $insertQueries);
                    $key = 'MySQLConnection-updateQueries';
                    $updateQueries = \MySQLConnection::getNumberOfUpdateQueries();
                    $this->_addPerformanceHeader($key, $updateQueries);
                    $key = 'MySQLConnection-cumulativeQueryDuration';
                    $cumulativeQueryDuration = \MySQLConnection::getCumulativeQueryDuration();
                    $args = array($key, $cumulativeQueryDuration);
                    $this->_addPerformanceHeader(... $args);
                }
                return $buffer;
            });
        }

        /**
         * _addPathHeader
         * 
         * @access  protected
         * @return  void
         */
        protected function _addPathHeader(): void
        {
            $request = $this->_request;
            $request->addCallback(function(string $buffer) {
                $key = null;
                $path = $this->_getRequestRoutePath();
                $this->_addPerformanceHeader($key, $path);
                return $buffer;
            });
        }

        /**
         * _addPerformanceHeader
         * 
         * @access  protected
         * @param   null|string $key
         * @param   string $value
         * @return  void
         */
        protected function _addPerformanceHeader(?string $key, string $value): void
        {
            $requestRouteHash = $this->_getRequestRouteHash();
            $headerKey = 'TurtlePHP-' . ($requestRouteHash);
            if ($key !== null) {
                $headerKey = ($headerKey) . '-' . ($key);
            }
            $headerValue = $value;
            $value = ($headerKey) . ': ' . ($headerValue);
            parent::_setHeader($value);
        }

        /**
         * _addRequestCacheCallback
         * 
         * @access  protected
         * @return  void
         */
        protected function _addRequestCacheCallback()
        {
            $request = $this->_request;
            $request->addCallback(function(string $buffer) {
                if (class_exists('RequestCache') === true) {
                    $key = 'RequestCache-misses';
                    $misses = \RequestCache::getMisses();
                    $this->_addPerformanceHeader($key, $misses);
                    $key = 'RequestCache-reads';
                    $reads = \RequestCache::getReads();
                    $this->_addPerformanceHeader($key, $reads);
                    $key = 'RequestCache-writes';
                    $writes = \RequestCache::getWrites();
                    $this->_addPerformanceHeader($key, $writes);
                }
                return $buffer;
            });
        }

        /**
         * _addRequestsCallback
         * 
         * @access  protected
         * @return  void
         */
        protected function _addRequestsCallback()
        {
            $request = $this->_request;
            $request->addCallback(function(string $buffer) {
                $requests = $this->_getNumberOfRequest();
                $this->_addPerformanceHeader('NumberOfRequests', $requests);
                return $buffer;
            });
        }

        /**
         * _getNumberOfRequest
         * 
         * @access  protected
         * @return  int
         */
        protected function _getNumberOfRequest(): int
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
         * @return  string
         */
        protected function _getPeakMemoryUsed(): string
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
         * @return  float
         */
        protected function _getRequestDuration(): float
        {
            $currentTimestamp = microtime(true);
            $requestTimestamp = $this->_getRequestTimestamp();
            $duration = $currentTimestamp - $requestTimestamp;
            $duration = round($duration, 4);
            return $duration;
        }

        /**
         * _getRequestRouteHash
         * 
         * @access  protected
         * @return  string
         */
        protected function _getRequestRouteHash(): string
        {
            $request = $this->_request;
            $requestRouteHash = $request->getRoutePathHash();
            return $requestRouteHash;
        }

        /**
         * _getRequestRoutePath
         * 
         * @access  protected
         * @return  null|string
         */
        protected function _getRequestRoutePath(): ?string
        {
            $request = $this->_request;
            $requestRoutePath = $request->getRoutePath();
            return $requestRoutePath;
        }

        /**
         * _getRequestTimestamp
         * 
         * @access  protected
         * @return  float
         */
        protected function _getRequestTimestamp(): float
        {
            $request = $this->_request;
            $requestTimestamp = $request->getCreatedTimestamp();
            return $requestTimestamp;
        }
    }
