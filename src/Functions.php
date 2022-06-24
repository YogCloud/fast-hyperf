<?php

declare(strict_types=1);

use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\JobInterface;

if (! function_exists('readFileName')) {
    /**
     * Get the file names of all PHP files in a directory.
     * @param string $path Folder directory
     * @return array file name
     */
    function readFileName(string $path): array
    {
        $data = [];
        if (! is_dir($path)) {
            return $data;
        }

        $files = scandir($path);
        foreach ($files as $file) {
            if (in_array($file, ['.', '..', '.DS_Store'])) {
                continue;
            }
            $data[] = preg_replace('/(\w+)\.php/', '$1', $file);
        }
        return $data;
    }
}

if (! function_exists('responseDataFormat')) {
    function responseDataFormat($code, string $message = '', array $data = []): array
    {
        return [
            'code' => $code,
            'msg'  => $message,
            'data' => $data,
        ];
    }
}

if (! function_exists('isDiRequestInit')) {
    function isDiRequestInit(): bool
    {
        try {
            \Hyperf\Utils\ApplicationContext::getContainer()->get(\Hyperf\HttpServer\Contract\RequestInterface::class)->input('test');
            $res = true;
        } catch (\TypeError $e) {
            $res = false;
        }
        return $res;
    }
}

if (! function_exists('di')) {
    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param null|string $id
     * @return mixed|\Psr\Container\ContainerInterface
     */
    function di($id = null)
    {
        $container = \Hyperf\Utils\ApplicationContext::getContainer();
        if ($id) {
            return $container->get($id);
        }
        return $container;
    }
}

if (! function_exists('format_throwable')) {
    /**
     * Push a job to async queue.
     */
    function format_throwable(Throwable $throwable): string
    {
        return di()->get(Hyperf\ExceptionHandler\Formatter\FormatterInterface::class)->format($throwable);
    }
}

if (! function_exists('dd')) {
    /**
     * Debug printing and transfer parameters to the console.
     */
    function dd(...$var)
    {
        var_dump($var);
        exit();
    }
}

if (! function_exists('queue_push')) {
    /**
     * Push a job to async queue.
     */
    function queue_push(JobInterface $job, int $delay = 0, string $key = 'default'): bool
    {
        $driver = di()->get(DriverFactory::class)->get($key);
        return $driver->push($job, $delay);
    }
}

if (! function_exists('parse_name')) {
    /**
     * String naming style conversion
     * type 0 Convert Java style to c style
     * type 1 Convert c style to Java style.
     * @param string $name
     * @param int $type
     * @param bool $ucfirst
     * @return string
     */
    function parse_name($name, $type = 0, $ucfirst = true)
    {
        if ($type) {
            $name = preg_replace_callback('/_([a-zA-Z])/', function ($match) {
                return strtoupper($match[1]);
            }, $name);

            return $ucfirst ? ucfirst($name) : lcfirst($name);
        }
        return strtolower(trim(preg_replace('/[A-Z]/', '_\\0', $name), '_'));
    }
}
