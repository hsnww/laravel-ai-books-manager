# استخراج النصوص من PDF

## المتطلبات

### 1. Python 3.8+
```bash
python --version
```

### 2. تثبيت المكتبات
```bash
pip install -r requirements.txt
```

### 3. تثبيت Tesseract OCR

#### على Windows:
1. تحميل Tesseract من: https://github.com/UB-Mannheim/tesseract/wiki
2. تثبيت مع دعم اللغة العربية
3. إضافة إلى PATH

#### على Ubuntu/Debian:
```bash
sudo apt update
sudo apt install tesseract-ocr
sudo apt install tesseract-ocr-ara  # دعم العربية
```

#### على macOS:
```bash
brew install tesseract
brew install tesseract-lang  # دعم اللغات
```

### 4. تحميل بيانات اللغات لـ Tesseract
```bash
# تحميل ملفات اللغات المطلوبة
languages=("ara" "eng" "fra" "spa" "deu" "rus" "jpn" "chi_sim" "kor" "ita" "por" "nld" "swe" "tur" "hin")

for lang in "${languages[@]}"; do
    wget "https://github.com/tesseract-ocr/tessdata/raw/main/${lang}.traineddata"
    sudo mv "${lang}.traineddata" /usr/share/tesseract-ocr/tessdata/
done
```

## الاستخدام

### اختبار السكريبت
```bash
python extract_pdf_text.py "path/to/your/file.pdf"
```

### المخرجات
```json
{
    "success": true,
    "text": "النص المستخرج...",
    "pages_count": 10,
    "language": "ar",
    "extraction_time": 2.5,
    "ocr_used": false
}
```

## المميزات

### ✅ استخراج النصوص العادية
- دعم 15 لغة مختلفة (العربية، الإنجليزية، الفرنسية، الإسبانية، الألمانية، الروسية، اليابانية، الصينية، الكورية، الإيطالية، البرتغالية، الهولندية، السويدية، التركية، الهندية)
- معالجة النصوص من اليمين لليسار للعربية
- تنظيف النصوص تلقائياً

### ✅ OCR للنصوص المصورة
- دعم Tesseract لجميع اللغات الـ 15
- تحسين الصور تلقائياً
- استخدام كحل بديل عند فشل الاستخراج العادي

### ✅ اكتشاف اللغة
- اكتشاف تلقائي للغة النص
- دعم جميع اللغات الـ 15

### ✅ معالجة الأخطاء
- تسجيل الأخطاء مفصلاً
- محاولة OCR كحل بديل
- استمرار المعالجة حتى مع فشل بعض الصفحات

## استكشاف الأخطاء

### مشكلة: Tesseract غير موجود
```bash
# التحقق من التثبيت
tesseract --version

# إعادة تثبيت إذا لزم الأمر
sudo apt install tesseract-ocr tesseract-ocr-ara
```

### مشكلة: مكتبات Python مفقودة
```bash
# إعادة تثبيت المكتبات
pip install --upgrade -r requirements.txt
```

### مشكلة: دعم اللغة العربية
```bash
# التحقق من دعم العربية
tesseract --list-langs

# يجب أن يظهر 'ara' في القائمة
```

## ملاحظات مهمة

1. **جودة OCR**: تعتمد على جودة الصورة الأصلية
2. **السرعة**: OCR أبطأ من استخراج النصوص العادية
3. **الذاكرة**: قد يحتاج ملفات كبيرة لمساحة ذاكرة إضافية
4. **الدقة**: قد تحتاج لمراجعة النصوص المستخرجة بالـ OCR 