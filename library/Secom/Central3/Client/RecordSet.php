<?php

namespace Secom\Central3\Client;

use \ArrayIterator;

class RecordSet extends ArrayIterator
{

    protected $head;

    public function __construct($array, $head) {
        $this->head = $head;
        parent::__construct($array);
    }

    public function getHead() {
        return $this->head;
    }

}