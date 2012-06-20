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
     * @author   Oliver Nassar <onassar@gmail.com>
     * @abstract
     * @notes    with PHP 5.4.x, $_SERVER['REQUEST_TIME'] can be used rather
     *           than the <START> constant, as it'll be set as a float including
     *           microtime
     */
    abstract class Performance
    {
        /**
         * init
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
         * @static
         * @return void
         */
        public static function init()
        {
            // response duration callback
            \Turtle\Request::addCallback(function($buffer) {
                \Turtle\Request::addCallback(function($buffer) {

                    // duration difference
                    $benchmark = round(microtime(true) - START, 4);
                    header('TurtlePHP-Duration: ' . ($benchmark));

                    // leave buffer unmodified
                    return $buffer;
                });

                // leave buffer unmodified
                return $buffer;
            });

            // request memory callback
            \Turtle\Request::addCallback(function($buffer) {
                \Turtle\Request::addCallback(function($buffer) {

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
    }

