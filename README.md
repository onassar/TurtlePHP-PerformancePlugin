TurtlePHP Performance Plugin
===
[TurtlePHP](https://github.com/onassar/TurtlePHP) Performance Plugin which
analyzes a response that is ready for flushing, determines it&#039;s processing
duration and memory usage, and returns them through custom response-headers.

Memory usage is returned in kilobytes, and represents the peak, **real** memory
that was reached/used during the lifetime of the request.

Class is instantiable rather than abstract to allow for multiple instances on
subrequests.

### Sample Initialization
``` php
<?php

    /**
     * Performance
     */
    require_once APP . '/plugins/TurtlePHP-PerformancePlugin/Performance.class.php';
    $request = \Turtle\Application::getRequest();
    (new \Plugin\Performance($request));

```

### Sample Response Headers
The following headers will be sent along with the response by the framework:

```
TurtlePHP-00c6f7: ^/$
TurtlePHP-00c6f7-Duration: 0.0044
TurtlePHP-00c6f7-Memory: 768
TurtlePHP-00c6f7-MySQLSelects: 18
```

They can easily be viewed by the document through your browser&#039;s
debug/inspector tool.
