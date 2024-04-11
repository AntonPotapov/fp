<?php

namespace FpDbTest;

use mysqli;

readonly class Database implements DatabaseInterface
{
    public function __construct(private mysqli $mysqli)
    {
    }

    /**
     * @throws \Exception
     */
    public function buildQuery(string $query, array $args = []): string
    {
        if (!count($args)) {
            return $query;
        }

        $queryArray = str_split($query . ' ');
        $argsNum = 0;
        $resultQuery = '';
        $block = $cntContinue = $blockFail = false;
        $blockQuery = null;

        foreach ($queryArray as $key => $word) {
            if ($cntContinue) {
                $cntContinue = false;
                continue;
            }

            if ($word == '{') {
                if ($block) {
                    throw new \Exception('Условные блоки не могут быть вложенными');
                }
                $block = true;
                $blockFail = false;
                continue;
            }

            if ($word == '?') {
                $value = $args[$argsNum++];

                $cntContinue = in_array($queryArray[$key + 1], ['a', 'f', 'd', '#']);
                if ($value === $word . $queryArray[$key + 1]) {
                    $blockFail = true;
                    continue;
                }

                $method = ConvertParameters::getMethod($value, $queryArray[$key + 1]);
                try {
                    $addStr = ConvertParameters::{$method}($value);
                } catch (\TypeError $e) {
                    throw new \Exception('Не допустимый тип данных для значения - ' . $e->getMessage());
                }
            }

            if ($word == '}') {
                $block = false;
                if ($blockFail) {
                    $blockQuery = null;
                    continue;
                }
            }

            if ($block) {
                $blockQuery .= $addStr ?? $word;
            } else {
                $resultQuery .= $blockQuery ?? $addStr ?? $word;
                $addStr = $blockQuery = null;
            }
        }

        return trim($resultQuery);
    }

    public function skip()
    {
        return '?d';
    }
}
