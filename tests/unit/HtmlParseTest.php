<?php

require __DIR__ . '/../../lib.php';

class HtmlParseTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideSimpleHtmlChunks
     */
    public function testSimpleHtml($html)
    {
        $q = sqc\Question::createFromHtml($html, false);

        $this->assertInstanceOf('\sqc\Question', $q);
        $this->assertEquals("Question", $q->title);
        $this->assertEquals("", $q->intro);

        $this->assertCount(2, $q->answers, "Wrong nomber of answers: " . print_r($q->answers, true));
        $this->assertEquals(false, $q->answers[0]->correct);
        $this->assertEquals("Incorrect", $q->answers[0]->content);
        $this->assertEquals(true, $q->answers[1]->correct);
        $this->assertEquals("Correct", $q->answers[1]->content);
    }

    /**
     * Return chunks of HTML with the same Question content, but different formattings.
     */
    public function provideSimpleHtmlChunks()
    {
        return array(
            array( // parameters of the first call
                '<p><strong>Question</strong></p>
                 <p><span style="text-decoration: line-through;">Incorrect</span></p>
                 <p><span style="text-decoration: underline;">Correct</span></p>'
            ),
            array(
                '<p><strong>Question</strong></p>
                 <p>
                 <span style="text-decoration: line-through;">Incorrect</span><br />
                 <span style="text-decoration: underline;">Correct</span></p>'
            ),
            array( // Word-10.0 2002 + IE8
                '<p><b>Question</b></p>
                 <p><span style="text-decoration: line-through;">Incorrect</span></p>
                 <p><span style="text-decoration: underline;">Correct</span></p>'
            ),
        );
    }

    public function testHtml1()
    {
        $html = '<p><strong>Question</strong></p>
<p><strong></strong>Une intro</p>
<p>plutôt longue</p>
<p>bref, du baratin</p>
<p><span style="text-decoration: underline;"></span><span style="text-decoration: line-through;">Incorrect</span></p>
<p><span style="text-decoration: underline;">Correct</span></p>
<p><span style="text-decoration: underline;">Correct2</span></p>';
        $q = sqc\Question::createFromHtml($html, false);

        $this->assertInstanceOf('\sqc\Question', $q);
        $this->assertEquals("Question", $q->title);
        $this->assertEquals('<p>Une intro</p> <p>plutôt longue</p> <p>bref, du baratin</p>', $q->intro);

        $this->assertCount(3, $q->answers, "Wrong nomber of answers: " . print_r($q->answers, true));
        $this->assertEquals(false, $q->answers[0]->correct);
        $this->assertEquals("Incorrect", $q->answers[0]->content);
        $this->assertEquals(true, $q->answers[1]->correct);
        $this->assertEquals("Correct", $q->answers[1]->content);
    }

    /**
     * @dataProvider provideFullHtmlChunks
     */
    public function testFullHtml($html)
    {
        $q = sqc\Question::createFromHtml($html, false);

        $this->assertInstanceOf('sqc\Question', $q);
        $this->assertEquals("Question", $q->title);
        $this->assertEquals("<p>Baratin<br/>long</p>", $q->intro);

        $this->assertCount(3, $q->answers, "Wrong nomber of answers: " . print_r($q->answers, true));
        $this->assertEquals(false, $q->answers[0]->correct);
        $this->assertEquals("Incorrect", $q->answers[0]->content);
        $this->assertEquals(true, $q->answers[2]->correct);
        $this->assertEquals("Correct2", strip_tags($q->answers[2]->content));
    }

    /**
     * Return chunks of HTML with the same Question content, but different formattings.
     */
    public function provideFullHtmlChunks()
    {
        return array(
            array( // parameters of the first call
                '<p><strong>Question</strong></p><p>Baratin<br />long</p>
                 <p><span style="text-decoration: line-through;">Incorrect<br /></span>
                 <span style="text-decoration: underline;">Correct1<br />
                 <span style="text-decoration: underline;">Correct2</span></span></p>'
            ),
            array(
                '<p><strong>Question<br /></strong>Baratin<br />long<strong><br /></strong></p>
                 <p><span style="text-decoration: line-through;">Incorrect<br /></span>
                 <span style="text-decoration: underline;">Correct1<br />
                 <span style="text-decoration: underline;">Correct2</span></span></p>'
            ),
            array(
                '<p><strong>Question<br /></strong>Baratin<br />long<strong><br /></strong></p>
                 <p><span style="text-decoration: line-through;">Incorrect</span></p>
                 <p><span style="text-decoration: line-through;"></span><span style="text-decoration: underline;">Correct1</span></p>
                 <p><span style="text-decoration: underline;">Correct2</span></p>'
            ),
            array( // Word-10.0 2002 + IE8
                '<p><b>Question</b></p>
                 <p>Baratin<br />long</p>
                 <p><span style="text-decoration: line-through;">Incorrect</span></p>
                 <p><span style="text-decoration: underline;">Correct1</span></p>
                 <p><span style="text-decoration: underline;">Correct2</span></p>'
            ),
        );
    }

    /**
     * @dataProvider provideSpanHtmlChunks
     */
    public function testSpanHtmlStrippedTags($html)
    {
        $q = sqc\Question::createFromHtml($html);

        $this->assertInstanceOf('sqc\Question', $q);
        $this->assertEquals("Question", $q->title);
        $this->assertEquals("Baratinlong", $q->intro);

        $this->assertCount(3, $q->answers, "Wrong nomber of answers: " . print_r($q->answers, true));
        $this->assertEquals(false, $q->answers[0]->correct);
        $this->assertEquals("Incorrect", $q->answers[0]->content);
        $this->assertEquals(true, $q->answers[2]->correct);
        $this->assertEquals("Correct2", strip_tags($q->answers[2]->content));
    }

    /**
     * Return chunks of HTML with the same Question content, but different formattings.
     */
    public function provideSpanHtmlChunks()
    {
        return array(
            array( // parameters of the first call
                '<p><strong>Question</strong></p><p>Baratin<br />long</p>
                 <p><span style="text-decoration: line-through;">Incorrect<br /></span>
                 <span style="text-decoration: underline;">Correct1<br />
                 <span style="text-decoration: underline;">Correct2</span></span></p>'
            ),
            array( // Word-10.0 2002 + IE8 (Copier depuis Word)
                '<p><b><span style="font-size: medium;" size="3"><span style="font-family: Times New Roman;" face="Times New Roman">Question</span></span></b></p>
                <p><span style="font-family: Times New Roman; font-size: medium;" size="3" face="Times New Roman">Baratin<br />long</span></p>
                <p><span style="text-decoration: line-through;"><span style="font-size: medium;" size="3"><span style="font-family: Times New Roman;" face="Times New Roman">Incorrect</span></span></span></p>
                <p><span style="text-decoration: underline;"><span style="font-size: medium;" size="3"><span style="font-family: Times New Roman;" face="Times New Roman">Correct1</span></span></span></p>
                <p><span style="text-decoration: underline;"><span style="font-size: medium;" size="3"><span style="font-family: Times New Roman;" face="Times New Roman">Correct2</span></span></span></p>
                <p><span style="text-decoration: underline;"><span style="font-family: Times New Roman; font-size: medium;" size="3" face="Times New Roman"> </span></span></p>'
            ),
        );
    }

    /**
     * @dataProvider provideMultiHtmlChunks
     */
    public function testMultiHtml($html)
    {
        $qs = sqc\Question::createMultiFromHtml($html, false);

        $this->assertCount(2, $qs, "Wrong nomber of questions: "); //  . print_r($qs, true)
        $this->assertInstanceOf('sqc\Question', $qs[0]);
        $this->assertInstanceOf('sqc\Question', $qs[1]);
        $this->assertEquals("Question1", $qs[0]->title);
        $this->assertEquals("Question2", $qs[1]->title);
        $this->assertEquals("<p>Baratin<br/>long</p>", $qs[0]->intro);
    }

    /**
     * Return chunks of HTML with the same Question content, but different formattings.
     */
    public function provideMultiHtmlChunks()
    {
        return array(
            array( // parameters of the first call
                '<p><strong>Question1<br /></strong>Baratin<br />long<strong><br /></strong></p>
                 <p><span style="text-decoration: line-through;">Incorrect<br /></span>
                 <span style="text-decoration: underline;">Correct1<br />
                 <span style="text-decoration: underline;">Correct2</span></span></p>
                 <p><strong>Question2</strong></p>
                 <p><span style="text-decoration: line-through;">Incorrect</span><br />
                 <span style="text-decoration: underline;">Correct</span></p>',
            ),
            array(
                '<p><strong>Question1<br /></strong>Baratin<br />long<strong><br /></strong></p>
                 <p><strong></strong><span style="text-decoration: line-through;">Incorrect<br /></span>
                 <span style="text-decoration: underline;">Correct1<br />
                 <span style="text-decoration: underline;">Correct2</span></span></p>
                 <p><strong>Question2</strong></p>
                 <p><strong /><span style="text-decoration: line-through;">Incorrect</span><br />
                 <span style="text-decoration: underline;">Correct</span></p>',
            ),
            array( // Word-10.0 2002 + IE8
                '<p><b>Question1</b></p>
                 <p>Baratin<br />long<br /></p>
                 <p><span style="text-decoration: line-through;">Incorrect</span></p>
                 <p><span style="text-decoration: underline;">Correct1</span></p>
                 <p><span style="text-decoration: underline;">Correct2</span></p>
                 <p><span style="text-decoration: underline;"> </span></p>
                 <p><b>Question2</b></p>
                 <p><span style="text-decoration: line-through;">Incorrect</span></p>
                 <p><span style="text-decoration: underline;">Correct</span></p>'
            ),
        );
    }
}

