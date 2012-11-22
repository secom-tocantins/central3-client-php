<?php

namespace Secom\Central3\Client;

class Record
{

    protected $head;

    public function __construct($data, $head) {
        foreach ($data as $k => $v) {
            $this->$k = $v;
        }
        $this->head = $head;
    }

    public function getHead() {
        return $this->head;
    }


}