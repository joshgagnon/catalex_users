<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Database\Eloquent\Collection;
use App\Library\StringManipulation;

class StringManipulationTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     * @expectedException Exception
     */
    public function buildCommaList_noWords()
    {
        $wordList = new Collection([]);
        $actual = StringManipulation::buildCommaList($wordList);
    }

    /**
     * @test
     */
    public function buildCommaList_oneWord()
    {
        $wordList = new Collection(['testing']);

        $expected = 'testing';
        $actual = StringManipulation::buildCommaList($wordList);

        $this->assertEquals($actual, $expected);
    }

    /**
     * @test
     */
    public function buildCommaList_twoWords()
    {
        $wordList = new Collection(['item one', 'item two']);

        $expected = 'item one and item two';
        $actual = StringManipulation::buildCommaList($wordList);

        $this->assertEquals($actual, $expected);
    }

    /**
     * @test
     */
    public function buildCommaList_threeWords()
    {
        $wordList = new Collection(['item one', 'item two', 'item three']);

        $expected = 'item one, item two, and item three';
        $actual = StringManipulation::buildCommaList($wordList);

        $this->assertEquals($actual, $expected);
    }
}
