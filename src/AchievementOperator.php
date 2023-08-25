<?php

namespace Aenzenith\LaravelAchiever;

use Aenzenith\LaravelAchiever\Models\Achievement;
use Aenzenith\LaravelAchiever\Models\AchievementProgress;
use Illuminate\Support\Facades\Log;

interface AchievementOperatorInterface
{
    public function getUserID($user_id = null);

    public function getPoints($points = null);

    public function run();
}

abstract class AchievementOperator implements AchievementOperatorInterface
{
    /**
     * Key of a specific achievement type.
     * This value must be defined in the constructor in subclasses.
     */
    protected $operation_key = null;

    /**
     * Determines whether achievements will be sorted and checked based on their order type (ascending/descending).
     * This value can be overridden in the constructor of subclasses.
     */
    protected $is_descender = false;

    /**
     * If there are multiple activatable achievements, but only the first achievement can be earned at once based on order,
     * this should be true.
     * Example: In a champions ranking, if only the last earned achievement should be given when passing a certain threshold, this should be true.
     * This value can be overridden in the constructor of subclasses.
     */
    protected $stop_at_once_unlocked = false;

    /**
     * If a achievement corresponds to a specific ID from any model, this value should be assigned.
     * Example: If a achievement is to be given to a user who solves questions in a specific category, this value should be the category ID.
     * This value must be defined in the constructor in necessary achievement types in subclasses.
     */
    protected $model_id = null;

    public function __construct($operation_key, $is_descender, $stop_at_once_unlocked = false, $model_id = null)
    {
        $this->operation_key = $operation_key;
        $this->is_descender = $is_descender;
        $this->stop_at_once_unlocked = $stop_at_once_unlocked;
        $this->model_id = $model_id;
    }

    /**
     * achievements are retrieved using this parameter. Points are determined in the called subclass using the getPoints() function.
     */
    public $points = 0;

    abstract public function getPoints($points = null);

    /**
     * User ID for whom the operation will be performed.
     */
    private $userID = null;

    /**
     * If operation is desired for a specific user, the user ID must be set using this function before calculating points.
     * Otherwise, auth()->user()->id is used.
     * @param int $user_id
     * @return void
     */
    public function getUserID($user_id = null)
    {
        if ($user_id != null) {
            $this->userID = $user_id;
        } else {
            $this->userID = auth()->user()->id;
        }
    }

    /**
     * List of achievement progresses to be processed for a specific user.
     */
    private $user_progresses = [];

    /**
     * Array storing previously unlocked achievement IDs during achievement checks for a user.
     */
    private $unlockedAchievementIDs = [];

    /**
     * Array storing achievement IDs that haven't been unlocked yet but have points surpassed during achievement checks for a user.
     */
    private $inProgressAchievementIDs = [];

    /**
     * Used to stop the loop in the check method.
     */
    private $allChecked = false;

    /**
     * Method used for achievement checks. Continues looping until $allChecked is true. Calls the checkAchievements() method in the loop.
     */
    private function check()
    {
        if ($this->userID == null) {
            $this->getUserID();
        }

        while (!$this->allChecked) {
            $this->checkAchievements();
        }
    }

    /**
     * Helper method used within the method for achievement checks.
     */
    private function checkAchievements()
    {
        $achievement = Achievement::where('operation_key', $this->operation_key)
            ->where('model_id', $this->model_id)
            ->whereNotIn('id', [...$this->unlockedAchievementIDs, ...$this->inProgressAchievementIDs])
            ->orderBy('points', $this->is_descender ? 'desc' : 'asc')
            ->where(function ($query) {
                if ($this->is_descender) {
                    $query->where('points', '>=', $this->points);
                } else {
                    $query->where('points', '<=', $this->points);
                }
            })
            ->first();

        if ($achievement == null) {
            $this->allChecked = true;
            return;
        }

        $user_progress = AchievementProgress::where('user_id', $this->userID)
            ->where('achievement_id', $achievement->id)
            ->first();

        if ($user_progress == null) {
            $user_progress = AchievementProgress::create([
                'user_id' => $this->userID,
                'achievement_id' => $achievement->id,
                'progress' => 0,
                'unlocked_at' => null,
                'notified_at' => null,
            ]);
        }

        if ($user_progress->unlocked_at == null) {
            $this->user_progresses[] = $user_progress;
            $this->inProgressAchievementIDs[] = $achievement->id;

            if ($this->stop_at_once_unlocked) {
                $this->allChecked = true;
            }
        } else {
            $this->unlockedAchievementIDs[] = $achievement->id;
        }
    }

    /**
     * Method used for processing achievements. Calls the processAchievement() method for achievement progresses in the $user_progresses array.
     */
    private function process()
    {
        foreach ($this->user_progresses as $user_progress) {
            $this->processAchievement($user_progress);
        }
    }

    /**
     * Helper method used within the method for processing achievements.
     */
    private function processAchievement($user_progress)
    {
        if ($this->points == null || $this->points == 0 || $user_progress == null) {
            return;
        }

        $progress = $this->is_descender ? max($this->points, $user_progress->achievement->points) : min($this->points, $user_progress->achievement->points);
        $user_progress->progress = $progress;

        if ($this->is_descender) {
            if ($progress <= $user_progress->achievement->points) {
                $user_progress->unlocked_at = now();
            }
        } else {
            if ($progress >= $user_progress->achievement->points) {
                $user_progress->unlocked_at = now();
            }
        }

        $user_progress->save();
    }

    /**
     * Method used for checking and processing achievements.
     */
    public function run()
    {
        try {
            $this->check();
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        $this->process();
    }
}
