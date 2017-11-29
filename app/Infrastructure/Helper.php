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
