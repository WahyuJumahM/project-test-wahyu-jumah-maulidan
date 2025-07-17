<?php
// app/Http/Controllers/IdeasController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\IdeasService;

class IdeasController extends Controller
{
    protected $ideasService;

    public function __construct(IdeasService $ideasService)
    {
        $this->ideasService = $ideasService;
    }

    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 10);
        $sort = $request->get('sort', 'newest');

        $perPage = in_array($perPage, [10, 20, 50]) ? $perPage : 10;
        $sort = in_array($sort, ['newest', 'oldest']) ? $sort : 'newest';

        $ideas = $this->ideasService->getIdeas($page, $perPage, $sort);

        // Process thumbnail for each idea
        if (isset($ideas['data'])) {
            foreach ($ideas['data'] as &$idea) {
                $idea['thumbnail'] = $this->getThumbnailUrl($idea);
            }
        }

        // Ensure consistent response structure
        if (!isset($ideas['data'])) {
            $ideas = [
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

        if ($request->ajax()) {
            return response()->json($ideas);
        }

        return view('ideas.index', compact('ideas', 'page', 'perPage', 'sort'));
    }

    private function getThumbnailUrl($idea)
    {
        // Priority: medium_image -> small_image -> first image from content -> placeholder

        // Check for medium_image first
        if (isset($idea['medium_image']) && is_array($idea['medium_image']) && count($idea['medium_image']) > 0) {
            return $idea['medium_image'][0]['url'] ?? null;
        }

        // Check for small_image
        if (isset($idea['small_image']) && is_array($idea['small_image']) && count($idea['small_image']) > 0) {
            return $idea['small_image'][0]['url'] ?? null;
        }

        // Extract first image from content as fallback
        if (!empty($idea['content'])) {
            $firstImageFromContent = $this->extractFirstImageFromContent($idea['content']);
            if ($firstImageFromContent) {
                return $firstImageFromContent;
            }
        }

        // Return placeholder if no image found
        return 'https://via.placeholder.com/400x200?text=No+Image';
    }

    private function extractFirstImageFromContent($content)
    {
        if (empty($content)) return null;

        // Extract first img tag from content
        preg_match('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $matches);
        return $matches[1] ?? null;
    }
}
