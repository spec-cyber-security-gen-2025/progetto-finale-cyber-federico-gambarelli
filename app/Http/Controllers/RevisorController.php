<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RevisorController extends Controller
{
    public function dashboard(){
        $unrevisionedArticles = Article::where('is_accepted', NULL)->get();
        $acceptedArticles = Article::where('is_accepted', true)->get();
        $rejectedArticles = Article::where('is_accepted', false)->get();

        return view('revisor.dashboard', compact('unrevisionedArticles', 'acceptedArticles', 'rejectedArticles'));
    }

    public function acceptArticle(Article $article){
        $article->is_accepted = true;
        $article->save();

        Log::info('Article accepted: ' . $article->title . ' by ' . Auth::user()->name);
        return redirect(route('revisor.dashboard'))->with('message', 'Article Published');
    }

    public function rejectArticle(Article $article){
        $article->is_accepted = false;
        $article->save();
        Log::info('Article rejected: ' . $article->title . ' by ' . Auth::user()->name);
        return redirect(route('revisor.dashboard'))->with('message', 'Article Declined');
    }

    public function undoArticle(Article $article){
        $article->is_accepted = NULL;
        $article->save();
        Log::info('Article back to review: ' . $article->title . ' by ' . Auth::user()->name);
        return redirect(route('revisor.dashboard'))->with('message', 'Article back to review');
    }
}
