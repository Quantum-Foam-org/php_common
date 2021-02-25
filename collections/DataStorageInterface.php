<?php

interface DataStorageInterface  {
    public function insert() : void;
    
    public function delete() : void;
    
    public function update() : void;   
}
