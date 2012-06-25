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
         * _attach
         * 
         * @var    Boolean
         * @access protected
         */
        protected $_attach = true;

        /**
         * _key
         * 
         * @var    String
         * @access protected
         */
        protected $_key = 'TurtlePHP-';

        /**
         * _metrics
         * 
         * @var    Array
         * @access protected
         */
        protected $_metrics = array(
            'duration' => array(
                'start' => 0,
                'end' => 0,
                'benchmark' => 0
            ),
            'memory' => 0
        );

        /**
         * _request
         * 
         * @var    Request
         * @access protected
         */
        protected $_request;

        /**
         * __construct
         * 
         * Initializes the performance plugin by registering analytical callback
         * methods on the request buffer.
         * 
         * @notes  The request callbacks registered here subsequently
         *         sub-register another request callback to ensure that they are
         *         the last ones run. This does not cause an issue, as the
         *         callbacks are retrieved and run by reference, rather than
         *         through returned Closure objects.
         * @access public
         * @param  Request $request
         * @return void
         */
        public function __construct(\Turtle\Request $request)
        {
            // set request
            $this->_request = $request;
            $self = $this;

            // response duration callback
            $request->addCallback(function($buffer) use ($request) {
                $request->addCallback(function($buffer) {
error_log('s');
                    return $buffer;
                });
                return $buffer;
            });
            /*
function($buffer) {

                    // duration difference
                    $benchmark = round(microtime(true) - START, 4);
                    header('TurtlePHP-Duration: ' . ($benchmark));

                    // leave buffer unmodified
                    return $buffer;
                });

                // leave buffer unmodified
                return $buffer;
            });
*/
return;
            // request memory callback
            $request->addCallback(function($buffer) use ($request) {
                $request->addCallback(function($buffer) {

                    // peak memory usage determination
                    $memory = (memory_get_peak_usage(true));
                    $memory = round($memory / 1024);
                    header('TurtlePHP-Memory: ' . ($memory));
    
                    // leave buffer unmodified
                    return $buffer;
                });

                // leave buffer unmodified
                return $buffer;
            });
        }

        /**
         * duration
         * 
         * Shouldn't be used publically, but must be set as public for 
         * 
         * @access public
         * @return void
         */
        public function duration()
        {
error_log('duration');
return;
            // calculations
            $end = microtime(true);
            $benchmark = round($end - START);
            $this->_metrics['end'] = $end;
            $this->_metrics['benchmark'] = $benchmark;
            
            // if ought to be attached
            if ($this->_attach) {

                // set header (with key)
                header(($this->_key) . '-Duration: ' . ($benchmark));
            }
        }

        /**
         * memory
         * 
         * 
         * 
         * @access public
         * @return void
         */
        public function memory()
        {
echo 'memory';
        }

        /**
         * attach
         * 
         * 
         * 
         * @access public
         * @return void
         */
        public function attach()
        {
            $this->_attach = true;
        }

        /**
         * detach
         * 
         * @access public
         * @return void
         */
        public function detach()
        {
            $this->_attach = false;
        }

        /**
         * getMetrics
         * 
         * @access public
         * @return Array
         */
        public function getMetrics()
        {
            return $this->_metrics;
        }

        /**
         * getRequest
         * 
         * 
         * 
         * @access public
         * @return void
         */
        public function getRequest()
        {
            return $this->_request;
        }

        /**
         * setKey
         * 
         * @access public
         * @param  String $key
         * @return void
         */
        public function setKey($key)
        {
            $this->_key = $key;
        }
    }

