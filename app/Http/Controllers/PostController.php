<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->string('type')->toString();

        $posts = Post::published()
            ->when(in_array($type, ['news', 'event'], true), fn ($q) => $q->where('type', $type))
            ->orderByDesc('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('pages.news.index', [
            'posts' => $posts,
            'type' => $type,
            // The next event only; the news page features it on its own.
            'upcoming' => Post::published()->events()->where('event_at', '>=', now())->orderBy('event_at')->take(1)->get(),
        ]);
    }

    /** Events get their own address; the listing itself is shared with news. */
    public function events(Request $request)
    {
        return $this->index($request->merge(['type' => 'event']));
    }

    public function show(Post $post)
    {
        abort_unless($post->published_at && $post->published_at->isPast(), 404);

        return view('pages.news.show', [
            'post' => $post,
            'related' => Post::published()->whereKeyNot($post->id)->orderByDesc('published_at')->take(3)->get(),
        ]);
    }
}
