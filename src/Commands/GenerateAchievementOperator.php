<?php

namespace Aenzenith\LaravelAchiever\Commands;

use Illuminate\Console\Command;

class GenerateAchievementOperator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:achievement-operator {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new achievement operator to be used in the system.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $operation_key = $this->ask('What is the operation_key? (required)');

        if ($operation_key == null) {
            $this->error('operation_key cannot be empty');
            return;
        }

        $is_model_id_required = $this->ask("Is model_id required? (e.g., If a achievement type check is specific to a certain category, the category id is requested as a parameter.) (y/n)");

        if ($is_model_id_required == 'y') {
            $is_model_id_required = true;
        } elseif ($is_model_id_required == 'n') {
            $is_model_id_required = false;
        } else {
            $this->error('Invalid answer: Please respond with yes(y) or no(n)');
            return;
        }

        $is_descender = $this->ask('Are achievements of this achievement type more valuable when the points are low? (e.g., Achievements for a ranking list.) (y/n)');
        if ($is_descender == 'y') {
            $is_descender = true;
        } elseif ($is_descender == 'n') {
            $is_descender = false;
        } else {
            $this->error('Invalid answer: Please respond with yes(y) or no(n)');
            return;
        }

        $stop_at_once_unlocked = $this->ask('Should the number of achievements that can be earned at once in this type be limited to 1? (e.g., Choose "y" for achievement types that you want to progress step by step.) (y/n) Default: n');
        if ($stop_at_once_unlocked == 'y') {
            $stop_at_once_unlocked = true;
        } elseif ($stop_at_once_unlocked == 'n') {
            $stop_at_once_unlocked = false;
        } else {
            $this->error('Invalid answer: Please respond with yes(y) or no(n)');
            return;
        }

        $constructorParameters = $is_model_id_required
            ? '$model_id'
            : '';

        $constructorCall = $is_model_id_required
            ? "parent::__construct('{$operation_key}', " . ($is_descender ? 'true' : 'false') . ", " . ($stop_at_once_unlocked ? 'true' : 'false') . ", \$model_id);"
            : "parent::__construct('{$operation_key}', " . ($is_descender ? 'true' : 'false') . ", " . ($stop_at_once_unlocked ? 'true' : 'false') . ");";

        $achievementOperatorClass = <<<EOT
            <?php

            namespace App\Achievements\Operators;

            use Aenzenith\LaravelAchiever\AchievementOperator;

            class {$this->argument('name')} extends AchievementOperator
            {
                public function getPoints(\$points = null)
                {
                    if (\$points !== null) {
                        return \$this->points = \$points;
                    }

                    // Write the calculation part here, or leave this part empty and perform calculations outside the class, passing them as a parameter to the getPoints method.

                    // Example:
                    // \$points = 0;
                    // return \$this->points = \$points;
                }

                public function __construct({$constructorParameters})
                {
                    {$constructorCall}
                }
            }
            EOT;

        if (!file_exists(app_path('Achievements/Operators'))) {
            mkdir(app_path('Achievements/Operators'), 0777, true);
        }

        file_put_contents(app_path("Achievements/Operators/{$this->argument('name')}.php"), $achievementOperatorClass);

        $this->info('Achievement operator created in app/Achievements/Operators');
    }
}
