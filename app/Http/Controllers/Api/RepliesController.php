<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ReplyRequest;
use App\Models\Reply;
use App\Models\Topic;
use App\Transformers\ReplyTransformer;
use Illuminate\Http\Request;

class RepliesController extends Controller
{
    public function store(ReplyRequest $request, Topic $topic, Reply $reply)
    {
        $reply->content = $request->content;
        $reply->topic()->associate($topic);
        $reply->user()->associate($this->user());
        $reply->save();

        return $this->response()->item($reply, new ReplyTransformer())
            ->setStatusCode(201);
    }

    public function destroy(Topic $topic, Reply $reply)
    {
        if ($reply->topic_id != $topic->id) {
            return $this->response()->errorBadRequest();
        }

        $this->authorize('destroy', $reply);
        $reply->delete();

        return $this->response()->noContent();
    }

    public function index(Topic $topic, Request $request)
    {
        app(\Dingo\Api\Transformer\Factory::class)->disableEagerLoading();

        $replies = $topic->replies()->paginate(20);

        if ($request->include) {
            $replies->load($request->include);
        }

        return $this->response->paginator($replies, new ReplyTransformer());
    }

    public function userIndex(User $user, Request $request)
    {
        app(\Dingo\Api\Transformer\Factory::class)->disableEagerLoading();

        $replies = $user->replies()->paginate(20);

        if ($request->include) {
            $replies->load($request->include);
        }

        return $this->response->paginator($replies, new ReplyTransformer());
    }
}
