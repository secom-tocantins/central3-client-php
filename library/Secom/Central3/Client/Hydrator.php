<?php

namespace Secom\Central3\Client;

use \RuntimeException,
    Secom\Central3\Client\Exception\ApiException;

class Hydrator
{

    public function __construct() {

    }

    public function hydrate($content) {

        if ($content->status == 0) {
            throw new ApiException($content->error_desc);
        }

        if (is_array($content->data)) {
            $arrayData = array();
            foreach ($content->data as $item) {
                $arrayData[] = $item;
            }
            unset($content->data);
            return new RecordSet ($arrayData, $content);
        }

        if (is_object($content->data)) {
            $data = $content->data;
            unset($content->data);
            return new Record($data, $content);
        }
    }

}