<?php

declare(strict_types=1);

namespace Pin\Route;

use SplFileInfo;
use Symfony\Component\Finder\Finder;

/**
 * RouteScanner
 *
 * PSR-4 方式扫描 Route Enum
 */
class RouteScanner
{
    /**
     * 扫描 Route Enum
     *
     * @param  array<string, string>  $paths
     * @return class-string<Routable>[]
     */
    public static function scan(array $paths): array
    {
        return collect($paths)
            ->flatMap(
                fn (string $namespace, string $path) => static::scanPath($path, $namespace)
            )
            ->all();
    }

    /**
     * PSR-4 class 解析
     */
    protected static function resolveClassFromFile(
        SplFileInfo $file,
        string $basePath,
        string $baseNamespace
    ): ?string {
        $path = $file->getRealPath();

        // 相对路径（去掉 base path）
        $relative = str_replace(
            [realpath($basePath), '/'],
            ['', '\\'],
            substr($path, 0, -4)
        );

        $relative = trim($relative, '\\');

        return trim($baseNamespace.'\\'.$relative, '\\');
    }

    /**
     * 扫描单个路径
     *
     * @return class-string<Routable>[]
     */
    protected static function scanPath(string $path, string $baseNamespace): array
    {
        $finder = new Finder();
        $finder->files()->in($path)->name('*Route.php');

        $items = [];
        foreach ($finder as $file) {
            $class = static::resolveClassFromFile($file, $path, $baseNamespace);
            if (is_subclass_of($class, Routable::class)) {
                $items[] = $class;
            }
        }

        return $items;
    }
}
