<?php

namespace App\Http\Controllers;

use app\Helpers\Helper;
use App\Http\Requests\Post\CreateValidate;
use App\Http\Requests\Post\UpdateValidate;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PostController extends Controller
{
    public static function getById($id): Post
    {
        $post = Post::with('user')->where(['id' => $id])->first();
        if (!$post) {
            throw new NotFoundHttpException("Post not found");
        }
        return $post;
    }

    private function checkPostOwnership(Post $post, $userId): bool
    {
        if ($post->author_id != $userId) {
            return false;
        }
        return true;
    }

    public function getAll(): JsonResponse
    {
        $user = Auth::user();
        try {
            $posts = Post::with('user');
            if ($user) {
                $posts = $posts->where(['author_id' => $user->id]);
            }
            $posts = $posts->orderBy('updated_at', 'desc')->paginate(10);
            return Helper::sendSuccessResponse($posts, Response::HTTP_OK, 'Get all post successful');
        } catch (\Exception $e) {
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            return Helper::sendErrorResponse(
                ['file' => $e->getFile(), 'line' => $e->getLine()],
                $statusCode,
                $e->getMessage()
            );
        }
    }

    public function getOne($id): JsonResponse
    {
        try {
            $post = $this->getById($id);
            return Helper::sendSuccessResponse(['post' => $post], Response::HTTP_OK, 'Get one post successful');
        } catch (\Exception $e) {
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            return Helper::sendErrorResponse(
                ['file' => $e->getFile(), 'line' => $e->getLine()],
                $statusCode,
                $e->getMessage()
            );
        }
    }

    public function create(CreateValidate $request): JsonResponse
    {
        $data = $request->validated();
        $user = Auth::user();
        DB::beginTransaction();
        try {
            $newPost = Post::create([
                'title' =>  $data['title'],
                'body' => $data['body'],
                'author_id' => $user->id,
            ]);
            DB::commit();
            return Helper::sendSuccessResponse(['new_post' => $newPost], Response::HTTP_CREATED, 'Post created successful');
        } catch (\Exception $e) {
            DB::rollBack();
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            return Helper::sendErrorResponse(
                ['file' => $e->getFile(), 'line' => $e->getLine()],
                $statusCode,
                $e->getMessage()
            );
        }
    }

    public function update(UpdateValidate $request, $id): JsonResponse
    {
        $user = Auth::user();
        $data = $request->validated();
        try {
            $post = $this->getById($id);
            $checkOwnership = $this->checkPostOwnership($post, $user->id);
            if (!$checkOwnership) throw new AccessDeniedHttpException("Can't edit this post. This is not your own post");

            DB::beginTransaction();
            // Update Post
            $post->update([
                'title' => $data['title'] ?? $post->title,
                'body' => $data['body'] ?? $post->body
            ]);
            DB::commit();
            return Helper::sendSuccessResponse(['updated_post' => $post], Response::HTTP_OK, 'Update one post successful');
        } catch (\Exception $e) {
            DB::rollBack();
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            if ($e instanceof AccessDeniedHttpException) {
                $statusCode = Response::HTTP_FORBIDDEN;
            }
            if ($e instanceof NotFoundHttpException) {
                $statusCode = Response::HTTP_NOT_FOUND;
            }
            return Helper::sendErrorResponse(
                ['file' => $e->getFile(), 'line' => $e->getLine()],
                $statusCode,
                $e->getMessage()
            );
        }
    }

    public function delete($id): JsonResponse
    {
        $user = Auth::user();
        try {
            $post = $this->getById($id);
            $checkOwnership = $this->checkPostOwnership($post, $user->id);
            if (!$checkOwnership) throw new AccessDeniedHttpException("Can't delete this post. This is not your own post");

            // Delete Post
            $post->delete();

            return Helper::sendSuccessResponse([], Response::HTTP_NO_CONTENT, 'Delete one post successful');
        } catch (\Exception $e) {
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            if ($e instanceof AccessDeniedHttpException) {
                $statusCode = Response::HTTP_FORBIDDEN;
            }
            if ($e instanceof NotFoundHttpException) {
                $statusCode = Response::HTTP_NOT_FOUND;
            }
            return Helper::sendErrorResponse(
                ['file' => $e->getFile(), 'line' => $e->getLine()],
                $statusCode,
                $e->getMessage()
            );
        }
    }
}
