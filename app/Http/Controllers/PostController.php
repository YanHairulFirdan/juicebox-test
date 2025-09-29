<?php

namespace App\Http\Controllers;

use App\Http\Requests\Post\StoreRequest;
use App\Http\Resources\Post\DetailResource;
use App\Http\Resources\Post\ListResource;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $posts = Post::query()
            ->with('user')
            ->when($request->has('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where('title', 'like', "%{$search}%");
                $query->orWhere('body', 'like', "%{$search}%");
                $query->orWhereHas('user', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%");
                });
            })
            ->paginate(10);

        return ListResource::collection($posts);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        try {
            $post = $user->posts()->create($request->validated());
            $resource = new DetailResource($post);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => 'Failed to create post'], 500);
        }

        return response()->json(['data' => $resource], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        return new DetailResource($post);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRequest $request, Post $post)
    {
        Gate::authorize('update', $post);

        try {
            $post->update($request->validated());
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => 'Failed to update post'], 500);
        }

        return response()->json(['data' => new DetailResource($post->refresh())]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        Gate::authorize('delete', $post);

        try {
            $post->delete();
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => 'Failed to delete post'], 500);
        }

        return empty_object(204);
    }
}
