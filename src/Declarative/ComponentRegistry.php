<?php

namespace Kingbes\Libui\Declarative;

// 1. 组件注册器（类似Stempler的component注册）
class ComponentRegistry
{
    private static array $components = [];

    public static function register(string $tag, string $class): void
    {
        self::$components[$tag] = $class;
    }

    public static function resolve(string $tag): ?string
    {
        // First try to find the exact tag
        if (isset(self::$components[$tag])) {
            return self::$components[$tag];
        }

        // If tag contains a colon (namespace prefix), try to find the local name
        if (strpos($tag, ':') !== false) {
            $localName = substr($tag, strpos($tag, ':') + 1);
            foreach (self::$components as $registeredTag => $class) {
                // Check if registered tag ends with the local name with a colon before it
                if (substr($registeredTag, -strlen($localName) - 1) === ':' . $localName) {
                    return $class;
                }
            }
        }

        // If tag doesn't have a namespace, check if any registered tag ends with this name
        foreach (self::$components as $registeredTag => $class) {
            if (strpos($registeredTag, ':') !== false) {
                $localName = substr($registeredTag, strrpos($registeredTag, ':') + 1);
                if ($localName === $tag) {
                    return $class;
                }
            }
        }

        return null;
    }
}