<?php

namespace Illuminated\Wikipedia\Grabber\Wikitext;

class Wikitext
{
    protected $body;

    public function __construct($body)
    {
        $this->body = $body;
    }

    public function removeLinks()
    {
        $body = $this->body;

        if (!preg_match_all('/\[\[(.*?)\]\]/', $body, $matches, PREG_SET_ORDER)) {
            return $body;
        }

        foreach ($matches as $match) {
            $link = $match[0];
            $title = last(explode('|', $match[1]));
            $body = str_replace_first($link, $title, $body);
        }

        return $body;
    }
}
