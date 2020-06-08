<?php

namespace App\Library\Webhook;

use Log;
use Loilo\GithubWebhook\Delivery;
use RuntimeException;
use Satis;
use Str;

/**
 * Represents the action to be taken given a WebHook's headers and payload
 */
class HookHandler
{
    /**
     * The webhook delivery to handle
     */
    protected Delivery $delivery;

    /**
     * The action's metadata (cache for `getActionData()`)
     * `false` indicates that nothing has been cached yet, as `null` may
     * be a valid cached value
     *
     * @var array|null
     */
    protected $actionData = false;

    public function __construct(Delivery $delivery)
    {
        $this->delivery = $delivery;
    }

    /**
     * Checks if the headers and payload result in an action that can be handled
     *
     * @return boolean
     */
    public function canBeHandled()
    {
        return !is_null($this->gatherActionData());
    }

    /**
     * Get the name of the action
     *
     * @return string|null
     */
    public function name()
    {
        return $this->gatherActionData()['name'] ?? null;
    }

    /**
     * Get the identifier of the action's repository (as user/repo)
     *
     * @return string|null
     */
    public function repository()
    {
        return $this->gatherActionData()['repository'] ?? null;
    }

    /**
     * Get the metadata of the action
     *
     * @return mixed
     */
    public function metadata()
    {
        return $this->gatherActionData()['meta'] ?? null;
    }

    /**
     * Get action data
     *
     * @return mixed
     */
    public function actionData()
    {
        return $this->gatherActionData();
    }

    /**
     * Creates a minimal set of metadata from headers and payload
     * The core is the returned `name`, which is one of:
     * create-repository, delete-repository, push, create-tag, delete-tag, create-branch, delete-branch
     *
     * @return array|null If none of the above actions was recognized, `null` will be returned. Otherwise, the return value is an associative array of the following structure:
     * [
     *   name => one of the actions listed above
     *   repository => the full user/name of the affected repository
     *   meta => any additional data needed for the hook
     * ]
     */
    protected function gatherActionData()
    {
        if ($this->actionData !== false) {
            return $this->actionData;
        }

        $name = null;
        $meta = null;

        $delivery = $this->delivery;
        $event = $delivery->event();
        $repository = $delivery->payload('repository.full_name');

        switch ($event) {
            case 'repository':
                switch ($delivery->payload('action')) {
                    // Create repository
                    case 'created':
                        $name = 'create-repository';
                        break;

                    // Delete repository
                    case 'deleted':
                        $name = 'delete-repository';
                        break;

                    default:
                        Log::notice('Unhandled hook by unhandled action', [
                            'event' => $event,
                            'payload' => $delivery->payload(),
                        ]);
                }
                break;

            case 'create':
                switch ($delivery->payload('ref_type')) {
                    // Create tag
                    case 'tag':
                        $name = 'create-tag';
                        $meta = ['tag' => $delivery->payload('ref')];
                        break;

                    // Create branch
                    case 'branch':
                        $name = 'create-branch';
                        $meta = ['branch' => $delivery->payload('ref')];
                        break;

                    default:
                        Log::notice('Unhandled hook by unhandled ref_type', [
                            'event' => $event,
                            'payload' => $delivery->payload(),
                        ]);
                }
                break;

            case 'delete':
                switch ($delivery->payload('ref_type')) {
                    // Delete tag
                    case 'tag':
                        $name = 'delete-tag';
                        $meta = ['tag' => $delivery->payload('ref')];
                        break;

                    // Delete branch
                    case 'branch':
                        $name = 'delete-branch';
                        $meta = ['branch' => $delivery->payload('ref')];
                        break;

                    default:
                        Log::notice('Unhandled hook by unhandled ref_type', [
                            'event' => $event,
                            'payload' => $delivery->payload(),
                        ]);
                }
                break;

            case 'push':
                // Standard push
                if (
                    Str::startsWith($delivery->payload('ref'), 'refs/heads/') &&
                    $delivery->payload('created') === false &&
                    $delivery->payload('deleted') === false
                ) {
                    $name = 'push';
                }
                break;

            default:
                Log::notice('Unhandled hook by unhandled X-GitHub-Event', [
                    'event' => $event,
                    'payload' => $delivery->payload(),
                ]);
        }

        if (is_null($name)) {
            $this->actionData = null;
        } else {
            $this->actionData = [
                'name' => $name,
                'repository' => $repository,
                'meta' => $meta,
            ];
        }

        return $this->actionData;
    }

    /**
     * Executes the action
     *
     * @param SatisWrapper $satis The SatisWrapper instance to use for execution
     * @return void
     */
    public function execute()
    {
        $repoName = $this->repository();
        $satisResult = null;

        switch ($this->name()) {
            case 'delete-repository':
                Log::info('Removing repository', [
                    'name' => $repoName,
                ]);

                if ($satisResult = Satis::removeRepo($repoName) !== false) {
                    Log::info('Successfully removed repository', [
                        'name' => $repoName,
                    ]);
                } else {
                    Log::info('Repository has already been removed', [
                        'name' => $repoName,
                    ]);
                }
                break;

            case 'create-repository':
            case 'create-branch':
            case 'delete-branch':
            case 'create-tag':
            case 'delete-tag':
            case 'push':
                Log::info('Updating repository', [
                    'name' => $repoName,
                ]);

                $satisResult = Satis::updateRepo(
                    $repoName,
                    config('app.repository.github_org'),
                    config('app.repository.package_vendor'),
                );

                Log::info('Successfully updated repository', [
                    'name' => $repoName,
                ]);
                break;
        }

        if ($satisResult !== 0) {
            throw new RuntimeException(
                "Satis command failed with exit code $satisResult",
            );
        }
    }
}
