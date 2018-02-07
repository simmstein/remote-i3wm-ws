<?php

/**
 * class Shell.
 *
 * @author Simon Vieille <simon@deblan.fr>
 */
class Shell
{
    /*
     * Executes the given command and wraps sprintf.
     *
     * @return mixed
     */
    public function exec()
    {
        if (func_num_args() > 0) {
            $command = call_user_func_array('sprintf', func_get_args());

            return shell_exec($command);
        }
    }
}
