<?php

namespace Illuminated\Wikipedia\Grabber\Parser;

use Illuminate\Support\Collection;
use Illuminated\Wikipedia\Grabber\Component\Section;
use Illuminated\Wikipedia\Grabber\Wikitext\Wikitext;

class SectionsAddImages
{
    protected $sections;
    protected $wikitextSections;
    protected $imagesResponseData;

    public function __construct(Collection $sections, array $imagesResponseData = null)
    {
        $this->sections = $sections;
        $this->imagesResponseData = $imagesResponseData;
    }

    public function filter()
    {
        if (empty($this->imagesResponseData)) {
            return $this->sections;
        }

        foreach ($this->sections as $section) {
            $title = $section->getTitle();
            $wikitextSection = $this->getWikitextSection($title);
            if (empty($wikitextSection)) {
                dd($title);
            }
        }

        dd($this->imagesResponseData);

        return true; ///////////////////////////////////////////////////////////////////////////////////////////////////
    }

    protected function getWikitextSection($title)
    {
        $wikitextSections = $this->getWikitextSections();

        return $wikitextSections->first(function (Section $section) use ($title) {
            return ($section->getTitle() == $title);
        });
    }

    protected function getWikitextSections()
    {
        if (!empty($this->wikitextSections)) {
            return $this->wikitextSections;
        }

        $main = $this->getMainSection();
        $wikitext = $this->imagesResponseData['wikitext'];
        $this->wikitextSections = (new SectionsParser($main->getTitle(), $wikitext))->sections();
        $this->sanitizeWikitextSections();

        return $this->wikitextSections;
    }

    protected function getMainSection()
    {
        return $this->sections->first->isMain();
    }

    protected function sanitizeWikitextSections()
    {
        $this->wikitextSections->each(function (Section $section) {
            $section->setTitle(
                (new Wikitext($section->getTitle()))->sanitize()
            );
        });
    }
}
