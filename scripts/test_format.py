#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Text Format Testing Script
Tests and validates text formatting for different languages
"""

import sys
import os
import re
import argparse
from pathlib import Path

def detect_language(text):
    """
    Detect the primary language of the text
    
    Args:
        text (str): Text to analyze
    
    Returns:
        str: Detected language ('arabic', 'english', 'mixed', 'unknown')
    """
    if not text:
        return 'unknown'
    
    # Arabic character ranges
    arabic_pattern = r'[\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\uFB50-\uFDFF\uFE70-\uFEFF]'
    
    # English character pattern
    english_pattern = r'[a-zA-Z]'
    
    # Count characters
    arabic_count = len(re.findall(arabic_pattern, text))
    english_count = len(re.findall(english_pattern, text))
    
    total_chars = len(text.replace(' ', '').replace('\n', ''))
    
    if total_chars == 0:
        return 'unknown'
    
    arabic_ratio = arabic_count / total_chars
    english_ratio = english_count / total_chars
    
    if arabic_ratio > 0.3:
        return 'arabic'
    elif english_ratio > 0.3:
        return 'english'
    elif arabic_count > 0 and english_count > 0:
        return 'mixed'
    else:
        return 'unknown'

def test_text_formatting(text, language=None):
    """
    Test text formatting and provide suggestions
    
    Args:
        text (str): Text to test
        language (str): Expected language (optional)
    
    Returns:
        dict: Formatting analysis results
    """
    if not text:
        return {'error': 'Empty text provided'}
    
    # Detect language if not provided
    detected_lang = detect_language(text)
    if not language:
        language = detected_lang
    
    analysis = {
        'language': language,
        'detected_language': detected_lang,
        'text_length': len(text),
        'word_count': len(text.split()),
        'line_count': len(text.split('\n')),
        'issues': [],
        'suggestions': []
    }
    
    # Check for common formatting issues
    if re.search(r'\s{3,}', text):
        analysis['issues'].append('Multiple consecutive spaces found')
        analysis['suggestions'].append('Normalize spacing')
    
    if re.search(r'\n\s*\n\s*\n', text):
        analysis['issues'].append('Multiple consecutive line breaks found')
        analysis['suggestions'].append('Normalize line breaks')
    
    if language == 'arabic':
        # Arabic-specific checks
        if not re.search(r'[\u0600-\u06FF]', text):
            analysis['issues'].append('No Arabic characters detected in Arabic text')
        
        # Check for mixed direction text
        if re.search(r'[a-zA-Z]', text) and re.search(r'[\u0600-\u06FF]', text):
            analysis['suggestions'].append('Consider separating Arabic and English text')
    
    elif language == 'english':
        # English-specific checks
        if not re.search(r'[a-zA-Z]', text):
            analysis['issues'].append('No English characters detected in English text')
    
    # Check text structure
    if len(text) < 50:
        analysis['suggestions'].append('Text is very short - consider adding more content')
    
    if len(text.split('\n')) < 3:
        analysis['suggestions'].append('Text has few line breaks - consider improving structure')
    
    return analysis

def format_text(text, language=None):
    """
    Format text based on language requirements
    
    Args:
        text (str): Text to format
        language (str): Language for formatting
    
    Returns:
        str: Formatted text
    """
    if not text:
        return text
    
    # Basic cleaning
    formatted = re.sub(r'\s+', ' ', text)  # Normalize spaces
    formatted = re.sub(r'\n\s*\n', '\n\n', formatted)  # Normalize line breaks
    formatted = formatted.strip()
    
    if language == 'arabic':
        # Arabic-specific formatting
        formatted = re.sub(r'([\u0600-\u06FF])\s+([\u0600-\u06FF])', r'\1 \2', formatted)
        # Add proper spacing for Arabic punctuation
        formatted = re.sub(r'([\u0600-\u06FF])([،؛؟])', r'\1 \2', formatted)
    
    elif language == 'english':
        # English-specific formatting
        formatted = re.sub(r'([a-zA-Z])([.!?])', r'\1 \2', formatted)
        # Capitalize sentences
        sentences = re.split(r'([.!?]\s+)', formatted)
        formatted = ''.join(s.capitalize() if i % 2 == 0 else s for i, s in enumerate(sentences))
    
    return formatted

def main():
    parser = argparse.ArgumentParser(description='Test and format text files')
    parser.add_argument('input_file', help='Input text file to test')
    parser.add_argument('-l', '--language', help='Expected language (arabic/english)')
    parser.add_argument('-f', '--format', action='store_true', help='Format the text')
    parser.add_argument('-o', '--output', help='Output file for formatted text')
    
    args = parser.parse_args()
    
    # Check if input file exists
    if not os.path.exists(args.input_file):
        print(f"Error: Input file not found: {args.input_file}")
        sys.exit(1)
    
    # Read input file
    try:
        with open(args.input_file, 'r', encoding='utf-8') as file:
            text = file.read()
    except Exception as e:
        print(f"Error reading file: {e}")
        sys.exit(1)
    
    # Test formatting
    analysis = test_text_formatting(text, args.language)
    
    print("=== Text Format Analysis ===")
    print(f"Language: {analysis['language']}")
    print(f"Detected Language: {analysis['detected_language']}")
    print(f"Text Length: {analysis['text_length']} characters")
    print(f"Word Count: {analysis['word_count']}")
    print(f"Line Count: {analysis['line_count']}")
    
    if analysis['issues']:
        print("\nIssues Found:")
        for issue in analysis['issues']:
            print(f"  - {issue}")
    
    if analysis['suggestions']:
        print("\nSuggestions:")
        for suggestion in analysis['suggestions']:
            print(f"  - {suggestion}")
    
    # Format text if requested
    if args.format:
        formatted_text = format_text(text, analysis['language'])
        
        if args.output:
            try:
                with open(args.output, 'w', encoding='utf-8') as file:
                    file.write(formatted_text)
                print(f"\nFormatted text saved to: {args.output}")
            except Exception as e:
                print(f"Error saving formatted text: {e}")
        else:
            print("\n=== Formatted Text ===")
            print(formatted_text[:500] + "..." if len(formatted_text) > 500 else formatted_text)

if __name__ == "__main__":
    main() 