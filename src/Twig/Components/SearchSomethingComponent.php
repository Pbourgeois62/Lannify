<?php

namespace App\Twig\Components;

use App\Entity\Event;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;

#[AsLiveComponent('search_something')]
class SearchSomethingComponent
{
    use DefaultActionTrait;    
}
