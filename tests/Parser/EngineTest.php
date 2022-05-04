<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class EngineTest extends TestCase
{
    private MythTPL\MythTPL $tpl;
    
    private array $config;
    
    public function setup(): void
    {
        $this->config = [
            'cache_dir'    => './tests/cache/',
            'tpl_dir' => './tests/tpl/',
            'tags_icase' => false,
            'allow_php' => false,
            'remove_comments' => true,
            'auto_escape' => false,
            'charset' => 'UTF-8'
        ];
        $this->tple = new MythTPL\Parser\Engine($this->config, []);
    }
    
    public function testCanCreateInstance(): void
    {
        $this->assertInstanceOf(
            MythTPL\Parser\Engine::class,
            $this->tple
        );
    }
    
    public function testTplFileCompile(): void
    {
        $tplName = 'engine_test';
        $tplDir = $this->config['tpl_dir'];
        $tplPath = $this->config['tpl_dir'].'engine_test.html';
        $outputFile = $this->config['cache_dir'].'engine_test.php';
        $this->tple->compileFile($tplDir, $tplPath, $outputFile);
        
        $this->assertFileExists(
            $outputFile
        );
    }
    
    public function testCompareCompiledFileOutputToExpectedFileOutput(): void
    {
        $expectedOutputFile = $this->config['tpl_dir'].'engine_test.expected.php';
        $outputFile = $this->config['cache_dir'].'engine_test.php';
        
        $this->assertFileExists($outputFile);
        $this->assertFileIsReadable($outputFile);
        
        // expected output file is always saved with UNIX line endings
        $expected_output = file_get_contents($expectedOutputFile);

        // ensure we have the proper unix line endings in the expected output file in case editors or file transfer change it
        $got_output = str_replace("\r\n", "\n", file_get_contents($outputFile));
        
        $this->assertEquals($expected_output, $got_output);
    }
    
    public function testTplStringCompile(): void
    {
        $string = 'This is a simple {$test|strtoupper} string template for testing'."\r\n".'The output {$str} should be using UNIX line endings'."\r\n";
        $outputFile = $this->config['cache_dir'].'string_test.php';
        $this->tple->compileString($string,  $outputFile);
        
        $this->assertFileExists(
            $outputFile
        );
    }
    
    public function testCompareCompiledStringOutputToExpectedStringOutput(): void
    {
        $expectedOutputFile = $this->config['tpl_dir'].'string_test.expected.php';
        $outputFile = $this->config['cache_dir'].'string_test.php';
        
        $this->assertFileExists($outputFile);
        $this->assertFileIsReadable($outputFile);
        
        // expected output file is always saved with UNIX line endings
        $expected_output = file_get_contents($expectedOutputFile);

        // ensure we have the proper unix line endings in the expected output file in case editors or file transfer change it
        $got_output = str_replace("\r\n", "\n", file_get_contents($outputFile));
        
        $this->assertEquals($expected_output, $got_output);
    }
    
   /*
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
    */

}