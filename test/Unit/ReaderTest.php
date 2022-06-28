<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use Tetraquark\{Reader, Content};
$schemat = require_once 'src/schemats/javascript.php';

final class ReaderTest extends TestCase
{
    protected Reader $jsReader;
    protected array $cases = [];

    public function __construct()
    {
        global $schemat;
        parent::__construct();
        $this->setupJsTestCases();
        $this->jsReader  = new Reader($schemat);
        $this->content = $this->createMock(Content::class);
    }

    private function setupJsTestCases(): void
    {
        $this->cases["js"] = [];
        $this->cases["js"]["full"] = "
            //comment line
            /* multile line on one line*/
            /*
            multile line one multil lines
            */
            //*/* single line but tricky
            let a = /* multile between variable */ 'ad';
            console.log('test'); // single after a method
        ";
        $this->cases["js"]["full--trimmed"] = "let a = 'ad';\nconsole.log('test');"
    }

    public function testJsReaderIsReader(): void
    {
        $this->assertInstanceOf(
            Reader::class,
            $this->jsReader
        );
    }

    public function testFindClosestMatchSuccess()
    {
        $content = new Content("char chaar char [#] char char");
        $pos = $this->jsReader->findClosestMatch('[#]', $content);
        $this->assertEquals(
            18,
            $pos
        );
    }

    public function testFindClosestMatchAtStartSuccess()
    {
        $content = new Content("[#]char chaar char char char");
        $pos = $this->jsReader->findClosestMatch('[#]', $content, 16);
        $this->assertEquals(
            0,
            $pos
        );
    }

    public function testFindClosestMatchStartingFromMiddleSuccess()
    {
        $content = new Content("char chaar [#] char [#] char char");
        $pos = $this->jsReader->findClosestMatch('[#]', $content, 16);
        $this->assertEquals(
            22,
            $pos
        );
    }

    public function testFindClosestMatchStartingEndSuccess()
    {
        $content = new Content("char chaar char char char[#]");
        $pos = $this->jsReader->findClosestMatch('[#]', $content, 16);
        $this->assertEquals(
            27,
            $pos
        );
    }

    public function testFindClosestMatchStartingException()
    {
        $content = new Content("");

        $message = null;
        $code    = null;

        try {
            $pos = $this->jsReader->findClosestMatch('', $content);
        } catch (\Tetraquark\Exception $e) {
            $message = $e->getMessage();
            $code    = $e->getCode();
        }

        $this->assertEquals(
            "Needle can't be empty",
            $message
        );

        $this->assertEquals(
            400,
            $code
        );
    }

    public function testFindClosestMatchStartingNotFoundNeedle()
    {
        $content = new Content("char chaar char char char");
        $pos = $this->jsReader->findClosestMatch('[#]', $content, 16);
        $this->assertEquals(
            false,
            $pos
        );
    }

    public function testRemoveAdditionalAndCommentsInJavascript(): void
    {
        $content = new Content($this->cases["js"]["full"]);
        $this->jsReader->removeAdditionalAndComments($content);
        $this->assertEquals(
            $this->cases["js"]["full--trimmed"],
            $content->__toString()
        );
    }
}
