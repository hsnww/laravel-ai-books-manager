# تثبيت ملفات اللغة الأوروبية لـ Tesseract OCR

## الملفات المطلوبة

لتشغيل السكربت مع دعم اللغات الأوروبية، تحتاج إلى تثبيت ملفات اللغة التالية:

### اللغات المدعومة حالياً:
- ✅ `eng.traineddata` - الإنجليزية (مثبت افتراضياً)
- ✅ `fra.traineddata` - الفرنسية (مثبت افتراضياً)
- ✅ `ara.traineddata` - العربية (مثبت افتراضياً)

### اللغات الجديدة المطلوبة:
- 📥 `por.traineddata` - البرتغالية
- 📥 `deu.traineddata` - الألمانية
- 📥 `ita.traineddata` - الإيطالية
- 📥 `spa.traineddata` - الإسبانية

## خطوات التثبيت

### 1. تحميل ملفات اللغة

#### للويندوز:
```bash
# إنشاء مجلد tessdata إذا لم يكن موجوداً
mkdir "C:\Program Files\Tesseract-OCR\tessdata"

# تحميل ملفات اللغة
curl -o "C:\Program Files\Tesseract-OCR\tessdata\por.traineddata" https://github.com/tesseract-ocr/tessdata/raw/main/por.traineddata
curl -o "C:\Program Files\Tesseract-OCR\tessdata\deu.traineddata" https://github.com/tesseract-ocr/tessdata/raw/main/deu.traineddata
curl -o "C:\Program Files\Tesseract-OCR\tessdata\ita.traineddata" https://github.com/tesseract-ocr/tessdata/raw/main/ita.traineddata
curl -o "C:\Program Files\Tesseract-OCR\tessdata\spa.traineddata" https://github.com/tesseract-ocr/tessdata/raw/main/spa.traineddata
```

#### للينكس/ماك:
```bash
# تحميل ملفات اللغة
wget -O /usr/share/tesseract-ocr/tessdata/por.traineddata https://github.com/tesseract-ocr/tessdata/raw/main/por.traineddata
wget -O /usr/share/tesseract-ocr/tessdata/deu.traineddata https://github.com/tesseract-ocr/tessdata/raw/main/deu.traineddata
wget -O /usr/share/tesseract-ocr/tessdata/ita.traineddata https://github.com/tesseract-ocr/tessdata/raw/main/ita.traineddata
wget -O /usr/share/tesseract-ocr/tessdata/spa.traineddata https://github.com/tesseract-ocr/tessdata/raw/main/spa.traineddata
```

### 2. التحقق من التثبيت

```bash
# فحص ملفات اللغة المثبتة
tesseract --list-langs
```

يجب أن تظهر القائمة:
```
eng
fra
ara
por
deu
ita
spa
```

### 3. اختبار السكربت

```bash
# اختبار مع ملف PDF برتغالي
python scripts/simple_extract.py "path/to/portuguese.pdf"

# اختبار مع ملف PDF ألماني
python scripts/simple_extract.py "path/to/german.pdf"
```

## ملاحظات مهمة

1. **حجم الملفات**: كل ملف لغة يبلغ حجمه حوالي 30-50 ميجابايت
2. **الأداء**: كلما زاد عدد اللغات، زاد وقت المعالجة
3. **الدقة**: دقة OCR تختلف حسب جودة PDF واللغة
4. **الاحتياطي**: إذا لم تتوفر لغة معينة، سيستخدم الإنجليزية كاحتياطي

## استكشاف الأخطاء

### مشكلة: "Language file not found"
```bash
# التحقق من مسار tessdata
echo $TESSDATA_PREFIX
# أو
tesseract --tessdata-dir "C:\Program Files\Tesseract-OCR\tessdata" --list-langs
```

### مشكلة: "Permission denied"
```bash
# تشغيل كمدير (Windows)
# أو
sudo chmod 644 /usr/share/tesseract-ocr/tessdata/*.traineddata
``` 