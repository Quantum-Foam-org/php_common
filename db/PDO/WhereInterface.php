<?php

interface Where {
    public function getWhere() : string;
    
    public function getValues() : array;
}