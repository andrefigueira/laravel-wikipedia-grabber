<?php

namespace Illuminated\Wikipedia\Grabber\Wikitext\Templates;

use Illuminated\Wikipedia\Grabber\Wikitext\Wikitext;

/**
 * @see https://en.wikipedia.org/wiki/Template:Multiple_image
 * @see https://ru.wikipedia.org/wiki/Шаблон:Кратное_изображение
 */
class MultipleImageTemplate
{
    protected $body;

    public function __construct($body)
    {
        $this->body = $body;
    }

    public function extract($file)
    {
        $extract = collect();

        $parts = $this->explode();
        $index = $this->getIndex($file, $parts);
        foreach ($parts as $part) {
            if ($this->isExtractingPart($part, $index)) {
                $part = $this->removeIndex($part, $index);
                $extract->push($part);
            }
        }

        return "{{{$extract->implode('|')}}}";
    }

    protected function explode()
    {
        $body = $this->body;

        $body = str_replace_first('{{', '', $body);
        $body = str_replace_last('}}', '', $body);
        $body = (new Wikitext($body))->plain();

        return array_map('trim', explode('|', $body));
    }

    protected function getIndex($file, array $parts)
    {
        $index = 1;

        foreach ($parts as $part) {
            if ($this->isMatch($part, $file)) {
                return $index;
            }

            if ($this->isFileName($part)) {
                $index++;
            }
        }

        return 0;
    }

    protected function isMatch($part, $file)
    {
        $fileWithSpaces = str_replace('_', ' ', $file);
        $fileWithUnderscores = str_replace(' ', '_', $file);

        return str_contains($part, $file)
            || str_contains($part, $fileWithSpaces)
            || str_contains($part, $fileWithUnderscores);
    }

    protected function isExtractingPart($part, $index)
    {
        if (!$this->isSomeParameter($part)) {
            return true;
        }

        return preg_match("/[^\d\s]+({$index}){0,1}(\s*?)=/", $part);
    }

    protected function removeIndex($part, $index)
    {
        if (!$this->isSomeParameter($part)) {
            return $part;
        }

        $parts = collect(explode('=', $part))->map(function ($item) {
            return trim($item);
        });

        $index = (string) $index;
        $parts[0] = str_replace_last($index, '', $parts[0]);

        return $parts->implode('=');
    }

    protected function isSomeParameter($string)
    {
        return preg_match('/^(\S+)(\s*?)(\S*)(\s*?)=/', $string)
            || preg_match('/^(\d+)(\s*)%$/', $string);
    }

    /**
     * @see https://www.mediawiki.org/wiki/Help:Images#Supported_media_types_for_images
     */
    protected function isFileName($part)
    {
        $extensions = collect([
            'jpg', 'jpeg', 'png', 'gif', 'svg', 'ogg', 'oga', 'ogv', 'pdf', 'djvu', 'tiff', 'mp3', 'wav', 'mp4', 'webm',
        ])->map(function ($ext) {
            return ".{$ext}";
        })->toArray();

        return ends_with($part, $extensions);
    }
}
