<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Requests\UpdateBookRequest;

class BookController extends Controller
{
    public function __construct() {
        $this->authorizeResource(Book::class);
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Book::class);

        $request->validate([
            'isbn'=> 'regex:/^\d+$/',
            'is_available' => 'sometimes|boolean'
            ]);

        $books = Book::when($request->has('title'), function ($query) use ($request) {
            $query->where('title', 'like', '%'.$request->input('title').'%');
        })->when($request->has('isbn'), function ($query) use ($request) {
            $query->where('ISBN', 'like', '%'.$request->input('isbn').'%');
        })->when($request->has('is_available'), function ($query) use ($request) {
            $query->where('is_available', $request->boolean('is_available'));
        })
            ->paginate();

        return response()->json(BookResource::collection($books));
    }


    public function update(UpdateBookRequest $request, Book $book)
    {
        $this->authorize('update', $book);
        $book->update($request->all());
        return response()->json(new BookResource($book));
    }

    public function destroy(Book $book)
    {
        $this->authorize('delete', $book);
        $book->delete();
        return response()->json(new BookResource($book));
    }



    public function show(Request $request, Book $book)
    {
        $this->authorize('view', $book);
        
        return response()->json(BookResource::make($book));
    }
   

    public function store(StoreBookRequest $request)
    {
        $this->authorize('create', Book::class);
        $book = Book::create($request->all());

        return response()->json(BookResource::make($book));
    }
}
