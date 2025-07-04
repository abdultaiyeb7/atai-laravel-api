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
            // DB::statement(
            //     'CALL sp_manage_questions(?, ?, ?, ?, ?, ?, ?, ?, @message)',
            //     [
            //         $validatedData['action_type'],
            //         $validatedData['p_id'] ?? null,
            //         $validatedData['p_question_text'],
            //         $validatedData['p_question_label'] ?? null,
            //         $validatedData['p_question_type'],
            //         $validatedData['p_client_id'],
            //         $validatedData['p_question_level'] ?? null,
            //         $validatedData['p_question_parent_level'] ?? null,
            //     ]
            // );

            DB::statement(
                'CALL sp_manage_questions(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @message)',
                [
                    $validatedData['action_type'],                         // 1
                    $validatedData['p_id'] ?? null,                        // 2
                    $validatedData['p_question_text'],                     // 3
                    $validatedData['p_question_label'] ?? null,            // 4
                    $validatedData['p_question_type'],                     // 5
                    $validatedData['p_client_id'],                         // 6
                    $validatedData['p_question_level'] ?? null,            // 7
                    $validatedData['p_question_parent_level'] ?? null,     // 8
                    $validatedData['p_page_size'] ?? null,                 // 9
                    $validatedData['p_page'] ?? null                       // 10
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
        // Validate request data (Allow array input for batch updates)
        $validatedData = $request->validate([
            'questions' => 'required|array|min:1',
            'questions.*.action_type' => 'required|in:U',
            'questions.*.p_id' => 'required|integer|min:1', // Ensure a valid question ID
            'questions.*.p_question_text' => 'required|string',
            'questions.*.p_question_label' => 'nullable|string|max:500',
            'questions.*.p_question_type' => 'required|integer|in:1,2,3,4,5,6',
            'questions.*.p_client_id' => 'required|integer',
            'questions.*.p_question_parent_level' => 'nullable|integer',
        ]);

        $messages = [];

        foreach ($validatedData['questions'] as $questionData) {
            // Fetch the existing question from the database
            $question = Question::find($questionData['p_id']);

            if (!$question) {
                $messages[] = [
                    'p_id' => $questionData['p_id'],
                    'message' => 'Error: Question ID not found.'
                ];
                continue; // Skip this question and move to the next one
            }

            // Ensure question_level does not change
            $p_question_level = $question->question_level;

            // Check if any changes were made
            $isSameText = $questionData['p_question_text'] === $question->question_text;
            $isSameLabel = $questionData['p_question_label'] === $question->question_label;
            $isSameType = $questionData['p_question_type'] == $question->question_type;
            $isSameParent = isset($questionData['p_question_parent_level']) && 
                            $questionData['p_question_parent_level'] == $question->question_parent_level;

            if ($isSameText && $isSameLabel && $isSameType && $isSameParent) {
                $messages[] = [
                    'p_id' => $questionData['p_id'],
                    'message' => 'No changes detected, question remains the same.'
                ];
                continue; // Skip this question and move to the next one
            }

            // Call the stored procedure for updating each question one by one
            // DB::statement(
            //     'CALL sp_manage_questions(?, ?, ?, ?, ?, ?, ?, ?, @message)',
            //     [
            //         $questionData['action_type'],
            //         $questionData['p_id'],
            //         $questionData['p_question_text'],
            //         $questionData['p_question_label'] ?? null,
            //         $questionData['p_question_type'],
            //         $questionData['p_client_id'],
            //         $p_question_level, // Keep the original level, do not change
            //         $questionData['p_question_parent_level'] ?? null,
            //     ]
            // );

            DB::statement(
                'CALL sp_manage_questions(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @message)',
                [
                    $questionData['action_type'],
                    $questionData['p_id'],
                    $questionData['p_question_text'],
                    $questionData['p_question_label'] ?? null,
                    $questionData['p_question_type'],
                    $questionData['p_client_id'],
                    $p_question_level,
                    $questionData['p_question_parent_level'] ?? null,
                    $questionData['p_page_size'] ?? null,
                    $questionData['p_page'] ?? null,
                ]
            );
            


            // Fetch the OUT parameter value
            $result = DB::select('SELECT @message AS message');
            $messages[] = [
                'p_id' => $questionData['p_id'],
                'message' => $result[0]->message
            ];
        }

        return response()->json(['messages' => $messages], 200);

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
        // DB::statement(
        //     'CALL sp_manage_questions(?, ?, ?, ?, ?, ?, ?, ?, @message)',
        //     [
        //         $validatedData['action_type'],
        //         $validatedData['p_id'],
        //         $validatedData['p_question_text'],
        //         $validatedData['p_question_label'] ?? null,
        //         $validatedData['p_question_type'],
        //         $validatedData['p_client_id'],
        //         $validatedData['p_question_level'] ?? null,
        //         $validatedData['p_question_parent_level'] ?? null,
        //     ]
        // );

        DB::statement(
            'CALL sp_manage_questions(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @message)',
            [
                $validatedData['action_type'],
                $validatedData['p_id'],
                $validatedData['p_question_text'],
                $validatedData['p_question_label'] ?? null,
                $validatedData['p_question_type'],
                $validatedData['p_client_id'],
                $validatedData['p_question_level'] ?? null,
                $validatedData['p_question_parent_level'] ?? null,
                $validatedData['p_page_size'] ?? null,
                $validatedData['p_page'] ?? null,
            ]
        );
        
        // Fetch the OUT parameter value
        $result = DB::select('SELECT @message AS message');
        $message = $result[0]->message;

        // return response()->json(['message' => $message], 200);
        return response()->json([
            'status' => 'success',
            'message' => 'Delete successful!'
        ], 200);


    } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 500);
    }
}


    /**
     * ✅ POST API: Check if client_id exists in the database
     */
    public function checkClientExists(Request $request)
    {
        $clientId = $request->input('client_id');

        $exists = DB::table('clients')->where('id', $clientId)->exists();

        if ($exists) {
            return response()->json(['message' => 'Client exists'], 200);
        } else {
            return response()->json(['message' => 'Client does not exist'], 404);
        }
    }

    /**
     * ✅ GET API: Get all questions by client_id
     */
    public function getQuestionsByClient(Request $request)
    {
        $clientId = $request->query('client_id');

        $questions = Question::where('client_id', $clientId)->get();

        if ($questions->isEmpty()) {
            return response()->json(['message' => 'No questions found for this client_id'], 404);
        }

        return response()->json(['questions' => $questions], 200);
    }

    public function getChildQuestions(Request $request)
    {
        // Validate input parameters
        $request->validate([
            'question_level' => 'required|integer',
            'client_id' => 'required|integer',
        ]);

        $questionLevel = $request->input('question_level');
        $clientId = $request->input('client_id');

        try {
            // Set the output variables before calling the procedure
            DB::statement("SET @p_id = 0;");
            DB::statement("SET @p_question_level = ?;", [$questionLevel]);
            DB::statement("SET @message = '';");

            // Call the stored procedure with correct parameters
            $questions = DB::select("CALL sp_manage_questions(?, @p_id, '', '', NULL, ?, @p_question_level, NULL, @message)", [
                'C',   // action_type
                $clientId
            ]);

            // Fetch the output parameters
            $messageResult = DB::select("SELECT @message AS message;");
            $message = $messageResult[0]->message ?? 'No message returned';

            // Handle the case where no data is returned
            if (empty($questions)) {
                return response()->json([
                    'message' => 'No questions found for the given criteria.',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'message' => $message ?: 'Questions fetched successfully.',
                'data' => $questions,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Database error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

}
