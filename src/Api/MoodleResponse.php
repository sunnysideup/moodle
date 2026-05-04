<?php

namespace Sunnysideup\Moodle\Api;

use SilverStripe\Model\List\ArrayList;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;

/**
 * class used to respond with JSON requests.
 */
class MoodleResponse
{
    public function __construct(private $content, private $error)
    {
        if (is_string($this->content)) {
            $tmppar = json_decode($this->content);
            if (is_object($tmppar) && (property_exists($tmppar, 'exception') && null !== $tmppar->exception)) {
                $this->error = $this->content;
                $this->content = null;
            }
        } else {
            $this->error = serialize($this->content);
            $this->error .= serialize($this->error);
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
            return json_decode((string) $this->content, true);
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
