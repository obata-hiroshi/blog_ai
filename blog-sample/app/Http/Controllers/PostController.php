<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Requests\PostRequest;
use Carbon\Carbon;

class PostController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::all();
        return view('posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('posts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PostRequest $request)
    {
        $post = $request->user()->posts()->create($request->validated());

        return redirect()->route('posts.show', $post)
            ->with('success', 'Post created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        return view('posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        return view('posts.edit', compact('post'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PostRequest $request, Post $post)
    {
        if ($request->user()->cannot('update', $post)) {
            abort(403);
        }
        $post->update($request->validated());

        return redirect()->route('posts.show', $post)
            ->with('success', 'Post updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        if (auth()->user()->cannot('delete', $post)) {
            abort(403);
        }
        $post->delete();

        return redirect()->route('posts.index')
            ->with('success', 'Post deleted successfully.');
    }

    /**
     * Display a calendar view of posts.
     */
    public function calendar(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);

        $date = Carbon::createFromDate($year, $month, 1);

        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        $posts = Post::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                        ->orderBy('created_at')
                        ->get();

        $postsByDay = [];
        foreach ($posts as $post) {
            $day = Carbon::parse($post->created_at)->day;
            if (!isset($postsByDay[$day])) {
                $postsByDay[$day] = [];
            }
            $postsByDay[$day][] = $post;
        }

        $daysInMonth = $date->daysInMonth;
        $startOfWeek = $date->copy()->startOfMonth()->dayOfWeek; // 0 for Sunday, 6 for Saturday
        $monthNameYear = $date->format('Y年n月');

        $prevMonthLinkData = [
            'year' => $date->copy()->subMonth()->year,
            'month' => $date->copy()->subMonth()->month,
        ];
        $nextMonthLinkData = [
            'year' => $date->copy()->addMonth()->year,
            'month' => $date->copy()->addMonth()->month,
        ];

        return view('posts.calendar', compact(
            'date',
            'postsByDay',
            'daysInMonth',
            'startOfWeek',
            'monthNameYear',
            'prevMonthLinkData',
            'nextMonthLinkData'
        ));
    }
}
