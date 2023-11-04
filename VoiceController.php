<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Voice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VoiceController extends Controller
{
    public function voice(Request $request){
        $validator = Validator::make($request->all(), [
            'question_id'   => 'required|integer|exists:questions,id',
            'value'         => 'required|boolean',
        ]);

        // Return response if validation fails
        if ($validator->fails()) {
            return response()->json([
                'status'    => 422,
                'message'   => 'Validation Error',
                'errors'    => $validator->errors(),
            ]);
        }

        $question = Question::find($request->question_id);
        if(!$question)
            return response()->json([
                'status'=>404,
                'message'=>'not found question ..'
            ]);
        if($question->user_id ==  auth()->id()){
            return response()->json([
                'status' => 500,
                'message' => 'The user is not allowed to vote to your question'
            ]);
        }
        //check if user voted 
        $voice = Voice::where([
                'user_id' => auth()->id(),
                'question_id' => $request->question_id
            ])->first();

        if (!is_null($voice)&&$voice->value===$request->value) {
            return response()->json([
                'status' => 500,
                'message' => 'The user is not allowed to vote more than once'
            ]);
        }else if (!is_null($voice)&&$voice->value!==$request->value){
            $voice->update([
                'value'=>$request->value
            ]);
            return response()->json([
                'status'=>201,
                'message'=>'update your voice'
            ]);
        }

        $question->voice()->create([
            'user_id'=>auth()->id(),
            'value'=>$request->value
        ]);

        return response()->json([
            'status'=>200,
            'message'=>'Voting completed successfully',
            'data' => [
                'question'  => $question,
                'voice'     => $voice
            ]
        ]);
    }
}
