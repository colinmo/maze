<?php

namespace VonExplaino\tests\units;

include __DIR__ . '/../../../../src/Maze.php';

use VonExplaino\Maze as Entity;
use atoum;

class Maze extends atoum
{
    public function testBasic()
    {
        $this
            ->given($entity = new Entity([]))
            ->then
                ->integer($entity->getMaxX());
        $this
            ->given($entity = new Entity([]))
            ->when($entity->createMaze())
            ->then
                ->string($entity->drawMazeSVG());
    }
    
    public function testSizes()
    {
        $this
            ->given($entity = new Entity([]))
            ->when($entity->createMaze())
            ->and($count = substr_count($entity->drawMazeSVG(), '<line'))
            ->dump($entity->drawMazeSVG())
            ->then
                ->integer($count)->isGreaterThan(20);
    }
}
