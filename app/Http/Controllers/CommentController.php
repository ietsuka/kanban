<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;

class CommentController extends Controller
{
  public function index()
  {
    $userId = auth()->id();
    return Comment::where('user_id', $userId)->orderBy('created_at', 'desc')->get();
  }

  /**
   * コメントを追加
   *
   * @param Request $request
   * @return mixed
   */
  public function store(Request $request)
  {
    $this->validate($request, [
      'comment' => ['required', 'string'],
      'task_id' => ['required', 'exists:tasks,id']
    ]);
    return $request->user()
      ->comments()
      ->create($request->only('comment', 'task_id'));
  }
}
