<?php

namespace App\Modules\NewsParser\Services;

use Carbon\Carbon;

class NewsParseService
{
    public function parseNewsItem(\SimpleXMLElement $newsItem): array
    {
        return [
            'guid' => $this->getGuid($newsItem),
            'title' => $this->getTitle($newsItem),
            'link' => $this->getLink($newsItem),
            'description' => $this->getDescription($newsItem),
            'publication_date' => $this->getPubDate($newsItem),
            'author' => $this->getAuthor($newsItem),
            'images' => $this->getImages($newsItem),
        ];
    }

    private function getGuid(\SimpleXMLElement $newsItem): string
    {
        return $newsItem->guid;
    }

    private function getTitle(\SimpleXMLElement $newsItem): string
    {
        return $newsItem->title;
    }

    private function getLink(\SimpleXMLElement $newsItem): string
    {
        return $newsItem->link;
    }

    private function getDescription(\SimpleXMLElement $newsItem): string
    {
        return $newsItem->description;
    }

    private function getPubDate(\SimpleXMLElement $newsItem): Carbon
    {
        return Carbon::parse($newsItem->pubDate);
    }

    private function getAuthor(\SimpleXMLElement $newsItem): ?string
    {
        $authorName = (string)$newsItem->author;
        return empty($authorName) ? null : $authorName;
    }

    /**
     * @param \SimpleXMLElement $newsItem
     * @return string
     */
    private function getImages(\SimpleXMLElement $newsItem): string
    {
        $images = [];
        foreach ($newsItem->enclosure as $item) {
            $mime = (string)$item['type'];
            if (strpos($mime, 'image') === 0) {
                $images[] = (string)$item['url'];
            }

        }

        return json_encode($images);
    }
}
