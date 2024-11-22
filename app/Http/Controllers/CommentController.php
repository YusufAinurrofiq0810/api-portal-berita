<?php

namespace App\Http\Controllers;

use app\Helpers\Helper;
use App\Http\Requests\Comment\CreateValidate;
use App\Http\Requests\Comment\UpdateValidate;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CommentController extends Controller
{

    private function checkCommentOwnership(Comment $comment, $userId): bool
    {
        if ($comment->user_id != $userId) return false;
        return true;
    }

    private function getById($id): Comment
    {
        $comment = Comment::with('user')->where(['id' => $id])->first();
        if (!$comment) throw new NotFoundHttpException('Comment not found');
        return $comment;
    }

    public function getAllByUser(): JsonResponse
    {
        try {
            $user = Auth::user();
            $comments = Comment::with(['post', 'user'])->where(['user_id' => $user->id])->get();
            if ($comments->count() == 0) {
                throw new NotFoundHttpException("You haven't commented yet");
            }
            return Helper::sendSuccessResponse(
                ['total' => $comments->count(), 'comments' => $comments],
                Response::HTTP_OK,
                "Get all $user->email's comment successful"
            );
        } catch (\Exception $e) {
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
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

    public function getAllByPost($postId): JsonResponse
    {
        try {
            $post = PostController::getById($postId);
            $comments = Comment::with(['user'])->where(['post_id' => $post->id])->orderBy('created_at', 'desc')->get();
            if ($comments->count() == 0) {
                throw new NotFoundHttpException("Comment on this post not found");
            }
            return Helper::sendSuccessResponse(['total' => $comments->count(), 'comment' => $comments], Response::HTTP_OK, 'Get all comment on this post successful');
        } catch (\Exception $e) {
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
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

    public function create(CreateValidate $request, $postId): JsonResponse
    {
        $user = Auth::user();
        $data = $request->validated();
        DB::beginTransaction();
        try {
            $post = PostController::getById($postId);
            // Create Comment
            $newComment = Comment::create([
                'body' => $data['body'],
                'post_id' => $post->id,
                'user_id' => $user->id,
            ]);
            DB::commit();
            return Helper::sendSuccessResponse(['new_comment' => $newComment], Response::HTTP_CREATED, 'Get all comment on this post successful');
        } catch (\Exception $e) {
            DB::rollBack();
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
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

    public function update(UpdateValidate $request, $commentId): JsonResponse
    {
        $data = $request->validated();
        $user = Auth::user();
        DB::beginTransaction();
        try {
            $comment = $this->getById($commentId);
            $checkOwnership = $this->checkCommentOwnership($comment, $user->id);
            if (!$checkOwnership) {
                throw new BadRequestHttpException("Can't edit this comment. This comment is not your own");
            }

            // Update Comment
            $comment->update([
                'body' => $data['body'] ?? $comment->body,
            ]);

            DB::commit();
            return Helper::sendSuccessResponse(['updated_comment' => $comment], Response::HTTP_OK, 'Update comment successful');
        } catch (\Exception $e) {
            DB::rollBack();
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            if ($e instanceof NotFoundHttpException) {
                $statusCode = Response::HTTP_NOT_FOUND;
            }
            if ($e instanceof BadRequestHttpException) {
                $statusCode = Response::HTTP_BAD_REQUEST;
            }
            return Helper::sendErrorResponse(
                ['file' => $e->getFile(), 'line' => $e->getLine()],
                $statusCode,
                $e->getMessage()
            );
        }
    }

    public function delete($commentId): JsonResponse
    {
        $user = Auth::user();
        DB::beginTransaction();
        try {
            $comment = $this->getById($commentId);
            $checkOwnership = $this->checkCommentOwnership($comment, $user->id);
            if (!$checkOwnership) {
                throw new BadRequestHttpException("Can't delete this comment. This comment is not your own");
            }

            // Delete Comment
            $comment->delete();

            DB::commit();
            return Helper::sendSuccessResponse([], Response::HTTP_NO_CONTENT, 'Delete comment successful');
        } catch (\Exception $e) {
            DB::rollBack();
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            if ($e instanceof NotFoundHttpException) {
                $statusCode = Response::HTTP_NOT_FOUND;
            }
            if ($e instanceof BadRequestHttpException) {
                $statusCode = Response::HTTP_BAD_REQUEST;
            }
            return Helper::sendErrorResponse(
                ['file' => $e->getFile(), 'line' => $e->getLine()],
                $statusCode,
                $e->getMessage()
            );
        }
    }
}
