<?php

namespace Ajaxray\Test\TestData;

class ChainedConstruct
{
    public function __construct(public ChainedLevelA $a)
    {
    }
}