# -*- coding: utf-8 -*-
"""
Simple PDF Text Extractor with OCR support
Extracts text from PDF files using PyPDF2 and OCR for image-based text
"""

import sys
import os
import json
import traceback
import time

def extract_text_with_ocr(pdf_path):
    """Extract text from PDF using OCR for image-based content"""
    start_time = time.time()
    
    try:
        # Import libraries inside function to handle import errors gracefully
        import PyPDF2
        import fitz  # PyMuPDF for better image extraction
        
        # Check if tesseract is available
        tesseract_available = False
        try:
            import pytesseract
            from PIL import Image
            import io
            
            # Set tesseract path for Windows
            tesseract_path = r"C:\Program Files\Tesseract-OCR\tesseract.exe"
            if os.path.exists(tesseract_path):
                pytesseract.pytesseract.tesseract_cmd = tesseract_path
            
            # Test if tesseract is actually available
            pytesseract.get_tesseract_version()
            tesseract_available = True
        except (ImportError, Exception) as e:
            tesseract_available = False
        
        # Check if file exists
        if not os.path.exists(pdf_path):
            return {
                'success': False,
                'error': f'File not found: {pdf_path}',
                'text': '',
                'pages_processed': 0,
                'language': 'unknown',
                'extraction_time': 0,
                'ocr_used': False
            }
        
        # Open PDF with PyMuPDF for better image extraction
        doc = fitz.open(pdf_path)
        text = ""
        pages = len(doc)
        ocr_used = False
        
        # Extract text from each page
        for page_num in range(pages):
            page = doc[page_num]
            
            # First try to extract text directly
            page_text = page.get_text()
            
            # If no text found or very short text, try OCR on images (only if tesseract is available)
            if (not page_text.strip() or len(page_text.strip()) < 50) and tesseract_available:
                # Get images from the page
                image_list = page.get_images()
                
                if image_list:  # If there are images, try OCR
                    for img_index, img in enumerate(image_list):
                        try:
                            # Get image data
                            xref = img[0]
                            pix = fitz.Pixmap(doc, xref)
                            
                            # Convert to PIL Image
                            img_data = pix.tobytes("png")
                            pil_image = Image.open(io.BytesIO(img_data))
                            
                            # Extract text using OCR
                            ocr_text = pytesseract.image_to_string(pil_image, lang='ara+eng')
                            
                            if ocr_text.strip():
                                text += ocr_text + "\n"
                                ocr_used = True
                            
                            pix = None  # Free memory
                        except Exception as e:
                            continue
                else:
                    # No images found, use direct text even if short
                    if page_text.strip():
                        text += page_text + "\n"
            else:
                # Use direct text if it's substantial or OCR not available
                text += page_text + "\n"
        
        doc.close()
        
        # If still no text and tesseract is available, try converting page to image and use OCR
        if not text.strip() and tesseract_available:
            doc = fitz.open(pdf_path)
            for page_num in range(pages):
                page = doc[page_num]
                
                # Convert page to image
                mat = fitz.Matrix(2, 2)  # 2x zoom for better OCR
                pix = page.get_pixmap(matrix=mat)
                img_data = pix.tobytes("png")
                pil_image = Image.open(io.BytesIO(img_data))
                
                # Extract text using OCR
                ocr_text = pytesseract.image_to_string(pil_image, lang='ara+eng')
                
                if ocr_text.strip():
                    text += ocr_text + "\n"
                    ocr_used = True
                
                pix = None  # Free memory
            
            doc.close()
        
        # Clean text from problematic characters and encode properly
        try:
            # Remove problematic characters that cause encoding issues
            cleaned_text = ""
            for char in text:
                try:
                    # Try to encode and decode the character
                    char.encode('utf-8')
                    cleaned_text += char
                except UnicodeEncodeError:
                    # Skip problematic characters
                    continue
            
            text = cleaned_text
        except:
            # If that fails, try a more aggressive cleaning
            text = ''.join(char for char in text if ord(char) < 65536 and char.isprintable())
        
        # Detect language
        arabic_chars = sum(1 for c in text if '\u0600' <= c <= '\u06FF')
        english_chars = sum(1 for c in text if c.isalpha() and ord(c) < 128)
        
        if arabic_chars > english_chars:
            language = 'ar'
        elif english_chars > arabic_chars:
            language = 'en'
        else:
            language = 'mixed'
        
        extraction_time = time.time() - start_time
        
        return {
            'success': True,
            'text': text.strip(),
            'pages_processed': pages,
            'language': language,
            'extraction_time': round(extraction_time, 2),
            'ocr_used': ocr_used,
            'error': None
        }
            
    except ImportError as e:
        return {
            'success': False,
            'error': f'Missing required library: {str(e)}',
            'text': '',
            'pages_processed': 0,
            'language': 'unknown',
            'extraction_time': 0,
            'ocr_used': False
        }
    except Exception as e:
        return {
            'success': False,
            'error': f'Extraction error: {str(e)}',
            'text': '',
            'pages_processed': 0,
            'language': 'unknown',
            'extraction_time': 0,
            'ocr_used': False
        }

def main():
    try:
        if len(sys.argv) < 2:
            result = {
                'success': False,
                'error': 'PDF file path required',
                'text': '',
                'pages_processed': 0,
                'language': 'unknown',
                'extraction_time': 0,
                'ocr_used': False
            }
        else:
            pdf_path = sys.argv[1]
            result = extract_text_with_ocr(pdf_path)
        
        # Always output JSON, even if there's an error
        # Use sys.stdout.buffer for binary output
        json_output = json.dumps(result, ensure_ascii=False)
        sys.stdout.buffer.write(json_output.encode('utf-8'))
        sys.stdout.buffer.write(b'\n')
        sys.stdout.buffer.flush()
        
        # Exit with error code only if extraction failed
        if not result['success']:
            sys.exit(1)
    
    except Exception as e:
        # Fallback error handling
        error_result = {
            'success': False,
            'error': f'Script error: {str(e)}',
            'text': '',
            'pages_processed': 0,
            'language': 'unknown',
            'extraction_time': 0,
            'ocr_used': False
        }
        json_output = json.dumps(error_result, ensure_ascii=False)
        sys.stdout.buffer.write(json_output.encode('utf-8'))
        sys.stdout.buffer.write(b'\n')
        sys.stdout.buffer.flush()
        sys.exit(1)

if __name__ == "__main__":
    main() 