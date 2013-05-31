<?php

require __DIR__ . '/../../lib.php';

class HtmlParseTest extends PHPUnit_Framework_TestCase
{
    public function testSimpleHtml()
    {
        $html = '<p><strong>Question</strong></p>
            <p><span style="text-decoration: line-through;">Incorrect</span><br />
            <span style="text-decoration: underline;">Correct</span></p>';
        $q = sqc\Question::createFromHtml($html);

        $this->assertInstanceOf('sqc\Question', $q);
        $this->assertEquals("Question", $q->title);
        $this->assertEquals("", $q->description);

        $this->assertCount(2, $q->answers);
        $this->assertEquals(false, $q->answers[0]->correct);
        $this->assertEquals("Incorrect", $q->answers[0]->content);
        $this->assertEquals(true, $q->answers[1]->correct);
        $this->assertEquals("Correct", $q->answers[1]->content);
    }

    public function testFullHtml()
    {
        $html = '<p><strong>Question<br /></strong>Baratin<br />long<strong><br /></strong></p>
            <p><span style="text-decoration: line-through;">Incorrect<br /></span>
            <span style="text-decoration: underline;">Correct1<br />
            <span style="text-decoration: underline;">Correct2</span></span></p>';
        $q = sqc\Question::createFromHtml($html);

        $this->assertInstanceOf('sqc\Question', $q);
        $this->assertEquals("Question", $q->title);
        $this->assertEquals("Baratin<br />long", $q->description);

        $this->assertCount(3, $q->answers);
        $this->assertEquals(false, $q->answers[0]->correct);
        $this->assertEquals("Incorrect", $q->answers[0]->content);
        $this->assertEquals(true, $q->answers[2]->correct);
        $this->assertEquals("Correct2", $q->answers[2]->content);
    }

    public function testMultiHtml()
    {
        $html = '<p><strong>Question1<br /></strong>Baratin<br />long<strong><br /></strong></p>
            <p><span style="text-decoration: line-through;">Incorrect<br /></span>
            <span style="text-decoration: underline;">Correct1<br />
            <span style="text-decoration: underline;">Correct2</span></span></p>
            <p><strong>Question2</strong></p>
            <p><span style="text-decoration: line-through;">Incorrect</span><br />
            <span style="text-decoration: underline;">Correct</span></p>';
        $qs = sqc\Question::createMultiFromHtml($html);

        $this->assertCount(2, $qs);
        $this->assertInstanceOf('sqc\Question', $qs[0]);
        $this->assertInstanceOf('sqc\Question', $qs[1]);
        $this->assertEquals("Question2", $qs[1]->title);
        $this->assertEquals("Baratin<br />long", $qs[0]->description);
    }
}

