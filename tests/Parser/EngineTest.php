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
    
    public function testComplexTplFileCompile(): void
    {
        $tplName = 'draw_all-tags';
        $tplDir = $this->config['tpl_dir'];
        $tplPath = $this->config['tpl_dir'].'draw_all-tags.html';
        $outputFile = $this->config['cache_dir'].'draw_all-tags.php';
        $this->tple->compileFile($tplDir, $tplPath, $outputFile);
        
        $this->assertFileExists(
            $outputFile
        );
    }
    
    public function testComplexCompareCompiledFileOutputToExpectedFileOutput(): void
    {
        $expectedOutputFile = $this->config['tpl_dir'].'draw_all-tags.expected.php';
        $outputFile = $this->config['cache_dir'].'draw_all-tags.php';
        
        $this->assertFileExists($outputFile);
        $this->assertFileIsReadable($outputFile);
        
        // expected output file is always saved with UNIX line endings
        $expected_output = file_get_contents($expectedOutputFile);

        // ensure we have the proper unix line endings in the expected output file in case editors or file transfer change it
        $got_output = str_replace("\r\n", "\n", file_get_contents($outputFile));
        
        $this->assertEquals($expected_output, $got_output);
    }
    

}