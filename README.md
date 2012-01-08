TurtlePHP Performance Plugin
===
TurtlePHP performance plugin which analyzes a response that is ready for
flushing, determines it&#039;s processing duration and memory usage, and returns
them through custom response-headers.

Memory usage is returned in kilobytes, and represents the peak, **real** memory
that was reached/used during the lifetime of the request.

### Sample Initialization
    /**
     * Performance
     */
    require_once APP . '/plugins/Performance.class.php';
    \Plugin\Performance::init();

### Sample Response Headers
    Duration: 0.0044
    Memory: 768

