<?php

namespace App\Support;

use App\Models\FriendRequest;
use App\Models\Kid;
use App\Models\Recipe;
use App\Models\Story;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class ContentAccess
{
    public static function canViewStory(Authenticatable $viewer, Story $story): bool
    {
        if ($viewer instanceof User && $viewer->role === 'parent' && (int) $story->user_id === (int) $viewer->id) {
            return true;
        }

        if ($viewer instanceof Kid && (int) $story->kid_id === (int) $viewer->id) {
            return true;
        }

        if ((int) ($story->is_featured ?? 0) === 1) {
            return true;
        }

        return self::viewerIsFriendOfStoryOwner($viewer, $story);
    }

    public static function canViewRecipe(Authenticatable $viewer, Recipe $recipe): bool
    {
        if ($viewer instanceof User && $viewer->role === 'parent' && (int) $recipe->user_id === (int) $viewer->id && empty($recipe->kid_id)) {
            return true;
        }

        if ($viewer instanceof Kid
            && (int) $recipe->kid_id === (int) $viewer->id
            && (int) $recipe->user_id === (int) $viewer->parent_id) {
            return true;
        }

        if ((int) ($recipe->recipe_of_the_week ?? 0) === 1) {
            return true;
        }

        return false;
    }

    private static function viewerIsFriendOfStoryOwner(Authenticatable $viewer, Story $story): bool
    {
        $viewerId = (int) $viewer->id;
        $viewerRole = $viewer instanceof Kid ? 'kid' : 'parent';

        $ownerId = $story->role === 'kid' ? (int) $story->kid_id : (int) $story->user_id;
        $ownerRole = $story->role === 'kid' ? 'kid' : 'parent';

        if ($ownerId < 1) {
            return false;
        }

        return FriendRequest::query()
            ->where('status', 1)
            ->where(function ($query) use ($viewerId, $viewerRole, $ownerId, $ownerRole) {
                $query->where(function ($q) use ($viewerId, $viewerRole, $ownerId, $ownerRole) {
                    $q->where('sender_id', $viewerId)
                        ->where('request_sent_by', $viewerRole)
                        ->where('receiver_id', $ownerId)
                        ->where('request_sent_to', $ownerRole);
                })->orWhere(function ($q) use ($viewerId, $viewerRole, $ownerId, $ownerRole) {
                    $q->where('sender_id', $ownerId)
                        ->where('request_sent_by', $ownerRole)
                        ->where('receiver_id', $viewerId)
                        ->where('request_sent_to', $viewerRole);
                });
            })
            ->exists();
    }
}