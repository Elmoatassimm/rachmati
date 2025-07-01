<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use App\Models\Comment;
use App\Models\Rachma;
use App\Models\Designer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class RatingController extends Controller
{
    /**
     * Submit a rating
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'target_id' => 'required|integer',
            'target_type' => 'required|in:rachma,store',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Verify target exists
            if ($request->target_type === 'rachma') {
                $target = Rachma::find($request->target_id);
                if (!$target) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Target not found'
                    ], 400);
                }
            } else {
                $target = Designer::find($request->target_id);
                if (!$target) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Target not found'
                    ], 400);
                }
            }

            // Check if user already rated this target
            $existingRating = Rating::where('user_id', $request->user()->id)
                ->where('target_id', $request->target_id)
                ->where('target_type', $request->target_type)
                ->first();

            if ($existingRating) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already rated this item'
                ], 400);
            }

            // Create rating
            $rating = Rating::create([
                'user_id' => $request->user()->id,
                'target_id' => $request->target_id,
                'target_type' => $request->target_type,
                'rating' => $request->rating,
            ]);

            // Create comment if provided
            if ($request->has('comment') && !empty($request->comment)) {
                Comment::create([
                    'user_id' => $request->user()->id,
                    'target_id' => $request->target_id,
                    'target_type' => $request->target_type,
                    'comment' => $request->comment,
                ]);
            }

            // Update average rating for the target
            if ($request->target_type === 'rachma') {
                $target->updateAverageRating();
            }

            return response()->json([
                'success' => true,
                'message' => 'Rating submitted successfully',
                'data' => $rating
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit rating',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get ratings for a target
     */
    public function index(Request $request, string $targetType, string $targetId): JsonResponse
    {
        try {
            // Validate target type
            if (!in_array($targetType, ['rachma', 'store'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid target type'
                ], 400);
            }

            // Get ratings with pagination
            $ratings = Rating::with('user')
                ->forTarget($targetId, $targetType)
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            // Get comments
            $comments = Comment::with('user')
                ->forTarget($targetId, $targetType)
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            // Calculate rating statistics
            $ratingStats = Rating::forTarget($targetId, $targetType)
                ->selectRaw('
                    AVG(rating) as average,
                    COUNT(*) as total,
                    SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                    SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                    SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                    SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                    SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
                ')
                ->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'ratings' => $ratings,
                    'comments' => $comments,
                    'statistics' => $ratingStats,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch ratings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's rating for a specific target
     */
    public function userRating(Request $request, string $targetType, string $targetId): JsonResponse
    {
        try {
            $rating = Rating::where('user_id', $request->user()->id)
                ->forTarget($targetId, $targetType)
                ->first();

            return response()->json([
                'success' => true,
                'data' => $rating
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user rating',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
