<?php

function defineKey($data, $key, $default)
{
    $data[$key] = isset($data[$key]) ? $data[$key] : $default;
    return $data;
}

function extendArray($arr1, $arr2)
{
    $result = [];

    foreach ($arr1 as $value) {
        $result[] = $value;
    }

    foreach ($arr2 as $value) {
        $result[] = $value;
    }

    return $result;
}

function getClassName($namespace)
{
    $parts = explode('\\', $namespace);
    return $parts[count($parts) - 1];
}

function getOnly($keys, $data)
{
    $newData = [];

    foreach ($keys as $key) {
        if (isset($data[$key])) {
            $newData[$key] = $data[$key];
        }
    }

    return $newData;
}

function startsWith($haystack, $needle)
{
    return strncmp($haystack, $needle, strlen($needle)) === 0;
}

function endsWith($haystack, $needle)
{
    return $needle === '' || substr_compare($haystack, $needle, -strlen($needle)) === 0;
}

function inspector()
{
    return resolve('App\Core\Inspector');
}

function peek($arr)
{
    return count($arr) - 1 < 0 ? null : $arr[count($arr) - 1];
}

function arrayFind($haystack, $needle, $useEquals = true)
{
    foreach ($haystack as $i => $val) {
        if ($useEquals) {
            if ($val->equals($needle)) {
                return $i;
            }
        } else {
            if ($val === $needle) {
                return $i;
            }
        }
    }

    return -1;
}

function getGrammarEntityName($ge)
{
    return $ge->getName();
}

function deepCloneArray($arr)
{
    $newArr = [];

    foreach ($arr as $value) {
        $newArr[] = is_array($value) ? deepCloneArray($value) : clone $value;
    }

    return $newArr;
}

function stackPeek($stack, $idx = 0)
{
    $stackArr = $stack->toArray();

    for ($i = 0; $i < count($stackArr); $i++) {
        if ($i === $idx) {
            return $stackArr[$i];
        }
    }

    throw new \Exception('Nothing to peek.');
}

function stackPop($stack, $count = 1)
{
    for ($i = 0; $i < $count; $i++) {
        $stack->pop();
    }
}

function joinPaths() {
    $paths = array();

    foreach (func_get_args() as $arg) {
        if ($arg !== '') { $paths[] = $arg; }
    }

    return preg_replace('#/+#','/',join('/', $paths));
}
