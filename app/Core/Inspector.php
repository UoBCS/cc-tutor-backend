<?php

namespace App\Core;

class Inspector
{
    private $state = [];

    public function createStore($store, $type, $object = false)
    {
        $this->state[$store] = $object ? new $type() : $type === 'array' ? array() : call_user_func($type);
    }

    public function setRootFn($rootFn)
    {
        $this->rootFn = $rootFn;
    }

    public function update($key, $data)
    {
        $this->state[$key] = $data;
    }

    public function updateArray($key, $item)
    {
        if (!isset($this->state[$key])) {
            $this->state[$key] = [];
        }
        $this->state[$key][] = $item;
    }

    public function getState($store = '')
    {
        return $store === '' ? $this->state : $this->state[$store];
    }

    public function reset()
    {
        $this->state = [];
    }

    public function breakpoint($label, $data)
    {
        $this->state['breakpoints'][] = [
            'label' => $label,
            'data'  => $data
        ];
    }
}
