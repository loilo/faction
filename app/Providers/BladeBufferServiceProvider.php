<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use UnexpectedValueException;

/**
 * Add @buffer and @endbuffer directives to Blade as a
 * declarative way to control output buffering.
 */
class BladeBufferServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Blade::directive('buffer', function () {
            return '<?php ob_start(); ?>';
        });

        Blade::directive('endbuffer', function (string $name) {
            if ($name === '') {
                return '<?php ob_end_clean(); ?>';
            } else {
                // Store all buffered output in the given variable.
                $name = preg_replace('/^(["\'])(.+)\1$/', '$2', $name);

                if (!preg_match('/^[a-z][0-9a-z_]*$/i', $name)) {
                    throw new UnexpectedValueException(
                        sprintf(
                            'Invalid variable name "%s" to hold buffered content',
                            $name,
                        ),
                    );
                }

                return "<?php \$$name = ob_get_contents(); ob_end_clean(); ?>";
            }
        });
    }
}
