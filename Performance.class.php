<?php

    // namespace
    namespace Plugin;

    /**
     * Performance
     * 
     * TurtlePHP performance plugin which analyzes a response that is ready for
     * flushing, determines it's processing duration and memory usage, and
     * returns them through custom response-headers.
     * 
     * @author Oliver Nassar <onassar@gmail.com>
     * @notes  with PHP 5.4.x, $_SERVER['REQUEST_TIME'] can be used rather than
     *         the <START> constant, as it'll be set as a float including
     *         microtime
     */
    class Performance
    {
        /**
         * _hash
         * 
         * @var    array
         * @access protected
         */
        protected $_hash;

        /**
         * __construct
         * 
         * Initializes the performance plugin by registering analytical callback
         * methods on the request buffer.
         * 
         * @notes  The request callbacks registered here subsequently register
         *         another request callback to ensure that they are the last
         *         ones run. This does not cause an issue, as the callbacks are
         *         retrieved and run by reference, rather than through returned
         *         Closure objects.
         * @access public
         * @param  Request $request
         * @return void
         */
        public function __construct(\Turtle\Request $request)
        {
            // instance reference
            $self = $this;

            // set header
            $request->addCallback(function($buffer) use ($request, $self) {
                $self->setPathHeader($request);
                return $buffer;
            });

            // Callbacks
            $this->_addDurationCallback($request);
            $this->_addMemcachedCacheCallback($request);
            $this->_addMemoryCallback($request);
            $this->_addMySQLConnectionCallback($request);
            $this->_addRequestCacheCallback($request);
            $this->_addRequestsCallback($request);
        }

        /**
         * _addDurationCallback
         * 
         * @access protected
         * @param  Request $request
         * @return void
         */
        protected function _addDurationCallback(\Turtle\Request $request)
        {
            $self = $this;
            $request->addCallback(function($buffer) use ($request, $self) {
                $request->addCallback(function($buffer) use($self) {
                    $benchmark = round(microtime(true) - START, 4);
                    header(
                        'TurtlePHP-' . ($self->getHash()) . '-Duration: ' .
                        ($benchmark)
                    );
                    return $buffer;
                });
                return $buffer;
            });
        }

        /**
         * _addMemcachedCacheCallback
         * 
         * @access protected
         * @param  Request $request
         * @return void
         */
        protected function _addMemcachedCacheCallback(\Turtle\Request $request)
        {
            $self = $this;
            $request->addCallback(function($buffer) use ($request, $self) {
                if (class_exists('MemcachedCache')) {
                    $request->addCallback(function($buffer) use($self) {

                        // misses
                        $numberOfMisses = \MemcachedCache::getMisses();
                        header(
                            'TurtlePHP-'. ($self->getHash()) . '-MemcachedCache-numberOfMisses: ' .
                            ($numberOfMisses)
                        );

                        // reads
                        $numberOfReads = \MemcachedCache::getReads();
                        header(
                            'TurtlePHP-'. ($self->getHash()) . '-MemcachedCache-numberOfReads: ' .
                            ($numberOfReads)
                        );

                        // writes
                        $numberOfWrites = \MemcachedCache::getWrites();
                        header(
                            'TurtlePHP-'. ($self->getHash()) . '-MemcachedCache-numberOfWrites: ' .
                            ($numberOfWrites)
                        );
                        return $buffer;
                    });
                    return $buffer;
                }
            });
        }

        /**
         * _addMemoryCallback
         * 
         * @access protected
         * @param  Request $request
         * @return void
         */
        protected function _addMemoryCallback(\Turtle\Request $request)
        {
            $self = $this;
            $request->addCallback(function($buffer) use ($request, $self) {
                $request->addCallback(function($buffer) use($self) {
                    $memory = (memory_get_peak_usage(true));
                    $memory = number_format(round($memory / 1024)) . 'kb';
                    header(
                        'TurtlePHP-'. ($self->getHash()) . '-Memory: ' .
                        ($memory)
                    );
                    return $buffer;
                });
                return $buffer;
            });
        }

        /**
         * _addMySQLConnectionCallback
         * 
         * @access protected
         * @param  Request $request
         * @return void
         */
        protected function _addMySQLConnectionCallback(\Turtle\Request $request)
        {
            $self = $this;
            $request->addCallback(function($buffer) use ($request, $self) {
                if (class_exists('MySQLConnection')) {
                    $request->addCallback(function($buffer) use($self) {

                        // select queries
                        $numberOfSelectQueries = \MySQLConnection::getNumberOfSelectQueries();
                        header(
                            'TurtlePHP-'. ($self->getHash()) . '-MySQLConnection-NumberOfSelectQueries: ' .
                            ($numberOfSelectQueries)
                        );

                        // insert queries
                        $numberOfInsertQueries = \MySQLConnection::getNumberOfInsertQueries();
                        header(
                            'TurtlePHP-'. ($self->getHash()) . '-MySQLConnection-NumberOfInsertQueries: ' .
                            ($numberOfInsertQueries)
                        );

                        // update queries
                        $numberOfUpdateQueries = \MySQLConnection::getNumberOfUpdateQueries();
                        header(
                            'TurtlePHP-'. ($self->getHash()) . '-MySQLConnection-NumberOfUpdateQueries: ' .
                            ($numberOfUpdateQueries)
                        );

                        // cumulative query duration
                        $cumulativeQueryDuration = \MySQLConnection::getCumulativeQueryDuration();
                        header(
                            'TurtlePHP-'. ($self->getHash()) . '-MySQLConnection-CumulativeQueryDuration: ' .
                            ($cumulativeQueryDuration)
                        );
                        return $buffer;
                    });
                    return $buffer;
                }
            });
        }

        /**
         * _addRequestCacheCallback
         * 
         * @access protected
         * @param  Request $request
         * @return void
         */
        protected function _addRequestCacheCallback(\Turtle\Request $request)
        {
            $self = $this;
            $request->addCallback(function($buffer) use ($request, $self) {
                if (class_exists('RequestCache')) {
                    $request->addCallback(function($buffer) use($self) {

                        // misses
                        $numberOfMisses = \RequestCache::getMisses();
                        header(
                            'TurtlePHP-'. ($self->getHash()) . '-RequestCache-numberOfMisses: ' .
                            ($numberOfMisses)
                        );

                        // reads
                        $numberOfReads = \RequestCache::getReads();
                        header(
                            'TurtlePHP-'. ($self->getHash()) . '-RequestCache-numberOfReads: ' .
                            ($numberOfReads)
                        );

                        // writes
                        $numberOfWrites = \RequestCache::getWrites();
                        header(
                            'TurtlePHP-'. ($self->getHash()) . '-RequestCache-numberOfWrites: ' .
                            ($numberOfWrites)
                        );
                        return $buffer;
                    });
                    return $buffer;
                }
            });
        }

        /**
         * _addRequestsCallback
         * 
         * @access protected
         * @param  Request $request
         * @return void
         */
        protected function _addRequestsCallback(\Turtle\Request $request)
        {
            $self = $this;
            $request->addCallback(function($buffer) use ($request, $self) {
                $request->addCallback(function($buffer) use($self) {
                    $numberOfRequests = count(\Turtle\Application::getRequests());
                    header(
                        'TurtlePHP-'. ($self->getHash()) . '-NumberOfRequests: ' .
                        ($numberOfRequests)
                    );
                    return $buffer;
                });
                return $buffer;
            });
        }

        /**
         * getHash
         * 
         * @access public
         * @return String
         */
        public function getHash()
        {
            return $this->_hash;
        }

        /**
         * setPathHeader
         * 
         * @access public
         * @param  Request $request
         * @return void
         */
        public function setPathHeader(\Turtle\Request $request)
        {
            // set path (for header passing)
            $route = $request->getRoute();
            $path = $route['path'];

            // grab md5 and truncate it
            $md5 = md5($path);
            $md5 = substr($md5, 0, 6);

            // set instance md5
            $this->_hash = $md5;

            // set path header
            header('TurtlePHP-' . ($md5) . ': ' . ($path));
        }
    }
