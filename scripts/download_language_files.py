#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
سكربت تحميل ملفات اللغة الأوروبية لـ Tesseract OCR
European Language Files Downloader for Tesseract OCR
"""

import os
import sys
import requests
import platform
from pathlib import Path

# قائمة ملفات اللغة المطلوبة
LANGUAGE_FILES = {
    'por': 'Portuguese',
    'deu': 'German', 
    'ita': 'Italian',
    'spa': 'Spanish'
}

# روابط التحميل
TESSDATA_BASE_URL = "https://github.com/tesseract-ocr/tessdata/raw/main"

def get_tessdata_path():
    """تحديد مسار مجلد tessdata حسب نظام التشغيل"""
    system = platform.system()
    
    if system == "Windows":
        # مسارات محتملة للويندوز
        possible_paths = [
            r"C:\Program Files\Tesseract-OCR\tessdata",
            r"C:\Program Files (x86)\Tesseract-OCR\tessdata",
            r"C:\tesseract\tessdata"
        ]
    elif system == "Darwin":  # macOS
        possible_paths = [
            "/usr/local/share/tessdata",
            "/usr/share/tessdata"
        ]
    else:  # Linux
        possible_paths = [
            "/usr/share/tesseract-ocr/tessdata",
            "/usr/local/share/tessdata",
            "/usr/share/tessdata"
        ]
    
    # البحث عن المسار الصحيح
    for path in possible_paths:
        if os.path.exists(path):
            return path
    
    # إذا لم يتم العثور على مسار، استخدام المسار الافتراضي للويندوز
    if system == "Windows":
        default_path = r"C:\Program Files\Tesseract-OCR\tessdata"
        print(f"لم يتم العثور على مجلد tessdata، سيتم استخدام: {default_path}")
        return default_path
    else:
        print("لم يتم العثور على مجلد tessdata")
        return None

def download_file(url, filepath):
    """تحميل ملف من URL"""
    try:
        print(f"تحميل {os.path.basename(filepath)}...")
        
        response = requests.get(url, stream=True)
        response.raise_for_status()
        
        # إنشاء المجلد إذا لم يكن موجوداً
        os.makedirs(os.path.dirname(filepath), exist_ok=True)
        
        # تحميل الملف
        with open(filepath, 'wb') as f:
            for chunk in response.iter_content(chunk_size=8192):
                f.write(chunk)
        
        file_size = os.path.getsize(filepath) / (1024 * 1024)  # MB
        print(f"تم تحميل {os.path.basename(filepath)} ({file_size:.1f} MB)")
        return True
        
    except Exception as e:
        print(f"❌ خطأ في تحميل {os.path.basename(filepath)}: {e}")
        return False

def check_existing_files(tessdata_path):
    """فحص الملفات الموجودة مسبقاً"""
    existing_files = []
    missing_files = []
    
    for lang_code, lang_name in LANGUAGE_FILES.items():
        filepath = os.path.join(tessdata_path, f"{lang_code}.traineddata")
        if os.path.exists(filepath):
            file_size = os.path.getsize(filepath) / (1024 * 1024)  # MB
            existing_files.append(f"✅ {lang_name} ({lang_code}) - {file_size:.1f} MB")
        else:
            missing_files.append(f"❌ {lang_name} ({lang_code})")
    
    return existing_files, missing_files

def main():
    """الدالة الرئيسية"""
    print("تحميل ملفات اللغة الاوروبية لـ Tesseract OCR")
    print("=" * 50)
    
    # تحديد مسار tessdata
    tessdata_path = get_tessdata_path()
    if not tessdata_path:
        print("لم يتم العثور على مجلد tessdata")
        print("يرجى تثبيت Tesseract OCR اولاً")
        return
    
    print(f"📁 مسار tessdata: {tessdata_path}")
    print()
    
    # فحص الملفات الموجودة
    existing_files, missing_files = check_existing_files(tessdata_path)
    
    if existing_files:
        print("📋 الملفات الموجودة مسبقاً:")
        for file_info in existing_files:
            print(f"  {file_info}")
        print()
    
    if missing_files:
        print("📋 الملفات المطلوبة:")
        for file_info in missing_files:
            print(f"  {file_info}")
        print()
        
        # سؤال المستخدم
        response = input("هل تريد تحميل الملفات المفقودة؟ (y/n): ").lower().strip()
        if response not in ['y', 'yes', 'نعم', 'y']:
            print("تم إلغاء التحميل")
            return
        
        # تحميل الملفات المفقودة
        success_count = 0
        total_count = len(missing_files)
        
        for lang_code, lang_name in LANGUAGE_FILES.items():
            filepath = os.path.join(tessdata_path, f"{lang_code}.traineddata")
            
            if not os.path.exists(filepath):
                url = f"{TESSDATA_BASE_URL}/{lang_code}.traineddata"
                if download_file(url, filepath):
                    success_count += 1
        
        print()
        print(f"📊 ملخص التحميل: {success_count}/{total_count} ملفات تم تحميلها بنجاح")
        
        if success_count == total_count:
            print("🎉 تم تحميل جميع ملفات اللغة بنجاح!")
        else:
            print("⚠️  بعض الملفات لم يتم تحميلها، يرجى المحاولة مرة أخرى")
    else:
        print("✅ جميع ملفات اللغة موجودة بالفعل!")
    
    print()
    print("🔍 للتحقق من التثبيت، قم بتشغيل:")
    print("tesseract --list-langs")

if __name__ == "__main__":
    main() 