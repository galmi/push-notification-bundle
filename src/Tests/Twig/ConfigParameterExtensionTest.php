<?php

namespace Dmytrof\PushNotificationBundle\Tests\Twig;

use Dmytrof\PushNotificationBundle\Twig\ConfigParameterExtension;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
        $container = $this->getMockBuilder(Container::class)
                            ->disableOriginalConstructor()
                            ->setMethods(['getParameter'])
                            ->getMock();
        $container
            ->method('getParameter')
            ->willReturnCallback([$this, 'getParameterCallback']);
        
        
        $extension = new ConfigParameterExtension();
        $extension->setContainer($container);
        
        $this->assertInstanceOf(ContainerInterface::class, $extension->getContainer());
        
        $extension->setConfigPrefix($this->configPrefix);
        
        $this->assertEquals($this->configPrefix, $extension->getConfigPrefix());
        
        return $extension;
    }
    
    /**
     *  Test set config prefix
     *
     *  @depends testCreateExtension
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