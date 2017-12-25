<?php

namespace App\Core\IO;

class InputStream
{
    const READ_BUFFER_SIZE = 1024;
    const INITIAL_BUFFER_SIZE = 1024;
    const EOF = -1;

    /**
     * The data being scanned
     */
    private $data;

    /**
     * How many characters are actually in the buffer
     */
    private $n;

    /**
     * 0..n-1 index into string of next char
     */
    private $p = 0;

    public function __construct(string $input = '')
    {
        $this->data = $input;
        $this->n = strlen($input);
    }

    public function reset()
    {
        $this->p = 0;
    }

    public function consume()
    {
        if ($this->p >= $this->n) {
            //assert LA(1) == IntStream.EOF;
            //throw new IllegalStateException("cannot consume EOF");
        }

        if ($this->p < $this->n) {
            $this->p++;
        }
    }

    public function LA(int $i)
    {
        if ($i === 0) {
            return 0; // undefined
        }

        if ($i < 0) {
            $i++; // e.g., translate LA(-1) to use offset i=0; then data[p+0-1]
            if (($this->p + $i - 1) < 0) {
                return self::EOF; // invalid; no char before first char
            }
        }

        if (($this->p + $i - 1) >= n) {
            return self::EOF;
        }

        return $this->data[$this->p + $i - 1];
    }

    public function index()
    {
        return $this->p;
    }

    public function size()
    {
        return $this->n;
    }

    public function seek(int $index) {
        if ($index <= $this->p) {
            $this->p = $index; // just jump; don't update stream state (line, ...)
            return;
        }
        // seek forward, consume until p hits index or n (whichever comes first)
        $index = min($index, $this->n);
        while ($this->p < $index) {
            $this->consume();
        }
    }
}
