<?php

namespace Dmytrof\PushNotificationBundle\Tests\Twig;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Dmytrof\PushNotificationBundle\Twig\WebPushSDKExtension;
use Dmytrof\PushNotificationBundle\Provider\OneSignal;
use Dmytrof\PushNotificationBundle\Provider\ProviderInterface;
use Dmytrof\PushNotificationBundle\Exception\RuntimeException;

/**
 * Test for twig extension
 */
class WebPushSDKExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string Dummy config prefix
     */
    protected $configPrefix = 'foo';
    
    /**
     * @var string Dummy parameter name
     */
    protected $paramName = 'bar';
    
    /**
     * @var string Dummy parameter value
     */
    protected $paramValue = 'baz';
    
    /**
     * @var string Dummy parameter value for provider
     */
    protected $providerParamValue = 'booz';
    
    /**
     * @var string Dummy provider code
     */
    protected $providerCode = 'test';
    
    /**
     * @var array Dummy tags
     */
    protected $tags = ['foo' => 'bar', 'tag1' => 'value1'];
    
    /**
     * @var array Dummy removable tags
     */
    protected $removableTags = ['tag2' => 'value2'];
   
    /**
     * @var string Dummy init template
     */
    protected $sdkInitTemplate = 'init.html.twig';
    
    /**
     * @var string Dummy rendered result of init template
     */
    protected $sdkInitTemplateRendered = 'inited';
    
    /**
     * @var string Dummy tags template
     */
    protected $sdkTagsTemplate = 'tags.html.twig';
    
    /**
     * @var string Dummy rendered result of tags template
     */
    protected $sdkTagsTemplateRendered = 'tags';
    
    /**
     * @var string Dummy rendered result of tags template with wrapping
     */
    protected $sdkTagsTemplateRenderedWrapped = 'tags_wrapped';
    
    /**
     * Test create ConfigParameterExtension
     */
    public function testCreateExtension()
    {
        $container = $this->getMockBuilder(Container::class)
                            ->disableOriginalConstructor()
                            ->setMethods(['getParameter'])
                            ->getMock();
        $container
            ->method('getParameter')
            ->willReturnCallback([$this, 'getParameterCallback']);
        
        $provider = $this->getMockBuilder(OneSignal::class)
                            ->disableOriginalConstructor()
                            ->setMethods(['getCode', 'getTags', 'getRemovableTags'])
                            ->getMock();
        $provider
            ->method('getCode')
            ->willReturn($this->providerCode);
        
        $provider
            ->method('getTags')
            ->willReturn($this->tags);
        
        $provider
            ->method('getRemovableTags')
            ->willReturn($this->removableTags);
        
        $extension = new WebPushSDKExtension();
        $extension->setContainer($container);
        
        $this->assertInstanceOf(ContainerInterface::class, $extension->getContainer());
        
        $extension->setProvider($provider);
        
        $this->assertInstanceOf(ProviderInterface::class, $extension->getProvider());
        
        $extension->setConfigPrefix($this->configPrefix);
        
        $this->assertEquals($this->configPrefix, $extension->getConfigPrefix());
        
        $this->assertNotTrue($extension->isSDKRendered());
        
        return $extension;
    }
    
    /**
     *  Test set config prefix
     *
     *  @depends testCreateExtension
     */
    public function testGetPushNotificationParameter(WebPushSDKExtension $extension)
    {
        $this->assertEquals($this->paramValue, $extension->getPushNotificationParameter($this->paramName));
        $this->assertEquals($this->paramValue, $extension->getPushNotificationParameter($this->configPrefix.'.'.$this->paramName));
        
        $this->assertEquals($this->providerParamValue, $extension->getPushNotificationProviderParameter($this->paramName));
        $this->assertEquals($this->providerParamValue, $extension->getPushNotificationProviderParameter($this->providerCode.'.'.$this->paramName));
        $this->assertEquals($this->providerParamValue, $extension->getPushNotificationProviderParameter($this->configPrefix.'.'.$this->providerCode.'.'.$this->paramName));
    }
    
    /**
     *  Test renderSDK
     *
     *  @depends testCreateExtension
     */
    public function testRenderSdk(WebPushSDKExtension $extension)
    {
        $twigEnv = $this->getMockBuilder(\Twig_Environment::class)
                            ->disableOriginalConstructor()
                            ->getMock();
        $twigEnv
            ->method('render')
            ->will($this->returnCallback(array($this, 'renderSDKCallback')));
        
        $this->assertEquals($this->sdkInitTemplateRendered, $extension->renderSDK($twigEnv));
        $this->assertTrue($extension->isSDKRendered());
        
        $this->expectException(RuntimeException::class);
        
        $this->assertEquals($this->sdkInitTemplateRendered, $extension->renderSDK($twigEnv));
    }
    
    /**
     *  Test renderTags
     *
     *  @depends testCreateExtension
     */
    public function testRenderTags(WebPushSDKExtension $extension)
    {
        $twigEnv = $this->getMockBuilder(\Twig_Environment::class)
                            ->disableOriginalConstructor()
                            ->getMock();
        $twigEnv
            ->method('render')
            ->will($this->returnCallback(array($this, 'renderTagsCallback')));

        $this->assertEquals($this->sdkTagsTemplateRendered, $extension->renderTags($twigEnv));
        $this->assertEquals($this->sdkTagsTemplateRenderedWrapped, $extension->renderTags($twigEnv, true));
    }
    
    /**
     * Callback of twigEnv->render
     *
     * @param string $template
     *
     * @return string
     */
    public function renderSDKCallback($template)
    {
        $this->assertEquals($this->sdkInitTemplate, $template);
        
        return $this->sdkInitTemplateRendered;
    }
    
    /**
     * Callback of twigEnv->render
     *
     * @param string $template
     * @param array  $templateArgs
     *
     * @return string
     */
    public function renderTagsCallback($template, array $templateArgs)
    {
        $this->assertEquals($this->sdkTagsTemplate, $template);
        
        $this->assertArrayHasKey('tags', $templateArgs);
        $this->assertEquals($this->tags, $templateArgs['tags']);
        
        $this->assertArrayHasKey('removableTags', $templateArgs);
        $this->assertEquals($this->removableTags, $templateArgs['removableTags']);
        
        $this->assertArrayHasKey('wrapScript', $templateArgs);
        
        return $templateArgs['wrapScript'] ? $this->sdkTagsTemplateRenderedWrapped : $this->sdkTagsTemplateRendered;
    }
    
    /**
     * Callback of container->getParameter
     *
     * @param string $name
     *
     * @return string
     */
    public function getParameterCallback($name)
    {
        $providerPrefix = $this->configPrefix.'.'.$this->providerCode;
        
        if ($name == $providerPrefix.'.web_sdk_init_template') {
            return $this->sdkInitTemplate;
        } elseif ($name == $providerPrefix.'.web_sdk_tags_template') {
            return $this->sdkTagsTemplate;
        } elseif (substr($name, 0, strlen($providerPrefix)) == $providerPrefix) {
            $this->assertEquals($providerPrefix.'.'.$this->paramName, $name);
            
            return $this->providerParamValue;
        }
        $this->assertEquals($this->configPrefix.'.'.$this->paramName, $name);
    
        return $this->paramValue;
    }
}