<?php

namespace Dmytrof\PushNotificationBundle\Model;

use Symfony\Component\Templating\EngineInterface;
use Dmytrof\PushNotificationBundle\Exception\RuntimeException;

class Notification
{
    const DEFAULT_LOCALE = 'en';

    protected $template;
    protected $locale;
    protected $subject;
    protected $content;
    protected $url;
    protected $data = [];
    protected $filters = [];
    protected $includedSegments = [];
    protected $excludedSegments = [];

    public function __construct($content=null, $subject=null, $locale=null)
    {
        $this->setContent($content);
        $this->setSubject($subject);
        $this->setLocale($locale ?: static::DEFAULT_LOCALE);
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string $template
     *
     * @return Notification
     */
    public function setTemplate($template, array $templateParams=[])
    {
        $this->template = func_get_args();
        return $this;
    }

    /**
     * @return Notification
     */
    public function removeTemplate()
    {
        $this->template = null;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $url
     *
     * @return Notification
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @param array $data
     *
     * @return Notification
     */
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @param array $data
     *
     * @return Notification
     */
    public function addData(array $data)
    {
        $this->data = array_merge((array) $this->data, $data);
        return $this;
    }

    /**
     * @return string $subject
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return string $content
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return string $locale
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $subject
     *
     * @return Notification
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @param string $content
     *
     * @return Notification
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @param string $locale
     *
     * @return Notification
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @return Notification
     */
    public function clearFilters()
    {
        $this->filters = [];
        return $this;
    }

    /**
     * @param string $key
     * @param string $relation
     * @param string $value
     * @param boolean $or
     *
     * @return Notification
     */
    public function filterByTag($key, $relation, $value='', $or=false)
    {
        if ($or) {
            array_push($this->fields, [
                'operator' => 'OR',
            ]);
        }
        array_push($this->filters, [
            'field' => 'tag',
            'key' => $key,
            'relation' => $relation,
            'value' => $value,
        ]);

        return $this;
    }

    /**
     * @return string[]
     */
    public function getIncludedSegments()
    {
        return $this->includedSegments;
    }

    /**
     * @return Notification
     */
    public function clearIncludedSegments()
    {
        $this->includedSegments = [];
        return $this;
    }

    /**
     * @param string[] $segments
     *
     * @return Notification
     */
    public function includeSegments(array $segments)
    {
        $this->includedSegments = array_merge($this->includedSegments, $segments);
        return $this;
    }

    /**
     * @return string[]
     */
    public function getExcludedSegments()
    {
        return $this->excludedSegments;
    }

    /**
     * @return Notification
     */
    public function clearExcludedSegments()
    {
        $this->excludedSegments = [];
        return $this;
    }

    /**
     * @param string[] $segments
     *
     * @return Notification
     */
    public function excludeSegments(array $segments)
    {
        $this->excludedSegments = array_merge($this->excludedSegments, $segments);
        return $this;
    }

    /**
     * @param EngineInterface $templating
     *
     * @return Notification
     */
    public function prepareFromTemplate(EngineInterface $templating)
    {
        if (is_null($this->getTemplate())) {
            throw new RuntimeException('Undefined template for rendering notification');
        }
        $dom = new \DOMDocument;
        $dom->loadXML(call_user_func_array([$templating, 'render'], $this->getTemplate()));
        $notification = simplexml_import_dom($dom);

        $this->setSubject(trim(strip_tags($notification->subject)));
        $this->setContent(trim(strip_tags($notification->content)));
        $this->setUrl(trim($notification->url));
        return $this;
    }
}