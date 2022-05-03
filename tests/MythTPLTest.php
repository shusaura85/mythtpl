<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MythTPLTest extends TestCase
{
    private MythTPL\MythTPL $tpl;
    
    public function setup(): void
    {
        $config = [
            'cache_dir'    => './cache/',
            'tpl_dir' => './tpl/',
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
    

}