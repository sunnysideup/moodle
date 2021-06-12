<?php

namespace Sunnysideup\Moodle\Api;

use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;

/**
 * class used to respond with JSON requests.
 */
class MoodleResponse
{
    private $error;

    private $content;

    public function __construct($content, $error)
    {
        $this->error = $error;
        $this->content = $content;
        if (is_string($content)) {
            $tmppar = json_decode($this->content);
            if (is_object($tmppar) && (property_exists($tmppar, 'exception') && null !== $tmppar->exception)) {
                $this->error = $content;
                $this->content = null;
            }
        } else {
            $this->error = serialize($content);
            $this->error .= serialize($error);
        }
    }

    public function hasError(): bool
    {
        return ! empty($this->content) && empty($this->error) ? false : true;
    }

    public function isSuccess(): bool
    {
        return ! $this->hasError();
    }

    public function getError(): string
    {
        return $this->error;
    }

    /**
     * JSON array of the result of the response.
     *
     * @return string (json array)
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Returns SilverStripe object representations of content.
     *
     * @return null|DataList|DataObject
     */
    public function getSilverstripeObject()
    {
        if (! is_string($this->content)) {
            return null;
        }

        return $this->parseobject(json_decode($this->content));
    }

    public function getContentAsArray(): array
    {
        if (! $this->hasError()) {
            return json_decode($this->content, true);
        }

        return [];
    }

    /**
     * Recursivity creates the SilverStripe dataobject represntation of content.
     *
     * @param mixed $array
     *
     * @return null|DataList|DataObject
     */
    private function parseobject($array)
    {
        if (is_object($array)) {
            if ($array instanceof \DataObject) {
                return $array;
            }
            $do = DataObject::create();
            foreach (get_object_vars($array) as $key => $obj) {
                if ('__Type' === $key) {
                    $do->setField('Title', $obj);
                } elseif (is_array($obj) || is_object($obj)) {
                    $do->setField($key, $this->parseobject($obj));
                } else {
                    $do->setField($key, $obj);
                }
            }

            return $do;
        }
        if (is_array($array)) {
            $dataList = ArrayList::create();
            foreach ($array as $obj) {
                $dataList->push($this->parseobject($obj));
            }

            return $dataList;
        }

        return null;
    }
}
