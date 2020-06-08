<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

/**
 * Add @code/@endcode directives to Blade to do server-side syntax highlighting.
 *
 * Use @code('js', 'alert("foo")') to output "alert("foo")" as an inline code
 * block highlighted as JavaScript code.
 * Similarly, use @code('js')alert("foo")@endcode to highlight the same code in
 * the same language, but render it as an actual code block.
 */
class BladeHighlightServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Blade::directive('code', function (
            string $language,
            ?string $code = null
        ) {
            // $languageString = var_export($language, true);
            // $codeString = var_export($code, true);

            if (is_null($code)) {
                return <<<PHP
<?php
\$__codeblock_language = ${language};
ob_start();
?>
PHP;
            } else {
                return <<<PHP
<?php
echo sprintf(
    '<code class="hljs hljs--%s">%s</code>',
    $language
    Cache::remember(
        'loilo/blade-syntax-highlighter.code.' . $language$code
        10080,
        function() {
            return Highlight::highlight($language$code
        }
    )
);
?>
PHP;
            }
        });

        Blade::directive('endcode', function () {
            return <<<'PHP'
            <?php
            $code = View\dedent(trim(ob_get_contents(), "\r\n"));
            ob_end_clean();
            echo sprintf(
                '<pre class="hljs hljs--%s"><code>%s</code></pre>',
                $__codeblock_language,
                Cache::remember(
                    'loilo/blade-syntax-highlighter.code.' . $__codeblock_language . '.' . md5($code),
                    10080,
                    function () use ($__codeblock_language, $code) {
                        return Highlight::highlight($__codeblock_language, $code)->value;
                    }
                )
            );
            unset($__codeblock_language, $code);
            ?>
            PHP;
        });
    }
}
