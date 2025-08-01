<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Post;
use App\Http\Requests\PostRequest;

class PostController extends Controller
{
    public function index()
    {
        // すべての投稿を更新日時が古い順で取得する
        $posts = Post::orderBy('updated_at', 'asc')->get();

        // ログインユーザーの投稿だけを表示したい場合はこちらを使う（必要に応じて切り替え）
        // $posts = Auth::user()->posts()->orderBy('updated_at', 'asc')->get();

        return view('posts.index', compact('posts'));
    }

    // 詳細ページ
    public function show(Post $post)
    {
        return view('posts.show', compact('post'));
    }

    // 作成ページ
    public function create()
    {
        return view('posts.create');
    }

    // 作成機能
    public function store(PostRequest $request)
    {
        $post = new Post();
        $post->title = $request->input('title');
        $post->content = $request->input('content');
        $post->user_id = Auth::id();
        $post->save();

        return redirect()->route('posts.index')->with('flash_message', '投稿が完了しました。');
    }

    // 編集ページ
    public function edit(Post $post)
    {
        // ログインユーザーが投稿者じゃない場合はリダイレクト
        if ($post->user_id !== Auth::id()) {
            return redirect()->route('posts.index')->with('error_message', '不正なアクセスです。');
        }
        return view('posts.edit', compact('post'));
    }

    // 投稿の更新処理を行うメソッド
    public function update(PostRequest $request, Post $post)
    {
        // ログインユーザーが投稿者でなければ、不正アクセスとして一覧にリダイレクト
        if ($post->user_id !== Auth::id()) {
            return redirect()->route('posts.index')->with('error_message', '不正なアクセスです。');
        }
        // フォームから送られてきたタイトルを投稿に反映
        $post->title = $request->input('title');
        // フォームから送られてきた本文を投稿に反映
        $post->content = $request->input('content');
        // データベースに保存（更新）
        $post->save();
        // 詳細ページにリダイレクトし、「投稿を編集しました」とメッセージ表示
        return redirect()->route('posts.show', $post)->with('flash_message', '投稿を編集しました。');
    }

    // 投稿削除
    public function destroy(Post $post)
    {
        if ($post->user_id !== Auth::id()) {
            return redirect()->route('posts.index')->with('error_message', '不正なアクセスです。');
        }
        $post->delete();

        return redirect()->route('posts.index')->with('flash_message', '投稿を削除しました。');
    }
}