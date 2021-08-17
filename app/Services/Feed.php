<?php

namespace App\Services;

use App\Logging\DatabaseLogger;
use Feed as LibFeed;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;
use SimpleXMLElement;

class Feed extends LibFeed
{
    public static function load($url, $user = null, $pass = null)
    {
        $xml = self::loadXml($url, $user, $pass);
        if ($xml->channel) {
            return self::fromRss($xml);
        }

        return self::fromAtom($xml);
    }

    private static function loadXml($url, $user, $pass)
    {
        $e = self::$cacheExpire;
        $cacheFile = self::$cacheDir . '/feed.' . md5(serialize(func_get_args())) . '.xml';

        if (self::$cacheDir
            && (time() - @filemtime($cacheFile) <= (is_string($e) ? strtotime($e) - time() : $e))
            && $data = @file_get_contents($cacheFile)
        ) {
            // ok
        } elseif ($data = trim(self::httpRequest($url, $user, $pass))) {
            if (self::$cacheDir) {
                file_put_contents($cacheFile, $data);
            }
        } elseif (self::$cacheDir && $data = @file_get_contents($cacheFile)) {
            // ok
        } else {
            throw new \Exception('Cannot load feed.');
        }

        return new SimpleXMLElement($data, LIBXML_NOWARNING | LIBXML_NOERROR | LIBXML_NOCDATA);
    }

    private static function httpRequest($url, $user, $pass)
    {
        /** @var Logger $logger */
        $logger = new DatabaseLogger();

        $stack = HandlerStack::create();
        $stack->push(
            LogMiddleware::debugRequest(
                $logger,
                new MessageFormatter('{req_body} - {res_body}')
            )
        );
        $client = new \GuzzleHttp\Client(
            [
                'handler' => $stack,
            ]
        );
        return Http::timeout(10)->setClient($client)->get($url)->body();
    }

    private static function fromRss(SimpleXMLElement $xml)
    {
        if (!$xml->channel) {
            throw new \Exception('Invalid feed.');
        }

        self::adjustNamespaces($xml);

        foreach ($xml->channel->item as $item) {
            // converts namespaces to dotted tags
            self::adjustNamespaces($item);

            // generate 'timestamp' tag
            if (isset($item->{'dc:date'})) {
                $item->timestamp = strtotime($item->{'dc:date'});
            } elseif (isset($item->pubDate)) {
                $item->timestamp = strtotime($item->pubDate);
            }
        }
        $feed = new self;
        $feed->xml = $xml->channel;
        return $feed;
    }

    private static function adjustNamespaces($el)
    {
        foreach ($el->getNamespaces(true) as $prefix => $ns) {
            $children = $el->children($ns);
            foreach ($children as $tag => $content) {
                $el->{$prefix . ':' . $tag} = $content;
            }
        }
    }

    private static function fromAtom(SimpleXMLElement $xml)
    {
        if (!in_array('http://www.w3.org/2005/Atom', $xml->getDocNamespaces(), true)
            && !in_array('http://purl.org/atom/ns#', $xml->getDocNamespaces(), true)
        ) {
            throw new \Exception('Invalid feed.');
        }

        // generate 'timestamp' tag
        foreach ($xml->entry as $entry) {
            $entry->timestamp = strtotime($entry->updated);
        }
        $feed = new self;
        $feed->xml = $xml;
        return $feed;
    }
}
