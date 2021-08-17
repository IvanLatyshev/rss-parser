<?php

namespace App\Console\Commands;

use App\Models\News;
use App\Modules\NewsParser\Services\NewsParseService;
use App\Services\Feed;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ParseNewsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:news';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse commands from http://static.feed.rbc.ru/rbc/logical/footer/news.rss';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $rss = Feed::load('http://static.feed.rbc.ru/rbc/logical/footer/news.rss');
        } catch (\FeedException $e) {
            Log::error('Failed to request rss', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error($e->getMessage());
            return 1;
        }

        $news = [];
        $newsParseService = new NewsParseService();
        foreach ($rss->item as $item) {
            $itemData = $newsParseService->parseNewsItem($item);
            $news[$itemData['guid']] = $itemData;
        }

        //  Получить идентификаторы, которых нет в базе данных
        $identifiersFromRss = array_keys($news);
        $existingNewsIds = DB::table('public.news')->select(['guid'])
            ->whereIn('guid', $identifiersFromRss)
            ->get()->map(function ($item) {
                return $item->guid;
            });

        foreach ($existingNewsIds as $existingNewsId) {
            if (isset($news[$existingNewsId])) {
                unset($news[$existingNewsId]);
            }
        }

        Log::info('Will be added news from rss', $news);

        News::query()->insert($news);

        return 0;
    }
}
