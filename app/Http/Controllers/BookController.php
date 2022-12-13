<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class BookController extends Controller
{

    public function index(){
        $books =  Book::with("category","editorial","authors")->get();
        return [
            "error"=> false,
            "message"=> "Succesfull query",
            "data"=> $books
        ];
    }

    public function addReview(Request $request){
        $validator = Validator::make($request->all(), [
            'comment' => 'required',
            'book_id' => 'required',
        ]);
        if (!$validator->fails()) {
            DB::beginTransaction();
            try {
                $user =  User::where("email",auth()->user()->email)
                    ->get()->first();
                var_dump($user);
                $bookReview = new BookReview();
                $bookReview->comment = $request->comment;
                $bookReview->edited = false;
                $bookReview->book_id = $request->book_id;
                $bookReview->user_id = $user->id;
                $bookReview->save();
                DB::commit();
                return $this->getResponse201('comment', 'created', $user);
            } catch (Exception $e) {
                DB::rollBack();
                return $this->getResponse500([$e->getMessage()]);
            }
        } else {
            return $this->getResponse500([$validator->errors()]);
        }
    }

    public function updateBookReview($id,Request $request){
        $validator = Validator::make($request->all(), [
            'comment' => 'required'
        ]);
        if (!$validator->fails()) {
            DB::beginTransaction();
            try {
                $user = auth()->user();
                $bookReview = BookReview::where("id",$id)->get()->first();
                if ($user->id==$bookReview->user_id){
                    $bookReview->comment = $request->comment;
                    $bookReview->edited = true;
                    $bookReview->save();
                    DB::commit();
                    return $this->getResponse201('comment', 'updated', $bookReview);
                }else{
                    return $this->getResponse403();
                }
            } catch (Exception $e) {
                DB::rollBack();
                return $this->getResponse500([$e->getMessage()]);
            }
        } else {
            return $this->getResponse500([$validator->errors()]);
        }
    }

    public function show($id){
        $books =  Book::where("id",$id)
                ->with("category","editorial","authors")
                ->get();
        return [
            "error"=> false,
            "message"=> "Succesfull query",
            "data"=> $books
        ];
    }

    public function store(Request $request){
        /*DB::beginTransaction();

        try {
            DB::commit();
        }catch (Exception $exception){
            DB::rollback();
        }*/

        $exist = Book::where('isbn',trim($request->isbn))->exists();
        if ($exist){
            return [
                "error"=> true,
                "message"=> "ISBN Invalid",
                "data"=> ""
            ];
        }
        $book = new Book();
        $book->isbn = trim($request->isbn);
        $book->title = $request->title;
        $book->category_id = $request->category["id"];
        $book->editorial_id = $request->editorial["id"];
        $book->publish_date = Carbon::now();
        $book->save();
        $bookId = $book->id;

        foreach($request->authors as $author){
            $book->authors()->attach($author);
        }
        return [
            "error"=> false,
            "message"=> "The book has been created!",
            "data"=>[
                "book_id"=> $bookId,
                "book"=> $book,
            ]
        ];
    }

    public function update(Request $request, $id){
        $response = $this->getResponse();
        DB::beginTransaction();

        try {
            $book = Book::find($id);
            if($book){
                $isBnOwner = Book::where("isbn",$request->isbn)->first();
                if($isBnOwner->id == $book->id){
                    $book->isbn = trim($request->isbn);
                }
                $book->title = $request->title;
                $book->category_id = $request->category["id"];
                $book->editorial_id = $request->editorial["id"];
                $book->publish_date = Carbon::now();
                $book->update();
                // Delete
                foreach($book->authors as $author){
                    $book->authors()->detach($author->id);
                }
                // Add
                foreach($request->authors as $author){
                    $book->authors()->attach($author);
                }
                $response["data"] = $book;
                return $response;
            }
            DB::commit();
        }catch (Exception $exception){
            $response["error"] = true;
            $response["message"] = "Book not found!";
            return $response;
            DB::rollback();
        }
    }

    public function destroy($id){
        $response = $this->getResponse();
        $book = Book::find($id);
        if(!$book){
            $response["error"] = false;
            $response["message"] = "Not found!";
            $response["data"] = $book;
            return $response;
        }
        foreach($book->authors as $author){
            $book->authors()->detach($author->id);
        }
        $book->delete();

        $response["error"] = false;
        $response["message"] = "Succesfull query";
        $response["data"] = $book;
        return $response;
    }

}
