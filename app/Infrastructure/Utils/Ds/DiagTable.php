<?php

namespace App\Infrastructure\Utils\Ds;

use Ds\Map;

class DiagTable
{
    private $rowsHeader;
    private $colsHeader;
    private $contents;

    public function __construct()
    {
        $this->rowsHeader = [];
        $this->colsHeader = [];
        $this->contents   = [];
    }

    public static function fromArray(array $rowsHeader, array $colsHeader, $initialData) : self
    {
        $table = new DiagTable();

        for ($i = 0; $i < count($rowsHeader); $i++) {
            $table->rowsHeader[] = $rowsHeader[$i];
        }

        for ($j = 0; $j < count($colsHeader); $j++) {
            $table->colsHeader[] = $colsHeader[$j];
        }

        for ($i = 0; $i < count($rowsHeader); $i++) {
            for ($j = 0; $j < count($colsHeader); $j++) {
                $table->contents[$i][$j] =
                    is_callable($initialData)
                    ? call_user_func($initialData, $rowsHeader[$i], $colsHeader[$j])
                    : $initialData;
            }
        }

        return $table;
    }

    public function findAndUpdate(callable $fn, $data, $symmetric = false, $ignoreDiagonal = false)
    {
        for ($i = 0; $i < count($this->rowsHeader); $i++) {
            for ($j = $symmetric ? $i + 1 : 0; $j < count($this->colsHeader); $j++) {
                if ($ignoreDiagonal && $i === $j) {
                    continue;
                }

                if (call_user_func(
                        $fn,
                        $i,
                        $j,
                        $this->rowsHeader[$i],
                        $this->colsHeader[$j],
                        $this->contents[$i][$j])
                    ) {

                    // Update contents
                    $this->contents[$i][$j] = $data;

                    if ($symmetric) {
                        $this->contents[$j][$i] = $data;
                    }
                }
            }
        }
    }

    public function get($vRow, $vCol)
    {
        $i = array_search($vRow, $this->rowsHeader);
        $j = array_search($vCol, $this->colsHeader);

        return $this->contents[$i][$j];
    }

    public function getHeaderPairs($content, $symmetric = false)
    {
        $result = [];

        for ($i = 0; $i < count($this->rowsHeader); $i++) {
            for ($j = $symmetric ? $i + 1 : 0; $j < count($this->colsHeader); $j++) {
                if ($this->contents[$i][$j] === $content) {
                    $result[] = [$this->rowsHeader[$i], $this->colsHeader[$j]];
                }
            }
        }

        return $result;
    }

    public function getContents()
    {
        return $this->contents;
    }
}
