<?php

namespace Ajaxray\Test\TestData;

class ChainedLevelA
{
    public function __construct(public ChainedLevelB $b)
    {
    }
}