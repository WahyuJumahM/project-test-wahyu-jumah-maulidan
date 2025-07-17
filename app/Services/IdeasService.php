<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class IdeasService
{
    protected $baseUrl = 'https://suitmedia-backend.suitdev.com/api/ideas';

    public function getIdeas($page = 1, $perPage = 10, $sort = 'newest')
    {
        $sortParam = $sort === 'newest' ? '-published_at' : 'published_at';
        $cacheKey = "ideas_page_{$page}_perpage_{$perPage}_sort_{$sort}";

        return Cache::remember($cacheKey, 300, function () use ($page, $perPage, $sortParam) {
            try {
                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'User-Agent' => 'Laravel Application'
                ])->timeout(30)->get($this->baseUrl, [
                    'page[number]' => $page,
                    'page[size]' => $perPage,
                    'append[]' => 'small_image',
                    'append[]' => 'medium_image',
                    'sort' => $sortParam
                ]);

                if ($response->successful()) {
                    $data = $response->json();

                    // Log the response for debugging
                    Log::info('API Response:', [
                        'status' => $response->status(),
                        'has_data' => isset($data['data']),
                        'data_count' => isset($data['data']) ? count($data['data']) : 0,
                        'first_item_keys' => isset($data['data'][0]) ? array_keys($data['data'][0]) : []
                    ]);

                    return $data;
                }

                Log::error('API Error:', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return $this->getErrorResponse($page, $perPage);
            } catch (\Exception $e) {
                Log::error('API Exception:', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                return $this->getErrorResponse($page, $perPage);
            }
        });
    }

    private function getErrorResponse($page = 1, $perPage = 10)
    {
        return [
            'data' => [],
            'meta' => [
                'current_page' => $page,
                'last_page' => 1,
                'per_page' => $perPage,
                'total' => 0,
                'from' => 0,
                'to' => 0
            ]
        ];
    }
}
