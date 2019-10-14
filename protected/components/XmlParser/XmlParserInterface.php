<?php

interface XmlParserInterface
{
    public function __construct($xmlFile);
    public function setFile($file);
    public function parse($storeId);
    public function load();
    public function validate();
}