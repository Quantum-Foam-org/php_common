<?php

namespace common\filters;

interface FilterInterface {
    public function validate() : bool;
}

