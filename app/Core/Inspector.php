<?php

namespace App\Core;

class Inspector
{
    const GENERAL = 0;
    const CALL = 1;

    private $state = [];
    private $currentBreakpointsScope = ['breakpoints'];
    private $currentIndexStack = [0];
    private $rootFn;

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

    public function breakpoint($label, $data, $from, $type = Inspector::GENERAL)
    {
        $path = $this->buildPath();
        $pathArr = explode('.', $path);
        array_pop($pathArr);

        if (count($pathArr) > 2) {
            $pathArr[count($pathArr) - 1] = 'label';

            if (data_get($this->state, implode('.', $pathArr)) !== "call:$from") {
                echo "lol";
                return;
            }
        } else if ($from !== $this->rootFn) {
            return;
        }

        data_set($this->state, $path, [
            'label' => $label,
            'type'  => $type,
            'data'  => $data
        ]);

        //var_dump($this->state['breakpoints']);

        if ($type === Inspector::CALL) {
            $this->currentIndexStack[] = 0;
            $this->currentBreakpointsScope[] = 'data';
        } else {
            $this->currentIndexStack[count($this->currentIndexStack) - 1]++;
        }
    }

    public function stepInto($targetFn, $srcFn)
    {
        $this->breakpoint("call:$targetFn", [], $srcFn, Inspector::CALL);
    }

    public function stepOut()
    {
        array_pop($this->currentBreakpointsScope);
        array_pop($this->currentIndexStack);
        $this->currentIndexStack[count($this->currentIndexStack) - 1]++;
    }

    private function buildPath()
    {
        $path = '';

        for ($i = 0; $i < count($this->currentBreakpointsScope); $i++) {
            $path .= $this->currentBreakpointsScope[$i] . '.' . $this->currentIndexStack[$i] . '.';
        }

        return substr($path, 0, -1);
    }
}
