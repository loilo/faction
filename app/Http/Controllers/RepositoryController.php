<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Carbon\Carbon;
use FS;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Handle requests that are related to the app's role as a Compsoer repository;
 * these requests are usually made by Composer itself.
 */
class RepositoryController extends Controller
{
    use SearchesPackages;

    /**
     * Provide a search endpoint for the Composer repository
     *
     * @param string $query The query to search for
     */
    public function search(string $query): JsonResponse
    {
        if (empty($query)) {
            return response()->json(['results' => []]);
        }

        $results = $this->searchPackages($query);

        return response()->json([
            'results' => array_map(function ($name) {
                $package = Package::findByName($name);
                return [
                    'name' => $package->name,
                    'description' => $package->description,
                    'url' => $package->url,
                    'repository' => $package->githubUrl,
                ];
            }, array_slice(
                $results,
                0,
                config('app.repository.max_search_results', 15),
            )),
        ]);
    }

    /**
     * Act as a proxy for static JSON files that are core
     * to a Composer repository (e.g. packages.json)
     *
     * @param string|null $query The file to serve, or null to serve packages.json
     */
    public function serve(?string $query = null)
    {
        $path = config('app.satis.output_path') . '/splitted';

        try {
            if (is_null($query)) {
                $file = "$path/packages.json";
            } else {
                $file = "$path/p/$query";
            }

            $data = FS::readFile($file);
            $lastModified = Carbon::createFromTimestamp(filemtime($file));

            return new JsonResponse(
                $data,
                200,
                [
                    'ETag' => substr(md5($data), 0, 10),
                    'Last-Modified' => $lastModified->format(
                        'D, j M Y H:i:s T',
                    ),
                ],
                true,
            );
        } catch (\Exception $e) {
            return response()->setStatusCode(404);
        }
    }
}
