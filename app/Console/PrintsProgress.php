<?php

namespace App\Console;

/**
 * Indicate a process that might take longer and may succeed or fail.
 * Provdes a `progress()` method that takes a message which describes what the
 * process does, returns a closure which might be called with `true` or `false`
 * to tell if the process succeeded and adjust the output accordingly.
 */
trait PrintsProgress
{
    /**
     * Start a progress report
     *
     * @param string $message
     * @param bool   $prependNewLine
     * @return \Closure
     */
    protected function progress(
        string $message,
        bool $prependNewLine = false
    ): \Closure {
        if ($prependNewLine) {
            $this->output->newLine();
        }

        $waitingSymbol = '⏳ ';

        $this->output->write($waitingSymbol . $message);

        return function (bool $success): void {
            static $written = false;

            if ($written === false) {
                $written = true;
                $this->output->write("\r" . ($success ? '✅' : '❌'));
                $this->output->newLine();
            }
        };
    }
}
