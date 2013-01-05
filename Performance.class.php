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

            // response duration callback
            $request->addCallback(function($buffer) use ($request, $self) {

                // request-path header
                $self->setPathHeader($request);

                // sub-callback
                $request->addCallback(function($buffer) use($self) {

                    // duration difference
                    $benchmark = round(microtime(true) - START, 4);
                    header(
                        'TurtlePHP-' . ($self->getHash()) . '-Duration: ' .
                        ($benchmark)
                    );

                    // leave buffer unmodified
                    return $buffer;
                });

                // leave buffer unmodified
                return $buffer;
            });

            /**
             * Memory
             * 
             */
            $request->addCallback(function($buffer) use ($request, $self) {
                $request->addCallback(function($buffer) use($self) {
                    $memory = (memory_get_peak_usage(true));
                    $memory = round($memory / 1024);
                    header(
                        'TurtlePHP-'. ($self->getHash()) . '-Memory: ' .
                        ($memory)
                    );
                    return $buffer;
                });
                return $buffer;
            });

            /**
             * MySQLConnection
             * 
             */
            $request->addCallback(function($buffer) use ($request, $self) {
                if (class_exists('MySQLConnection')) {
                    $request->addCallback(function($buffer) use($self) {
                        $numberOfSelectQueries = \MySQLConnection::getNumberOfSelectQueries();
                        header(
                            'TurtlePHP-'. ($self->getHash()) . '-MySQLSelects: ' .
                            ($numberOfSelectQueries)
                        );
                        return $buffer;
                    });
                    return $buffer;
                }
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
