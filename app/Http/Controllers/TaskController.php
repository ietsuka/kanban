<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;

class TaskController extends Controller
{
  public function index()
  {
    $tasks = auth()->user()->statuses()->with('tasks')->get();
    return view('tasks.index', compact('tasks'));
  }

  /**
   * タスクカードを追加
   *
   * @param Request $request
   * @return mixed
   * @throws \Illuminate\Validation\ValidationException
   */
  public function store(Request $request)
  {
    $this->validate($request, [
      'title' => ['required', 'string', 'max:56'],
      'description' => ['required', 'string'],
      'status_id' => ['required', 'exists:statuses,id']
    ]);

    return $request->user()
      ->tasks()
      ->create($request->only('title', 'description', 'status_id'));
  }

  /**
   * タスクの並び順を更新
   *
   * @param Request $request
   * @return mixed
   * @throws \Illuminate\Validation\ValidationException
   */
  public function sync(Request $request)
  {
    $this->validate(request(), [
      'columns' => ['required', 'array']
    ]);

    foreach ($request->columns as $status) {
      foreach ($status['tasks'] as $i => $task) {
        $order = $i + 1;
        if ($task['status_id'] !== $status['id'] || $task['order'] !== $order) {
          request()->user()->tasks()
            ->find($task['id'])
            ->update(['status_id' => $status['id'], 'order' => $order]);
        }
      }
    }

    return $request->user()->statuses()->with('tasks')->get();
  }

  /**
   * タスクを削除
   *
   * @param int $taskId
   * @return int
   */
  public function destroy(int $taskId)
  {
    $del_task = Task::find($taskId);
    $del_task->comments()->delete();
    $del_task->delete();

    return (200);
  }
}
