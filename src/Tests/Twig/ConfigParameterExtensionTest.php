<?php

namespace Dmytrof\PushNotificationBundle\Tests\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Dmytrof\PushNotificationBundle\Twig\ConfigParameterExtension;

/**
 * Test for twig extension
 */
class ConfigParameterExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string dummy config prefix
     */
    protected $configPrefix = 'foo';
    
    /**
     * @var string dummy parameter name
     */
    protected $paramName = 'bar';
    
    /**
     * @var string dummy parameter value
     */
    protected $paramValue = 'baz';
    
    /**
     * Test create ConfigParameterExtension
     */
    public function testCreateExtension()
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
                            ->disableOriginalConstructor()
                            ->getMock();
        $container
            ->method('getParameter')
            ->willReturnCallback([$this, 'getParameterCallback']);
        
        
        $extension = new ConfigParameterExtension($container);
        
        $this->assertInstanceOf(ConfigParameterExtension::class, $extension->getContainer());
        
        return $extension;
    }
    
    /**
     *  Test set config prefix
     *
     *  @depends testCreateExtension
     */
    public function testSetConfigPrefix(ConfigParameterExtension $extension)
    {
        $extension->setConfigPrefix($this->configPrefix);
        
        $this->assertEquals($this->configPrefix, $extension->getConfigPrefix());
        
        return $extension;
    }
    
    /**
     *  Test set config prefix
     *
     *  @depends testSetConfigPrefix
     */
    public function testGetPushNotificationParameter(ConfigParameterExtension $extension)
    {
        $extension->getPushNotificationParameter($this->paramName);
    
        $this->assertEquals($this->paramValue, $extension->getPushNotificationParameter($this->paramName));
        
        $extension->getPushNotificationParameter($this->paramName);
        
        $this->assertEquals($this->paramValue, $extension->getPushNotificationParameter($this->configPrefix.'.'.$this->paramName));
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
        $fullName = $this->configPrefix.'.'.$this->paramName;
        
        $this->assertEquals($fullName, $name);
        
        return $this->paramValue;
    }
}