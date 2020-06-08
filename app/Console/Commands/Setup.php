<?php

namespace App\Console\Commands;

use App;
use Artisan;
use FS;
use Illuminate\Console\Command;
use RuntimeException;
use Str;

/**
 * Write essential data to the .env file
 */
class Setup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'faction:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Q&A session to fill essentials of the .env file';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->output->title('Set up Faction');

        // Use vendor as a hint if this is the first configuration
        $firstConfiguration = empty(env('REPOSITORY_PACKAGE_VENDOR'));

        if (
            $firstConfiguration &&
            !$this->output->confirm(
                'Would you like to configure your installation now? Otherwise, the setup has to be done manually through editing the .env file.',
            )
        ) {
            return 0;
        }

        $envVars = [];

        $confirmed = false;

        $environmentRaw = [
            'Development' => 'local',
            'Production' => 'production',
        ];
        $environmentDecorated = [
            'local' => 'Development',
            'production' => 'Production',
        ];

        $validateName = function (string $value) {
            $value = trim($value);
            if (!preg_match('/^[a-zA-Z0-9._-]+$/', $value)) {
                throw new RuntimeException('Invalid value');
            }

            return $value;
        };

        while (!$confirmed) {
            $this->output->section('General Application Settings');

            $envVars['APP_ENV'] =
                $environmentRaw[
                    $this->output->choice(
                        'In which <fg=cyan>mode</> should Faction run?',
                        ['Development', 'Production'],
                        // TODO: FIX DEFAULT VALUE
                        $environmentDecorated[
                            $envVars['APP_ENV'] ?? env('APP_ENV', 'production')
                        ],
                    )
                ];

            $envVars['APP_URL'] = $this->output->ask(
                'Under which <fg=cyan>URL</> should this app run?',
                $envVars['APP_URL'] ?? env('APP_URL', 'https://localhost'),
                function (string $value) {
                    if (!preg_match('@^https?://@i', $value)) {
                        $value = "https://$value";
                    }

                    $value = Str::unfinish($value, '/');

                    return $value;
                },
            );

            $envVars['REPOSITORY_PACKAGE_VENDOR'] = $this->output->ask(
                'Which <fg=cyan>Composer vendor</>\'s packages should be listed by the app?',
                $envVars['REPOSITORY_PACKAGE_VENDOR'] ??
                    env('REPOSITORY_PACKAGE_VENDOR'),
                $validateName,
            );
            $envVars['REPOSITORY_GITHUB_ORG'] = $this->output->ask(
                'Which <fg=cyan>GitHub organization</> owns the repositories of the listed packages?',
                $envVars['REPOSITORY_GITHUB_ORG'] ??
                    env('REPOSITORY_GITHUB_ORG'),
                $validateName,
            );

            $this->output->section('GitHub Credentials');

            $envVars['REPOSITORY_GITHUB_TOKEN'] = $this->output->ask(
                'GitHub <fg=cyan>access token</> for reading packages (with "repo" scope enabled)',
                $envVars['REPOSITORY_GITHUB_TOKEN'] ??
                    env('REPOSITORY_GITHUB_TOKEN'),
                function (string $value) {
                    $value = trim($value);
                    if (!preg_match('/^[0-9a-f]{40}$/', $value)) {
                        throw new RuntimeException('Invalid value');
                    }

                    return $value;
                },
            );

            if (
                empty(
                    env(
                        'REPOSITORY_GITHUB_WEBHOOK_SECRET',
                        $envVars['REPOSITORY_GITHUB_WEBHOOK_SECRET'] ?? '',
                    )
                )
            ) {
                $this->output->writeln([
                    sprintf(
                        '💡 To keep packages updated automatically, a webhook to the <fg=cyan>%s/webhook</> endpoint is needed.',
                        Str::unfinish($envVars['APP_URL'], '/'),
                    ),
                    sprintf(
                        'Such an organization-wide webhook can be set up in the GitHub %s.',
                        $this->createLink(
                            "https://github.com/organizations/{$envVars['REPOSITORY_GITHUB_ORG']}/settings/hooks",
                            'organization settings',
                        ),
                    ),
                    sprintf(
                        "The following events are required from the webhook:\n%s",
                        join(
                            "\n",
                            array_map(fn($event) => "- $event", [
                                'Branch or tag creation',
                                'Branch or tag deletion',
                                'Pushes',
                                'Repositories',
                            ]),
                        ),
                    ),
                ]);
            }

            $envVars['REPOSITORY_GITHUB_WEBHOOK_SECRET'] = $this->output->ask(
                'GitHub <fg=cyan>hook secret</>',
                $envVars['REPOSITORY_GITHUB_WEBHOOK_SECRET'] ??
                    env('REPOSITORY_GITHUB_WEBHOOK_SECRET'),
                fn(string $value) => trim($value),
            );

            $this->output->section('Access Control');

            if (empty($envVars['GITHUB_CLIENT_ID'])) {
                $this->output->writeln([
                    sprintf(
                        '💡 For verifying users\' membership of an organization, a GitHub OAuth app with a callback URL pointing to <fg=cyan>%s/login/callback</> needs to be created.',
                        Str::unfinish($envVars['APP_URL'], '/'),
                    ),
                    sprintf(
                        'This can be done in the GitHub %s.',
                        $this->createLink(
                            "https://github.com/organizations/{$envVars['REPOSITORY_GITHUB_ORG']}/settings/applications",
                            'organization settings',
                        ),
                    ),
                ]);

                $orgAccessControlEnabled = $this->output->confirm(
                    sprintf(
                        'Do you want to add an OAuth app to restrict Faction access to only members of <fg=cyan>%s</>?',
                        $envVars['REPOSITORY_GITHUB_ORG'],
                    ),
                    $orgAccessControlEnabled ?? true,
                );
            } else {
                $orgAccessControlEnabled = true;
            }

            $envVars['AUTH_GITHUB_ORGS_WHITELIST'] =
                $envVars['AUTH_GITHUB_ORGS_WHITELIST'] ?? '';
            $envVars['GITHUB_CLIENT_ID'] =
                $envVars['GITHUB_CLIENT_ID'] ?? env('GITHUB_CLIENT_ID', '');
            $envVars['GITHUB_CLIENT_SECRET'] =
                $envVars['GITHUB_CLIENT_SECRET'] ??
                env('GITHUB_CLIENT_SECRET', '');

            if ($orgAccessControlEnabled) {
                $envVars['AUTH_GITHUB_ORGS_WHITELIST'] = empty(
                    env('REPOSITORY_GITHUB_ORG')
                )
                    ? $envVars['REPOSITORY_GITHUB_ORG']
                    : env('AUTH_GITHUB_ORGS_WHITELIST');

                $envVars['GITHUB_CLIENT_ID'] = $this->output->ask(
                    'The <fg=cyan>Client ID</> of the OAuth app',
                    $envVars['GITHUB_CLIENT_ID'],
                    function (string $value) {
                        $value = trim($value);
                        if (
                            strlen($value) > 0 &&
                            !preg_match('/^[0-9a-f]{20}$/', $value)
                        ) {
                            throw new RuntimeException('Invalid value');
                        }

                        return $value;
                    },
                );

                $envVars['GITHUB_CLIENT_SECRET'] = $this->output->ask(
                    'The <fg=cyan>Client Secret</> of the OAuth app',
                    $envVars['GITHUB_CLIENT_SECRET'],
                    function (string $value) {
                        $value = trim($value);
                        if (
                            strlen($value) > 0 &&
                            !preg_match('/^[0-9a-f]{40}$/', $value)
                        ) {
                            throw new RuntimeException('Invalid value');
                        }

                        return $value;
                    },
                );
            }

            $this->output->section('Verify');

            $orgsWhitelist = $envVars['AUTH_GITHUB_ORGS_WHITELIST'] ?: '-';
            $hookSecret = $envVars['REPOSITORY_GITHUB_WEBHOOK_SECRET'] ?: '-';
            $clientId = $envVars['GITHUB_CLIENT_ID'] ?: '-';
            $clientSecret = $envVars['GITHUB_CLIENT_SECRET'] ?: '-';
            $this->output->write(
                <<<EOT
                <fg=yellow>General Application Settings</>
                <fg=green>Environment:</>         {$environmentDecorated[$envVars['APP_ENV']]}
                <fg=green>URL:</>                 {$envVars['APP_URL']}
                <fg=green>Package vendor:</>      {$envVars['REPOSITORY_PACKAGE_VENDOR']}
                <fg=green>GitHub organization:</> {$envVars['REPOSITORY_GITHUB_ORG']}
                </>
                <fg=yellow>GitHub Credentials</>
                <fg=green>GitHub access token:</>       {$envVars['REPOSITORY_GITHUB_TOKEN']}
                <fg=green>GitHub webhook secret:</>     {$hookSecret}
                <fg=green>Organizations with access:</> {$orgsWhitelist}
                </>
                <fg=yellow>Access Control</>
                <fg=green>OAuth Client ID:</>     {$clientId}
                <fg=green>OAuth Client Secret:</> {$clientSecret}
                EOT
                ,
            );

            $confirmed = $this->output->confirm('Is this okay?');

            if (!$confirmed) {
                if (!$firstConfiguration) {
                    // Cancel when this is not the initial run of the setup
                    return 0;
                } elseif (
                    !$this->output->confirm(
                        'Do you want to go through the Q&A again? (Choose "no" to cancel the setup and edit the .env manually. You may also come back anytime by running <fg=cyan>php artisan faction:setup</>)',
                    )
                ) {
                    return 0;
                }
            }
        }

        $envPath = App::environmentFilePath();
        $envData = FS::readFile($envPath);
        $envData = preg_replace(
            array_map(fn($var) => "/^{$var}=.*$/m", array_keys($envVars)),
            array_map(
                fn($var, $value) => "{$var}={$value}",
                array_keys($envVars),
                array_values($envVars),
            ),
            $envData,
            -1,
            $count,
        );

        FS::dumpFile($envPath, $envData);
        $this->output->success('Successfully configured the environment');

        if ($firstConfiguration) {
            $this->output->writeln(
                'You\'re now ready to initialize your Faction installation. Run <fg=cyan>php artisan faction:initialize-repository</> to scan your GitHub organization for packages.',
            );

            $this->output->block(
                [
                    'Please note that the scanning process may take between some minutes and a couple hours, depending on the number of packages/tags to scan.',
                    'You may also exceed the hourly rate limit of your GitHub access token. In that case, just cancel the command and run it again when your rate limit has been reset (the error message will tell you when that will be) — intermediate results are cached so the initialize command never needs to start over from scratch and will at some point finish eventually.',
                ],
                null,
                'bg=blue;fg=black',
                '  ',
                2,
            );
        }
    }

    /**
     * Create a terminal link
     */
    protected function createLink(
        string $url,
        string $text,
        string $fallbackFormat = '%s (%s)'
    ): string {
        $OSC = "\u{001B}]";
        $BEL = "\u{0007}";
        $SEP = ';';

        $supportedApps = ['vscode', 'iTerm.app', 'Apple_Terminal', 'hyper'];

        if (
            $this->output->isDecorated() &&
            in_array(env('TERM_PROGRAM'), $supportedApps)
        ) {
            return join('', [
                $OSC,
                '8',
                $SEP,
                $SEP,
                $url,
                $BEL,
                $text,
                $OSC,
                '8',
                $SEP,
                $SEP,
                $BEL,
            ]);
        } else {
            return sprintf($fallbackFormat, $text, $url);
        }
    }
}
