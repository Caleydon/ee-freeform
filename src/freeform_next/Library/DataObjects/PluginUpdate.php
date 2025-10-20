<?php

namespace Solspace\Addons\FreeformNext\Library\DataObjects;

use DateTime;
class PluginUpdate
{
    private array $bugfixes;

    private array $features;

    private array $notes;

    /**
     * PluginUpdate constructor.
     *
     * @param string    $version
     * @param string    $downloadUrl
     */
    public function __construct(private $version, private $downloadUrl, private DateTime $date, array $items)
    {
        $this->bugfixes = [];
        $this->features = [];
        $this->notes    = [];

        $this->parseItems($items);
    }

    private function parseItems(array $items)
    {
        foreach ($items as $item) {
            if (preg_match('/\[(\w+)\]\s*(.*)/', $item, $matches)) {
                [$match, $type, $string] = $matches;

                switch (strtolower($type)) {
                    case 'fixed':
                        $this->bugfixes[] = $string;
                        break;

                    case 'improved':
                        $this->notes[] = $string;
                        break;

                    case 'added':
                    default:
                        $this->features[] = $string;
                        break;
                }
            }
        }
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return string
     */
    public function getDownloadUrl()
    {
        return $this->downloadUrl;
    }

    /**
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return array
     */
    public function getBugfixes()
    {
        return $this->bugfixes;
    }

    /**
     * @return array
     */
    public function getFeatures()
    {
        return $this->features;
    }

    /**
     * @return array
     */
    public function getNotes()
    {
        return $this->notes;
    }
}
