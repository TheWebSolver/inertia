## Welcome
An **[InertiaJS][Inertia]** Adapter for PHP projects that uses implementation of **[PSR7 (HTTP Message Interface)][PSR7]**, **[PSR15 (Server Request Handlers)][PSR15]**, and optionally **[PSR11 (Container Interface)][PSR11]**.

## Installation (via Composer)
Install library using composer command:
```sh
$ composer require thewebsolver/inertia
```

For seamless handling of Middlewares and storing each middleware response, require [Pipeline][pipeline] library and use it in your request handler. For more details see [pipeline docs][pipelineDocs].

```sh
$ composer require thewebsolver/pipeline
```

## Benefits

- Provides seamless integration with any PHP Project that adheres to the PSR-7 & PSR-15 implementation.
- Fluent API with very less friction and easy setup.
- Works with [Pipeline][pipeline] library to handle Middlewares (including this library's [middleware][middleware]) and retrieve Response back with headers intact.
- Provides option to register your resource/asset version as well as root-view/template using middleware's helper method.
- Provides additional subscription option that gets invoked alongside this library's [middleware][middleware].
- Supports Dependency Injection with your own App Container (that implements [PSR-11][PSR11] _`Psr\Container\ContainerInterface`_).

## Usage

For usage details, visit [Wiki page][wiki].

<!-- MARKDOWN LINKS -->
<!-- https://www.markdownguide.org/basic-syntax/#reference-style-links -->
[Inertia]: https://inertiajs.com/
[PSR7]: https://www.php-fig.org/psr/psr-7/
[PSR11]: https://www.php-fig.org/psr/psr-11/
[PSR15]: https://www.php-fig.org/psr/psr-15/
[pipeline]: https://github.com/TheWebSolver/pipeline
[pipelineDocs]: https://github.com/TheWebSolver/pipeline/wiki/PSR%E2%80%907-&-PSR%E2%80%9015-Bridge
[pipeline]: https://github/com/TheWebSolver/pipeline
[middleware]: /Src/Middleware.php
[wiki]: https://github.com/TheWebSolver/inertia/wiki
