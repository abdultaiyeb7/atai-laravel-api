<?php

namespace App\Http\Controllers;

use App\Http\Requests\ManageQuestionRequest;
use Illuminate\Support\Facades\DB;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function manageQuestion(ManageQuestionRequest $request)
    {
        $validatedData = $request->validated();
        $message = '';

        try {
            // Call the stored procedure with the provided data
            DB::statement(
                'CALL sp_manage_questions(?, ?, ?, ?, ?, ?, ?, ?, @message)',
                [
                    $validatedData['action_type'],
                    $validatedData['p_id'] ?? null,
                    $validatedData['p_question_text'],
                    $validatedData['p_question_label'] ?? null,
                    $validatedData['p_question_type'],
                    $validatedData['p_client_id'],
                    $validatedData['p_question_level'] ?? null,
                    $validatedData['p_question_parent_level'] ?? null,
                ]
            );

            $result = DB::select('SELECT @message AS message');
            $message = $result[0]->message;

            return response()->json(['message' => $message], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function getQuestions(Request $request)
{
    try {
        $clientId = $request->query('client_id');
        if (!$clientId) {
            return response()->json(['message' => 'Client ID is required.'], 400);
        }

        // Fetch all questions for the specified client ID
        $questions = Question::where('client_id', $clientId)
            ->orderBy('question_level', 'asc')
            ->get();

        // Build a hierarchical structure dynamically
        $questionsByParent = [];
        foreach ($questions as $question) {
            $questionsByParent[$question->question_parent_level][] = $question;
        }

        // Recursively build the hierarchy
        $hierarchicalData = $this->buildHierarchy($questionsByParent, 0);

        return response()->json(['questions' => $hierarchicalData], 200);

    } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 500);
    }
}

// Recursive function to build the hierarchy
private function buildHierarchy($questionsByParent, $parentLevel)
{
    $result = [];
    if (isset($questionsByParent[$parentLevel])) {
        foreach ($questionsByParent[$parentLevel] as $question) {
            $children = $this->buildHierarchy($questionsByParent, $question->question_level);
            if ($children) {
                $question->children = $children;
            }
            $result[] = $question;
        }
    }
    return $result;
}


public function updateQuestion(Request $request)
{
    try {
        $validatedData = $request->validate([
            'action_type' => 'required|in:U',
            'p_id' => 'required|integer',
            'p_question_text' => 'required|string',
            'p_question_label' => 'nullable|string|max:500',
            'p_question_type' => 'nullable|integer|in:1,2,3,4,5,6',
            'p_client_id' => 'required|integer',
            'p_question_level' => 'nullable|integer',
            'p_question_parent_level' => 'nullable|integer',
        ]);

        // Call the stored procedure for updating the question
        DB::statement(
            'CALL sp_manage_questions(?, ?, ?, ?, ?, ?, ?, ?, @message)',
            [
                $validatedData['action_type'],
                $validatedData['p_id'],
                $validatedData['p_question_text'],
                $validatedData['p_question_label'] ?? null,
                $validatedData['p_question_type'] ?? null,
                $validatedData['p_client_id'],
                $validatedData['p_question_level'] ?? null,
                $validatedData['p_question_parent_level'] ?? null,
            ]
        );

        // Fetch the OUT parameter value
        $result = DB::select('SELECT @message AS message');
        $message = $result[0]->message;

        return response()->json(['message' => $message], 200);
    } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 500);
    }
}

    
public function deleteQuestion(Request $request)
{
    try {
        // Validate request data
        $validatedData = $request->validate([
            'action_type' => 'required|in:D',
            'p_id' => 'required|integer|min:1', // Ensure p_id is required and greater than 0
            'p_question_text' => 'required|string',
            'p_question_label' => 'nullable|string|max:500',
            'p_question_type' => 'required|integer|in:1,2,3,4,5,6',
            'p_client_id' => 'required|integer',
            'p_question_level' => 'nullable|integer',
            'p_question_parent_level' => 'nullable|integer',
        ]);

        // Call the stored procedure for deletion
        DB::statement(
            'CALL sp_manage_questions(?, ?, ?, ?, ?, ?, ?, ?, @message)',
            [
                $validatedData['action_type'],
                $validatedData['p_id'],
                $validatedData['p_question_text'],
                $validatedData['p_question_label'] ?? null,
                $validatedData['p_question_type'],
                $validatedData['p_client_id'],
                $validatedData['p_question_level'] ?? null,
                $validatedData['p_question_parent_level'] ?? null,
            ]
        );

        // Fetch the OUT parameter value
        $result = DB::select('SELECT @message AS message');
        $message = $result[0]->message;

        return response()->json(['message' => $message], 200);

    } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 500);
    }
}



}
