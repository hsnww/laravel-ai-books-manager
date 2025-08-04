#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Enhanced PDF Text Extractor with OCR support
Extracts text from PDF files with improved formatting and OCR for image-based text
"""

import sys
import os
import re
import json
import traceback

def clean_text(text):
    """
    Clean and format extracted text
    
    Args:
        text (str): Raw extracted text
    
    Returns:
        str: Cleaned and formatted text
    """
    if not text:
        return ""
    
    # Remove excessive whitespace
    text = re.sub(r'\s+', ' ', text)
    
    # Remove page markers
    text = re.sub(r'--- Page \d+ ---', '', text)
    
    # Fix common OCR issues
    text = re.sub(r'([a-zA-Z])\|([a-zA-Z])', r'\1l\2', text)  # Fix l vs |
    text = re.sub(r'([a-zA-Z])0([a-zA-Z])', r'\1o\2', text)   # Fix o vs 0
    
    # Clean up line breaks
    text = re.sub(r'\n\s*\n', '\n\n', text)
    
    # Remove leading/trailing whitespace
    text = text.strip()
    
    return text

def extract_text_with_ocr(pdf_path):
    """Extract text from PDF using OCR for image-based content"""
    try:
        # Import libraries inside function to handle import errors gracefully
        import pytesseract
        from PIL import Image
        import io
        import fitz  # PyMuPDF for better image extraction
        
        # Check if file exists
        if not os.path.exists(pdf_path):
            return {
                'success': False,
                'error': f'File not found: {pdf_path}',
                'text': '',
                'pages': 0
            }
        
        # Open PDF with PyMuPDF for better image extraction
        doc = fitz.open(pdf_path)
        text = ""
        pages = len(doc)
        
        # Extract text from each page
        for page_num in range(pages):
            page = doc[page_num]
            
            # First try to extract text directly
            page_text = page.get_text()
            
            # If no text found, try OCR on images
            if not page_text.strip():
                # Get images from the page
                image_list = page.get_images()
                
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
                        
                        pix = None  # Free memory
                        
                    except Exception as e:
                        continue
            else:
                text += page_text + "\n"
        
        doc.close()
        
        # Clean the extracted text
        cleaned_text = clean_text(text)
        
        return {
            'success': True,
            'text': cleaned_text,
            'pages': pages,
            'error': None
        }
            
    except ImportError as e:
        return {
            'success': False,
            'error': f'Missing required library: {str(e)}',
            'text': '',
            'pages': 0
        }
    except Exception as e:
        return {
            'success': False,
            'error': f'Extraction error: {str(e)}',
            'text': '',
            'pages': 0
        }

def main():
    try:
        if len(sys.argv) < 2:
            result = {
                'success': False,
                'error': 'PDF file path required',
                'text': '',
                'pages': 0
            }
        else:
            pdf_path = sys.argv[1]
            result = extract_text_with_ocr(pdf_path)
        
        # Always output JSON, even if there's an error
        print(json.dumps(result, ensure_ascii=False))
        
        # Exit with error code only if extraction failed
        if not result['success']:
            sys.exit(1)
            
    except Exception as e:
        # Fallback error handling
        error_result = {
            'success': False,
            'error': f'Script error: {str(e)}',
            'text': '',
            'pages': 0
        }
        print(json.dumps(error_result, ensure_ascii=False))
        sys.exit(1)

if __name__ == "__main__":
    main() 
            if not args.output:
                print("\nExtracted text preview:")
                print(text[:500] + "..." if len(text) > 500 else text)
        else:
            print("Failed to extract text from PDF")
        sys.exit(1)

if __name__ == "__main__":

    main() 