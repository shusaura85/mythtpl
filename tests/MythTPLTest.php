<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MythTPLTest extends TestCase
{
    private MythTPL\MythTPL $tpl;
    
    public function setup(): void
    {
        $config = [
            'cache_dir'    => './tests/cache/',
            'tpl_dir' => './tests/tpl/',
        ];
        $this->tpl = new MythTPL\MythTPL($config);
    }
    
    public function testCanCreateInstance(): void
    {
        $this->assertInstanceOf(
            MythTPL\MythTPL::class,
            new MythTPL\MythTPL()
        );
    }
    
    public function testCacheDir(): void
    {
        $this->assertStringContainsString(
            './cache/',
            $this->tpl->setCacheDir('./cache/')->getCacheDir()
        );
    }
    
    public function testTemplateDir(): void
    {
        $this->assertStringContainsString(
            './tpl/',
            $this->tpl->setTplDir('./tpl/')->getTplDir()
        );
    }

    public function testTemplateExt(): void
    {
        $this->assertStringContainsString(
            'html',
            $this->tpl->setTplExt('html')->getTplExt()
        );
    }

    public function testDebug(): void
    {
        $this->assertIsBool(
            $this->tpl->getDebug()
        );
        
        $this->assertTrue(
            $this->tpl->setDebug(true)->getDebug()
        );
        
        $this->assertNotTrue(
            $this->tpl->setDebug(false)->getDebug()
        );
    }
    
    public function testTagPhp(): void
    {
        $this->assertIsBool(
            $this->tpl->getTagPhp()
        );
        
        $this->assertTrue(
            $this->tpl->setTagPhp(true)->getTagPhp()
        );
        
        $this->assertNotTrue(
            $this->tpl->setTagPhp(false)->getTagPhp()
        );
    }
    
    public function testTagUseCaseInsensitive(): void
    {
        $this->assertIsBool(
            $this->tpl->getTagICase()
        );
        
        $this->assertTrue(
            $this->tpl->setTagICase(true)->getTagICase()
        );
        
        $this->assertNotTrue(
            $this->tpl->setTagICase(false)->getTagICase()
        );
    }
    
    public function testTagAutoescape(): void
    {
        $this->assertIsBool(
            $this->tpl->getTagAutoescape()
        );
        
        $this->assertTrue(
            $this->tpl->setTagAutoescape(true)->getTagAutoescape()
        );
        
        $this->assertNotTrue(
            $this->tpl->setTagAutoescape(false)->getTagAutoescape()
        );
    }
    
    public function testHtmlComments(): void
    {
        $this->assertIsBool(
            $this->tpl->getHtmlComments()
        );
        
        $this->assertTrue(
            $this->tpl->setHtmlComments(true)->getHtmlComments()
        );
        
        $this->assertNotTrue(
            $this->tpl->setHtmlComments(false)->getHtmlComments()
        );
    }
    
    
    public function testAssignVariables(): void
    {
        $array = ['test_bool' => true, 'test_string' => 'this is a string'];
        $this->tpl->assign($array);
        
        $this->assertIsBool(
            $this->tpl->readVar('test_bool')
        );
        
        $this->assertIsString(
            $this->tpl->readVar('test_string')
        );
        
        $array = ['test_array' => ['name' => 'test', 'email' => 'email@example.com'], 'test_int' => 1024];
        $this->tpl->pAssign($array);
        
        $this->assertIsInt(
            $this->tpl->readVar('test_int', true)
        );

        $this->assertIsArray(
            $this->tpl->readVar('test_array', true)
        );
        
    }

    public function testReadInvalidVariables(): void
    {
        // try to read invalid variable
        $this->expectException(MythTPL\Error\NotFoundException::class);
        $this->tpl->readVar('invalid_string');
        
    }
    
    
    public function testResetVariables(): void
    {
        $this->tpl->assignVar('valid_string', 'this should dissapear');
        $this->tpl->reset();
        // try to read invalid variable
        $this->expectException(MythTPL\Error\NotFoundException::class);
        $this->tpl->readVar('valid_string');
        
    }
    
    
    public function testDrawTemplate(): void
    {
        $this->setup();
        $this->tpl->assign(['test' => 'test string 1', 'test2' => 'second test string']);
        $got_output = $this->tpl->draw('engine_test', true);

        $expectedOutputFile = './tests/tpl/engine_test.expected.html';
        
        // expected output contents
        $expected_output = file_get_contents($expectedOutputFile);
        
        $this->assertEquals($expected_output, $got_output);
    }
    
    
    public function testDrawComplexTemplate(): void
    {
        $this->setup();
        // add custom tags
        // add a tag: {@text@}
        MythTPL\MythTPL::registerTag("simple_custom_tag",
            "{@(.*?)@}", // preg match
            function ($params) { // function called by the tag
                $value = $params[1][0];
                return "Translate: <b>$value</b>";
            }
        );


        // add a tag: {%text1|text2%}
        MythTPL\MythTPL::registerTag("another_custom_tag",
            "{%(.*?)(?:\|(.*?))%}", // preg match
            function ($params) { // function called by the tag
                $value = $params[1][0];
                $value2 = $params[2][0];

                return "Translate: <b>$value</b> in <b>$value2</b>";
            }
        );

        // set custom define
        DEFINE("TEST_CONSTANT_DEFINE", "-TEST-VERSION-1-2-3-");

        $this->tpl->assign([
            "variable" => "Hello World!",
            "bad_variable" => "<script>alert('evil javascript here');</script>",
            "safe_variable" => "<script>console.log('this is safe')</script>",
            "version" => "1.0.3",
            "menu" => [
                ["name" => "Home", "link" => "index.php", "selected" => true],
                ["name" => "FAQ", "link" => "index.php/FAQ/", "selected" => null],
                ["name" => "Documentation", "link" => "index.php/doc/", "selected" => null]
            ],
            "week" => ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"],
            "user" => (object)["name" => "Myth", "citizen" => "Earth", "race" => "Human"],
            "numbers" => [3, 2, 1],
            "bad_text" => 'Hey this is a malicious XSS <script>alert("auto_escape is always enabled");</script>',
            "table" => [["Apple", "1996"], ["PC", "1997"]],
            "title" => "Myth TPL - Easy and Fast template engine",
            "copyright" => "Copyright 2022 Myth TPL<br>Project By Shu Saura",
            "num1" => 10,
            "num2" => 20,
            "empty_array" => []
        ]);
        $got_output = $this->tpl->draw('draw_all-tags', true);

        $expectedOutputFile = './tests/tpl/draw_all-tags.expected.html';
        
        // expected output contents
        $expected_output = file_get_contents($expectedOutputFile);
        
        $this->assertEquals($expected_output, $got_output);
    }
    

}