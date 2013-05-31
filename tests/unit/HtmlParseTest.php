<?php

require __DIR__ . '/../../lib.php';

class HtmlParseTest extends PHPUnit_Framework_TestCase
{
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
    }
}

