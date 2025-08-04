<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AiProcessorService;
use Illuminate\Support\Facades\Auth;

class AiTrialController extends Controller
{
    protected $aiProcessorService;

    public function __construct(AiProcessorService $aiProcessorService)
    {
        $this->aiProcessorService = $aiProcessorService;
    }

    /**
     * عرض صفحة تجربة الذكاء الاصطناعي
     */
    public function index()
    {
        return view('ai-trial.index');
    }

    /**
     * معالجة النص المقدم من المستخدم
     */
    public function process(Request $request)
    {
        // التحقق من صحة البيانات
        $request->validate([
            'text' => 'required|string|min:50|max:5000',
            'processing_type' => 'required|in:extract_info,summarize,translate,enhance,improve_format',
            'language' => 'required|string',
        ]);

        try {
            // الحصول على النص ونوع المعالجة واللغة
            $text = $request->input('text');
            $processingType = $request->input('processing_type');
            $language = $request->input('language');

            // معالجة النص باستخدام AiProcessorService للتجربة
            $result = $this->aiProcessorService->processTextForTrial($text, $processingType, $language);

            return response()->json([
                'success' => true,
                'result' => $result,
                'processing_type' => $processingType,
                'language' => $language,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء معالجة النص: ' . $e->getMessage(),
            ], 500);
        }
    }
} 